<?php
// Ativa tipagem estrita para segurança
declare(strict_types=1);

// Middleware de autenticação do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
// Conexão com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model de turmas
require_once __DIR__ . '/../app/models/TurmaModel.php';

// Inicializa model
$turmaModel = new TurmaModel($pdo);

// ID da escola do admin logado
$escolaId = (int)$_SESSION['usuario']['escola_id'];

// Busca todas as turmas da escola
$turmas = $turmaModel->listarPorEscola($escolaId);

// Título da página
$title = 'Selecionar Turma';
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
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="color: #2c3e50; font-weight: 700; margin-bottom: 10px;">Gestão de Desempenho</h2>
                <p style="color: #7f8c8d;">Selecione uma turma para visualizar a análise completa de desempenho e insights de IA.</p>
            </div>

            <?php if (!empty($turmas)): ?>
                <div class="row g-4">
                    <?php foreach ($turmas as $t): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; transition: all 0.3s;">
                            <div class="card-header bg-primary bg-opacity-10 border-0 p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold text-primary mb-0"><?= e($t['nome']) ?></h5>
                                    <span class="badge bg-primary rounded-pill"><?= e($t['turno']) ?></span>
                                </div>
                                <p class="text-muted small mb-0 mt-1"><?= e($t['serie']) ?></p>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="bi bi-people text-secondary"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Visão Geral</small>
                                        <span class="fw-bold">Acesso Administrativo Total</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 p-4 pt-0">
                                <a href="<?= base_url('admin/desempenho.php?turma_id=' . $t['id']) ?>" 
                                   class="btn btn-primary w-100 fw-bold py-2 shadow-sm" 
                                   style="border-radius: 10px;">
                                    <i class="bi bi-graph-up-arrow me-2"></i> Analisar Desempenho
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 15px;">
                    <i class="bi bi-grid-3x3-gap text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-secondary">Nenhuma turma cadastrada</h5>
                    <p class="text-muted">Cadastre turmas no menu lateral para começar a análise.</p>
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
