<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

$escolaId = (int)$_SESSION['usuario']['escola_id'];
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
$tipoRelatorio = isset($_GET['tipo']) && in_array($_GET['tipo'], ['ia', 'professor']) ? $_GET['tipo'] : '';

$turmaModel = new TurmaModel($pdo);
$usuarioModel = new UsuarioModel($pdo);
$relatorioModel = new RelatorioAlunoModel($pdo);
$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);

$turmas = $turmaModel->listarPorEscola($escolaId);

$alunos = [];
$relatoriosIA = [];
$turmaAtual = null;

if ($turmaId > 0) {
    $turmaAtual = $turmaModel->buscarPorId($turmaId, $escolaId);
    
    if ($tipoRelatorio === 'professor') {
        $alunos = $usuarioModel->listarPorTurma($turmaId);
    } elseif ($tipoRelatorio === 'ia') {
        $sqlIA = "SELECT r.*, p.nome_completo as professor_nome, a.nome_completo as aluno_nome 
                  FROM relatorios_alunos r
                  INNER JOIN usuarios p ON p.id = r.professor_id
                  LEFT JOIN usuarios a ON a.id = r.aluno_id
                  WHERE r.turma_id = :tid AND r.escola_id = :eid AND r.tipo = 'ia'
                  ORDER BY r.criado_em DESC";
        $stmtIA = $pdo->prepare($sqlIA);
        $stmtIA->execute([':tid' => $turmaId, ':eid' => $escolaId]);
        $relatoriosIA = $stmtIA->fetchAll(PDO::FETCH_ASSOC);
    }
}

