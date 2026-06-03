<?php
// Ativa tipagem estrita para segurança
declare(strict_types=1);

// Middleware de autenticação do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
// Conexão com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model de turmas
require_once __DIR__ . '/../app/models/TurmaModel.php';
// Model de relatórios
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';

// Inicializa models
$turmaModel = new TurmaModel($pdo);
$relatorioModel = new RelatorioAlunoModel($pdo);

// ID da escola do admin logado
$escolaId = (int)$_SESSION['usuario']['escola_id'];
// Turma selecionada (via GET)
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
// Tipo de relatório (via GET: 'ia' ou 'professor')
$tipoRelatorio = isset($_GET['tipo']) && in_array($_GET['tipo'], ['ia', 'professor']) ? $_GET['tipo'] : 'ia';

// Busca todas as turmas da escola para o select
$turmas = $turmaModel->listarPorEscola($escolaId);

// Busca relatórios se uma turma for selecionada
$relatorios = [];
$turmaSelecionada = null;
if ($turmaId > 0) {
    $relatorios = $relatorioModel->listarPorTurma($turmaId, $escolaId, $tipoRelatorio);
    // Busca dados da turma selecionada
    foreach ($turmas as $t) {
        if ((int)$t['id'] === $turmaId) {
            $turmaSelecionada = $t;
            break;
        }
    }
}

// Título da página
$title = 'Relatórios Pedagógicos';
// Header padrão
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php // Menu lateral do admin
    require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">
        <?php // Painel superior
        require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            <!-- Título e Seleção de Turma -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="fw-bold text-secondary mb-1">Relatórios Pedagógicos</h4>
                        <p class="text-muted mb-0">Visualize os registros enviados pelos professores e pela IA</p>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="tipo" value="<?= e($tipoRelatorio) ?>">
                            <select name="turma_id" class="form-select shadow-sm" style="border-radius: 10px;" onchange="this.form.submit()">
                                <option value="">Selecione uma Turma</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $turmaId === (int)$t['id'] ? 'selected' : '' ?>>
                                        <?= e($t['nome']) ?> (<?= e($t['serie']) ?> - <?= e($t['turno']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($turmaId > 0): ?>
                <!-- Abas de Navegação -->
                <ul class="nav nav-pills mb-4 bg-white p-2 shadow-sm rounded-pill d-inline-flex">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-4 <?= $tipoRelatorio === 'ia' ? 'active' : '' ?>" 
                           href="?turma_id=<?= $turmaId ?>&tipo=ia">
                            <i class="bi bi-robot me-2"></i>Relatório IA
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-4 <?= $tipoRelatorio === 'professor' ? 'active' : '' ?>" 
                           href="?turma_id=<?= $turmaId ?>&tipo=professor">
                            <i class="bi bi-person-workspace me-2"></i>Relatório Professor
                        </a>
                    </li>
                </ul>

                <?php if (!empty($relatorios)): ?>
                    <div class="row">
                        <?php foreach ($relatorios as $rel): ?>
                            <div class="col-12 mb-3">
                                <div class="card border-0 shadow-sm" style="border-radius: 15px; border-left: 5px solid <?= $tipoRelatorio === 'ia' ? '#8e44ad' : '#3498db' ?>;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="fw-bold text-primary mb-1">
                                                    <?php if ($rel['aluno_id'] == 0): ?>
                                                        <i class="bi bi-people-fill me-2"></i>Relatório da Turma
                                                    <?php else: ?>
                                                        <i class="bi bi-person-badge me-2"></i><?= e($rel['aluno_nome']) ?>
                                                    <?php endif; ?>
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    <i class="bi bi-person-circle me-1"></i> Solicitado por: <?= e($rel['professor_nome']) ?>
                                                </p>
                                            </div>
                                            <span class="badge bg-light text-secondary border px-3 py-2" style="border-radius: 8px;">
                                                <i class="bi bi-calendar3 me-2"></i><?= date('d/m/Y H:i', strtotime($rel['criado_em'])) ?>
                                            </span>
                                        </div>
                                        <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-wrap; line-height: 1.6;">
                                            <?= e($rel['conteudo']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 15px;">
                        <i class="bi bi-journal-x text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-secondary">Nenhum relatório encontrado</h5>
                        <p class="text-muted">Não há registros de <?= $tipoRelatorio === 'ia' ? 'IA' : 'professores' ?> para a turma selecionada até o momento.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 15px;">
                    <i class="bi bi-arrow-up-circle text-primary opacity-25" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-secondary">Aguardando Seleção</h5>
                    <p class="text-muted">Selecione uma turma acima para visualizar os relatórios correspondentes.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php // Footer padrão
require_once __DIR__ . '/../partials/footer.php'; ?>
