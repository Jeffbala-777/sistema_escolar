<?php
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

$alunoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;

if ($alunoId <= 0 || $turmaId <= 0) {
    header('Location: relatorios_turma.php');
    exit;
}

// Busca dados do aluno e turma para o cabeçalho
$stmtInfo = $pdo->prepare("SELECT u.nome_completo as aluno_nome, t.nome as turma_nome 
                           FROM usuarios u 
                           INNER JOIN matriculas m ON m.aluno_id = u.id
                           INNER JOIN turmas t ON t.id = m.turma_id
                           WHERE u.id = :aid AND t.id = :tid");
$stmtInfo->execute([':aid' => $alunoId, ':tid' => $turmaId]);
$info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    header('Location: relatorios_turma.php');
    exit;
}

// Dados da escola
$escolaId = (int)$_SESSION['usuario']['escola_id'];

// Filtros de visualização
$tipoFiltro = isset($_GET['tipo']) ? $_GET['tipo'] : 'professor'; // professor ou ia
$professorFiltro = isset($_GET['professor_id']) ? (int)$_GET['professor_id'] : 0;
$periodoFiltro = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0;

// Busca TODOS os professores da escola
$stmtProfessores = $pdo->prepare("SELECT u.id, u.nome_completo FROM usuarios u INNER JOIN perfis p ON p.id = u.perfil_id WHERE u.escola_id = :eid AND p.nome = 'professor' AND u.ativo = 1 ORDER BY u.nome_completo ASC");
$stmtProfessores->execute([':eid' => $escolaId]);
$professoresEscola = $stmtProfessores->fetchAll(PDO::FETCH_ASSOC);

// Busca períodos letivos ativos
$stmtPeriodos = $pdo->prepare("SELECT pl.id, pl.nome FROM periodos_letivos pl 
                               INNER JOIN anos_letivos al ON al.id = pl.ano_letivo_id 
                               WHERE al.escola_id = :eid AND al.ativo = 1 
                               ORDER BY pl.ordem ASC");
$stmtPeriodos->execute([':eid' => $escolaId]);
$periodosDisponiveis = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);

// Constrói a query de relatórios
$sql = "SELECT r.*, u.nome_completo as professor_nome 
        FROM relatorios_alunos r 
        INNER JOIN usuarios u ON u.id = r.professor_id 
        WHERE r.aluno_id = :aid AND r.turma_id = :tid AND r.tipo = :tipo";

$params = [':aid' => $alunoId, ':tid' => $turmaId, ':tipo' => $tipoFiltro];

if ($professorFiltro > 0) {
    $sql .= " AND r.professor_id = :pid";
    $params[':pid'] = $professorFiltro;
}

// Filtro de período para IA via LIKE
if ($tipoFiltro === 'ia' && $periodoFiltro > 0) {
    $periodoNome = null;
    foreach ($periodosDisponiveis as $per) {
        if ((int)$per['id'] === $periodoFiltro) {
            $periodoNome = $per['nome'];
            break;
        }
    }
    if ($periodoNome) {
        $sql .= " AND r.conteudo LIKE :filtro_periodo";
        $params[':filtro_periodo'] = "%[" . $periodoNome . "]%";
    }
}

$sql .= " ORDER BY r.criado_em DESC";
$stmtRel = $pdo->prepare($sql);
$stmtRel->execute($params);
$relatorios = $stmtRel->fetchAll(PDO::FETCH_ASSOC);

$title = 'Relatórios do Aluno';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            <!-- Cabeçalho -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-file-alt text-primary fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Relatórios: <?= htmlspecialchars($info['aluno_nome']) ?></h4>
                            <p class="text-muted mb-0"><i class="fas fa-users me-1"></i> Turma: <?= htmlspecialchars($info['turma_nome']) ?></p>
                        </div>
                    </div>
                    <a href="relatorios_turma.php?turma_id=<?= $turmaId ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Voltar
                    </a>
                </div>
            </div>

            <!-- Abas de Tipo de Relatório -->
            <div class="mb-4">
                <ul class="nav nav-pills bg-white p-2 shadow-sm rounded-pill d-inline-flex flex-wrap gap-2">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-4 <?= $tipoFiltro === 'professor' ? 'active' : '' ?>" 
                           href="?id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&tipo=professor">
                            <i class="bi bi-person-workspace me-2"></i>Do Professor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-4 <?= $tipoFiltro === 'ia' ? 'active' : '' ?>" 
                           href="?id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&tipo=ia">
                            <i class="bi bi-robot me-2"></i>Análise IA
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Filtros -->
            <div class="card border-0 shadow-sm p-3 mb-4" style="border-radius: 12px;">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="id" value="<?= $alunoId ?>">
                    <input type="hidden" name="turma_id" value="<?= $turmaId ?>">
                    <input type="hidden" name="tipo" value="<?= $tipoFiltro ?>">
                    
                    <div class="col-md-<?= $tipoFiltro === 'ia' ? '6' : '12' ?>">
                        <label class="form-label small fw-bold text-muted">
                            <i class="bi bi-funnel me-1"></i>Filtrar por Professor
                        </label>
                        <select name="professor_id" class="form-select border-0 bg-light rounded-3" onchange="this.form.submit()">
                            <option value="0">Todos os Professores</option>
                            <?php foreach ($professoresEscola as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $professorFiltro == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nome_completo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($tipoFiltro === 'ia'): ?>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">
                                <i class="bi bi-calendar-event me-1"></i>Filtrar por Período
                            </label>
                            <select name="periodo_id" class="form-select border-0 bg-light rounded-3" onchange="this.form.submit()">
                                <option value="0">Todos os Períodos</option>
                                <?php foreach ($periodosDisponiveis as $per): ?>
                                    <option value="<?= $per['id'] ?>" <?= $periodoFiltro === (int)$per['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($per['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Lista de Relatórios -->
            <?php if (empty($relatorios)): ?>
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 15px;">
                    <i class="bi bi-journal-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-secondary">Nenhum relatório encontrado</h5>
                    <p class="text-muted">Não há registros para este aluno com os filtros aplicados.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($relatorios as $rel): ?>
                        <div class="col-12 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 5px solid <?= $rel['tipo'] == 'ia' ? '#6366f1' : '#3b82f6' ?> !important;">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1">
                                                <?= $rel['tipo'] == 'ia' ? '<i class="fas fa-robot text-indigo me-1"></i> Análise de Inteligência Artificial' : '<i class="fas fa-user-tie text-primary me-1"></i> Professor: ' . htmlspecialchars($rel['professor_nome']) ?>
                                            </h6>
                                            <span class="badge bg-light text-muted fw-normal">
                                                <i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y à\s H:i', strtotime($rel['criado_em'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="report-content bg-light p-3 rounded-3 mb-0" style="white-space: pre-wrap; line-height: 1.6;">
                                        <?= nl2br(htmlspecialchars($rel['conteudo'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
