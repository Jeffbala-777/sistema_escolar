<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/middleware/verificar_admin_supremo.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/EscolaModel.php';
require_once __DIR__ . '/../app/models/AdminSupremoModel.php';

$escolaModel = new EscolaModel($pdo);
$adminSupremoModel = new AdminSupremoModel($pdo);

$escolaId = isset($_GET['escola_id']) ? (int)$_GET['escola_id'] : 0;
$escolas = $escolaModel->listarTodas();

$dadosEscola = null;
if ($escolaId > 0) {
    // Buscar estatísticas e dados específicos da escola selecionada
    $sqlTurmas = "SELECT t.*, (SELECT COUNT(*) FROM matriculas m WHERE m.turma_id = t.id AND m.status = 'ativa') as total_alunos 
                  FROM turmas t WHERE t.escola_id = :escola_id";
    $stmt = $pdo->prepare($sqlTurmas);
    $stmt->execute([':escola_id' => $escolaId]);
    $turmas = $stmt->fetchAll();

    $sqlNotas = "SELECT AVG(nota) as media_geral FROM notas WHERE escola_id = :escola_id";
    $stmt = $pdo->prepare($sqlNotas);
    $stmt->execute([':escola_id' => $escolaId]);
    $mediaGeral = $stmt->fetchColumn();
}

$title = 'Monitoramento Global - Admin Supremo';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content" style="background-color: #f5f5f5; min-height: 100vh; width: 100%;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <div class="container-boletim" style="max-width: 1200px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                
                <h2 style="color: #456b82; font-weight: 400; margin-bottom: 25px;">Monitoramento Global de Escolas</h2>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Selecione uma Escola para Visualizar</label>
                        <select class="form-select" onchange="window.location.href='?escola_id='+this.value">
                            <option value="">Selecione...</option>
                            <?php foreach ($escolas as $e): ?>
                                <option value="<?= $e['id'] ?>" <?= $escolaId == $e['id'] ? 'selected' : '' ?>><?= e($e['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($escolaId > 0): ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light text-center">
                                <h6 class="text-muted mb-1">Média Geral da Escola</h6>
                                <h3 class="mb-0 text-primary"><?= $mediaGeral ? number_format((float)$mediaGeral, 1) : '0.0' ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light text-center">
                                <h6 class="text-muted mb-1">Total de Turmas</h6>
                                <h3 class="mb-0 text-success"><?= count($turmas) ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light text-center">
                                <h6 class="text-muted mb-1">Total de Alunos Ativos</h6>
                                <?php 
                                    $totalAlunos = array_sum(array_column($turmas, 'total_alunos'));
                                ?>
                                <h3 class="mb-0 text-info"><?= $totalAlunos ?></h3>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3" style="color: #456b82;">Visão das Turmas</h5>
                    <div class="table-responsive">
                        <table class="table" style="font-size: 0.85rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid #eee;">
                                    <th>Turma</th>
                                    <th>Série</th>
                                    <th>Turno</th>
                                    <th class="text-center">Alunos</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($turmas as $t): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 12px 8px;"><?= e($t['nome']) ?></td>
                                        <td style="padding: 12px 8px;"><?= e($t['serie']) ?></td>
                                        <td style="padding: 12px 8px;"><?= e(ucfirst($t['turno'])) ?></td>
                                        <td style="padding: 12px 8px; text-align: center;"><?= $t['total_alunos'] ?> / <?= $t['capacidade'] ?></td>
                                        <td style="padding: 12px 8px; text-align: center;">
                                            <span class="badge bg-<?= $t['ativo'] ? 'success' : 'danger' ?>">
                                                <?= $t['ativo'] ? 'Ativa' : 'Inativa' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-search" style="font-size: 3rem;"></i>
                        <p class="mt-3">Escolha uma escola acima para ver os dados detalhados.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
