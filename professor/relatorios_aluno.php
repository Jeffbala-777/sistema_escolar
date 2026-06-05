<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';

$professorId = $_SESSION['usuario']['id'];
$escolaId = $_SESSION['usuario']['escola_id'];
$alunoId = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : 0;
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;

$relatorioModel = new RelatorioAlunoModel($pdo);
$usuarioModel = new UsuarioModel($pdo);
$turmaModel = new TurmaModel($pdo);

$aluno = $usuarioModel->buscarPorId($alunoId);
$turma = $turmaModel->buscarPorId($turmaId, $escolaId);

if (!$aluno || !$turma || (int)$aluno['escola_id'] !== $escolaId) {
    header('Location: selecionar_turma.php');
    exit;
}

// Lógica de Adicionar Relatório
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_relatorio'])) {
    $conteudo = trim($_POST['conteudo'] ?? '');
    if (!empty($conteudo)) {
        $relatorioModel->adicionar([
            'escola_id' => $escolaId,
            'aluno_id' => $alunoId,
            'professor_id' => $professorId,
            'turma_id' => $turmaId,
            'conteudo' => $conteudo,
            'tipo' => 'professor'
        ]);
        header("Location: relatorios_aluno.php?aluno_id=$alunoId&turma_id=$turmaId&sucesso=1");
        exit;
    }
}

// Lógica de Excluir Relatório
if (isset($_GET['excluir_id'])) {
    $excluirId = (int)$_GET['excluir_id'];
    $relatorioModel->excluir($excluirId, $professorId);
    header("Location: relatorios_aluno.php?aluno_id=$alunoId&turma_id=$turmaId&excluido=1");
    exit;
}

// Buscar faltas do aluno por mês
$sqlFaltas = "
    SELECT 
        DATE_FORMAT(a.data_aula, '%m/%Y') as mes,
        COUNT(p.id) as total_faltas
    FROM presencas p
    INNER JOIN aulas a ON a.id = p.aula_id
    WHERE p.aluno_id = :aluno_id
    AND p.status = 'falta'
    AND p.escola_id = :escola_id
    GROUP BY DATE_FORMAT(a.data_aula, '%m/%Y')
    ORDER BY a.data_aula DESC
";

$stmtFaltas = $pdo->prepare($sqlFaltas);
$stmtFaltas->execute([':aluno_id' => $alunoId, ':escola_id' => $escolaId]);
$faltasPorMes = $stmtFaltas->fetchAll();

// Total de faltas
$sqlTotalFaltas = "
    SELECT COUNT(p.id) as total
    FROM presencas p
    WHERE p.aluno_id = :aluno_id
    AND p.status = 'falta'
    AND p.escola_id = :escola_id
";

$stmtTotal = $pdo->prepare($sqlTotalFaltas);
$stmtTotal->execute([':aluno_id' => $alunoId, ':escola_id' => $escolaId]);
$totalFaltas = $stmtTotal->fetch()['total'] ?? 0;

$relatorios = $relatorioModel->listarPorAluno($alunoId, $turmaId);

$title = 'Relatórios de ' . $aluno['nome_completo'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            
            <div class="page-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="dashboard-title mb-1" style="font-size: 24px;">Relatórios do Aluno</div>
                        <div class="dashboard-subtitle mb-0">
                            <strong><?= e($aluno['nome_completo']) ?></strong> | 
                            Turma: <strong><?= e($turma['nome']) ?></strong>
                        </div>
                    </div>
                    <a href="relatorios_turma.php?turma_id=<?= $turmaId ?>" class="btn btn-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <!-- Coluna Esquerda: Relatórios -->
                <div class="col-lg-8">
                    <!-- Formulário para Adicionar -->
                    <div class="dashboard-card mb-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle me-2 text-primary"></i>Novo Registro</h6>
                        <form method="POST">
                            <div class="mb-3">
                                <textarea name="conteudo" class="form-control" rows="4" placeholder="Descreva o desempenho ou ocorrência do aluno..." required style="resize: none;"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="adicionar_relatorio" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-check2-circle me-2"></i> Adicionar Registro
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Listagem de Relatórios -->
                    <div class="relatorios-list">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-2 text-primary"></i> Histórico Recente</h6>
                        
                        <?php if (!empty($relatorios)): ?>
                            <?php foreach ($relatorios as $rel): ?>
                            <div class="dashboard-card mb-3" style="border-left: 4px solid #0E79EB;">
                                <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                                    <div class="small text-muted">
                                        <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y H:i', strtotime($rel['criado_em'])) ?>
                                        <span class="mx-2">|</span>
                                        <i class="bi bi-person-badge me-1"></i> Prof. <?= e($rel['professor_nome']) ?>
                                    </div>
                                    <?php if ((int)$rel['professor_id'] === $professorId): ?>
                                        <a href="?aluno_id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&excluir_id=<?= $rel['id'] ?>" 
                                           class="btn btn-sm text-danger p-0" 
                                           onclick="return confirm('Deseja excluir este registro?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="text-dark" style="line-height: 1.6; font-size: 14px;">
                                    <?= nl2br(e($rel['conteudo'])) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="page-card text-center py-5">
                                <i class="bi bi-journal-x text-muted opacity-25 d-block mb-3" style="font-size: 48px;"></i>
                                <p class="text-muted mb-0">Nenhum registro encontrado para este aluno.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Coluna Direita: Faltas -->
                <div class="col-lg-4">
                    <div class="dashboard-card sticky-top" style="top: 110px;">
                        <h6 class="fw-bold text-dark mb-4"><i class="bi bi-calendar-check text-danger me-2"></i> Controle de Faltas</h6>
                        
                        <div class="text-center mb-4 p-3 rounded-3" style="background: #fff5f5; border: 1px solid #fed7d7;">
                            <div class="small text-muted text-uppercase fw-bold mb-1" style="font-size: 10px;">Total de Faltas</div>
                            <h2 class="fw-bold text-danger mb-0"><?= $totalFaltas ?></h2>
                        </div>

                        <h6 class="fw-bold text-dark mb-3 small">Faltas por Mês</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" style="font-size: 13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mês</th>
                                        <th class="text-center">Faltas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($faltasPorMes)): ?>
                                        <?php foreach ($faltasPorMes as $falta): ?>
                                        <tr>
                                            <td><?= e($falta['mes']) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-danger rounded-pill">
                                                    <?= $falta['total_faltas'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3 italic">Sem faltas registradas</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
