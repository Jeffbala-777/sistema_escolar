<?php
declare(strict_types=1);

// Protege acesso do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';

$id = (int)($_GET['id'] ?? 0); // ID do aluno
$usuarioModel = new UsuarioModel($pdo);
$aluno = $usuarioModel->buscarPorId($id); // Busca dados

if (!$aluno) { exit('Aluno não encontrado.'); }

$mensagem = $erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'perfil_id' => $aluno['perfil_id'],
        'nome_completo' => $_POST['nome'],
        'email' => $_POST['email'],
        'cpf' => $_POST['cpf'],
        'telefone' => $_POST['telefone'],
        'nascimento' => $_POST['nascimento'],
        'ativo' => (int)$_POST['ativo']
    ];
    
    if ($usuarioModel->atualizar($id, $dados)) {
        $mensagem = 'Dados atualizados com sucesso!';
        $aluno = $usuarioModel->buscarPorId($id); // Recarrega
    } else {
        $erro = 'Erro ao atualizar dados.';
    }
}

$title = 'Editar Aluno';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="main-content flex-grow-1">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
        <div class="p-4">
            <div class="page-card p-4 shadow-sm bg-white rounded">
                <h4>Editar Aluno: <?= e($aluno['nome_completo']) ?></h4>
                <?php if ($mensagem): ?><div class="alert alert-success"><?= $mensagem ?></div><?php endif; ?>
                <?php if ($erro): ?><div class="alert alert-danger"><?= $erro ?></div><?php endif; ?>

                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nome completo</label>
                            <input type="text" name="nome" class="form-control" value="<?= e($aluno['nome_completo']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select name="ativo" class="form-select">
                                <option value="1" <?= $aluno['ativo'] == 1 ? 'selected' : '' ?>>Ativo</option>
                                <option value="0" <?= $aluno['ativo'] == 0 ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= e($aluno['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">CPF</label>
                            <input type="text" name="cpf" class="form-control" value="<?= e($aluno['cpf'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="<?= e($aluno['telefone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nascimento</label>
                            <input type="date" name="nascimento" class="form-control" value="<?= e($aluno['nascimento'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        <a href="alunos.php" class="btn btn-secondary">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
