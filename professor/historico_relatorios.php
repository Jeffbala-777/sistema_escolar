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

// Lógica de Editar Relatório
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_relatorio'])) {
    $relId = (int)$_POST['relatorio_id'];
    $conteudo = trim($_POST['conteudo'] ?? '');
    if (!empty($conteudo)) {
        $relatorioModel->atualizar($relId, $conteudo, $professorId);
        header("Location: historico_relatorios.php?aluno_id=$alunoId&turma_id=$turmaId&editado=1");
        exit;
    }
}

// Lógica de Excluir Relatório
if (isset($_GET['excluir_id'])) {
    $excluirId = (int)$_GET['excluir_id'];
    $relatorioModel->excluir($excluirId, $professorId);
    header("Location: historico_relatorios.php?aluno_id=$alunoId&turma_id=$turmaId&excluido=1");
    exit;
}

$relatorios = $relatorioModel->listarPorAluno($alunoId, $turmaId, null, (int)$professorId);

$title = 'Histórico de Relatórios - ' . $aluno['nome_completo'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            
            <div class="mb-4">
                <a href="relatorios_turma.php?turma_id=<?= $turmaId ?>" class="btn btn-secondary btn-sm d-flex align-items-center gap-2" style="width: fit-content;">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>

            <div class="page-card p-4 mb-4">
                <div class="d-flex align-items-center">
                    <div class="topbar-avatar me-4" style="background: #0E79EB; width: 60px; height: 60px; font-size: 1.5rem; border: none;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="dashboard-title mb-1" style="font-size: 24px;">Histórico de Registros</div>
                        <div class="dashboard-subtitle mb-0">
                            Aluno: <strong><?= e($aluno['nome_completo']) ?></strong> | Turma: <strong><?= e($turma['nome']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['excluido'])): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Registro removido com sucesso.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['editado'])): ?>
                <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-pencil-square me-2"></i> Registro atualizado com sucesso.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="relatorios-timeline">
                <?php if (!empty($relatorios)): ?>
                    <?php foreach ($relatorios as $rel): ?>
                    <div class="dashboard-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-3 text-primary">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">
                                        <?= date('d/m/Y', strtotime($rel['criado_em'])) ?> às <?= date('H:i', strtotime($rel['criado_em'])) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        Professor: <strong><?= e($rel['professor_nome']) ?></strong>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ((int)$rel['professor_id'] === $professorId): ?>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm px-3" 
                                        onclick="abrirModalEdicao(<?= $rel['id'] ?>, `<?= addslashes($rel['conteudo']) ?>`)">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <a href="?aluno_id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&excluir_id=<?= $rel['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm px-3" 
                                   onclick="return confirm('Deseja excluir este registro?')">
                                    <i class="bi bi-trash"></i> Excluir
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 rounded-3" style="background: #f8f9fa; color: #333; line-height: 1.6; font-size: 14px; border-left: 4px solid #0E79EB;">
                            <?= nl2br(e($rel['conteudo'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="page-card text-center py-5">
                        <i class="bi bi-journal-x text-muted opacity-25 d-block mb-3" style="font-size: 48px;"></i>
                        <h5 class="fw-bold text-secondary">Nenhum registro encontrado</h5>
                        <p class="text-muted mb-0">Este aluno ainda não possui relatórios nesta turma.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="modalEditarRelatorio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,.2);">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Editar Registro</h5>
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
