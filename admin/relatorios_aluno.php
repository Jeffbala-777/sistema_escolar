<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';
require_once __DIR__ . '/../app/ia/AIModel.php';

$adminId   = (int)$_SESSION['usuario']['id'];
$adminNome = $_SESSION['usuario']['nome_completo'] ?? 'Administrador';
$escolaId  = (int)$_SESSION['usuario']['escola_id'];

$alunoId      = isset($_GET['aluno_id'])     ? (int)$_GET['aluno_id']     : 0;
$turmaId      = isset($_GET['turma_id'])     ? (int)$_GET['turma_id']     : 0;
$disciplinaId = isset($_GET['disciplina_id'])? (int)$_GET['disciplina_id']: 0;
$modo         = isset($_GET['modo']) && in_array($_GET['modo'], ['adicionar', 'visualizar']) ? $_GET['modo'] : 'adicionar';
$abaAtiva     = isset($_GET['aba']) && in_array($_GET['aba'], ['manual', 'ia']) ? $_GET['aba'] : 'manual';
$periodoIdIA  = isset($_POST['periodo_id_ia']) ? (int)$_POST['periodo_id_ia'] : 0;
$editarId     = isset($_GET['editar_id']) ? (int)$_GET['editar_id'] : 0;
$relatorioEditando = null;

$relatorioModel = new RelatorioAlunoModel($pdo);
$usuarioModel   = new UsuarioModel($pdo);
$turmaModel     = new TurmaModel($pdo);
$aiModel        = new AIModel($pdo);

$aluno = $usuarioModel->buscarPorId($alunoId);
$turma = $turmaModel->buscarPorId($turmaId, $escolaId);

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
}

if (!$aluno || !$turma || (int)$aluno['escola_id'] !== $escolaId || empty($disciplinasTurma)) {
    header('Location: relatorios_turma.php');
    exit;
}

if ($disciplinaId <= 0) $disciplinaId = (int)$disciplinasTurma[0]['id'];

$disciplinaNome = '';
foreach ($disciplinasTurma as $dp) {
    if ((int)$dp['id'] === $disciplinaId) {
        $disciplinaNome = $dp['nome'];
        break;
    }
}

// Busca o PTD (professor_turma_disciplina) para faltas — usa o professor vinculado à disciplina
$ptdId = 0;
$sqlPTD = "SELECT id FROM professor_turma_disciplina
           WHERE turma_id = :tid AND disciplina_id = :did AND escola_id = :eid AND ativo = 1
           LIMIT 1";
$stmtPTD = $pdo->prepare($sqlPTD);
$stmtPTD->execute([':tid' => $turmaId, ':did' => $disciplinaId, ':eid' => $escolaId]);
$ptdId = (int)($stmtPTD->fetchColumn() ?: 0);

// Editar relatório
if ($editarId > 0) {
    $relatorioEditando = $relatorioModel->buscarPorId($editarId);
    if (!$relatorioEditando) {
        header("Location: relatorios_aluno.php?aluno_id=$alunoId&turma_id=$turmaId&disciplina_id=$disciplinaId&aba=manual&modo=visualizar");
        exit;
    }
}

// Salvar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_edicao'])) {
    $conteudo = trim($_POST['conteudo'] ?? '');
    $idEdicao = (int)($_POST['relatorio_id'] ?? 0);
    if (!empty($conteudo) && $idEdicao > 0) {
        $pdo->prepare("UPDATE relatorios_alunos SET conteudo = :conteudo WHERE id = :id")
            ->execute([':conteudo' => $conteudo, ':id' => $idEdicao]);
        header("Location: relatorios_aluno.php?aluno_id=$alunoId&turma_id=$turmaId&disciplina_id=$disciplinaId&aba=manual&modo=visualizar&atualizado=1");
        exit;
    }
}

// Adicionar relatório manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_relatorio'])) {
    $conteudo = trim($_POST['conteudo'] ?? '');
    if (!empty($conteudo)) {
        $cabecalho = "Matéria: {$disciplinaNome}\n\n";
        $relatorioModel->adicionar([
            'escola_id'    => $escolaId,
            'aluno_id'     => $alunoId,
            'professor_id' => $adminId,
            'turma_id'     => $turmaId,
            'conteudo'     => $cabecalho . $conteudo,
            'tipo'         => 'professor'
        ]);
        header("Location: relatorios_aluno.php?aluno_id=$alunoId&turma_id=$turmaId&disciplina_id=$disciplinaId&aba=manual&modo=visualizar&sucesso=1");
        exit;
    }
}

