<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

$escolaId = (int)$_SESSION['usuario']['escola_id'];
$alunoId = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : 0;
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
$tipoRelatorio = isset($_GET['tipo']) && in_array($_GET['tipo'], ['ia', 'professor']) ? $_GET['tipo'] : 'professor';
$professorFiltroId = isset($_GET['professor_id']) ? (int)$_GET['professor_id'] : 0;

$relatorioModel = new RelatorioAlunoModel($pdo);
$usuarioModel = new UsuarioModel($pdo);
$turmaModel = new TurmaModel($pdo);
$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);

$aluno = $usuarioModel->buscarPorId($alunoId);
$turma = $turmaModel->buscarPorId($turmaId, $escolaId);
$professoresDaTurma = $ptdModel->listarProfessoresTurma($turmaId, $escolaId);

if (!$aluno || !$turma || (int)$aluno['escola_id'] !== $escolaId) {
    header('Location: relatorios_turma.php');
    exit;
}

// Lógica de Editar Relatório (Admin pode editar qualquer um)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_relatorio'])) {
    $relId = (int)$_POST['relatorio_id'];
    $conteudo = trim($_POST['conteudo'] ?? '');
    if (!empty($conteudo)) {
        $sql = "UPDATE relatorios_alunos SET conteudo = :conteudo WHERE id = :id AND escola_id = :escola_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $relId, ':conteudo' => $conteudo, ':escola_id' => $escolaId]);
        header("Location: historico_relatorios.php?aluno_id=$alunoId&turma_id=$turmaId&tipo=$tipoRelatorio&professor_id=$professorFiltroId&editado=1");
        exit;
    }
}

// Lógica de Excluir Relatório (Admin pode excluir qualquer um)
if (isset($_GET['excluir_id'])) {
    $excluirId = (int)$_GET['excluir_id'];
    $sql = "DELETE FROM relatorios_alunos WHERE id = :id AND escola_id = :escola_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $excluirId, ':escola_id' => $escolaId]);
    header("Location: historico_relatorios.php?aluno_id=$alunoId&turma_id=$turmaId&tipo=$tipoRelatorio&professor_id=$professorFiltroId&excluido=1");
    exit;
}

// Listar relatórios com filtro de professor
$sql = "SELECT r.*, u.nome_completo as professor_nome 
        FROM relatorios_alunos r
        INNER JOIN usuarios u ON u.id = r.professor_id
        WHERE r.aluno_id = :aluno_id AND r.turma_id = :turma_id";

$params = [
    ':aluno_id' => $alunoId,
    ':turma_id' => $turmaId
];

if ($tipoRelatorio === 'ia') {
    $sql .= " AND r.tipo = 'ia'";
} else {
    $sql .= " AND (r.tipo = 'professor' OR r.tipo IS NULL)";
    if ($professorFiltroId > 0) {
        $sql .= " AND r.professor_id = :professor_id";
        $params[':professor_id'] = $professorFiltroId;
    }
}

$sql .= " ORDER BY r.criado_em DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = 'Histórico Admin - ' . $aluno['nome_completo'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            
            <div class="page-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="topbar-avatar me-4" style="background: <?= $tipoRelatorio === 'ia' ? '#8e44ad' : '#2F3740' ?>; width: 60px; height: 60px; font-size: 1.5rem; border: none;">
                            <?= $tipoRelatorio === 'ia' ? '<i class="bi bi-robot"></i>' : '<i class="bi bi-shield-check"></i>' ?>
                        </div>
                        <div>
                            <div class="dashboard-title mb-1" style="font-size: 24px;">Histórico de Relatórios (<?= $tipoRelatorio === 'ia' ? 'IA' : 'Professor' ?>)</div>
                            <div class="dashboard-subtitle mb-0">
                                Aluno: <strong><?= e($aluno['nome_completo']) ?></strong> | Turma: <strong><?= e($turma['nome']) ?></strong>
                            </div>
                        </div>
                    </div>
                    <a href="relatorios_turma.php?turma_id=<?= $turmaId ?>&tipo=<?= $tipoRelatorio ?>" class="btn btn-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if ($tipoRelatorio === 'professor'): ?>
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-secondary">Filtrar por Professor</label>
                        <form method="GET">
                            <input type="hidden" name="aluno_id" value="<?= $alunoId ?>">
                            <input type="hidden" name="turma_id" value="<?= $turmaId ?>">
                            <input type="hidden" name="tipo" value="professor">
                            <select name="professor_id" class="form-select" onchange="this.form.submit()">
                                <option value="0">Todos os Professores</option>
                                <?php foreach ($professoresDaTurma as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $professorFiltroId === (int)$p['id'] ? 'selected' : '' ?>>
                                        Prof. <?= e($p['nome_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['excluido'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-trash3-fill me-2"></i> Registro removido permanentemente pelo administrador.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['editado'])): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Registro atualizado com sucesso pelo administrador.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <?php if (!empty($relatorios)): ?>
                        <?php foreach ($relatorios as $rel): ?>
                        <div class="dashboard-card mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 me-3 text-primary">
                                        <i class="bi <?= $tipoRelatorio === 'ia' ? 'bi-robot' : 'bi-person-badge' ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;">
                                            <?= $tipoRelatorio === 'ia' ? 'Inteligência Artificial' : 'Prof. ' . e($rel['professor_nome']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 12px;">
                                            <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y', strtotime($rel['criado_em'])) ?> às <?= date('H:i', strtotime($rel['criado_em'])) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" 
                                            onclick="abrirModalEdicao(<?= $rel['id'] ?>, `<?= addslashes($rel['conteudo']) ?>`)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <a href="?aluno_id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&tipo=<?= $tipoRelatorio ?>&professor_id=<?= $professorFiltroId ?>&excluir_id=<?= $rel['id'] ?>" 
                                       class="btn btn-outline-danger btn-sm px-3" 
                                       onclick="return confirm('Confirmar exclusão permanente?')">
                                        <i class="bi bi-trash"></i> Excluir
                                    </a>
                                </div>
                            </div>
                            <div class="p-3 rounded-3" style="background: #f8f9fa; color: #2F3740; line-height: 1.7; font-size: 14px; border-left: 4px solid <?= $tipoRelatorio === 'ia' ? '#8e44ad' : '#2F3740' ?>;">
                                <?= nl2br(e($rel['conteudo'])) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="page-card text-center py-5">
                            <i class="bi bi-journal-x text-muted opacity-25 d-block mb-3" style="font-size: 48px;"></i>
                            <h5 class="fw-bold text-secondary">Nenhum registro encontrado</h5>
                            <p class="text-muted mb-0">Não existem relatórios para os critérios selecionados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="modalEditarRelatorio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,.2);">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-shield-lock me-2 text-primary"></i>Editar Registro (Admin)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="relatorio_id" id="edit_relatorio_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Conteúdo do Relatório</label>
                        <textarea name="conteudo" id="edit_conteudo" class="form-control" rows="6" required 
                                  style="resize: none;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="editar_relatorio" class="btn btn-primary px-4">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalEdicao(id, conteudo) {
    document.getElementById('edit_relatorio_id').value = id;
    document.getElementById('edit_conteudo').value = conteudo;
    var myModal = new bootstrap.Modal(document.getElementById('modalEditarRelatorio'));
    myModal.show();
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
