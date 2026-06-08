<?php
// Ativa tipagem estrita para segurança
declare(strict_types=1);

// Middleware de autenticação do professor
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
// Conexão com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model de Turmas
require_once __DIR__ . '/../app/models/TurmaModel.php';
// Model de Disciplinas
require_once __DIR__ . '/../app/models/DisciplinaModel.php';

// IDs vindos da URL
$escolaId = (int)$_SESSION['usuario']['escola_id'];
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
$disciplinaId = isset($_GET['disciplina_id']) ? (int)$_GET['disciplina_id'] : 0;
$periodoId = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0;

// Validação básica
if ($turmaId <= 0 || $disciplinaId <= 0) {
    header('Location: selecionar_turma.php');
    exit;
}

// Busca informações da escola para saber o tipo de período e escala de nota
$sqlEscola = "SELECT tipo_periodo, escala_nota FROM escolas WHERE id = ?";
$stmtEscola = $pdo->prepare($sqlEscola);
$stmtEscola->execute([$escolaId]);
$escola = $stmtEscola->fetch();
$tipoPeriodo = $escola['tipo_periodo'] ?? 'bimestral';

// Busca informações da turma
$sqlTurma = "SELECT nome, serie, turno FROM turmas WHERE id = ? AND escola_id = ? LIMIT 1";
$stmtTurma = $pdo->prepare($sqlTurma);
$stmtTurma->execute([$turmaId, $escolaId]);
$turma = $stmtTurma->fetch();

// Busca informações do período se houver filtro
$periodoAtual = null;
if ($periodoId > 0) {
    $sqlP = "SELECT * FROM periodos_letivos WHERE id = ? AND escola_id = ?";
    $stmtP = $pdo->prepare($sqlP);
    $stmtP->execute([$periodoId, $escolaId]);
    $periodoAtual = $stmtP->fetch();
}

// Busca informações da disciplina
$sqlDisc = "SELECT nome FROM disciplinas WHERE id = ? AND escola_id = ? LIMIT 1";
$stmtDisc = $pdo->prepare($sqlDisc);
$stmtDisc->execute([$disciplinaId, $escolaId]);
$disciplina = $stmtDisc->fetch();

if (!$turma || !$disciplina) {
    header('Location: selecionar_turma.php');
    exit;
}

// Busca alunos da turma
$sqlAlunos = "
    SELECT 
        u.id,
        u.nome_completo,
        m.numero_matricula
    FROM usuarios u
    INNER JOIN matriculas m ON m.aluno_id = u.id
    WHERE m.turma_id = :turma_id
    AND m.escola_id = :escola_id
    AND m.status = 'ativa'
    ORDER BY u.nome_completo ASC
";
$pAlunos = [':turma_id' => $turmaId, ':escola_id' => $escolaId];
$stmtAlunos = $pdo->prepare($sqlAlunos);
$stmtAlunos->execute($pAlunos);
$alunosNotas = $stmtAlunos->fetchAll();

// Se periodo_id for 0, busca todos os períodos disponíveis
$periodosDisp = [];
if ($periodoId === 0) {
    $sqlPerios = "SELECT id, nome, ordem FROM periodos_letivos WHERE escola_id = ? AND ativo = 1 ORDER BY ordem ASC";
    $stmtPerios = $pdo->prepare($sqlPerios);
    $stmtPerios->execute([$escolaId]);
    $periodosDisp = $stmtPerios->fetchAll();
}

