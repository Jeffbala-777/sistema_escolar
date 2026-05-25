<?php
declare(strict_types=1); // Forca tipos estritos

// Protege acesso: apenas Admin Supremo pode ver
require_once __DIR__ . '/../app/middleware/verificar_admin_supremo.php';
// Conecta ao banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Carrega modelo de Escolas
require_once __DIR__ . '/../app/models/EscolaModel.php';
// Carrega modelo do Admin Supremo
require_once __DIR__ . '/../app/models/AdminSupremoModel.php';

$escolaModel = new EscolaModel($pdo); // Instancia model de escolas
$adminModel = new AdminSupremoModel($pdo); // Instancia model do supremo

$escolas = $escolaModel->listarTodas(); // Busca todas as escolas no banco

$title = 'Gestão de Escolas - Admin Supremo'; // Titulo da aba
require_once __DIR__ . '/../partials/header.php'; // Topo do site
?>

<div class="d-flex"> <!-- Layout flexivel para sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?> <!-- Menu lateral padronizado -->

    <div class="main-content flex-grow-1"> <!-- Area de conteudo principal -->
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Barra superior -->

        <div class="p-4"> <!-- Espacamento interno -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">Gestão de Escolas</h3> <!-- Titulo da secao -->
                <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-2"></i> Adicionar Escola</button> <!-- Botao novo -->
            </div>

            <div class="card shadow-sm border-0"> <!-- Card da tabela -->
                <div class="card-body p-0">
                    <div class="table-responsive"> <!-- Suporte para tabelas em telas pequenas -->
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark"> <!-- Cabecalho escuro padrao -->
                                <tr>
                                    <th class="px-4">Unidade</th> <!-- Nome/Cidade -->
                                    <th>Documento / Código</th> <!-- CNPJ/Codigo -->
                                    <th>Plano</th> <!-- Plano SaaS -->
                                    <th>Status</th> <!-- Ativo/Suspenso -->
                                    <th>Expiração</th> <!-- Data de validade -->
                                    <th class="text-center">Ações</th> <!-- Botoes de acao -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($escolas as $e): ?> <!-- Percorre lista de escolas -->
                                    <tr>
                                        <td class="px-4">
                                            <div class="fw-bold"><?= e($e['nome']) ?></div> <!-- Nome da escola -->
                                            <small class="text-muted"><?= e($e['cidade'] ?? 'N/A') ?></small> <!-- Cidade -->
                                        </td>
                                        <td>
                                            <div><?= e($e['cnpj'] ?? '00.000.000/0001-00') ?></div> <!-- CNPJ -->
                                            <code class="small"><?= e($e['codigo']) ?></code> <!-- Codigo unico -->
                                        </td>
                                        <td>
                                            <!-- Badge colorida conforme o plano -->
                                            <span class="badge bg-<?= ($e['plano'] ?? 'basico') === 'premium' ? 'warning text-dark' : (($e['plano'] ?? 'basico') === 'pro' ? 'primary' : 'secondary') ?>">
                                                <?= strtoupper($e['plano'] ?? 'BASICO') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Indicador de status ativo/inativo -->
                                            <?php if ($e['ativo']): ?>
                                                <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> ATIVO</span>
                                            <?php else: ?>
                                                <span class="text-danger small fw-bold"><i class="bi bi-x-circle-fill me-1"></i> SUSPENSO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- Data de expiracao com alerta se vencido -->
                                            <div class="<?= (strtotime($e['expiracao'] ?? '2026-12-31') < time()) ? 'text-danger fw-bold' : '' ?>">
                                                <?= date('d/m/Y', strtotime($e['expiracao'] ?? '2026-12-31')) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <!-- Grupo de botoes de acao -->
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" title="Configurar"><i class="bi bi-gear"></i></button>
                                                <button class="btn btn-sm btn-outline-danger" title="Suspender"><i class="bi bi-slash-circle"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape e scripts finais -->
