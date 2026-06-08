<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/middleware/verificar_admin_supremo.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/AdminSupremoModel.php';

$adminModel = new AdminSupremoModel($pdo);

$title = 'Suporte Global - Admin Supremo';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content" style="background-color: #f8f9fc; min-height: 100vh; width: 100%;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <h2 class="mb-4" style="color: #4e73df; font-weight: 700;">Central de Suporte SaaS</h2>

            <div class="row">
                <!-- Resumo de Chamados -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow border-0 bg-primary text-white p-3">
                        <h6>Chamados Abertos</h6>
                        <h3>0</h3>
                        <small>Todas as unidades operando normalmente.</small>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card shadow border-0 bg-success text-white p-3">
                        <h6>Tempo Médio de Resposta</h6>
                        <h3>14 min</h3>
                        <small>Eficiência dentro do SLA contratado.</small>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card shadow border-0 bg-info text-white p-3">
                        <h6>Uptime do Sistema</h6>
                        <h3>99.9%</h3>
                        <small>Monitoramento de infraestrutura OK.</small>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tickets Recentes de Administradores</h6>
                </div>
                <div class="card-body py-5 text-center text-muted">
                    <i class="bi bi-chat-left-dots" style="font-size: 3rem;"></i>
                    <p class="mt-3">Nenhum ticket pendente no momento.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
