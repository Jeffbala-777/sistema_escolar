<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';
require_once __DIR__ . '/../app/ia/AIModel.php';

$adminId   = (int)$_SESSION['usuario']['id'];
$adminNome = $_SESSION['usuario']['nome_completo'] ?? 'Administrador';
$escolaId  = (int)$_SESSION['usuario']['escola_id'];

$turmaId        = isset($_GET['turma_id'])     ? (int)$_GET['turma_id']     : 0;
$disciplinaId   = isset($_GET['disciplina_id'])? (int)$_GET['disciplina_id']: 0;
$tipoRelatorio  = isset($_GET['tipo']) && in_array($_GET['tipo'], ['ia', 'professor']) ? $_GET['tipo'] : 'professor';
$filtroHistorico = isset($_GET['filtro_historico']) ? $_GET['filtro_historico'] : '';
$periodoIdGerar  = isset($_POST['periodo_id_gerar']) ? (int)$_POST['periodo_id_gerar'] : 0;

$relatorioModel = new RelatorioAlunoModel($pdo);
$turmaModel     = new TurmaModel($pdo);
$aiModel        = new AIModel($pdo);

// Todas as turmas da escola
$turmas = $turmaModel->listarPorEscola($escolaId);

$turmaAtual = null;
if ($turmaId > 0) {
    $turmaAtual = $turmaModel->buscarPorId($turmaId, $escolaId);
}

// Disciplinas disponíveis na turma (todas, independente de professor)
$disciplinasTurma = [];
if ($turmaId > 0) {
    $sqlDT = "SELECT DISTINCT d.id, d.nome
              FROM disciplinas d
              INNER JOIN professor_turma_disciplina ptd ON ptd.disciplina_id = d.id
              WHERE ptd.turma_id = :tid AND ptd.escola_id = :eid AND ptd.ativo = 1
              ORDER BY d.nome ASC";
    $stmtDT = $pdo->prepare($sqlDT);
    $stmtDT->execute([':tid' => $turmaId, ':eid' => $escolaId]);
    $disciplinasTurma = $stmtDT->fetchAll(PDO::FETCH_ASSOC);

    if ($disciplinaId <= 0 && !empty($disciplinasTurma)) {
        $disciplinaId = (int)$disciplinasTurma[0]['id'];
    }
}

$disciplinaNome = '';
foreach ($disciplinasTurma as $dp) {
    if ((int)$dp['id'] === $disciplinaId) {
        $disciplinaNome = $dp['nome'];
        break;
    }
}

// Tipo de período da escola
$stmtEscola = $pdo->prepare("SELECT tipo_periodo FROM escolas WHERE id = :id LIMIT 1");
$stmtEscola->execute([':id' => $escolaId]);
$tipoPeriodo = $stmtEscola->fetch(PDO::FETCH_ASSOC)['tipo_periodo'] ?? 'bimestral';

// Períodos letivos ativos
$stmtPeriodos = $pdo->prepare("SELECT pl.id, pl.nome
                               FROM periodos_letivos pl
                               INNER JOIN anos_letivos al ON al.id = pl.ano_letivo_id
                               WHERE al.escola_id = :eid AND al.ativo = 1
                               ORDER BY pl.ordem ASC");
$stmtPeriodos->execute([':eid' => $escolaId]);
$periodosDisponiveis = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);

$periodoInfoGerar = null;
if ($periodoIdGerar > 0) {
    foreach ($periodosDisponiveis as $p) {
        if ((int)$p['id'] === $periodoIdGerar) {
            $periodoInfoGerar = $p;
            break;
        }
    }
}

$alunos       = [];
$relatoriosIA = [];
$mensagemSucesso = '';
$mensagemErro    = '';

if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1) {
    $mensagemSucesso = 'Análise gerada e salva com sucesso!';
}

