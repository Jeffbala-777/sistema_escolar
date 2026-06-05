<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

$title = 'Selecionar Turma';

$model = new ProfessorTurmaDisciplinaModel($pdo);

$professorId = $_SESSION['usuario']['id'];
$escolaId = $_SESSION['usuario']['escola_id'];

// Buscar apenas as turmas que o professor tem acesso (com suas disciplinas)
$turmas = $model->listarTurmasProfessor($professorId, $escolaId);

require_once __DIR__ . '/../partials/header.php';
?>

<?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
<?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

<div class="main-content" style="background-color: #f5f5f5; min-height: 100vh; padding: 20px;">

    <div class="container-selecao" style="max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #2c3e50; font-weight: 600; margin-bottom: 5px;">Gestão de Turmas</h2>
                <p style="color: #7f8c8d; margin-bottom: 0;">Selecione uma turma para visualizar o desempenho ou gerenciar relatórios.</p>
            </div>
            <a href="dashboard.php" class="btn btn-secondary btn-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if (!empty($turmas)): ?>
            <div class="row g-3">
                <?php 
                // Agrupar turmas para evitar duplicatas
                $turmasAgrupadas = [];
                foreach ($turmas as $turma) {
                    $key = $turma['turma_id'];
                    if (!isset($turmasAgrupadas[$key])) {
                        $turmasAgrupadas[$key] = [
                            'turma_id' => $turma['turma_id'],
                            'turma' => $turma['turma'],
                            'serie' => $turma['serie'],
                            'ano' => $turma['ano'],
                            'disciplinas' => []
                        ];
                    }
                    $turmasAgrupadas[$key]['disciplinas'][] = $turma['disciplina'];
                }
                ?>

                <?php foreach ($turmasAgrupadas as $turmaInfo): 
                    $totalFaltas = $model->buscarFaltasPorTurma($turmaInfo['turma_id'], $escolaId);
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; transition: all 0.3s;">
                        <div class="card-header bg-primary bg-opacity-10 border-0 p-3">
                            <h6 class="fw-bold text-primary mb-0"><?= e($turmaInfo['turma']) ?></h6>
                            <small class="text-muted"><?= e($turmaInfo['serie']) ?> • <?= e($turmaInfo['ano']) ?></small>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <small class="text-muted d-block fw-bold mb-2">Disciplinas:</small>
                                <?php foreach ($turmaInfo['disciplinas'] as $disc): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary me-1 mb-1">
                                        <?= e($disc) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 p-3">
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('professor/desempenho.php?turma_id=' . $turmaInfo['turma_id']) ?>" 
                                   class="btn btn-sm btn-info" style="border-radius: 8px; font-weight: 600; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="bi bi-graph-up"></i> Desempenho
                                </a>
                                <a href="<?= base_url('professor/relatorios_turma.php?turma_id=' . $turmaInfo['turma_id']) ?>" 
                                   class="btn btn-sm btn-success" style="border-radius: 8px; font-weight: 600; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="bi bi-clipboard"></i> Relatórios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1 d-block mb-3"></i>
                <p class="text-muted">Você não possui turmas vinculadas.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }

    .btn {
        transition: all 0.3s ease;
    }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
