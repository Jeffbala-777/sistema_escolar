<?php
// Ativa tipagem estrita para segurança
declare(strict_types=1);

// Middleware de autenticação do professor
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
// Conexão com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model de vínculos professor-turma-disciplina
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

// Inicializa model
$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);

// ID do professor logado e escola
$professorId = (int)$_SESSION['usuario']['id'];
$escolaId = (int)$_SESSION['usuario']['escola_id'];

// Busca todas as turmas vinculadas ao professor
$turmas = $ptdModel->listarTurmasProfessor($professorId, $escolaId);

// Título da página
$title = 'Selecionar Turma';
// Header padrão
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php // Menu lateral do professor
    require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <div class="content-area p-4">
        <?php // Painel superior
        require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="color: #2c3e50; font-weight: 700; margin-bottom: 10px;">Gestão de Turmas</h2>
                <p style="color: #7f8c8d;">Selecione uma turma para gerenciar o desempenho ou os relatórios pedagógicos.</p>
            </div>

            <?php if (!empty($turmas)): ?>
                <div class="row g-4">
                    <?php foreach ($turmas as $t): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; transition: all 0.3s;">
                            <div class="card-header bg-primary bg-opacity-10 border-0 p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold text-primary mb-0"><?= e($t['turma']) ?></h5>
                                    <span class="badge bg-primary rounded-pill"><?= e($t['turno'] ?? 'N/A') ?></span>
                                </div>
                                <p class="text-muted small mb-0 mt-1"><?= e($t['disciplina']) ?></p>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="bi bi-people text-secondary"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Visão Geral</small>
                                        <span class="fw-bold">Acesso ao Desempenho</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 p-4 pt-0">
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('professor/desempenho.php?turma_id=' . $t['turma_id']) ?>" 
                                       class="btn btn-info w-100 fw-bold py-2 shadow-sm text-white" 
                                       style="border-radius: 10px; background-color: #0dcaf0; border: none;">
                                        <i class="bi bi-graph-up-arrow me-2"></i> Analisar Desempenho
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 15px;">
                    <i class="bi bi-grid-3x3-gap text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-secondary">Nenhuma turma vinculada</h5>
                    <p class="text-muted">Você ainda não possui turmas vinculadas ao seu perfil nesta escola.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    .btn:hover {
        filter: brightness(1.1);
        transform: scale(1.02);
    }
</style>

<?php // Footer padrão
require_once __DIR__ . '/../partials/footer.php'; ?>
