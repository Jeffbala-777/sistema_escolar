<?php
// Ativa tipagem estrita para segurança
declare(strict_types=1);

// Middleware de autenticação do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
// Conexão com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model de Turmas
require_once __DIR__ . '/../app/models/TurmaModel.php';

// Inicializa models
$turmaModel = new TurmaModel($pdo);

// Dados básicos
$escolaId = (int)$_SESSION['usuario']['escola_id'];
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
$periodoId = isset($_POST['periodo_id']) ? (int)$_POST['periodo_id'] : 0;

// Valida se a turma foi selecionada, caso contrário volta para a seleção
if ($turmaId <= 0) {
    header('Location: selecionar_turma.php');
    exit;
}

// Busca dados da turma para o título
$turmaAtual = null;
$todasTurmas = $turmaModel->listarPorEscola($escolaId);
foreach ($todasTurmas as $t) {
    if ((int)$t['id'] === $turmaId) {
        $turmaAtual = $t;
        break;
    }
}

// Busca tipo de período da escola
$stmtEscola = $pdo->prepare("SELECT tipo_periodo FROM escolas WHERE id = :id LIMIT 1");
$stmtEscola->execute([':id' => $escolaId]);
$escolaInfo = $stmtEscola->fetch(PDO::FETCH_ASSOC);
$tipoPeriodo = $escolaInfo['tipo_periodo'] ?? 'bimestral';

// Busca períodos da escola
$stmtPeriodos = $pdo->prepare("SELECT pl.id, pl.nome, pl.data_inicio, pl.data_fim FROM periodos_letivos pl 
                                INNER JOIN anos_letivos al ON al.id = pl.ano_letivo_id 
                                WHERE al.escola_id = :eid AND al.ativo = 1 
                                ORDER BY pl.ordem ASC");
$stmtPeriodos->execute([':eid' => $escolaId]);
$periodosDisponiveis = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);

// Coleta dados reais da turma selecionada
$periodoInfo = null;
if ($periodoId > 0) {
    foreach ($periodosDisponiveis as $p) {
        if ((int)$p['id'] === $periodoId) {
            $periodoInfo = $p;
            break;
        }
    }
}

$dadosEstatisticos = [
    'desempenho_disciplinas' => [],
    'alunos_criticos_faltas' => []
];

// Consulta de médias por disciplina filtrada por período
$sqlD = "SELECT d.id, d.nome as disciplina, AVG(n.nota) as media 
         FROM disciplinas d 
         INNER JOIN professor_turma_disciplina ptd ON ptd.disciplina_id = d.id
         LEFT JOIN notas n ON n.disciplina_id = d.id AND n.escola_id = :eid " . ($periodoId > 0 ? " AND n.periodo_id = :pid" : "") . "
         WHERE ptd.turma_id = :tid AND ptd.ativo = 1
         GROUP BY d.id, d.nome";
$paramsD = [':eid' => $escolaId, ':tid' => $turmaId];
if ($periodoId > 0) $paramsD[':pid'] = $periodoId;
$stmtD = $pdo->prepare($sqlD);
$stmtD->execute($paramsD);
$dadosEstatisticos['desempenho_disciplinas'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);

// Busca alunos com mais faltas na turma
$sqlFaltas = "SELECT u.nome_completo, COUNT(p.id) as total_faltas
              FROM usuarios u
              INNER JOIN matriculas m ON m.aluno_id = u.id
              LEFT JOIN presencas p ON p.aluno_id = u.id AND p.status = 'falta'
              LEFT JOIN aulas a ON a.id = p.aula_id
              WHERE m.turma_id = :turma_id AND m.status = 'ativa'";
if ($periodoInfo && !empty($periodoInfo['data_inicio']) && !empty($periodoInfo['data_fim'])) {
    $sqlFaltas .= " AND a.data_aula BETWEEN :d1 AND :d2";
}
$sqlFaltas .= " GROUP BY u.id, u.nome_completo HAVING total_faltas > 0 ORDER BY total_faltas DESC LIMIT 5";

$stmtFaltas = $pdo->prepare($sqlFaltas);
$paramsFaltas = [':turma_id' => $turmaId];
if ($periodoInfo && !empty($periodoInfo['data_inicio']) && !empty($periodoInfo['data_fim'])) {
    $paramsFaltas[':d1'] = $periodoInfo['data_inicio'];
    $paramsFaltas[':d2'] = $periodoInfo['data_fim'];
}
$stmtFaltas->execute($paramsFaltas);
$alunosFaltosos = $stmtFaltas->fetchAll(PDO::FETCH_ASSOC);
$dadosEstatisticos['alunos_criticos_faltas'] = $alunosFaltosos;

