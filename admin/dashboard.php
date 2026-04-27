<?php
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/models/usuarioModel.php';

$usuarioModel = new usuarioModel($pdo);
$turmas = $usuarioModel->listarTurmas();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Olá, <?= e($_SESSION['usuario']['nome_completo']) ?></h3>
        <a href="/sistema_escolar/public/logout.php" class="btn btn-outline-danger">Sair</a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Alunos</h5>
                    <p>Cadastrar alunos automaticamente.</p>
                    <a href="/sistema_escolar/admin/cadastrar_aluno.php" class="btn btn-primary">Abrir</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Professores</h5>
                    <p>Vincular professor a turma e matéria.</p>
                    <a href="/sistema_escolar/admin/vincular_professor.php" class="btn btn-success">Abrir</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Turmas</h5>
                    <p>Listar turmas cadastradas.</p>
                    <a href="/sistema_escolar/admin/turmas.php" class="btn btn-info">Abrir</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>