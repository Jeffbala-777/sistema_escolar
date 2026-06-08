<?php
// Ativa tipagem estrita para segurança
declare(strict_types=1);

// Middleware de autenticação do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
// Conexão com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model de turmas
require_once __DIR__ . '/../app/models/TurmaModel.php';
// Model de relatórios
require_once __DIR__ . '/../app/models/RelatorioAlunoModel.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

// Inicializa models
$turmaModel = new TurmaModel($pdo);
$relatorioModel = new RelatorioAlunoModel($pdo);
$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);

// ID da escola do admin logado
$escolaId = (int)$_SESSION['usuario']['escola_id'];
// Turma selecionada (via GET)
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
// Tipo de visualização (sempre IA agora nesta tela geral, ou redireciona)
$tipoVisao = 'ia';
// Filtro de professor
$professorFiltro = isset($_GET['professor_id']) ? (int)$_GET['professor_id'] : 0;
// Filtro de período
$periodoFiltro = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0;

// Busca todas as turmas da escola para o select
$turmas = $turmaModel->listarPorEscola($escolaId);

// Busca professores e períodos se uma turma for selecionada
$relatorios = [];
$turmaSelecionada = null;
$professoresEscola = [];
$periodosDisponiveis = [];

if ($turmaId > 0) {
    $turmaSelecionada = $turmaModel->buscarPorId($turmaId, $escolaId);
    
    // Busca TODOS os professores da escola
    $stmtProfessores = $pdo->prepare("SELECT u.id, u.nome_completo FROM usuarios u INNER JOIN perfis p ON p.id = u.perfil_id WHERE u.escola_id = :eid AND p.nome = 'professor'AND u.ativo = 1 ORDER BY u.nome_completo ASC");
    $stmtProfessores->execute([':eid' => $escolaId]);
    $professoresEscola = $stmtProfessores->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca períodos letivos ativos
    $stmtPeriodos = $pdo->prepare("SELECT pl.id, pl.nome FROM periodos_letivos pl 
                                   INNER JOIN anos_letivos al ON al.id = pl.ano_letivo_id 
                                   WHERE al.escola_id = :eid AND al.ativo = 1 
                                   ORDER BY pl.ordem ASC");
    $stmtPeriodos->execute([':eid' => $escolaId]);
    $periodosDisponiveis = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);
    
    // Relatórios de IA
    $sql = "SELECT r.*, p.nome_completo as professor_nome, a.nome_completo as aluno_nome 
            FROM relatorios_alunos r
            INNER JOIN usuarios p ON p.id = r.professor_id
            LEFT JOIN usuarios a ON a.id = r.aluno_id
            WHERE r.turma_id = :tid AND r.escola_id = :eid AND r.tipo = 'ia'";
    
    $params = [':tid' => $turmaId, ':eid' => $escolaId];
    
    if ($professorFiltro > 0) {
        $sql .= " AND r.professor_id = :pid";
        $params[':pid'] = $professorFiltro;
    }
    
    // Filtro de período via LIKE
    if ($periodoFiltro > 0) {
        $periodoNome = null;
        foreach ($periodosDisponiveis as $per) {
            if ((int)$per['id'] === $periodoFiltro) {
                $periodoNome = $per['nome'];
                break;
            }
        }
        if ($periodoNome) {
            $sql .= " AND r.conteudo LIKE :filtro_periodo";
            $params[':filtro_periodo'] = "%[" . $periodoNome . "]%";
        }
    }
    
    $sql .= " ORDER BY r.criado_em DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Título da página
$title = 'Relatórios de IA';
// Header padrão
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php // Menu lateral do admin
    require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">
        <?php // Painel superior
        require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            <!-- Título e Seleção de Turma -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="fw-bold text-secondary mb-1">Análises de Inteligência Artificial</h4>
                        <p class="text-muted mb-0">Visualize os registros gerados pela IA para cada turma</p>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <form method="GET" class="d-flex gap-2">
                            <select name="turma_id" class="form-select shadow-sm" style="border-radius: 10px;" onchange="this.form.submit()">
                                <option value="">Selecione uma Turma</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $turmaId === (int)$t['id'] ? 'selected' : '' ?>>
                                        <?= e($t['nome']) ?> (<?= e($t['serie']) ?> - <?= e($t['turno']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($turmaId > 0): ?>
                <!-- Filtros -->
                <div class="card border-0 shadow-sm p-3 mb-4" style="border-radius: 12px;">
                    <form method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="turma_id" value="<?= $turmaId ?>">
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">
                                <i class="bi bi-funnel me-1"></i>Filtrar por Professor
                            </label>
                            <select name="professor_id" class="form-select border-0 bg-light rounded-3" onchange="this.form.submit()">
                                <option value="0">Todos os Professores</option>
                                <?php foreach ($professoresEscola as $prof): ?>
                                    <option value="<?= $prof['id'] ?>" <?= $professorFiltro === (int)$prof['id'] ? 'selected' : '' ?>>
                                        <?= e($prof['nome_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">
                                <i class="bi bi-calendar-event me-1"></i>Filtrar por Período
                            </label>
                            <select name="periodo_id" class="form-select border-0 bg-light rounded-3" onchange="this.form.submit()">
                                <option value="0">Todos os Períodos</option>
                                <?php foreach ($periodosDisponiveis as $per): ?>
                                    <option value="<?= $per['id'] ?>" <?= $periodoFiltro === (int)$per['id'] ? 'selected' : '' ?>>
                                        <?= e($per['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Lista de Relatórios -->
                <?php if (!empty($relatorios)): ?>
                    <div class="row">
                        <?php foreach ($relatorios as $rel): ?>
                            <div class="col-12 mb-3">
                                <div class="card border-0 shadow-sm" style="border-radius: 15px; border-left: 5px solid #8e44ad;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="fw-bold text-primary mb-1">
                                                    <?php if ($rel['aluno_id'] == 0 || $rel['aluno_id'] === null): ?>
                                                        <i class="bi bi-robot me-2"></i>Análise de IA da Turma
                                                    <?php else: ?>
                                                        <i class="bi bi-person-badge me-2"></i><?= e($rel['aluno_nome'] ?? 'Aluno') ?>
                                                    <?php endif; ?>
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    <i class="bi bi-robot me-1"></i> 
                                                    Solicitado por: <?= e($rel['professor_nome']) ?>
                                                </p>
                                            </div>
                                            <span class="badge bg-light text-secondary border px-3 py-2" style="border-radius: 8px;">
                                                <i class="bi bi-calendar3 me-2"></i><?= date('d/m/Y H:i', strtotime($rel['criado_em'])) ?>
                                            </span>
                                        </div>
                                        <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-wrap; line-height: 1.6;">
                                            <?= e($rel['conteudo']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 15px;">
                        <i class="bi bi-robot text-muted opacity-25" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-secondary">Nenhuma análise encontrada</h5>
                        <p class="text-muted">Não há registros de IA para a turma selecionada com os filtros aplicados.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 15px;">
                    <i class="bi bi-arrow-up-circle text-primary opacity-25" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-secondary">Aguardando Seleção</h5>
                    <p class="text-muted">Selecione uma turma acima para visualizar as análises de IA correspondentes.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php // Footer padrão
require_once __DIR__ . '/../partials/footer.php'; ?>
