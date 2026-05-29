<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

$title = 'Minhas Turmas';

$model = new ProfessorTurmaDisciplinaModel($pdo);

$professorId = $_SESSION['usuario']['id'];
$escolaId = $_SESSION['usuario']['escola_id'];

$turmas = $model->listarTurmasProfessor(
    $professorId,
    $escolaId
);

require_once __DIR__ . '/../partials/header.php';
?>

<div class="dashboard-container">

    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <main class="main-content">

        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="top-page">
            <h1>Minhas Turmas</h1>
            <p>Gerencie suas turmas e disciplinas.</p>
        </div>

        <?php if (empty($turmas)): ?>
            <div class="page-card">
                <p class="mb-0 text-muted">
                    Nenhuma turma vinculada a este professor no momento.
                </p>
            </div>
        <?php else: ?>
            <div class="cards-grid">

            <?php foreach ($turmas as $turma): ?>

                <div class="dashboard-card">

                    <div class="card-icon">
                        <i class="bi bi-mortarboard"></i>
                    </div>

                    <div class="card-body">

                        <h3>
                            <?= e($turma['turma']) ?>
                        </h3>

                        <p>
                            <?= e($turma['disciplina']) ?>
                        </p>

                        <span>
                            <?= e($turma['serie']) ?>
                            •
                            <?= e($turma['turno']) ?>
                        </span>

                    </div>

                    <div class="card-footer" style="display: flex; gap: 10px; flex-wrap: wrap;">

                        <a
                            href="<?= base_url('professor/lancar_notas_turma.php?id=' . $turma['id']) ?>"
                            class="btn btn-primary btn-sm"
                        >
                            <i class="bi bi-journal-check"></i> Notas
                        </a>

                        <a
                            href="<?= base_url('professor/lancar_faltas_turma.php?id=' . $turma['id']) ?>"
                            class="btn btn-secondary btn-sm"
                        >
                            <i class="bi bi-calendar-check"></i> Faltas
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

            </div>
        <?php endif; ?>

    </main>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>