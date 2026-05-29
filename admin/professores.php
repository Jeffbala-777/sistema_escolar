<?php
declare(strict_types=1); // Forca tipagem estrita

// Protege acesso do admin da escola
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php'; // Banco
require_once __DIR__ . '/../app/models/TurmaModel.php'; // Turmas
require_once __DIR__ . '/../app/models/UsuarioModel.php'; // Usuarios

$turmaModel = new TurmaModel($pdo); // Inicia models
$usuarioModel = new UsuarioModel($pdo);

$escolaId = (int)$_SESSION['usuario']['escola_id']; // ID da escola logada
$turmas = $turmaModel->listarPorEscola($escolaId); // Lista turmas da escola

$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0; // Turma escolhida
$professores = [];

if ($turmaId > 0) { // Se escolheu turma, busca professores vinculados a ela
    // Busca professores da turma via tabela de vinculos
    $stmt = $pdo->prepare("SELECT DISTINCT u.* FROM usuarios u 
                           INNER JOIN professor_turma_disciplina ptd ON ptd.professor_id = u.id 
                           WHERE ptd.turma_id = :tid AND u.ativo = 1");
    $stmt->execute([':tid' => $turmaId]);
    $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$title = 'Gestão de Professores';
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?> <!-- Menu lateral -->

    <div class="main-content flex-grow-1">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Painel superior -->

        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Gerenciar Professores</h3>
                <a href="cadastrar_professor.php" class="btn btn-primary btn-sm">Novo Professor</a>
            </div>

            <!-- Filtro por Turma (Igual ao do Professor/Aluno) -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-bold">Selecione a Turma para ver os Professores</label>
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

            <!-- Lista de Professores (so aparece se escolher turma) -->
            <?php if ($turmaId > 0): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Professores da Turma Selecionada</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($professores as $p): ?>
                                        <tr>
                                            <td><strong><?= e($p['nome_completo']) ?></strong></td>
                                            <td><?= e($p['email']) ?></td>
                                            <td><?= e($p['telefone'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary">Ver Perfil</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($professores)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum professor vinculado a esta turma.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-info-circle display-4 d-block mb-3"></i>
                    Selecione uma turma acima para listar os professores vinculados.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape -->
