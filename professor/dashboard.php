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

                Gerencie suas turmas e consulte boletins.

            </div>

            <div class="row g-3">

                <div class="col-md-6">

                    <a
                        href="<?= base_url('professor/minhas_turmas.php') ?>"
                        class="text-decoration-none">

                        <div class="p-3 border rounded-2" style="background:#f8f9fa;cursor:pointer;transition:.2s ease;border-color:#dce3ea!important;">

                            <div style="display:flex;align-items:center;gap:12px;">

                                <i class="bi bi-grid" style="font-size:24px;color:#0E79EB;"></i>

                                <div>

                                    <h6 class="mb-1" style="font-weight:600;color:#2F3740;">Minhas Turmas</h6>

                                    <p class="mb-0" style="font-size:13px;color:#6c757d;">Visualizar turmas vinculadas</p>

                                </div>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-6">

                    <a
                        href="<?= base_url('professor/boletim.php') ?>"
                        class="text-decoration-none">

                        <div class="p-3 border rounded-2" style="background:#f8f9fa;cursor:pointer;transition:.2s ease;border-color:#dce3ea!important;">

                            <div style="display:flex;align-items:center;gap:12px;">

                                <i class="bi bi-bar-chart" style="font-size:24px;color:#0E79EB;"></i>

                                <div>

                                    <h6 class="mb-1" style="font-weight:600;color:#2F3740;">Boletins</h6>

                                    <p class="mb-0" style="font-size:13px;color:#6c757d;">Consultar desempenho geral</p>

                                </div>

                            </div>

                        </div>

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
