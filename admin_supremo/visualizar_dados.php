<?php

declare(strict_types=1);

// Protege acesso
require_once __DIR__ . '/../app/middleware/verificar_admin_supremo.php';
require_once __DIR__ . '/../app/database/database.php'; // Conecta banco
require_once __DIR__ . '/../app/models/EscolaModel.php'; // Model escolas
require_once __DIR__ . '/../app/models/UsuarioModel.php'; // Model usuarios

$escolaModel = new EscolaModel($pdo); // Inicia modelos
$usuarioModel = new UsuarioModel($pdo);

$escolas = $escolaModel->listarTodas(); // Lista todas as escolas pro select
$escola_id = isset($_GET['escola_id']) ? (int)$_GET['escola_id'] : 0; // Pega escola escolhida
$dados_escola = null;
$professores = [];
$alunos = [];
$turmas = [];

if ($escola_id > 0) { // Se escolheu uma escola
    // Pega info da escola
    $stmt = $pdo->prepare("SELECT * FROM escolas WHERE id = :id");
    $stmt->execute([':id' => $escola_id]);
    $dados_escola = $stmt->fetch();

    if ($dados_escola) {
        // Pega professores ativos dessa escola
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE escola_id = :escola_id AND perfil_id = 3 AND ativo = 1");
        $stmt->execute([':escola_id' => $escola_id]);
        $professores = $stmt->fetchAll();

        // Pega alunos ativos dessa escola
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE escola_id = :escola_id AND perfil_id = 4 AND ativo = 1");
        $stmt->execute([':escola_id' => $escola_id]);
        $alunos = $stmt->fetchAll();

        // Pega turmas e o ano letivo (JOIN evita erro de coluna faltando)
        $stmt = $pdo->prepare("SELECT t.*, al.ano as ano_letivo FROM turmas t JOIN anos_letivos al ON al.id = t.ano_letivo_id WHERE t.escola_id = :escola_id AND t.ativo = 1");
        $stmt->execute([':escola_id' => $escola_id]);
        $turmas = $stmt->fetchAll();
    }
}

$title = 'Visualizar Dados das Unidades - Admin Supremo'; // Titulo
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/sidebar.php'; ?> <!-- Menu -->

    <div class="main-content">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Painel topo -->

        <div class="content-wrapper">
            <div class="container-fluid p-4">
                <div class="mb-4">
                    <h1>Acesso Global aos Dados</h1>
                    <p class="text-muted">Visualize os dados internos de qualquer unidade escolar.</p>
                </div>

                <!-- Select de escolha da escola -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">Selecione a Escola</label>
                                <select name="escola_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- Escolha uma unidade --</option>
                                    <?php foreach ($escolas as $e): ?>
                                        <option value="<?= $e['id'] ?>" <?= $escola_id == $e['id'] ? 'selected' : '' ?>>
                                            <?= e($e['nome']) ?> (<?= e($e['cidade'] ?? 'N/A') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Carregar Dados</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($dados_escola): ?> <!-- Se tem escola selecionada -->
                    <div class="row">
                        <!-- Card Professores -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-mortarboard text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h5>Professores</h5>
                                    <h2 class="display-6"><?= count($professores) ?></h2>
                                </div>
                            </div>
                        </div>
                        <!-- Card Alunos -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-people text-success mb-2" style="font-size: 2rem;"></i>
                                    <h5>Alunos</h5>
                                    <h2 class="display-6"><?= count($alunos) ?></h2>
                                </div>
                            </div>
                        </div>
                        <!-- Card Turmas -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-grid text-info mb-2" style="font-size: 2rem;"></i>
                                    <h5>Turmas</h5>
                                    <h2 class="display-6"><?= count($turmas) ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Abas de conteudo -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <ul class="nav nav-tabs card-header-tabs" id="dataTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" id="prof-tab" data-bs-toggle="tab" data-bs-target="#prof-content" type="button">Professores</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="aluno-tab" data-bs-toggle="tab" data-bs-target="#aluno-content" type="button">Alunos</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="turma-tab" data-bs-toggle="tab" data-bs-target="#turma-content" type="button">Turmas</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body tab-content" id="dataTabsContent">
                            <!-- Lista de Professores -->
                            <div class="tab-pane fade show active" id="prof-content">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($professores as $p): ?>
                                            <tr>
                                                <td><?= e($p['nome_completo']) ?></td>
                                                <td><?= e($p['email']) ?></td>
                                                <td>
                                                    <a href="usuarios.php?acao=editar&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Lista de Alunos -->
                            <div class="tab-pane fade" id="aluno-content">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alunos as $a): ?>
                                            <tr>
                                                <td><?= e($a['nome_completo']) ?></td>
                                                <td><?= e($a['email']) ?></td>
                                                <td>
                                                    <a href="usuarios.php?acao=editar&id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Lista de Turmas -->
                            <div class="tab-pane fade" id="turma-content">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Ano Letivo</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($turmas as $t): ?>
                                            <tr>
                                                <td><?= e($t['nome']) ?></td>
                                                <td><?= e($t['ano_letivo'] ?? 'N/A') ?></td> <!-- Mostra ano letivo -->
                                                <td><span class="badge bg-success">Ativa</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?> <!-- Se nao escolheu escola ainda -->
                    <div class="alert alert-info text-center py-5">
                        <i class="bi bi-info-circle display-4 d-block mb-3"></i>
                        Selecione uma escola acima para visualizar os dados detalhados.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape -->
