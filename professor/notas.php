<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);

$professorId = (int)$_SESSION['usuario']['id'];
$escolaId = (int)$_SESSION['usuario']['escola_id'];
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : null;
$disciplinaId = isset($_GET['disciplina_id']) ? (int)$_GET['disciplina_id'] : null;

// Se tiver turma_id, valida se o professor tem acesso
if ($turmaId) {
    $turmasProfessor = $ptdModel->listarTurmasProfessor($professorId, $escolaId);
    $temAcesso = false;
    foreach ($turmasProfessor as $tp) {
        if ((int)$tp['turma_id'] === $turmaId) {
            $temAcesso = true;
            break;
        }
    }
    if (!$temAcesso) {
        header('Location: selecionar_turma.php');
        exit;
    }
}

// Buscar disciplinas do professor nesta turma
$sqlDisciplinas = "
    SELECT DISTINCT d.id, d.nome
    FROM disciplinas d
    INNER JOIN professor_turma_disciplina ptd ON ptd.disciplina_id = d.id
    WHERE ptd.professor_id = :professor_id
    AND ptd.turma_id = :turma_id
    AND ptd.escola_id = :escola_id
    ORDER BY d.nome
";

$stmtDisciplinas = $pdo->prepare($sqlDisciplinas);
$stmtDisciplinas->execute([
    ':professor_id' => $professorId,
    ':turma_id' => $turmaId,
    ':escola_id' => $escolaId
]);
$disciplinas = $stmtDisciplinas->fetchAll();

// Se não houver disciplina selecionada, usar a primeira
if (!$disciplinaId && !empty($disciplinas)) {
    $disciplinaId = $disciplinas[0]['id'];
}

// Buscar notas dos alunos da disciplina selecionada
$sql = "
    SELECT 
        u.id,
        u.nome_completo,
        AVG(n.nota) as media,
        COUNT(n.id) as total_notas
    FROM usuarios u
    INNER JOIN matriculas m ON m.aluno_id = u.id
    LEFT JOIN notas n ON n.aluno_id = u.id AND n.disciplina_id = :disciplina_id
    WHERE m.turma_id = :turma_id
    AND m.escola_id = :escola_id
    AND m.status = 'ativa'
    GROUP BY u.id, u.nome_completo
    ORDER BY media DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':turma_id' => $turmaId,
    ':escola_id' => $escolaId,
    ':disciplina_id' => $disciplinaId
]);
$notasAlunos = $stmt->fetchAll();

// Buscar informações da turma
$sqlTurma = "
    SELECT DISTINCT t.nome, t.serie, d.nome as disciplina
    FROM turmas t
    INNER JOIN professor_turma_disciplina ptd ON ptd.turma_id = t.id
    INNER JOIN disciplinas d ON d.id = ptd.disciplina_id
    WHERE t.id = :turma_id
    AND ptd.professor_id = :professor_id
    LIMIT 1
";

$stmtTurma = $pdo->prepare($sqlTurma);
$stmtTurma->execute([':turma_id' => $turmaId, ':professor_id' => $professorId]);
$turmaInfo = $stmtTurma->fetch();

// Buscar nome da disciplina selecionada
$disciplinaSelecionada = null;
foreach ($disciplinas as $d) {
    if ($d['id'] == $disciplinaId) {
        $disciplinaSelecionada = $d['nome'];
        break;
    }
}

$title = 'Notas - ' . ($turmaInfo ? $turmaInfo['nome'] : 'Turmas');
require_once __DIR__ . '/../partials/header.php';
?>

<?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
<?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

<div class="main-content" style="background-color: #f5f5f5; min-height: 100vh; padding: 20px;">

    <div class="container-notas" style="max-width: 1000px; margin: 0 auto;">
        
        <div class="mb-4">
            <a href="selecionar_turma.php" class="btn btn-link text-decoration-none p-0 text-secondary fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Voltar para seleção de turma
            </a>
        </div>

        <!-- Cabeçalho -->
        <div class="bg-white shadow-sm rounded-3 p-4 mb-4 border-bottom border-3 border-primary">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                        <i class="bi bi-file-earmark-text fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Notas</h4>
                        <?php if ($turmaInfo): ?>
                            <p class="text-muted mb-0">Turma: <span class="fw-bold"><?= e($turmaInfo['nome']) ?></span></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Seletor de Disciplinas -->
                <?php if (!empty($disciplinas)): ?>
                <div class="d-flex gap-2 align-items-center">
                    <label class="text-muted fw-bold mb-0">Disciplina:</label>
                    <div class="btn-group" role="group">
                        <?php foreach ($disciplinas as $d): ?>
                            <a href="?turma_id=<?= $turmaId ?>&disciplina_id=<?= $d['id'] ?>" 
                               class="btn btn-sm <?= $d['id'] == $disciplinaId ? 'btn-primary' : 'btn-outline-primary' ?>" 
                               style="border-radius: 8px;">
                                <?= e($d['nome']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabela de Notas -->
        <div class="bg-white shadow-sm rounded-3 p-4">
            <h5 class="fw-bold text-secondary mb-4"><i class="bi bi-list-check me-2"></i> Desempenho dos Alunos <?php if ($disciplinaSelecionada): ?> - <?= e($disciplinaSelecionada) ?><?php endif; ?></h5>
            
            <?php if (!empty($notasAlunos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Aluno</th>
                                <th class="text-center">Média</th>
                                <th class="text-center">Total de Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notasAlunos as $aluno): ?>
                            <tr>
                                <td class="fw-bold"><?= e($aluno['nome_completo']) ?></td>
                                <td class="text-center">
                                    <?php if ($aluno['media'] == 0 || $aluno['media'] === null): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning fw-bold">N/A</span>
                                    <?php else: ?>
                                        <span class="badge bg-<?= (float)$aluno['media'] >= 6 ? 'success' : 'danger' ?> bg-opacity-10 text-<?= (float)$aluno['media'] >= 6 ? 'success' : 'danger' ?> fw-bold">
                                            <?= number_format((float)$aluno['media'], 1) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        <?= $aluno['total_notas'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <p>Nenhum aluno encontrado nesta turma.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
