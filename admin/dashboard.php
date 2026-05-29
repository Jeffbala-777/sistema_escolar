<?php
// Protege acesso do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
$title = 'Dashboard Admin';
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="d-flex page-wrap">

    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?> <!-- Menu lateral -->

    <div class="content-area p-4">

        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Painel superior -->

        <div class="page-card p-4">

            <div class="dashboard-title mb-2">

                Painel Administrativo

            </div>

            <div class="dashboard-subtitle mb-4">

                Gerencie alunos, professores, turmas e disciplinas.

            </div>

            <div class="row g-3">

                <div class="col-md-4">

                    <a href="alunos.php" class="text-decoration-none">

                        <div class="p-3 border rounded-2" style="background:#f8f9fa;cursor:pointer;transition:.2s ease;border-color:#dce3ea!important;">

                            <div style="display:flex;align-items:center;gap:12px;">

                                <i class="bi bi-people" style="font-size:24px;color:#0E79EB;"></i>

                                <div>

                                    <h6 class="mb-1" style="font-weight:600;color:#2F3740;">Alunos</h6>

                                    <p class="mb-0" style="font-size:13px;color:#6c757d;">Gerencie alunos</p>

                                </div>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="professores.php" class="text-decoration-none">

                        <div class="p-3 border rounded-2" style="background:#f8f9fa;cursor:pointer;transition:.2s ease;border-color:#dce3ea!important;">

                            <div style="display:flex;align-items:center;gap:12px;">

                                <i class="bi bi-mortarboard" style="font-size:24px;color:#0E79EB;"></i>

                                <div>

                                    <h6 class="mb-1" style="font-weight:600;color:#2F3740;">Professores</h6>

                                    <p class="mb-0" style="font-size:13px;color:#6c757d;">Gerencie docentes</p>

                                </div>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="turmas.php" class="text-decoration-none">

                        <div class="p-3 border rounded-2" style="background:#f8f9fa;cursor:pointer;transition:.2s ease;border-color:#dce3ea!important;">

                            <div style="display:flex;align-items:center;gap:12px;">

                                <i class="bi bi-grid" style="font-size:24px;color:#0E79EB;"></i>

                                <div>

                                    <h6 class="mb-1" style="font-weight:600;color:#2F3740;">Turmas</h6>

                                    <p class="mb-0" style="font-size:13px;color:#6c757d;">Organização das turmas</p>

                                </div>

                            </div>

                        </div>

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape -->
