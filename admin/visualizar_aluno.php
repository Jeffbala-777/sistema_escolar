<?php
// Ativa tipagem estrita
declare(strict_types=1);

// Verifica se o usuario e administrador
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
// Conexao com o banco
require_once __DIR__ . '/../app/database/database.php';
// Model para buscar notas e faltas consolidadas
require_once __DIR__ . '/../app/models/NotasModel.php';
// Model para listar periodos (Bimestres/Trimestres)
require_once __DIR__ . '/../app/models/PeriodoLetivoModel.php';
// Model para dados do usuario
require_once __DIR__ . '/../app/models/UsuarioModel.php';
// Model de IA
require_once __DIR__ . '/../app/ia/AIModel.php';
// Model de Relatórios
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';

// Pega o ID do aluno via URL
$alunoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Pega o ID da escola do administrador
$escolaId = (int)$_SESSION['usuario']['escola_id'];
// Define o ano letivo padrao
$anoLetivoId = 1;

// Instancia os modelos
$notasModel = new NotaModel($pdo);
$periodoModel = new PeriodoLetivoModel($pdo);
$usuarioModel = new UsuarioModel($pdo);
$aiModel = new AIModel($pdo);
$relatorioModel = new RelatorioAlunoModel($pdo);

// Busca dados do aluno
$aluno = $usuarioModel->buscarPorId($alunoId);
// Se o aluno nao existir ou for de outra escola, redireciona
if (!$aluno || (int)$aluno['escola_id'] !== $escolaId) {
    header('Location: alunos.php');
    exit;
}

// Busca os periodos da escola no banco
$periodosBanco = $periodoModel->listarPorAno($anoLetivoId, $escolaId);

// Lógica de períodos conforme o tipo de escola
$periodosParaExibir = [];
if (count($periodosBanco) > 0) {
    $periodosParaExibir = $periodosBanco;
} else {
    // Fallback: 4 bimestres
    for ($i = 1; $i <= 4; $i++) {
        $periodosParaExibir[] = ['id' => $i, 'nome' => $i . 'º Bimestre'];
    }
}

// Busca o boletim completo
$boletim = $notasModel->buscarNotasCompletasAluno($alunoId, $anoLetivoId);

// Busca informacoes da escola diretamente do banco
$stmtEscola = $pdo->prepare("SELECT nome, tipo_periodo FROM escolas WHERE id = :id LIMIT 1");
$stmtEscola->execute([':id' => $escolaId]);
$escolaInfo = $stmtEscola->fetch(PDO::FETCH_ASSOC);
$escolaNomeReal = $escolaInfo['nome'] ?? 'Minha Escola';
$tipoPeriodo = $escolaInfo['tipo_periodo'] ?? 'bimestral';