// Períodos letivos
$stmtEscola = $pdo->prepare("SELECT tipo_periodo FROM escolas WHERE id = :id LIMIT 1");
$stmtEscola->execute([':id' => $escolaId]);
$tipoPeriodo = $stmtEscola->fetch(PDO::FETCH_ASSOC)['tipo_periodo'] ?? 'bimestral';

$stmtPeriodos = $pdo->prepare("SELECT pl.id, pl.nome, pl.data_inicio, pl.data_fim
                               FROM periodos_letivos pl
                               INNER JOIN anos_letivos al ON al.id = pl.ano_letivo_id
                               WHERE al.escola_id = :eid AND al.ativo = 1
                               ORDER BY pl.ordem ASC");
$stmtPeriodos->execute([':eid' => $escolaId]);
$periodosDisponiveis = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);

// Geração de IA para o aluno
$mensagemIA = '';
$sucessoIA  = false;
if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1) {
    $sucessoIA = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_ia_aluno'])) {
    $periodoInfoIA = null;
    if ($periodoIdIA > 0) {
        foreach ($periodosDisponiveis as $p) {
            if ((int)$p['id'] === $periodoIdIA) { $periodoInfoIA = $p; break; }
        }
    }
    $dadosIA = $aiModel->coletarDadosAluno($alunoId, $escolaId, $turmaId, $periodoIdIA > 0 ? $periodoIdIA : null, $disciplinaId);
    if (!isset($dadosIA['error'])) {
        $analiseIA = $aiModel->analisarDesempenho($dadosIA, 'aluno', $tipoPeriodo, $periodoInfoIA);
        if (!empty($analiseIA) && !str_starts_with($analiseIA, '❌') && !str_starts_with($analiseIA, '⚠️')) {
            $cabecalho = "Matéria: {$disciplinaNome}\n\n";
            $salvou = $relatorioModel->adicionar([
                'escola_id'    => $escolaId,
                'aluno_id'     => $alunoId,
                'professor_id' => $adminId,
                'turma_id'     => $turmaId,
                'conteudo'     => $cabecalho . $analiseIA,
                'tipo'         => 'ia'
            ]);
            if ($salvou) {
                header("Location: relatorios_aluno.php?aluno_id=$alunoId&turma_id=$turmaId&disciplina_id=$disciplinaId&aba=ia&modo=visualizar&sucesso=1");
                exit;
            } else {
                $mensagemIA = 'A análise foi gerada, mas não foi possível salvar no banco.';
            }
        } else {
            $mensagemIA = $analiseIA;
        }
    }
}

// Excluir relatório (admin pode excluir qualquer um)
if (isset($_GET['excluir_id'])) {
    $pdo->prepare("DELETE FROM relatorios_alunos WHERE id = :id")
        ->execute([':id' => (int)$_GET['excluir_id']]);
    header("Location: relatorios_aluno.php?aluno_id=$alunoId&turma_id=$turmaId&disciplina_id=$disciplinaId&aba=$abaAtiva&modo=$modo&excluido=1");
    exit;
}

// Faltas do aluno na disciplina
$sqlFaltas = "SELECT DATE_FORMAT(a.data_aula, '%m/%Y') as mes, COUNT(p.id) as total_faltas
              FROM presencas p
              INNER JOIN aulas a ON a.id = p.aula_id
              WHERE p.aluno_id = :aid AND p.status = 'falta' AND p.escola_id = :eid AND a.professor_turma_disciplina_id = :ptd
              GROUP BY DATE_FORMAT(a.data_aula, '%m/%Y')
              ORDER BY a.data_aula DESC";
$stmtF = $pdo->prepare($sqlFaltas);
$stmtF->execute([':aid' => $alunoId, ':eid' => $escolaId, ':ptd' => $ptdId]);
$faltasPorMes = $stmtF->fetchAll();

$sqlTotal = "SELECT COUNT(p.id) FROM presencas p INNER JOIN aulas a ON a.id = p.aula_id
             WHERE p.aluno_id = :aid AND p.status = 'falta' AND p.escola_id = :eid AND a.professor_turma_disciplina_id = :ptd";
$stmtT = $pdo->prepare($sqlTotal);
$stmtT->execute([':aid' => $alunoId, ':eid' => $escolaId, ':ptd' => $ptdId]);
$totalFaltas = $stmtT->fetchColumn();

