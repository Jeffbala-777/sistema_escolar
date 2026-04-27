<?php
require_once __DIR__ . '/../app/middleware/verificar_aluno.php';
require_once __DIR__ . '/../app/models/NotaModel.php';
require_once __DIR__ . '/../app/models/FaltaModel.php';

$notaModel = new NotaModel($pdo);
$faltaModel = new FaltaModel($pdo);

$aluno_id = (int)$_SESSION['usuario']['id'];
$notas = $notaModel->getNotasAluno($aluno_id);
$faltas_total = $faltaModel->contarFaltasAluno($aluno_id);

// ... (Mantenha a lógica de processamento $porMateria que já fizemos) ...
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Boletim Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: sans-serif; }
        .card-custom { border: 1px solid #dee2e6; border-radius: 0; }
        .bg-gray-light { background-color: #f4f4f4; color: #333; }
        .table thead { background-color: #f4f4f4; }
    </style>
</head>
<body class="p-4">

<!-- Card de Informações do Aluno (Visual da Foto) -->
<div class="card card-custom shadow-sm mb-4">
    <div class="card-header bg-gray-light fw-bold text-uppercase">Informações do Aluno</div>
    <div class="card-body">
        <p class="mb-1"><strong>Nome:</strong> <?= e($_SESSION['usuario']['nome_completo']) ?></p>
        <p class="mb-1"><strong>Matrícula:</strong> <?= (int)$_SESSION['usuario']['id'] ?></p>
        <p class="mb-0"><strong>Turma:</strong> <?= e($_SESSION['usuario']['turma_nome'] ?? 'Ensino Médio - 2ª Série') ?></p>
    </div>
</div>

<!-- Card do Boletim (Visual da Foto) -->
<div class="card card-custom shadow-sm">
    <div class="card-header bg-gray-light fw-bold text-uppercase">Boletins Escolares</div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th>Matéria</th>
                    <th>B1</th>
                    <th>B2</th>
                    <th>B3</th>
                    <th>B4</th>
                    <th>Média</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($porMateria as $materia => $dados): ?>
                <tr>
                    <td><?= e($materia) ?></td>
                    <td><?= $dados['notas'][1] ?? '-' ?></td>
                    <td><?= $dados['notas'][2] ?? '-' ?></td>
                    <td><?= $dados['notas'][3] ?? '-' ?></td>
                    <td><?= $dados['notas'][4] ?? '-' ?></td>
                    <td class="fw-bold"><?= e($dados['media']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Botões de Ação Centralizados -->
<div class="footer-actions">
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm px-4">Voltar</a>
</div>

</body>
</html>