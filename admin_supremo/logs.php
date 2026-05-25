<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/middleware/verificar_admin_supremo.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/AdminSupremoModel.php';

$adminModel = new AdminSupremoModel($pdo);
$logs = $adminModel->listarLogsRecentes(50); // Pega histórico maior

$title = 'Logs de Auditoria - Admin Supremo';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content" style="background-color: #f8f9fc; min-height: 100vh; width: 100%;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Histórico de Auditoria Global</h5>
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-download me-1"></i> Exportar CSV</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Data/Hora</th>
                                    <th>Usuário</th>
                                    <th>Ação Realizada</th>
                                    <th>Detalhes do Evento</th>
                                    <th>IP Origem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>#<?= $log['id'] ?></td>
                                        <td><?= date('d/m/Y H:i:s', strtotime($log['data_criacao'])) ?></td>
                                        <td class="fw-bold"><?= e($log['nome_completo']) ?></td>
                                        <td><span class="badge bg-info text-dark"><?= e($log['acao']) ?></span></td>
                                        <td><small><?= e($log['detalhes'] ?? 'Sem detalhes') ?></small></td>
                                        <td><code><?= e($log['ip_origem']) ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if(empty($logs)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">Nenhuma atividade registrada no sistema.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
