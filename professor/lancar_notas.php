<?php
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/models/notaModel.php';
require_once __DIR__ . '/../app/models/faltaModel.php';

$notaModel = new notaModel($pdo);
$faltaModel = new faltaModel($pdo);

$professor_id = (int)($_POST['professor_id'] ?? 0);
$turma_id = (int)($_POST['turma_id'] ?? 0);
$materia = trim($_POST['materia'] ?? '');

$notas = $_POST['nota'] ?? [];
$faltas = $_POST['falta'] ?? [];

if ($professor_id <= 0 || $turma_id <= 0 || $materia === '') {
    header('Location: /sistema_escolar/professor/dashboard.php');
    exit;
}

$pdo->beginTransaction();

try {
    foreach ($notas as $aluno_id => $bimestres) {
        $aluno_id = (int)$aluno_id;
        foreach ($bimestres as $bimestre => $nota) {
            $nota = (float)$nota;
            if ($nota >= 0 && $nota <= 10) {
                $notaModel->salvarOuAtualizar($aluno_id, $professor_id, $materia, (int)$bimestre, $nota);
            }
        }
    }

    foreach ($faltas as $aluno_id => $valor) {
        $faltaModel->registrar((int)$aluno_id, $turma_id, 'falta');
    }

    $pdo->commit();
    header('Location: /sistema_escolar/professor/dashboard.php?turma_id=' . $turma_id . '&materia=' . urlencode($materia));
    exit;
} catch (Throwable $e) {
    $pdo->rollBack();
    die('Erro ao salvar dados.');
}