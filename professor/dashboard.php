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

                Acesse relatórios e desempenho dos seus alunos.

            </div>

            <div class="row g-4">

                <div class="col-md-6">

                    <a href="<?= base_url('professor/relatorios_turma.php') ?>" class="text-decoration-none">
                        <div class="dashboard-card p-5 border-0 h-100" style="
                            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                            border-left: 6px solid #0E79EB;
                            border-radius: 16px;
                            box-shadow: 0 4px 15px rgba(14, 121, 235, 0.08);
                            cursor: pointer;
                            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
                            min-height: 140px;
                            display: flex;
                            align-items: center;
                        " onmouseover="this.style.boxShadow='0 8px 25px rgba(14, 121, 235, 0.15)'; this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='0 4px 15px rgba(14, 121, 235, 0.08)'; this.style.transform='translateY(0)';">
                            <div style="display: flex; align-items: center; gap: 20px; width: 100%;">
                                <div style="
                                    width: 80px;
                                    height: 80px;
                                    border-radius: 14px;
                                    background: linear-gradient(135deg, #0E79EB 0%, #0a5ac4 100%);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    flex-shrink: 0;
                                    box-shadow: 0 4px 12px rgba(14, 121, 235, 0.25);
                                ">
                                    <i class="bi bi-file-earmark-text" style="font-size: 36px; color: #ffffff;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h5 class="mb-2" style="font-weight: 700; color: #2F3740; font-size: 18px;">Relatórios</h5>
                                    <p class="mb-0" style="font-size: 13px; color: #6c757d; line-height: 1.5;">Lançar e gerenciar relatórios de alunos e turmas</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="<?= base_url('professor/desempenho.php') ?>" class="text-decoration-none">
                        <div class="dashboard-card p-5 border-0 h-100" style="
                            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                            border-left: 6px solid #28a745;
                            border-radius: 16px;
                            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.08);
                            cursor: pointer;
                            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
                            min-height: 140px;
                            display: flex;
                            align-items: center;
                        " onmouseover="this.style.boxShadow='0 8px 25px rgba(40, 167, 69, 0.15)'; this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='0 4px 15px rgba(40, 167, 69, 0.08)'; this.style.transform='translateY(0)';">
                            <div style="display: flex; align-items: center; gap: 20px; width: 100%;">
                                <div style="
                                    width: 80px;
                                    height: 80px;
                                    border-radius: 14px;
                                    background: linear-gradient(135deg, #28a745 0%, #1e8449 100%);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    flex-shrink: 0;
                                    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25);
                                ">
                                    <i class="bi bi-bar-chart-fill" style="font-size: 36px; color: #ffffff;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h5 class="mb-2" style="font-weight: 700; color: #2F3740; font-size: 18px;">Desempenho</h5>
                                    <p class="mb-0" style="font-size: 13px; color: #6c757d; line-height: 1.5;">Consultar desempenho geral dos alunos</p>
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