// Prepara dados de notas e faltas por período para cada aluno
$notasPorPeriodo = [];
$faltasPorPeriodo = [];
$mediaGeral = [];
if ($periodoId === 0 && !empty($periodosDisp)) {
    
    foreach ($alunosNotas as $aluno) {

    $notasPorPeriodo[$aluno['id']] = [];
    $faltasPorPeriodo[$aluno['id']] = [];

    $somaNotas = 0;
    $totalNotas = 0;
    $totalFaltas = 0;

    foreach ($periodosDisp as $p) {

        // MÉDIA DE NOTAS DO PERÍODO
        $sqlNP = "
            SELECT AVG(nota) as media
            FROM notas
            WHERE aluno_id = ?
            AND disciplina_id = ?
            AND periodo_id = ?
        ";

        $stmtNP = $pdo->prepare($sqlNP);
        $stmtNP->execute([
            $aluno['id'],
            $disciplinaId,
            $p['id']
        ]);

        $resNP = $stmtNP->fetch();

        $notaPeriodo = $resNP['media'] !== null
            ? (float)$resNP['media']
            : null;

        $notasPorPeriodo[$aluno['id']][$p['id']] = $notaPeriodo;

        if ($notaPeriodo !== null) {
            $somaNotas += $notaPeriodo;
            $totalNotas++;
        }

        // FALTAS DO PERÍODO
        $sqlFP = "
            SELECT COUNT(p.id) as total
            FROM presencas p
            INNER JOIN aulas a
                ON a.id = p.aula_id
            INNER JOIN professor_turma_disciplina ptd
                ON ptd.id = a.professor_turma_disciplina_id
            WHERE p.aluno_id = ?
            AND ptd.disciplina_id = ?
            AND p.status = 'falta'
            AND a.data_aula BETWEEN
                (
                    SELECT data_inicio
                    FROM periodos_letivos
                    WHERE id = ?
                )
            AND
                (
                    SELECT data_fim
                    FROM periodos_letivos
                    WHERE id = ?
                )
        ";

        $stmtFP = $pdo->prepare($sqlFP);
        $stmtFP->execute([
            $aluno['id'],
            $disciplinaId,
            $p['id'],
            $p['id']
        ]);

        $resFP = $stmtFP->fetch();

        $faltasPeriodo = (int)($resFP['total'] ?? 0);

        $faltasPorPeriodo[$aluno['id']][$p['id']] = $faltasPeriodo;

        $totalFaltas += $faltasPeriodo;
}

    // MÉDIA ARITMÉTICA
    $mediaGeral[$aluno['id']] =
    $totalNotas > 0
        ? round($somaNotas, 1)
        : null;

    // TOTAL DE FALTAS
    $faltasPorPeriodo[$aluno['id']]['total'] = $totalFaltas;
}
}
    
    else if ($periodoId > 0) {
    // Se há filtro de período, busca notas e faltas apenas daquele período
    foreach ($alunosNotas as $aluno) {
        $sqlNota = "SELECT AVG(nota) as media, COUNT(id) as total_notas FROM notas WHERE aluno_id = ? AND disciplina_id = ? AND periodo_id = ?";
        $stmtNota = $pdo->prepare($sqlNota);
        $stmtNota->execute([$aluno['id'], $disciplinaId, $periodoId]);
        $resNota = $stmtNota->fetch();
        $notasPorPeriodo[$aluno['id']][$periodoId] = $resNota['media'] !== null ? (float)$resNota['media'] : null;
        
        $sqlFalta = "SELECT COUNT(p.id) as total FROM presencas p 
                     INNER JOIN aulas a ON a.id = p.aula_id 
                     INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
                     WHERE p.aluno_id = ? AND ptd.disciplina_id = ? AND p.status = 'falta'
                     AND a.data_aula BETWEEN (SELECT data_inicio FROM periodos_letivos WHERE id = ?) 
                     AND (SELECT data_fim FROM periodos_letivos WHERE id = ?)";
        $stmtFalta = $pdo->prepare($sqlFalta);
        $stmtFalta->execute([$aluno['id'], $disciplinaId, $periodoId, $periodoId]);
        $resFalta = $stmtFalta->fetch();
        $faltasPorPeriodo[$aluno['id']][$periodoId] = (int)($resFalta['total'] ?? 0);
    }
}

$totalNotasTurma = 0;
$totalFaltasTurma = 0;
$quantidadeAlunosComNota = 0;

foreach ($mediaGeral as $valor) {
    if ($valor !== null) {
        $totalNotasTurma += $valor;
        $quantidadeAlunosComNota++;
    }
}

foreach ($faltasPorPeriodo as $dados) {
    $totalFaltasTurma += (int)($dados['total'] ?? 0);
}

$mediaTurma = $quantidadeAlunosComNota > 0
    ? round($totalNotasTurma / $quantidadeAlunosComNota, 1)
    : 0;
    
    $totalNotasTurma = 0;
$totalFaltasTurma = 0;
$quantidadeAlunosComNota = 0;

foreach ($mediaGeral as $valor) {
    if ($valor !== null) {
        $totalNotasTurma += $valor;
        $quantidadeAlunosComNota++;
    }
}

foreach ($faltasPorPeriodo as $dados) {
    $totalFaltasTurma += (int)($dados['total'] ?? 0);
}

$mediaTurma = $quantidadeAlunosComNota > 0
    ? round($totalNotasTurma / $quantidadeAlunosComNota, 1)
    : 0;

