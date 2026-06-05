<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';

$professorId = $_SESSION['usuario']['id'];
$escolaId = $_SESSION['usuario']['escola_id'];
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;

$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);
$relatorioModel = new RelatorioAlunoModel($pdo);
$turmaModel = new TurmaModel($pdo);

// Valida acesso à turma e pega a disciplina vinculada
$turmasProfessor = $ptdModel->listarTurmasProfessor($professorId, $escolaId);
$turmaAtual = null;
foreach ($turmasProfessor as $tp) {
    if ((int)$tp['turma_id'] === $turmaId) {
        $turmaAtual = $tp;
        break;
    }
}

if (!$turmaAtual) {
    header('Location: selecionar_turma.php');
    exit;
}

// Listar alunos da turma
$sqlAlunos = "SELECT u.id, u.nome_completo 
              FROM usuarios u 
              JOIN matriculas m ON m.aluno_id = u.id 
              WHERE m.turma_id = :tid AND u.ativo = 1 
              ORDER BY u.nome_completo ASC";
$stmtAlunos = $pdo->prepare($sqlAlunos);
$stmtAlunos->execute([':tid' => $turmaId]);
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

$title = 'Relatórios da Turma - ' . $turmaAtual['turma'];
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
                        <div class="dashboard-title mb-1" style="font-size: 24px;">Relatórios de Alunos</div>
                        <div class="dashboard-subtitle mb-0">
                            Turma: <strong><?= e($turmaAtual['turma']) ?></strong> | 
                            Disciplina: <strong><?= e($turmaAtual['disciplina']) ?></strong>
                        </div>
                    </div>
                    <a href="selecionar_turma.php" class="btn btn-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <?php if (!empty($alunos)): ?>
                    <?php foreach ($alunos as $aluno): 
                        $ultimoRelatorio = $relatorioModel->buscarUltimoPorAluno($aluno['id'], $turmaId);
                        $primeiraLetra = mb_strtoupper(mb_substr($aluno['nome_completo'], 0, 1));
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="dashboard-card h-100 d-flex flex-column">
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="topbar-avatar me-3" style="background: #0E79EB; width: 45px; height: 45px; font-size: 1.1rem; border: none;">
                                    <?= $primeiraLetra ?>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 15px;"><?= e($aluno['nome_completo']) ?></h6>
                                </div>
                            </div>
                            
                            <div class="mb-3 flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase;">Último Registro</small>
                                    <?php if ($ultimoRelatorio): ?>
                                        <small class="text-muted" style="font-size: 11px;">
                                            <?= date('d/m/Y', strtotime($ultimoRelatorio['criado_em'])) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="p-3 rounded-3" style="background: #f8f9fa; border: 1px solid #e9ecef; font-size: 13px; color: #495057; min-height: 80px; line-height: 1.5;">
                                    <?php if ($ultimoRelatorio): ?>
                                        <?= nl2br(e(mb_strimwidth($ultimoRelatorio['conteudo'], 0, 140, "..."))) ?>
                                    <?php else: ?>
                                        <div class="text-center py-3 text-muted opacity-50 italic">Nenhum relatório cadastrado.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row g-2 mt-auto">
                                <div class="col-6">
                                    <a href="relatorios_aluno.php?aluno_id=<?= $aluno['id'] ?>&turma_id=<?= $turmaId ?>" 
                                       class="btn btn-primary w-100 btn-sm d-flex align-items-center justify-content-center gap-2" style="min-height: 38px;">
                                        <i class="bi bi-plus-lg"></i> Adicionar
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="historico_relatorios.php?aluno_id=<?= $aluno['id'] ?>&turma_id=<?= $turmaId ?>" 
                                       class="btn btn-outline-secondary w-100 btn-sm d-flex align-items-center justify-content-center gap-2" style="min-height: 38px; border-radius: 12px; font-weight: 600;">
                                        <i class="bi bi-eye"></i> Histórico
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="page-card p-5">
                            <i class="bi bi-people text-muted opacity-25 d-block mb-3" style="font-size: 48px;"></i>
                            <h5 class="text-secondary fw-bold">Nenhum aluno encontrado</h5>
                            <p class="text-muted mb-0">Não existem alunos matriculados nesta turma.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
