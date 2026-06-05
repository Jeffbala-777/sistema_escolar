<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';
require_once __DIR__ . '/../app/ia/AIModel.php';

$aiModel = new AIModel($pdo);
$analiseIA = '';

$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);

$professorId = (int)$_SESSION['usuario']['id'];
$escolaId = (int)$_SESSION['usuario']['escola_id'];
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
$periodoId = isset($_POST['periodo_id']) ? (int)$_POST['periodo_id'] : (isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0);

if ($turmaId <= 0) {
    header('Location: selecionar_turma.php');
    exit;
}

$sqlTurma = "
    SELECT DISTINCT t.id, t.nome, t.serie, t.turno
    FROM turmas t
    INNER JOIN professor_turma_disciplina ptd ON ptd.turma_id = t.id
    WHERE t.id = :tid
      AND ptd.professor_id = :pid
      AND t.escola_id = :eid
    LIMIT 1
";
$stmtT = $pdo->prepare($sqlTurma);
$stmtT->execute([
    ':tid' => $turmaId,
    ':pid' => $professorId,
    ':eid' => $escolaId
]);
$turmaAtual = $stmtT->fetch(PDO::FETCH_ASSOC);

if (!$turmaAtual) {
    header('Location: selecionar_turma.php');
    exit;
}

$stmtEscola = $pdo->prepare("SELECT tipo_periodo FROM escolas WHERE id = :id LIMIT 1");
$stmtEscola->execute([':id' => $escolaId]);
$escolaInfo = $stmtEscola->fetch(PDO::FETCH_ASSOC);
$tipoPeriodo = $escolaInfo['tipo_periodo'] ?? 'bimestral';

$stmtPeriodos = $pdo->prepare("
    SELECT pl.id, pl.nome, pl.ordem, pl.data_inicio, pl.data_fim
    FROM periodos_letivos pl
    INNER JOIN anos_letivos al ON al.id = pl.ano_letivo_id
    WHERE al.escola_id = :eid
      AND al.ativo = 1
    ORDER BY pl.ordem ASC
");
$stmtPeriodos->execute([':eid' => $escolaId]);
$periodosDisponiveis = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);

if ($periodoId < 0) {
    $periodoId = 0;
}

// Busca informações do período selecionado
$periodoInfo = null;
if ($periodoId > 0) {
    foreach ($periodosDisponiveis as $p) {
        if ((int)$p['id'] === $periodoId) {
            $periodoInfo = $p;
            break;
        }
    }
}

$sqlTurmas = "SELECT ptd.id, ptd.turma_id, ptd.disciplina_id, t.nome AS turma, d.nome AS disciplina
              FROM professor_turma_disciplina ptd
              INNER JOIN turmas t ON t.id = ptd.turma_id
              INNER JOIN disciplinas d ON d.id = ptd.disciplina_id
              WHERE ptd.professor_id = :prof_id AND ptd.escola_id = :escola_id AND ptd.ativo = 1 AND ptd.turma_id = :turma_id";
$stmtTurmas = $pdo->prepare($sqlTurmas);
$stmtTurmas->execute([':prof_id' => $professorId, ':escola_id' => $escolaId, ':turma_id' => $turmaId]);
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

$dadosEstatisticos = [];
foreach ($turmas as $t) {
    $sqlMedia = "SELECT AVG(nota) as media FROM notas WHERE disciplina_id = :disc_id AND escola_id = :esc_id";
    $paramsMedia = [':disc_id' => $t['disciplina_id'], ':esc_id' => $escolaId];
    if ($periodoId > 0) {
        $sqlMedia .= " AND periodo_id = :periodo_id";
        $paramsMedia[':periodo_id'] = $periodoId;
    }
    $stmtMedia = $pdo->prepare($sqlMedia);
    $stmtMedia->execute($paramsMedia);
    $mediaGeral = $stmtMedia->fetchColumn();

    $dadosEstatisticos[] = [
        'disciplina_id' => $t['disciplina_id'],
        'disciplina' => $t['disciplina'],
        'media_geral' => $mediaGeral !== null ? (float)$mediaGeral : 0
    ];
}

// Busca alunos com mais faltas na turma
$sqlFaltas = "SELECT u.nome_completo, COUNT(p.id) as total_faltas
              FROM usuarios u
              INNER JOIN matriculas m ON m.aluno_id = u.id
              LEFT JOIN presencas p ON p.aluno_id = u.id AND p.status = 'falta'
              LEFT JOIN aulas a ON a.id = p.aula_id
              LEFT JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
              WHERE m.turma_id = :turma_id
              AND m.status = 'ativa'
              AND ptd.professor_id = :professor_id";
if ($periodoInfo && !empty($periodoInfo['data_inicio']) && !empty($periodoInfo['data_fim'])) {
    $sqlFaltas .= " AND a.data_aula BETWEEN :d1 AND :d2";
}
$sqlFaltas .= " GROUP BY u.id, u.nome_completo HAVING total_faltas > 0 ORDER BY total_faltas DESC LIMIT 5";

$stmtFaltas = $pdo->prepare($sqlFaltas);
$paramsFaltas = [':turma_id' => $turmaId,':professor_id' => $professorId];
if ($periodoInfo && !empty($periodoInfo['data_inicio']) && !empty($periodoInfo['data_fim'])) {
    $paramsFaltas[':d1'] = $periodoInfo['data_inicio'];
    $paramsFaltas[':d2'] = $periodoInfo['data_fim'];
}
$stmtFaltas->execute($paramsFaltas);
$alunosFaltosos = $stmtFaltas->fetchAll(PDO::FETCH_ASSOC);