if ($turmaId > 0 && $turmaAtual && $disciplinaId > 0) {

    // Geração de análise IA da turma
    if (isset($_POST['gerar_analise_geral'])) {
        // Busca o professor vinculado a esta turma/disciplina para usar como referência nos dados
        $sqlProfRef = "SELECT professor_id FROM professor_turma_disciplina
                       WHERE turma_id = :tid AND disciplina_id = :did AND escola_id = :eid AND ativo = 1
                       LIMIT 1";
        $stmtProfRef = $pdo->prepare($sqlProfRef);
        $stmtProfRef->execute([':tid' => $turmaId, ':did' => $disciplinaId, ':eid' => $escolaId]);
        $profRefId = (int)($stmtProfRef->fetchColumn() ?: 0);

        if ($profRefId <= 0) {
            $mensagemErro = 'Nenhum professor vinculado a esta turma/disciplina para gerar análise.';
        } else {
            $dadosIA = $aiModel->coletarDadosProfessor(
                $profRefId,
                $escolaId,
                $turmaId,
                $periodoIdGerar > 0 ? $periodoIdGerar : null,
                $disciplinaId
            );

            if (empty($dadosIA)) {
                $mensagemErro = 'Não foram encontrados dados para esta turma/disciplina.';
            } else {
                $analiseIA = $aiModel->analisarDesempenho($dadosIA, 'professor', $tipoPeriodo, $periodoInfoGerar);

                if (!empty($analiseIA) && !str_starts_with($analiseIA, '❌')) {
                    $salvou = $relatorioModel->adicionar([
                        'escola_id'   => $escolaId,
                        'aluno_id'    => null,
                        'professor_id'=> $adminId,
                        'turma_id'    => $turmaId,
                        'conteudo'    => $analiseIA,
                        'tipo'        => 'ia'
                    ]);

                    if ($salvou) {
                        header("Location: relatorios_turma.php?turma_id=$turmaId&tipo=ia&disciplina_id=$disciplinaId&sucesso=1");
                        exit;
                    } else {
                        $mensagemErro = 'A análise foi gerada, mas não foi possível salvar no banco.';
                    }
                } else {
                    $mensagemErro = !empty($analiseIA) ? $analiseIA : 'Erro desconhecido na geração da IA';
                }
            }
        }
    }

    // Lista de alunos para registros manuais
    if ($tipoRelatorio === 'professor') {
        $sqlAlunos = "SELECT u.id, u.nome_completo
                      FROM usuarios u
                      JOIN matriculas m ON m.aluno_id = u.id
                      WHERE m.turma_id = :tid AND u.ativo = 1
                      ORDER BY u.nome_completo ASC";
        $stmtAlunos = $pdo->prepare($sqlAlunos);
        $stmtAlunos->execute([':tid' => $turmaId]);
        $alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Relatórios IA da turma (todos os autores, filtrados por disciplina)
        $sqlIA = "SELECT r.*, p.nome_completo as professor_nome
                  FROM relatorios_alunos r
                  INNER JOIN usuarios p ON p.id = r.professor_id
                  WHERE r.turma_id = :tid
                    AND r.escola_id = :eid
                    AND r.tipo = 'ia'
                    AND r.aluno_id IS NULL
                    AND (r.conteudo LIKE :disciplina_filtro OR r.conteudo LIKE :disciplina_filtro2)";

        $paramsIA = [
            ':tid' => $turmaId,
            ':eid' => $escolaId,
            ':disciplina_filtro' => "% | 📚 DISCIPLINA: " . $disciplinaNome . "%",
            ':disciplina_filtro2' => "%📚 DISCIPLINA: " . $disciplinaNome . "%"
        ];

        if (!empty($filtroHistorico)) {
            $sqlIA .= " AND r.conteudo LIKE :filtro";
            $paramsIA[':filtro'] = "%[" . $filtroHistorico . "]%";
        }

        $stmtIA = $pdo->prepare($sqlIA . " ORDER BY r.criado_em DESC");
        $stmtIA->execute($paramsIA);
        $relatoriosIA = $stmtIA->fetchAll(PDO::FETCH_ASSOC);
    }
}

