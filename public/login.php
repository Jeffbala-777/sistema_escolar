<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/models/usuarioModel.php';

$usuarioModel = new usuarioModel($pdo);
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $erro = 'Token CSRF inválido.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        $usuario = $usuarioModel->getByEmail($email);

        if ($usuario && (int)$usuario['ativo'] === 1 && password_verify($senha, $usuario['senha'])) {
            if (password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
                $novaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
                $stmt->execute(['senha' => $novaHash, 'id' => $usuario['id']]);
                $usuario['senha'] = $novaHash;
            }

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
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $erro = 'Login Inválido. Tente Novamente.';
} else {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $usuario = $usuarioModel->getByEmail($email);

    if ($usuario && (int)$usuario['ativo'] === 1 && password_verify($senha, $usuario['senha'])) {
        if (password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
            $novaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
            $stmt->execute(['senha' => $novaHash, 'id' => $usuario['id']]);
            $usuario['senha'] = $novaHash;
        }

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f0f0f0;">
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm border-0" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">Login</h4>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= e($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="position-relative">
                        <input type="password" id="senha" name="senha" class="form-control pe-5" required>
                        <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted"
                        id="toggleSenha" style="cursor:pointer;"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
</div>

<script>
const toggle = document.getElementById('toggleSenha');
const senha = document.getElementById('senha');

toggle.addEventListener('click', function () {
    if (senha.type === 'password') {
        senha.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
    } else {
        senha.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
    }
});

</script>
</body>
</html>