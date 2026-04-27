<?php
require_once __DIR__ . '/../app/middleware/verificar_aluno.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Aluno - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Olá, <?= e($_SESSION['usuario']['nome_completo']) ?></h3>
        <a href="/sistema_escolar/public/logout.php" class="btn btn-outline-danger">Sair</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Boletim e desempenho</h5>
            <p>Veja suas notas, faltas e média geral.</p>
            <a href="/sistema_escolar/aluno/boletim.php" class="btn btn-primary">Abrir boletim</a>
        </div>
    </div>
</div>
</body>
</html>