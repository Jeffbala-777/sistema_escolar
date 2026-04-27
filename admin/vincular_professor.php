<?php
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/models/usuarioModel.php';

$usuarioModel = new usuarioModel($pdo);
$professores = $usuarioModel->listarProfessores();
$turmas = $usuarioModel->listarTurmas();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $erro = 'Token CSRF inválido.';
    } else {
        $professor_id = (int)($_POST['professor_id'] ?? 0);
        $turma_id = (int)($_POST['turma_id'] ?? 0);
        $materia = trim($_POST['materia'] ?? '');

        if ($professor_id <= 0 || $turma_id <= 0 || $materia === '') {
            $erro = 'Preencha todos os campos.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO professor_turma (professor_id, turma_id, materia) VALUES (:professor_id, :turma_id, :materia)");
            try {
                $stmt->execute([
                    'professor_id' => $professor_id,
                    'turma_id' => $turma_id,
                    'materia' => $materia
                ]);
                $sucesso = 'Professor vinculado com sucesso.';
            } catch (PDOException $e) {
                $erro = 'Esse vínculo já existe ou houve erro ao salvar.';
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
    <title>Vincular Professor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 720px;">
    <h3 class="mb-4">Vincular Professor à Turma / Matéria</h3>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= e($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= e($sucesso) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
            <label class="form-label">Professor</label>
            <select name="professor_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($professores as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= e($p['nome_completo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Turma</label>
            <select name="turma_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Matéria</label>
            <input type="text" name="materia" class="form-control" required>
        </div>

        <button class="btn btn-primary" type="submit">Salvar vínculo</button>
        <div class="footer-actions">
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm px-4">Voltar</a>
</div>
    </form>
</div>
</body>
</html>