// Título da página
$title = 'Análise de Desempenho - ' . ($turmaAtual['nome'] ?? 'Turma');
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php // Menu lateral do admin
    require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">
        <?php // Painel superior
        require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            <!-- Cabeçalho de Navegação -->
            <div class="mb-4">
                <a href="selecionar_turma.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar tela
                </a>
            </div>

            <!-- Título -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-secondary mb-1">Desempenho: <?= e($turmaAtual['nome']) ?></h4>
                        <p class="text-muted mb-0">Análise o desempenho da turma de cada disciplina</p>
                    </div>
                </div>
            </div>

            <!-- Filtro de Período -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px; background: #f8f9fa;">
                <form method="POST" class="d-flex align-items-center gap-3 flex-wrap">
                    <label class="fw-bold text-secondary mb-0">Selecione o <?= ucfirst($tipoPeriodo === 'bimestral' ? 'Bimestre' : ($tipoPeriodo === 'trimestral' ? 'Trimestre' : 'Semestre')) ?>:</label>
                    <select name="periodo_id" class="form-select" style="max-width: 300px; border-radius: 8px;" onchange="this.form.submit()">
                        <option value="0" <?= $periodoId === 0 ? 'selected' : '' ?>>Todos os Períodos</option>
                        <?php foreach ($periodosDisponiveis as $p): ?>
                            <option value="<?= (int)$p['id'] ?>" <?= $periodoId === (int)$p['id'] ? 'selected' : '' ?>>
                                <?= e($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <!-- Dados estatísticos da turma -->
            <?php if (!empty($dadosEstatisticos['desempenho_disciplinas'])): ?>
            <div class="row">
                <!-- Médias por Disciplina (Todas as matérias da turma) -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 15px;">
                        <h5 class="fw-bold text-secondary mb-4">
                            <i class="bi bi-book me-2"></i> Desempenho por Disciplina
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Disciplina</th>
                                        <th class="text-center">Média da Turma</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Define meta dinâmica baseada no período
                                    $meta = 6.0; // Padrão Bimestral
                                    if ($tipoPeriodo === 'trimestral' && $periodoId > 0) {
                                        // Busca ordem do período selecionado
                                        $stmtOrdem = $pdo->prepare("SELECT ordem FROM periodos_letivos WHERE id = :id LIMIT 1");
                                        $stmtOrdem->execute([':id' => $periodoId]);
                                        $ordemP = (int)$stmtOrdem->fetchColumn();
                                        
                                        if ($ordemP == 1) $meta = 18.0; // 60% de 30
                                        elseif ($ordemP == 2) $meta = 18.0; // 60% de 30
                                        elseif ($ordemP == 3) $meta = 24.0; // 60% de 40
                                    } elseif ($tipoPeriodo === 'trimestral' && $periodoId === 0) {
                                        $meta = 60.0; // 60% de 100
                                    }

                                    foreach ($dadosEstatisticos['desempenho_disciplinas'] as $disc): 
                                        $media = (float)($disc['media'] ?? 0);
                                        $cor = $media >= $meta ? 'success' : 'danger';
                                    ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <a href="detalhes_disciplina.php?turma_id=<?= $turmaId ?>&disciplina_id=<?= $disc['id'] ?>&periodo_id=<?= $periodoId ?>"
                                               class="text-decoration-none text-primary">
                                                <?= e($disc['disciplina']) ?>
                                                <i class="bi bi-arrow-right-short ms-1"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $cor ?> bg-opacity-10 text-<?= $cor ?> fs-6 px-3">
                                                <?= number_format($media, 1) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="bi bi-circle-fill text-<?= $cor ?> small me-1"></i>
                                            <?= $media >= $meta ? 'Dentro da Meta' : 'Abaixo da Meta' ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ranking de Faltas da Turma -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 15px;">
                        <h5 class="fw-bold text-secondary mb-4">
                            <i class="bi bi-person-x me-2"></i> Alunos com Mais Faltas
                        </h5>
                        <div class="list-group list-group-flush">
                            <?php if (!empty($dadosEstatisticos['alunos_criticos_faltas'])): ?>
                                <?php foreach ($dadosEstatisticos['alunos_criticos_faltas'] as $aluno): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= e($aluno['nome_completo']) ?></h6>
                                        <small class="text-muted">Faltas nesta turma</small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill px-3 py-2"><?= $aluno['total_faltas'] ?> faltas</span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">Nenhum registro de falta nesta turma.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle me-2"></i> Nenhum dado disponível para o período selecionado.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php // Footer padrão
require_once __DIR__ . '/../partials/footer.php'; ?>
