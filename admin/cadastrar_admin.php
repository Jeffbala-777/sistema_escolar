<?php
require_once __DIR__ . '/../app/config/config.php';
// Se quiser proteger: require_once __DIR__ . '/../app/middleware/verificar_admin.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-custom { border: 1px solid #dee2e6; border-radius: 0; }
        .bg-gray-light { background-color: #f4f4f4; }
    </style>
</head>
<body class="p-4">

<div class="card card-custom shadow-sm mx-auto" style="max-width: 500px;">
    <div class="card-header bg-gray-light fw-bold">Cadastro de Administrador</div>
    <div class="card-body">
        <form method="POST" action="processar_admin.php">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            
            <div class="mb-3">
                <label class="form-label">Nome Completo</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
        </form>
    </div>
</div>

</body>
</html>