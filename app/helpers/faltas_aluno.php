<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';

header('Content-Type: application/json');

$alunoId = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : 0;
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
$escolaId = $_SESSION['usuario']['escola_id'];

if (!$alunoId || !$turmaId) {
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit;
}

// Buscar faltas do aluno por mês
$sql = "
    SELECT 
        DATE_FORMAT(a.data_aula, '%m/%Y') as mes,
        COUNT(p.id) as total_faltas
    FROM presencas p
    INNER JOIN aulas a ON a.id = p.aula_id
    INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
    WHERE p.aluno_id = :aluno_id
    AND ptd.turma_id = :turma_id
    AND p.status = 'falta'
    AND p.escola_id = :escola_id
    GROUP BY DATE_FORMAT(a.data_aula, '%m/%Y')
    ORDER BY a.data_aula DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':aluno_id' => $alunoId,
    ':turma_id' => $turmaId,
    ':escola_id' => $escolaId
]);

$faltas = $stmt->fetchAll();

$meses = array_map(fn($f) => $f['mes'], $faltas);
$dados = array_map(fn($f) => (int)$f['total_faltas'], $faltas);

echo json_encode([
    'meses' => $meses,
    'faltas' => $dados
]);