// Gera análise de IA se solicitado (ATIVADO PARA PROFESSOR)
if (isset($_POST['gerar_analise'])) {
    $dadosIA = $aiModel->coletarDadosProfessor($professorId, $escolaId, $turmaId, $periodoId > 0 ? $periodoId : null);
    $analiseIA = $aiModel->analisarDesempenho($dadosIA, 'professor', $tipoPeriodo, $periodoInfo);

    // Salva o relatório da IA se a chamada for bem-sucedida (não começar com ❌ ou ⚠️)
    if (!empty($analiseIA) && !str_starts_with($analiseIA, '❌') && !str_starts_with($analiseIA, '⚠️')) {
        require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';
        $relatorioModel = new RelatorioAlunoModel($pdo);
        
        $relatorioModel->adicionar([
            'escola_id' => $escolaId,
            'aluno_id' => null, // null indica relatório geral da turma
            'professor_id' => $_SESSION['usuario']['id'],
            'turma_id' => $turmaId,
            'conteudo' => $analiseIA,
            'tipo' => 'ia'
        ]);
    }
}

$title = 'Desempenho: ' . $turmaAtual['nome'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            <div class="mb-4">
                <a href="selecionar_turma.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar tela
                </a>
            </div>

            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-secondary mb-1">Desempenho: <?= e($turmaAtual['nome']) ?></h4>
                        <p class="text-muted mb-0">Análise o desempenho dos alunos da sua disciplina</p>
                    </div>
                </div>
            </div>

            <!-- Filtro de Período -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px; background: #f8f9fa;">
                <form method="POST" class="d-flex align-items-center gap-3 flex-wrap">
                    <label class="fw-bold text-secondary mb-0">Selecione o <?= ucfirst($tipoPeriodo === 'bimestral' ? 'Bimestre' : ($tipoPeriodo === 'trimestral' ? 'Trimestre' : 'Semestre')) ?>:</label>
                    <select name="periodo_id" class="form-select" style="max-width: 300px; border-radius: 8px;" onchange="this.form.submit()">
                        <option value="0" <?= $periodoId === 0 ? 'selected' : '' ?>>Todos os Períodos</option>
                        <?php foreach ($periodosDisponiveis as $p): ?>
                            <option value="<?= (int)$p['id'] ?>" <?= $periodoId === (int)$p['id'] ? 'selected' : '' ?>>
                                <?= e($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"
                        name="gerar_analise"
                        class="btn btn-primary fw-bold px-4 shadow-sm"
                        style="border-radius: 10px;">
                        <i class="bi bi-cpu me-2"></i> IA - Análise
                    </button>
                </form>
            </div>

            <?php if ($analiseIA): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4" style="border-radius: 15px; border-left: 5px solid #3498db;">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-robot me-2"></i> IA - Analisando a situação...
                            <?php if ($periodoInfo): ?> - <?= e($periodoInfo['nome']) ?><?php endif; ?>
                        </h5>
                        <div class="ai-content text-secondary" style="line-height: 1.8;">
                            <?= nl2br(e($analiseIA)) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($dadosEstatisticos)): ?>
            <div class="row">
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:15px;">
                        <h5 class="fw-bold text-secondary mb-4">
                            <i class="bi bi-book me-2"></i>
                            Desempenho por Disciplina
                        </h5>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Disciplina</th>
                                        <th class="text-center">Média da Turma</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $meta = 6.0;
                                    if ($tipoPeriodo === 'trimestral' && $periodoId > 0) {
                                        $stmtOrdem = $pdo->prepare("SELECT ordem FROM periodos_letivos WHERE id = :id LIMIT 1");
                                        $stmtOrdem->execute([':id' => $periodoId]);
                                        $ordemP = (int)$stmtOrdem->fetchColumn();
                                        
                                        if ($ordemP == 1) $meta = 18.0;
                                        elseif ($ordemP == 2) $meta = 18.0;
                                        elseif ($ordemP == 3) $meta = 24.0;
                                    } elseif ($tipoPeriodo === 'trimestral' && $periodoId === 0) {
                                        $meta = 60.0;
                                    }

                                    foreach ($dadosEstatisticos as $item):
                                        $media = (float)($item['media_geral'] ?? 0);
                                        $cor = $media >= $meta ? 'success' : 'danger';
                                    ?>
                                        <tr>
                                            <td class="fw-bold">
                                                <a href="detalhes_disciplina.php?turma_id=<?= $turmaId ?>&disciplina_id=<?= (int)$item['disciplina_id'] ?>&periodo_id=<?= $periodoId ?>" class="text-decoration-none text-primary">
                                                    <?= e($item['disciplina'] ?? $item['nome_disciplina'] ?? $item['nome'] ?? 'Disciplina') ?>
                                                    <i class="bi bi-arrow-right-short ms-1"></i>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $cor ?> bg-opacity-10 text-<?= $cor ?> fs-6 px-3">
                                                    <?= number_format($media, 1) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="bi bi-circle-fill text-<?= $cor ?> small me-1"></i>
                                                <?= $media >= $meta ? 'Dentro da Meta' : 'Abaixo da Meta' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:15px;">
                        <h5 class="fw-bold text-secondary mb-4">
                            <i class="bi bi-person-x me-2"></i>
                            Alunos com Mais Faltas
                        </h5>
                        <?php if (!empty($alunosFaltosos)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <tbody>
                                        <?php foreach ($alunosFaltosos as $aluno): ?>
                                            <tr>
                                                <td><?= e($aluno['nome_completo']) ?></td>
                                                <td class="text-end"><span class="badge bg-danger bg-opacity-10 text-danger"><?= $aluno['total_faltas'] ?> faltas</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                Nenhum registro de falta nesta turma.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Nenhum dado disponível para suas disciplinas nesta turma.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