// Relatórios manuais (filtrados por disciplina)
$sqlManual = "SELECT r.*, u.nome_completo as professor_nome
              FROM relatorios_alunos r
              INNER JOIN usuarios u ON u.id = r.professor_id
              WHERE r.aluno_id = :aid AND r.turma_id = :tid
                AND (r.tipo = 'professor' OR r.tipo IS NULL)
                AND r.conteudo LIKE :disciplina_filtro
              ORDER BY r.criado_em DESC";
$stmtM = $pdo->prepare($sqlManual);
$stmtM->execute([':aid' => $alunoId, ':tid' => $turmaId, ':disciplina_filtro' => "Matéria: " . $disciplinaNome . "%"]);
$relatoriosManual = $stmtM->fetchAll(PDO::FETCH_ASSOC);

// Relatórios IA (filtrados por disciplina)
$sqlIA = "SELECT r.*, u.nome_completo as professor_nome
          FROM relatorios_alunos r
          INNER JOIN usuarios u ON u.id = r.professor_id
          WHERE r.aluno_id = :aid AND r.turma_id = :tid AND r.tipo = 'ia'
            AND r.conteudo LIKE :disciplina_filtro
          ORDER BY r.criado_em DESC";
$stmtIA = $pdo->prepare($sqlIA);
$stmtIA->execute([':aid' => $alunoId, ':tid' => $turmaId, ':disciplina_filtro' => "Matéria: " . $disciplinaNome . "%"]);
$relatoriosIA = $stmtIA->fetchAll(PDO::FETCH_ASSOC);

