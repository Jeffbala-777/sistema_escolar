<?php
require_once __DIR__ . '/../app/middleware/verificar_professor.php';

$title = 'Dashboard Professor';

$usuario = $_SESSION['usuario'];

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">

    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <div class="content-area p-4">

        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="page-card p-4">

            <div class="dashboard-title mb-2">

                Painel do Professor

            </div>

            <div class="dashboard-subtitle mb-4">

                Gerencie turmas, notas, presença e diário de classe.

            </div>

            <div class="row g-4">

                <div class="col-md-6 col-xl-3">

                    <a
                        href="<?= base_url('professor/minhas_turmas.php') ?>"
                        class="text-decoration-none">

                        <div class="dashboard-card">

                            <div class="dashboard-card-icon bg-primary">

                                <i class="bi bi-grid"></i>

                            </div>

                            <div class="dashboard-card-title">
                                Minhas Turmas
                            </div>

                            <div class="dashboard-card-text">
                                Visualizar turmas vinculadas.
                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-6 col-xl-3">

                    <a
                        href="<?= base_url('professor/lancar_notas.php') ?>"
                        class="text-decoration-none">

                        <div class="dashboard-card">

                            <div class="dashboard-card-icon bg-success">

                                <i class="bi bi-journal-check"></i>

                            </div>

                            <div class="dashboard-card-title">
                                Notas
                            </div>

                            <div class="dashboard-card-text">
                                Lançamento de avaliações.
                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-6 col-xl-3">

                    <a
                        href="<?= base_url('professor/lancar_faltas.php') ?>"
                        class="text-decoration-none">

                        <div class="dashboard-card">

                            <div class="dashboard-card-icon bg-warning">

                                <i class="bi bi-calendar-check"></i>

                            </div>

                            <div class="dashboard-card-title">
                                Presença
                            </div>

                            <div class="dashboard-card-text">
                                Controle de frequência.
                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-6 col-xl-3">

                    <a
                        href="<?= base_url('professor/boletim_turma.php') ?>"
                        class="text-decoration-none">

                        <div class="dashboard-card">

                            <div class="dashboard-card-icon bg-info">

                                <i class="bi bi-bar-chart"></i>

                            </div>

                            <div class="dashboard-card-title">
                                Boletins
                            </div>

                            <div class="dashboard-card-text">
                                Consultar desempenho.
                            </div>

                        </div>

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>