// Busca dados da matricula
$stmtMatricula = $pdo->prepare("SELECT t.id as turma_id, t.nome as turma_nome, t.serie FROM matriculas m 
                                JOIN turmas t ON t.id = m.turma_id 
                                WHERE m.aluno_id = :aid AND m.status = 'ativa' LIMIT 1");
$stmtMatricula->execute([':aid' => $alunoId]);
$infoMatricula = $stmtMatricula->fetch(PDO::FETCH_ASSOC);
$turmaId = $infoMatricula['turma_id'] ?? 0;

// Filtro de período para IA
$periodoIdIA = isset($_POST['periodo_id_ia']) ? (int)$_POST['periodo_id_ia'] : 0;
$periodoInfoIA = null;
if ($periodoIdIA > 0) {
    foreach ($periodosParaExibir as $p) {
        if ((int)$p['id'] === $periodoIdIA) {
            $periodoInfoIA = $p;
            break;
        }
    }
}

// Processa geração de IA
$analiseIA = '';
$mensagemSucesso = '';
if (isset($_POST['gerar_ia_aluno'])) {
    $dadosIA = $aiModel->coletarDadosAluno($alunoId, $escolaId, $turmaId, $periodoIdIA > 0 ? $periodoIdIA : null);
    if (!isset($dadosIA['error'])) {
        $analiseIA = $aiModel->analisarDesempenho($dadosIA, 'aluno', $tipoPeriodo, $periodoInfoIA);
        if (!empty($analiseIA) && !str_starts_with($analiseIA, '❌') && !str_starts_with($analiseIA, '⚠️')) {
            $relatorioModel->adicionar([
                'escola_id' => $escolaId,
                'aluno_id' => $alunoId,
                'professor_id' => $_SESSION['usuario']['id'],
                'turma_id' => $turmaId,
                'conteudo' => $analiseIA,
                'tipo' => 'ia'
            ]);
            $mensagemSucesso = 'Análise de IA gerada e salva com sucesso!';
        }
    }
}

// Filtro de tipo de relatório (ia, professor, todos)
$tipoFiltro = isset($_GET['tipo']) && in_array($_GET['tipo'], ['ia', 'professor', 'todos']) ? $_GET['tipo'] : 'todos';

// Busca relatórios (IA e Professor)
if ($tipoFiltro === 'todos') {
    $relatorios = $relatorioModel->listarPorAluno($alunoId, $turmaId);
} else {
    $sql = "SELECT r.*, u.nome_completo as professor_nome 
            FROM relatorios_alunos r 
            INNER JOIN usuarios u ON u.id = r.professor_id 
            WHERE r.aluno_id = :aid AND r.turma_id = :tid AND r.tipo = :tipo
            ORDER BY r.criado_em DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':aid' => $alunoId, ':tid' => $turmaId, ':tipo' => $tipoFiltro]);
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$title = 'Visualizar Aluno - ' . $aluno['nome_completo'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            <div class="mb-4">
                <a href="alunos.php" class="btn btn-link text-decoration-none p-0 text-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para lista de alunos
                </a>
            </div>

            <?php if ($mensagemSucesso): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle me-2"></i> <?= e($mensagemSucesso) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-sm rounded p-4 mb-4">
                <!-- Cabecalho Informativo -->
                <div class="small mb-4 text-dark border-bottom pb-2">
                    <strong>Escola:</strong> <?= e($escolaNomeReal) ?> | 
                    <strong>Turma:</strong> <?= e($infoMatricula['turma_nome'] ?? '-') ?> | 
                    <strong>Ano de Escolaridade:</strong> <?= e($infoMatricula['serie'] ?? '-') ?> | 
                    <strong>Ano Escolar:</strong> <?= date('Y') ?>
                </div>

                <h5 class="fw-bold text-secondary mb-4">Boletim do Aluno: <?= e($aluno['nome_completo']) ?></h5>

                <!-- Tabela de Notas Padronizada -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle text-center small">
                        <thead class="table-light">
                            <tr style="font-size: 0.65rem;">
                                <th rowspan="2" class="text-start py-3" style="width: 250px;">Áreas de Conhecimento Disciplinas</th>
                                <?php foreach ($periodosParaExibir as $p): ?>
                                    <th colspan="3" class="text-uppercase"><?= e($p['nome']) ?></th>
                                <?php endforeach; ?>
                                <th rowspan="2" style="width: 60px;">AP FINAL</th>
                                <th rowspan="2" style="width: 80px;">RECUPERAÇÃO FINAL</th>
                                <th rowspan="2" style="width: 60px;">RES FINAL</th>
                            </tr>
                            <tr style="font-size: 0.6rem;">
                                <?php foreach ($periodosParaExibir as $p): ?>
                                    <th>NOTA</th>
                                    <th>FALTA</th>
                                    <th>F.J.</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $faltasPorPeriodo = [];
                            foreach ($periodosParaExibir as $p) { $faltasPorPeriodo[$p['id']] = 0; }
                            
                            foreach ($boletim as $disciplina => $dados): 
                                $somaNotas = 0;
                                $contNotas = 0;
                            ?>
                                <tr>
                                    <td class="text-start fw-bold text-uppercase" style="font-size: 0.7rem;"><?= e($disciplina) ?></td>
                                    <?php foreach ($periodosParaExibir as $p): 
                                        $pid = $p['id'];
                                        $n = $dados[$pid]['nota'] ?? '--';
                                        $f = $dados[$pid]['faltas'] ?? 0;
                                        if ($n !== '--' && $n !== '-') { $somaNotas += (float)$n; $contNotas++; }
                                        $faltasPorPeriodo[$pid] += (int)$f;
                                    ?>
                                        <td class="fw-bold"><?= $n ?></td>
                                        <td class="<?= $f > 0 ? 'text-danger' : 'text-muted' ?>"><?= $f ?></td>
                                        <td class="text-muted">0</td>
                                    <?php endforeach; ?>
                                    
                                    <?php $media = $contNotas > 0 ? round($somaNotas / $contNotas, 1) : '--'; ?>
                                    <td class="fw-bold bg-light"><?= $media ?></td>
                                    <td class="text-muted">---</td>
                                    <td class="fw-bold bg-light"><?= $media ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <tr class="fw-bold" style="background: #fafafa;">
                                <td class="text-start">TOTAL DE FALTAS</td>
                                <?php foreach ($periodosParaExibir as $p): ?>
                                    <td>--</td>
                                    <td class="text-danger"><?= $faltasPorPeriodo[$p['id']] ?></td>
                                    <td>0</td>
                                <?php endforeach; ?>
                                <td>--</td>
                                <td>--</td>
                                <td>--</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Seção de Relatórios e IA -->
            <div class="bg-white shadow-sm rounded p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 flex-wrap gap-3">
                    <h5 class="fw-bold text-secondary mb-0">Relatórios Pedagógicos e IA</h5>
                    
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <!-- Filtro de Tipo -->
                        <div class="btn-group btn-group-sm me-2">
                            <a href="?id=<?= $alunoId ?>&tipo=todos" class="btn <?= $tipoFiltro === 'todos' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Todos</a>
                            <a href="?id=<?= $alunoId ?>&tipo=ia" class="btn <?= $tipoFiltro === 'ia' ? 'btn-secondary' : 'btn-outline-secondary' ?>">IA</a>
                            <a href="?id=<?= $alunoId ?>&tipo=professor" class="btn <?= $tipoFiltro === 'professor' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Professor</a>
                        </div>

                        <!-- Geração de IA -->
                        <form method="POST" class="d-flex gap-2 align-items-center">
                            <select name="periodo_id_ia" class="form-select form-select-sm" style="width: 180px; border-radius: 8px;">
                                <option value="0">Todos os Períodos</option>
                                <?php foreach ($periodosParaExibir as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" <?= $periodoIdIA === (int)$p['id'] ? 'selected' : '' ?>>
                                        <?= e($p['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="gerar_ia_aluno" class="btn btn-primary btn-sm d-flex align-items-center gap-2" style="border-radius: 8px;">
                                <i class="bi bi-robot"></i> Gerar IA
                            </button>
                        </form>
                    </div>
                </div>

                <div class="row g-4">
                    <?php if (!empty($relatorios)): ?>
                        <?php foreach ($relatorios as $rel): 
                            $isIA = isset($rel['tipo']) && $rel['tipo'] === 'ia';
                        ?>
                            <div class="col-12">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid <?= $isIA ? '#8e44ad' : '#3498db' ?>;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1 <?= $isIA ? 'text-purple' : 'text-primary' ?>">
                                                    <i class="bi <?= $isIA ? 'bi-robot' : 'bi-person-workspace' ?> me-2"></i>
                                                    <?= $isIA ? 'Análise Automática da IA' : 'Relatório do Professor' ?>
                                                </h6>
                                                <small class="text-muted">
                                                    Registrado por: <strong><?= e($rel['professor_nome']) ?></strong> em <?= date('d/m/Y H:i', strtotime($rel['criado_em'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-3 bg-light text-secondary" style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;">
                                            <?= e($rel['conteudo']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-journal-x text-muted opacity-25 d-block mb-3" style="font-size: 48px;"></i>
                            <h6 class="text-secondary">Nenhum relatório encontrado para este aluno.</h6>
                            <p class="text-muted small">Utilize o botão acima para gerar uma análise de IA baseada no desempenho atual.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-bordered td, .table-bordered th { border: 1px solid #e0e0e0 !important; }
    .text-purple { color: #8e44ad; }
    .bg-light { background-color: #f8f9fa !important; }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
