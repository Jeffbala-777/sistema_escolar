<?php
declare(strict_types=1); // Forca tipagem estrita no PHP

// Verifica se o usuario logado e um admin supremo
require_once __DIR__ . '/../app/middleware/verificar_admin_supremo.php';
// Conecta com o banco de dados via PDO
require_once __DIR__ . '/../app/database/database.php';
// Carrega o modelo de dados do admin supremo
require_once __DIR__ . '/../app/models/AdminSupremoModel.php';

$adminModel = new AdminSupremoModel($pdo); // Instancia o model com a conexao
$stats = $adminModel->buscarKpisSaaS(); // Busca estatisticas globais (escolas/alunos)
$logs = $adminModel->listarLogsRecentes(5); // Busca os 5 ultimos registros de log

$title = 'Dashboard Admin Supremo'; // Define o titulo da pagina
require_once __DIR__ . '/../partials/header.php'; // Inclui o topo do site
?>

<div class="d-flex"> <!-- Container flexivel para sidebar e conteudo -->
    <?php require_once __DIR__ . '/sidebar.php'; ?> <!-- Inclui o menu lateral padronizado -->

    <div class="main-content flex-grow-1"> <!-- Area principal do conteudo -->
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Painel superior com toggle -->

        <div class="p-4"> <!-- Espacamento interno do conteudo -->
            <h3 class="mb-4">Visão Geral do Sistema</h3> <!-- Titulo da secao -->

            <div class="row g-4 mb-4"> <!-- Grid para os cards de resumo -->
                <!-- Card que mostra o total de escolas ativas -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary text-white p-3 rounded-3 me-3">
                                <i class="bi bi-building fs-3"></i> <!-- Icone de predio -->
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Escolas Ativas</h6> <!-- Rotulo -->
                                <h2 class="mb-0 fw-bold"><?= $stats['escolas_ativas'] ?></h2> <!-- Valor vindo do banco -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card que mostra o total de alunos no sistema todo -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success text-white p-3 rounded-3 me-3">
                                <i class="bi bi-people fs-3"></i> <!-- Icone de pessoas -->
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Total de Alunos</h6> <!-- Rotulo -->
                                <h2 class="mb-0 fw-bold"><?= $stats['total_alunos'] ?></h2> <!-- Valor vindo do banco -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row"> <!-- Linha para a tabela de logs -->
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold">Atividades Recentes</h6> <!-- Titulo da tabela -->
                        </div>
                        <div class="card-body">
                            <div class="table-responsive"> <!-- Tabela responsiva para mobile -->
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Usuário</th> <!-- Coluna nome -->
                                            <th>Ação</th> <!-- Coluna o que fez -->
                                            <th>Data/Hora</th> <!-- Coluna quando fez -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?> <!-- Percorre cada log encontrado -->
                                            <tr>
                                                <td><?= e($log['nome_completo']) ?></td> <!-- Nome do usuario logado -->
                                                <td><span class="badge bg-light text-dark"><?= e($log['acao']) ?></span></td> <!-- Acao realizada -->
                                                <td><small><?= date('d/m/Y H:i', strtotime($log['data_criacao'])) ?></small></td> <!-- Data formatada -->
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
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Inclui o rodape e scripts -->
