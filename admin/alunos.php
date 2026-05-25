<?php
declare(strict_types=1);

// Protege acesso do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php'; // Banco
require_once __DIR__ . '/../app/models/TurmaModel.php'; // Model turmas
require_once __DIR__ . '/../app/models/UsuarioModel.php'; // Model usuarios

$turmaModel = new TurmaModel($pdo); // Inicia models
$usuarioModel = new UsuarioModel($pdo);

$escolaId = (int)$_SESSION['usuario']['escola_id']; // ID da escola logada
$turmas = $turmaModel->listarPorEscola($escolaId); // Lista turmas da escola

$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0; // Turma escolhida
$alunos = [];

if ($turmaId > 0) { // Se escolheu turma, busca alunos
    $alunos = $usuarioModel->listarPorTurma($turmaId); 
}

$title = 'Gestão de Alunos';
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?> <!-- Menu -->

    <div class="main-content flex-grow-1">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Gerenciar Alunos</h3>
                <a href="cadastrar_alunos.php" class="btn btn-primary btn-sm">Novo Aluno</a>
            </div>

            <!-- Filtro por Turma (Estilo Professor) -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-bold">Selecione a Turma para ver os Alunos</label>
                            <select name="turma_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Escolha uma Turma --</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $turmaId == $t['id'] ? 'selected' : '' ?>>
                                        <?= e($t['nome']) ?> (<?= e($t['serie']) ?> - <?= e($t['turno']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Alunos (so aparece se escolher turma) -->
            <?php if ($turmaId > 0): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Alunos da Turma Selecionada</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alunos as $a): ?>
                                        <tr>
                                            <td><?= $a['id'] ?></td>
                                            <td><strong><?= e($a['nome_completo']) ?></strong></td>
                                            <td><?= e($a['email']) ?></td>
                                            <td><?= e($a['telefone'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <a href="editar_aluno.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($alunos)): ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum aluno nesta turma.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-info-circle display-4 d-block mb-3"></i>
                    Selecione uma turma acima para listar os alunos.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