$title = 'Desempenho por Turma';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            
            <div class="page-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="dashboard-title mb-1" style="font-size: 24px;">Desempenho Escolar</div>
                        <div class="dashboard-subtitle mb-0">Selecione uma turma para visualizar os indicadores de desempenho.</div>
                    </div>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Selecione a Turma</label>
                        <form method="GET">
                            <select name="turma_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Escolher Turma...</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $turmaId === (int)$t['id'] ? 'selected' : '' ?>>
                                        <?= e($t['nome']) ?> (<?= e($t['serie']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($turmaId > 0): ?>
                <div class="mb-4">
                    <div class="d-flex gap-3">
                        <a href="?turma_id=<?= $turmaId ?>&tipo=ia" 
                           class="btn <?= $tipoRelatorio === 'ia' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 py-2 d-flex align-items-center gap-2" style="border-radius: 12px; font-weight: 600;">
                            <i class="bi bi-robot"></i> Indicadores da IA
                        </a>
                        <a href="?turma_id=<?= $turmaId ?>&tipo=professor" 
                           class="btn <?= $tipoRelatorio === 'professor' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 py-2 d-flex align-items-center gap-2" style="border-radius: 12px; font-weight: 600;">
                            <i class="bi bi-person-workspace"></i> Registros Pedagógicos
                        </a>
                    </div>
                </div>

                <?php if ($tipoRelatorio === 'ia'): ?>
                    <div class="row g-3">
                        <?php if (!empty($relatoriosIA)): ?>
                            <?php foreach ($relatoriosIA as $rel): ?>
                            <div class="col-12">
                                <div class="dashboard-card" style="border-left: 5px solid #8e44ad;">
                                    <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">
                                                <i class="bi bi-robot me-2 text-primary"></i>
                                                <?= $rel['aluno_id'] ? 'Análise do Aluno: ' . e($rel['aluno_nome']) : 'Panorama Geral da Turma' ?>
                                            </h6>
                                            <small class="text-muted">Gerado por solicitação de: <strong><?= e($rel['professor_nome']) ?></strong></small>
                                        </div>
                                        <div class="badge bg-light text-dark border"><?= date('d/m/Y', strtotime($rel['criado_em'])) ?></div>
                                    </div>
                                    <div class="p-3 rounded-3" style="background: #fdfaff; color: #2F3740; line-height: 1.7; font-size: 14px;">
                                        <?= nl2br(e($rel['conteudo'])) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <div class="page-card p-5">
                                    <i class="bi bi-robot text-muted opacity-25 d-block mb-3" style="font-size: 48px;"></i>
                                    <h5 class="fw-bold text-secondary">Nenhuma análise da IA disponível</h5>
                                    <p class="text-muted mb-0">Aguardando geração de indicadores automáticos para esta turma.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($tipoRelatorio === 'professor'): ?>
                    <div class="row g-3">
                        <?php if (!empty($alunos)): ?>
                            <?php foreach ($alunos as $aluno): 
                                $sqlUltimo = "SELECT r.*, u.nome_completo as professor_nome 
                                              FROM relatorios_alunos r 
                                              INNER JOIN usuarios u ON u.id = r.professor_id
                                              WHERE r.aluno_id = :aid AND r.turma_id = :tid AND (r.tipo = 'professor' OR r.tipo IS NULL)
                                              ORDER BY r.criado_em DESC LIMIT 1";
                                $stmtUltimo = $pdo->prepare($sqlUltimo);
                                $stmtUltimo->execute([':aid' => $aluno['id'], ':tid' => $turmaId]);
                                $ultimoRelatorio = $stmtUltimo->fetch(PDO::FETCH_ASSOC);
                                $primeiraLetra = mb_strtoupper(mb_substr($aluno['nome_completo'], 0, 1));
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="dashboard-card h-100 d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                        <div class="topbar-avatar me-3" style="background: #2F3740; width: 45px; height: 45px; font-size: 1.1rem; border: none;">
                                            <?= $primeiraLetra ?>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 15px;"><?= e($aluno['nome_completo']) ?></h6>
                                        </div>
                                    </div>

                                    <div class="mb-3 flex-grow-1">
                                        <div class="d-flex justify-content-between mb-2">
                                            <small class="text-secondary fw-bold" style="font-size: 11px; text-transform: uppercase;">Último Registro</small>
                                            <?php if ($ultimoRelatorio): ?>
                                                <small class="text-muted" style="font-size: 11px;"><?= date('d/m/Y', strtotime($ultimoRelatorio['criado_em'])) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="p-3 rounded-3" style="background: #f8f9fa; border: 1px solid #e9ecef; font-size: 13px; color: #495057; min-height: 80px; line-height: 1.5;">
                                            <?php if ($ultimoRelatorio): ?>
                                                <div class="mb-2 d-flex align-items-center gap-1 text-primary fw-bold" style="font-size: 11px;">
                                                    <i class="bi bi-person-workspace"></i> Prof. <?= e($ultimoRelatorio['professor_nome']) ?>
                                                </div>
                                                <?= nl2br(e(mb_strimwidth($ultimoRelatorio['conteudo'], 0, 160, "..."))) ?>
                                            <?php else: ?>
                                                <div class="h-100 d-flex align-items-center justify-content-center text-muted opacity-50 italic">
                                                    Nenhum registro pedagógico.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mt-auto pt-2">
                                        <a href="historico_relatorios.php?aluno_id=<?= $aluno['id'] ?>&turma_id=<?= $turmaId ?>&tipo=professor" 
                                           class="btn btn-outline-primary w-100 btn-sm d-flex align-items-center justify-content-center gap-2" style="min-height: 38px; border-radius: 12px; font-weight: 600;">
                                            <i class="bi bi-clock-history"></i> Histórico de Desempenho
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <div class="page-card p-5">
                                    <i class="bi bi-people text-muted opacity-25 d-block mb-3" style="font-size: 48px;"></i>
                                    <h5 class="fw-bold text-secondary">Nenhum aluno matriculado</h5>
                                    <p class="text-muted mb-0">Esta turma não possui alunos ativos no sistema.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5 mt-4">
                    <div class="page-card p-5 d-inline-block" style="max-width: 500px;">
                        <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 70px; height: 70px;">
                            <i class="bi bi-search fs-2"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Aguardando Seleção</h5>
                        <p class="text-muted mb-0">Escolha uma turma para gerenciar o desempenho escolar.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