$title = 'Relatórios de Alunos';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
        <div class="container-fluid mt-4">

            <div class="page-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="dashboard-title mb-1" style="font-size: 24px;">Relatórios de Alunos</div>
                        <div class="dashboard-subtitle mb-0">
                            <?php if ($turmaAtual): ?>
                                Turma: <strong><?= e($turmaAtual['nome']) ?></strong>
                                <?php if (!empty($disciplinasTurma)): ?>
                                    | Disciplina:
                                    <select class="form-select d-inline-block w-auto ms-1 py-0"
                                            onchange="location.href='?turma_id=<?= $turmaId ?>&tipo=<?= $tipoRelatorio ?>&disciplina_id=' + this.value">
                                        <?php foreach ($disciplinasTurma as $dp): ?>
                                            <option value="<?= $dp['id'] ?>" <?= (int)$dp['id'] === $disciplinaId ? 'selected' : '' ?>>
                                                <?= e($dp['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            <?php else: ?>
                                Selecione uma turma para visualizar os relatórios.
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($turmaAtual): ?>
                        <a href="relatorios_turma.php" class="btn btn-secondary btn-sm d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Trocar Turma
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($mensagemSucesso)): ?>
                <div class="alert alert-success"><?= e($mensagemSucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagemErro)): ?>
                <div class="alert alert-danger"><?= e($mensagemErro) ?></div>
            <?php endif; ?>

            <?php if ($turmaId <= 0 || !$turmaAtual): ?>
                <!-- Seleção de turma -->
                <div class="row g-4">
                    <?php foreach ($turmas as $t): ?>
                        <div class="col-md-6 col-lg-4">
                            <a href="?turma_id=<?= $t['id'] ?>" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm card-hover" style="border-radius: 15px;">
                                    <div class="card-header bg-primary bg-opacity-10 border-0 p-4">
                                        <h5 class="fw-bold text-primary mb-0"><?= e($t['nome']) ?></h5>
                                        <p class="text-muted small mb-0 mt-1"><?= e($t['serie'] ?? '') ?></p>
                                    </div>
                                    <div class="card-body p-4 text-center text-primary fw-bold">Acessar Relatórios</div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px; background: #f8f9fa;">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <a href="?turma_id=<?= $turmaId ?>&tipo=professor&disciplina_id=<?= $disciplinaId ?>"
                                   class="btn <?= $tipoRelatorio === 'professor' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 py-2"
                                   style="border-radius: 12px; font-weight: 600;">Registros da Turma</a>
                                <a href="?turma_id=<?= $turmaId ?>&tipo=ia&disciplina_id=<?= $disciplinaId ?>"
                                   class="btn <?= $tipoRelatorio === 'ia' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 py-2"
                                   style="border-radius: 12px; font-weight: 600;">Análises da IA</a>
                            </div>
                        </div>

                        <?php if ($tipoRelatorio === 'ia'): ?>
                            <div class="col-md-6 text-md-end">
                                <form method="GET" class="d-inline-block">
                                    <input type="hidden" name="turma_id" value="<?= $turmaId ?>">
                                    <input type="hidden" name="tipo" value="ia">
                                    <input type="hidden" name="disciplina_id" value="<?= $disciplinaId ?>">
                                    <label class="small fw-bold text-muted me-2">Filtrar Histórico:</label>
                                    <select name="filtro_historico" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                                        <option value="">Todos os Registros</option>
                                        <option value="Geral" <?= $filtroHistorico === 'Geral' ? 'selected' : '' ?>>Somente Gerais</option>
                                        <?php foreach ($periodosDisponiveis as $p): ?>
                                            <option value="<?= e($p['nome']) ?>" <?= $filtroHistorico === $p['nome'] ? 'selected' : '' ?>>
                                                <?= e($p['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($tipoRelatorio === 'ia'): ?>
                    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                        <h6 class="fw-bold mb-3"><i class="bi bi-cpu-fill me-2 text-success"></i>Gerar Nova Análise da Turma</h6>
                        <form method="POST" class="row g-3 align-items-end">
                            <input type="hidden" name="tipo" value="ia">
                            <input type="hidden" name="disciplina_id" value="<?= $disciplinaId ?>">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Escolha o Período para Analisar:</label>
                                <select name="periodo_id_gerar" class="form-select">
                                    <option value="0">Análise Geral (Todo o Histórico)</option>
                                    <?php foreach ($periodosDisponiveis as $p): ?>
                                        <option value="<?= (int)$p['id'] ?>"><?= e($p['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="gerar_analise_geral" class="btn btn-success w-100 fw-bold py-2">GERAR AGORA</button>
                            </div>
                        </form>
                    </div>

                    <div class="row g-3">
                        <?php if (!empty($relatoriosIA)): foreach ($relatoriosIA as $rel): ?>
                            <div class="col-12">
                                <div class="dashboard-card p-4" style="border-left: 5px solid #8e44ad;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-robot me-2"></i>Panorama da Turma</h6>
                                            <small class="text-muted"><?= e($rel['professor_nome']) ?></small>
                                        </div>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($rel['criado_em'])) ?></small>
                                    </div>
                                    <div class="p-3 bg-light rounded-3 mt-2" style="white-space: pre-wrap; line-height: 1.6; font-size: 14px;"><?= e($rel['conteudo']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div class="col-12 text-center py-5 text-muted">
                                <p>Nenhuma análise encontrada para o filtro selecionado.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($alunos as $aluno):
                            $sqlUltimo = "SELECT r.*
                                          FROM relatorios_alunos r
                                          WHERE r.aluno_id = :aid
                                            AND r.turma_id = :tid
                                            AND (r.tipo = 'professor' OR r.tipo IS NULL)
                                            AND r.conteudo LIKE :disciplina_filtro
                                          ORDER BY r.criado_em DESC
                                          LIMIT 1";
                            $stmtUltimo = $pdo->prepare($sqlUltimo);
                            $stmtUltimo->execute([
                                ':aid' => $aluno['id'],
                                ':tid' => $turmaId,
                                ':disciplina_filtro' => "%📚 DISCIPLINA: " . $disciplinaNome . "%"
                            ]);
                            $ultimo = $stmtUltimo->fetch(PDO::FETCH_ASSOC);
                        ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="dashboard-card h-100 d-flex flex-column p-4">
                                    <h6 class="fw-bold mb-1 text-truncate"><?= e($aluno['nome_completo']) ?></h6>
                                    <small class="text-muted d-block mb-3"><?= e($disciplinaNome) ?></small>
                                    <div class="p-3 rounded-3 bg-light flex-grow-1 mb-3" style="font-size: 13px; min-height: 80px;">
                                        <?= $ultimo ? nl2br(e(mb_strimwidth($ultimo['conteudo'], 0, 100, "..."))) : 'Nenhum registro.' ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="relatorios_aluno.php?aluno_id=<?= $aluno['id'] ?>&turma_id=<?= $turmaId ?>&disciplina_id=<?= $disciplinaId ?>&modo=adicionar" class="btn btn-outline-primary btn-sm fw-bold flex-grow-1">Adicionar</a>
                                        <a href="relatorios_aluno.php?aluno_id=<?= $aluno['id'] ?>&turma_id=<?= $turmaId ?>&disciplina_id=<?= $disciplinaId ?>&modo=visualizar" class="btn btn-outline-primary btn-sm fw-bold flex-grow-1">Visualizar</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
