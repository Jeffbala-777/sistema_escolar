<?php
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/models/usuarioModel.php';
require_once __DIR__ . '/../app/models/notaModel.php';
require_once __DIR__ . '/../app/models/faltaModel.php';

$usuarioModel = new usuarioModel($pdo);
$notaModel = new notaModel($pdo);
$faltaModel = new faltaModel($pdo);

$professor_id = (int)$_SESSION['usuario']['id'];

$stmt = $pdo->prepare("
    SELECT pt.turma_id, t.nome AS turma_nome, pt.materia
    FROM professor_turma pt
    INNER JOIN turmas t ON t.id = pt.turma_id
    WHERE pt.professor_id = :professor_id
    ORDER BY t.nome, pt.materia
");
$stmt->execute(['professor_id' => $professor_id]);
$vinculos = $stmt->fetchAll();

$turma_id = (int)($_GET['turma_id'] ?? 0);
$materia = trim($_GET['materia'] ?? '');

$alunos = [];
if ($turma_id > 0 && $materia !== '') {
    $alunos = $usuarioModel->listarAlunosPorTurma($turma_id);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Professor: <?= e($_SESSION['usuario']['nome_completo']) ?></h3>
        <a href="/sistema_escolar/public/logout.php" class="btn btn-outline-danger">Sair</a>
    </div>

    <?php if (!$turma_id || !$materia): ?>
        <div class="card">
            <div class="card-body">
                <h5>Selecione uma turma e matéria</h5>
                <div class="list-group">
                    <?php foreach ($vinculos as $v): ?>
                        <a class="list-group-item list-group-item-action"
                           href="?turma_id=<?= (int)$v['turma_id'] ?>&materia=<?= urlencode($v['materia']) ?>">
                            <?= e($v['turma_nome']) ?> - <?= e($v['materia']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5>Turma: <?= e($alunos[0]['turma_nome'] ?? '') ?> | Matéria: <?= e($materia) ?></h5>
            </div>
        </div>

        <form method="POST" action="/sistema_escolar/professor/lancar_notas.php">
            <input type="hidden" name="professor_id" value="<?= $professor_id ?>">
            <input type="hidden" name="turma_id" value="<?= $turma_id ?>">
            <input type="hidden" name="materia" value="<?= e($materia) ?>">

            <table class="table table-bordered table-hover align-middle bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Aluno</th>
                        <th>B1</th>
                        <th>B2</th>
                        <th>B3</th>
                        <th>B4</th>
                        <th>Falta hoje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                        <?php $notasAluno = $notaModel->getNotasAluno((int)$aluno['id']); ?>
                        <tr>
                            <td><?= e($aluno['nome_completo']) ?></td>
                            <?php for ($b = 1; $b <= 4; $b++): 
                                $valor = '';
                                foreach ($notasAluno as $n) {
                                    if ($n['materia'] === $materia && (int)$n['bimestre'] === $b) {
                                        $valor = $n['nota'];
                                        break;
                                    }
                                }
                            ?>
                                <td>
                                    <input type="number" min="0" max="10" step="0.1"
                                           name="nota[<?= (int)$aluno['id'] ?>][<?= $b ?>]"
                                           class="form-control form-control-sm"
                                           value="<?= e($valor) ?>">
                                </td>
                            <?php endfor; ?>
                            <td class="text-center">
                                <input type="checkbox" name="falta[<?= (int)$aluno['id'] ?>]" value="1">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button class="btn btn-primary" type="submit">Salvar notas e faltas</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>