$title = 'Relatórios de ' . $aluno['nome_completo'];
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
                        <div class="dashboard-title mb-1" style="font-size: 24px;">Relatórios do Aluno</div>
                        <div class="dashboard-subtitle mb-0">
                            <strong><?= e($aluno['nome_completo']) ?></strong> | Turma: <strong><?= e($turma['nome']) ?></strong> | Disciplina:
                            <select class="form-select d-inline-block w-auto ms-1 py-0"
                                    onchange="location.href='?aluno_id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&disciplina_id=' + this.value + '&modo=<?= $modo ?>&aba=<?= $abaAtiva ?>'">
                                <?php foreach ($disciplinasTurma as $dp): ?>
                                    <option value="<?= $dp['id'] ?>" <?= (int)$dp['id'] === $disciplinaId ? 'selected' : '' ?>><?= e($dp['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <a href="relatorios_turma.php?turma_id=<?= $turmaId ?>&disciplina_id=<?= $disciplinaId ?>" class="btn btn-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="dashboard-card mb-4 p-0 overflow-hidden">
                        <ul class="nav nav-tabs border-0 bg-light" id="relatorioTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a href="?aluno_id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&disciplina_id=<?= $disciplinaId ?>&aba=manual&modo=<?= $modo ?>"
                                   class="nav-link <?= $abaAtiva === 'manual' ? 'active' : '' ?> border-0 px-4 py-3 fw-bold" role="tab">
                                    <i class="bi bi-pencil-square me-2"></i><?= $modo === 'adicionar' ? 'Adicionar Registro' : 'Registros' ?>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a href="?aluno_id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&disciplina_id=<?= $disciplinaId ?>&aba=ia&modo=<?= $modo ?>"
                                   class="nav-link <?= $abaAtiva === 'ia' ? 'active' : '' ?> border-0 px-4 py-3 fw-bold text-primary" role="tab">
                                    <i class="bi bi-robot me-2"></i>Análises da IA
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-4">
                            <?php if ($modo === 'adicionar'): ?>
                                <?php if ($abaAtiva === 'manual'): ?>
                                    <div class="dashboard-card mb-4 p-3 bg-light border">
                                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle me-2 text-primary"></i>Novo Registro (<?= e($disciplinaNome) ?>)</h6>
                                        <form method="POST">
                                            <div class="mb-3">
                                                <textarea name="conteudo" class="form-control" rows="5" placeholder="Descreva o desempenho..." required style="resize: none; border-radius: 10px;"></textarea>
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" name="adicionar_relatorio" class="btn btn-primary px-4 fw-bold" style="border-radius: 10px;">Salvar Registro</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="dashboard-card mb-4 p-3 bg-light border">
                                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-robot me-2 text-success"></i>Gerar Nova Análise (<?= e($disciplinaNome) ?>)</h6>

                                        <?php if ($sucessoIA): ?>
                                            <div class="alert alert-success d-flex align-items-center justify-content-between p-3" style="border-radius: 12px; border: none; background-color: #d1e7dd; color: #0f5132;">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                                                    <div>
                                                        <div class="fw-bold">Sucesso!</div>
                                                        <div class="small">O relatório foi gerado e salvo com sucesso.</div>
                                                    </div>
                                                </div>
                                                <a href="?aluno_id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&disciplina_id=<?= $disciplinaId ?>&aba=ia&modo=visualizar" class="btn btn-success btn-sm fw-bold px-3" style="border-radius: 8px;">Ver Relatórios</a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($mensagemIA): ?>
                                            <div class="alert alert-warning small mb-3"><?= $mensagemIA ?></div>
                                        <?php endif; ?>

                                        <form method="POST">
                                            <div class="row align-items-end g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label small fw-bold text-secondary">Selecione o Período:</label>
                                                    <select name="periodo_id_ia" class="form-select" style="border-radius: 10px;">
                                                        <option value="0">Todos os Períodos</option>
                                                        <?php foreach ($periodosDisponiveis as $p): ?>
                                                            <option value="<?= (int)$p['id'] ?>"><?= e($p['nome']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="submit" name="gerar_ia_aluno" class="btn btn-success w-100 fw-bold" style="border-radius: 10px;">Gerar IA</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php
                                $lista = ($abaAtiva === 'ia') ? $relatoriosIA : $relatoriosManual;
                                if (!empty($lista)): foreach ($lista as $rel):
                                ?>
                                    <div class="mb-4 pb-4 border-bottom last-child-border-0">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge <?= $rel['tipo'] === 'ia' ? 'bg-info' : 'bg-secondary' ?> mb-2">
                                                    <?= $rel['tipo'] === 'ia' ? 'IA' : 'Manual' ?>
                                                </span>
                                                <div class="text-muted small">
                                                    <?= date('d/m/Y H:i', strtotime($rel['criado_em'])) ?>
                                                    | <?= e($rel['professor_nome']) ?>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <?php if ($rel['tipo'] !== 'ia'): ?>
                                                    <button type="button" class="btn btn-light btn-sm text-primary"
                                                            onclick="abrirModalEdicao(<?= $rel['id'] ?>, `<?= addslashes($rel['conteudo']) ?>`)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <a class="btn btn-light btn-sm text-danger"
                                                   href="?aluno_id=<?= $alunoId ?>&turma_id=<?= $turmaId ?>&disciplina_id=<?= $disciplinaId ?>&excluir_id=<?= $rel['id'] ?>&aba=<?= $abaAtiva ?>&modo=visualizar"
                                                   onclick="return confirm('Excluir?')"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </div>
                                        <div class="relatorio-conteudo p-3 rounded bg-light" style="white-space: pre-wrap;"><?= e($rel['conteudo']) ?></div>
                                    </div>
                                <?php endforeach; else: ?>
                                    <div class="text-center py-5 text-muted">Nenhum registro encontrado para esta matéria.</div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="dashboard-card mb-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2 text-danger"></i>Controle de Faltas (<?= e($disciplinaNome) ?>)</h6>
                        <div class="text-center p-3 bg-danger bg-opacity-10 rounded-3 mb-4">
                            <div class="small text-danger fw-bold text-uppercase">Total de Faltas</div>
                            <div class="display-5 fw-bold text-danger"><?= $totalFaltas ?></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover border-0">
                                <thead class="bg-light"><tr><th>Mês</th><th class="text-center">Faltas</th></tr></thead>
                                <tbody>
                                    <?php if (!empty($faltasPorMes)): foreach ($faltasPorMes as $f): ?>
                                        <tr><td><?= $f['mes'] ?></td><td class="text-center"><span class="badge bg-danger rounded-pill"><?= $f['total_faltas'] ?></span></td></tr>
                                    <?php endforeach; else: ?>
                                        <tr><td colspan="2" class="text-center py-3 text-muted small">Nenhuma falta.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="modalEditarRelatorio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,.2);">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Editar Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="relatorio_id" id="edit_relatorio_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Conteúdo do Relatório</label>
                        <textarea name="conteudo" id="edit_conteudo" class="form-control" rows="6" required
                                  style="resize: none; border-radius: 12px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 10px;">Cancelar</button>
                    <button type="submit" name="salvar_edicao" class="btn btn-primary px-4" style="border-radius: 10px;">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalEdicao(id, conteudo) {
    document.getElementById('edit_relatorio_id').value = id;
    document.getElementById('edit_conteudo').value = conteudo;
    var myModal = new bootstrap.Modal(document.getElementById('modalEditarRelatorio'));
    myModal.show();
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
