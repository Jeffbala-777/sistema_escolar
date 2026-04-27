<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/models/usuarioModel.php';

$usuarioModel = new usuarioModel($pdo);
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $usuario = $usuarioModel->getByEmail($email);

    if ($usuario && (int)$usuario['ativo'] === 1 && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;

        $tipo = $usuario['tipo'];
        if (in_array($tipo, ['admin', 'admin_supremo'], true)) {
            header('Location: /sistema_escolar/admin/dashboard.php');
        } elseif ($tipo === 'professor') {
            header('Location: /sistema_escolar/professor/dashboard.php');
        } else {
            header('Location: /sistema_escolar/aluno/dashboard.php');
        }
        exit;
    } else {
        $erro = 'Login inválido.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4">
            <h4 class="mb-4 text-center">Entrar</h4>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= e($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>

                <button class="btn btn-primary w-100" type="submit">Entrar</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>