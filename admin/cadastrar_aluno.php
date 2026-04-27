<?php
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/models/usuarioModel.php';

$usuarioModel = new usuarioModel($pdo);
$turmas = $usuarioModel->listarTurmas();
$erro = '';
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $erro = 'Token CSRF inválido.';
    } else {
        $nome_completo = trim($_POST['nome_completo'] ?? '');
        $turma_id = (int)($_POST['turma_id'] ?? 0);

        if ($nome_completo === '' || $turma_id <= 0) {
            $erro = 'Preencha todos os campos.';
        } else {
            $email = gerarEmailPadrao($nome_completo);
            $senha = gerarSenhaAleatoria();
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $ok = $usuarioModel->insert([
                'nome_completo' => $nome_completo,
                'email' => $email,
                'senha' => $hash,
                'tipo' => 'aluno',
                'turma_id' => $turma_id,
                'ativo' => 1
            ]);

            if ($ok) {
                $sucesso = compact('nome_completo', 'email', 'senha');
            } else {
                $erro = 'Erro ao cadastrar aluno.';
            }
        }
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 720px;">
    <h3 class="mb-4">Cadastrar Aluno</h3>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= e($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="alert alert-success">
            <strong>Aluno cadastrado com sucesso!</strong><br>
            Nome: <?= e($sucesso['nome_completo']) ?><br>
            E-mail: <?= e($sucesso['email']) ?><br>
            Senha: <code><?= e($sucesso['senha']) ?></code>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
            <label class="form-label">Nome completo</label>
            <input type="text" name="nome_completo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Turma</label>
            <select name="turma_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($turmas as $turma): ?>
                    <option value="<?= (int)$turma['id'] ?>"><?= e($turma['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="btn btn-primary" type="submit">Cadastrar</button>
        <div class="footer-actions">
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm px-4">Voltar</a>
</div>
    </form>
</div>

<style>
.footer-actions{
    position: fixed;
    left: 0;
    right: 0;
    bottom: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0 40px;
    z-index: 999;
}
</style>

</body>
</html>