// Título da página
$title = 'Detalhes: ' . e($disciplina['nome']) . ' - ' . e($turma['nome']);
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            <!-- Navegação -->
            <div class="mb-4">
                <a href="desempenho.php?turma_id=<?= $turmaId ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar tela
                </a>
            </div>

            <!-- Cabeçalho Detalhado -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px; border-top: 5px solid #3498db;">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                        <i class="bi bi-journal-check fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-secondary mb-1"><?= e($disciplina['nome']) ?></h4>
                        <p class="text-muted mb-0">
                            Turma: <span class="fw-bold text-dark"><?= e($turma['nome']) ?></span> | 
                            Série: <span class="fw-bold text-dark"><?= e($turma['serie']) ?></span> | 
                            Turno: <span class="fw-bold text-dark"><?= e($turma['turno']) ?></span> |
                            Período: <span class="fw-bold text-primary"><?= $periodoAtual ? e($periodoAtual['nome']) : 'Todos os Períodos' ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabela de Alunos -->
            <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
                <h5 class="fw-bold text-secondary mb-4"><i class="bi bi-people me-2"></i> Rendimento dos Alunos</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nome do Aluno</th>
                                <?php if ($periodoId === 0 && !empty($periodosDisp)): ?>
                                    <?php foreach ($periodosDisp as $p): ?>
                                        <th class="text-center" colspan="2"><?= e($p['nome']) ?></th>
                                    <?php endforeach; ?>
                                        <th class="text-center">Média Geral</th>
                                        <th class="text-center">Total Faltas</th>
                                <?php else: ?>
                                    <th class="text-center">Média Atual</th>
                                    <th class="text-center">Total Faltas</th>
                                <?php endif; ?>
                            </tr>
                            <?php if ($periodoId === 0 && !empty($periodosDisp)): ?>
                            <tr>
                                <th></th>
                                <?php foreach ($periodosDisp as $p): ?>
                                    <th class="text-center small">Nota</th>
                                    <th class="text-center small">Faltas</th>
                                <?php endforeach; ?>
                            </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php foreach ($alunosNotas as $aluno): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= e($aluno['nome_completo']) ?></td>
                                <?php if ($periodoId === 0 && !empty($periodosDisp)): ?>
                                    <?php foreach ($periodosDisp as $p): 
                                        $nota = $notasPorPeriodo[$aluno['id']][$p['id']] ?? null;
                                        $faltas = $faltasPorPeriodo[$aluno['id']][$p['id']] ?? 0;
                                        
                                        // Meta Dinâmica para Cores
                                        $metaCor = 6.0;
                                        if ($tipoPeriodo === 'trimestral') {
                                            $ordemP = (int)($p['ordem'] ?? 0);
                                            $metaCor = ($ordemP === 3) ? 24.0 : 18.0;
                                        }
                                        
                                        $notaRuim = ($nota !== null && $nota < $metaCor);
                                    ?>
                                        <td class="text-center">
                                            <?php if ($nota !== null): ?>
                                                <span class="badge bg-light fs-6 px-2 <?= $notaRuim ? 'text-danger' : 'text-dark' ?>"><?= number_format($nota, 1) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">--</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6 px-2">
                                                <?= $faltas ?>
                                            </span>
                                        </td>
                                    <?php endforeach; ?>

<td class="text-center">
    <?php $mediaFinal = $mediaGeral[$aluno['id']] ?? null; ?>

    <?php if ($mediaFinal !== null): ?>

        <?php $mediaRuim = $mediaFinal < 18; ?>

        <span class="badge bg-light fs-6 px-2 <?= $mediaRuim ? 'text-danger' : 'text-dark' ?>">
            <?= number_format($mediaFinal, 1) ?>
        </span>

    <?php else: ?>

        <span class="text-muted small">--</span>

    <?php endif; ?>
</td>
        <td class="text-center">
    <span class="badge bg-warning bg-opacity-10 text-warning fs-6 px-2">
        <?= $faltasPorPeriodo[$aluno['id']]['total'] ?? 0 ?>
    </span>
</td>

<?php else: ?>
                                    <td class="text-center">
                                        <?php 
                                            $media = $notasPorPeriodo[$aluno['id']][$periodoId] ?? null;
                                            
                                            // Meta Dinâmica para Cores (Filtro Único)
                                            $metaCorU = 6.0;
                                            if ($tipoPeriodo === 'trimestral' && $periodoAtual) {
                                                $ordemU = (int)($periodoAtual['ordem'] ?? 0);
                                                $metaCorU = ($ordemU === 3) ? 24.0 : 18.0;
                                            }
                                            
                                            $corMedia = ($media !== null && $media >= $metaCorU) ? 'success' : ($media !== null ? 'danger' : 'secondary');
                                        ?>
                                        <?php if ($media !== null): ?>
                                            <span class="badge bg-<?= $corMedia ?> bg-opacity-10 text-<?= $corMedia ?> fs-6 px-3">
                                                <?= number_format($media, 1) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">Sem notas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6 px-3">
                                            <?= $faltasPorPeriodo[$aluno['id']][$periodoId] ?? 0 ?>
                                        </span>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                                                
                                  <tr class="table-light fw-bold">
    <td>TOTAL</td>

    <?php foreach ($periodosDisp as $p): ?>
        <td colspan="2" class="text-center">--</td>
    <?php endforeach; ?>

    <td class="text-center text-dark">
        <?= number_format((float)$mediaTurma, 1) ?>
    </td>

    <td class="text-center text-dark">
        <?= $totalFaltasTurma ?>
    </td>
</tr>                
                                                
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
