<?php
// Ativa tipagem estrita
declare(strict_types=1);

// Middleware de autenticação do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
// Conexão com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Models necessários
require_once __DIR__ . '/../app/models/TurmaModel.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

// Inicializa models
$turmaModel = new TurmaModel($pdo);
$usuarioModel = new UsuarioModel($pdo);
$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);

// Dados básicos
$escolaId = (int)$_SESSION['usuario']['escola_id'];
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
$tipoVisao = isset($_GET['tipo']) ? $_GET['tipo'] : 'alunos'; // alunos ou ia_turma
$professorFiltro = isset($_GET['professor_id']) ? (int)$_GET['professor_id'] : 0;
$periodoFiltro = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0;

// Busca todas as turmas da escola
$turmas = $turmaModel->listarPorEscola($escolaId);

// Dados da turma selecionada
$alunos = [];
$turmaAtual = null;
$professoresEscola = [];
$periodosDisponiveis = [];

if ($turmaId > 0) {
    $turmaAtual = $turmaModel->buscarPorId($turmaId, $escolaId);
    
    // Busca alunos da turma
    $stmtAlunos = $pdo->prepare("SELECT u.id, u.nome_completo, u.email, u.cpf 
                                FROM usuarios u 
                                INNER JOIN matriculas m ON m.aluno_id = u.id 
                                WHERE m.turma_id = :tid AND m.status = 'ativa' 
                                ORDER BY u.nome_completo ASC");
    $stmtAlunos->execute([':tid' => $turmaId]);
    $alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca TODOS os professores da escola (não apenas os da turma)
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
}

$title = 'Relatórios por Turma';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="container-fluid mt-4">
            
            <!-- Título e Seleção de Turma -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="fw-bold text-secondary mb-1">Relatórios Pedagógicos</h4>
                        <p class="text-muted mb-0">Visualize e gerencie os registros da turma.</p>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <form method="GET" class="d-flex gap-2">
                            <select name="turma_id" class="form-select shadow-sm" style="border-radius: 10px;" onchange="this.form.submit()">
                                <option value="">Escolher Turma...</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $turmaId === (int)$t['id'] ? 'selected' : '' ?>>
                                        <?= e($t['nome']) ?> (<?= e($t['serie']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($turmaId > 0): ?>
                <!-- Abas de Navegação -->
                <div class="mb-4">
                    <ul class="nav nav-pills bg-white p-2 shadow-sm rounded-pill d-inline-flex flex-wrap gap-2">
                        <li class="nav-item">
                            <a class="nav-link rounded-pill px-4 <?= $tipoVisao === 'alunos' ? 'active' : '' ?>" 
                               href="?turma_id=<?= $turmaId ?>&tipo=alunos">
                                <i class="bi bi-people me-2"></i>Alunos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill px-4 <?= $tipoVisao === 'ia_turma' ? 'active' : '' ?>" 
                               href="?turma_id=<?= $turmaId ?>&tipo=ia_turma">
                                <i class="bi bi-robot me-2"></i>IA da Turma
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- VISUALIZAÇÃO: ALUNOS -->
                <?php if ($tipoVisao === 'alunos'): ?>
                    <div class="row g-4">
                        <?php if (!empty($alunos)): ?>
                            <?php foreach ($alunos as $aluno): ?>
                            <div class="col-md-6 col-lg-4">
                                <!-- Cartão individual do aluno (Layout padronizado com o do professor) -->
                                <div class="card border-0 shadow-sm h-100 card-hover" style="border-radius: 15px;">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <!-- Avatar representativo -->
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                                <i class="bi bi-person-fill fs-4"></i>
                                            </div>
                                            <div>
                                                <!-- Nome do aluno -->
                                                <h6 class="mb-0 fw-bold text-dark"><?= e($aluno['nome_completo']) ?></h6>
                                            </div>
                                        </div>
                                        
                                        <!-- Informações adicionais -->
                                        <div class="mb-4">
                                            <div class="small text-muted mb-1">
                                                <i class="bi bi-envelope me-2"></i> <?= e($aluno['email'] ?? 'E-mail não informado') ?>
                                            </div>
                                            <div class="small text-muted">
                                            </div>
                                        </div>

                                        <!-- Botão Visualizar -->
                                        <a href="relatorios_aluno.php?id=<?= $aluno['id'] ?>&turma_id=<?= $turmaId ?>" class="btn btn-primary btn-sm w-100 fw-bold py-2 shadow-sm" style="border-radius: 10px;">
                                            <i class="bi bi-eye me-1"></i> Visualizar
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <i class="bi bi-people text-muted opacity-25 d-block mb-3" style="font-size: 64px;"></i>
                                <h5 class="fw-bold text-secondary">Nenhum aluno nesta turma</h5>
                            </div>
                        <?php endif; ?>
                    </div>

                <!-- VISUALIZAÇÃO: IA DA TURMA -->
                <?php elseif ($tipoVisao === 'ia_turma'): ?>
                    <!-- Filtros de Professor e Período para IA -->
                    <div class="card border-0 shadow-sm p-3 mb-4" style="border-radius: 12px;">
                        <form method="GET" class="row g-3 align-items-end">
                            <input type="hidden" name="turma_id" value="<?= $turmaId ?>">
                            <input type="hidden" name="tipo" value="ia_turma">
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Filtrar por Professor</label>
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
                                <label class="form-label small fw-bold text-muted">Filtrar por Período</label>
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

                    <!-- Lista de Análises de IA -->
                    <?php
                        $sqlRelIA = "SELECT r.*, p.nome_completo as professor_nome 
                                    FROM relatorios_alunos r
                                    INNER JOIN usuarios p ON p.id = r.professor_id
                                    WHERE r.turma_id = :tid AND r.escola_id = :eid AND r.tipo = 'ia' AND r.aluno_id IS NULL";
                        
                        $paramsRelIA = [':tid' => $turmaId, ':eid' => $escolaId];
                        
                        if ($professorFiltro > 0) {
                            $sqlRelIA .= " AND r.professor_id = :pid";
                            $paramsRelIA[':pid'] = $professorFiltro;
                        }
                        
                        // Filtro de período via LIKE (já que o período é armazenado no conteúdo)
                        if ($periodoFiltro > 0) {
                            $periodoNome = null;
                            foreach ($periodosDisponiveis as $per) {
                                if ((int)$per['id'] === $periodoFiltro) {
                                    $periodoNome = $per['nome'];
                                    break;
                                }
                            }
                            if ($periodoNome) {
                                $sqlRelIA .= " AND r.conteudo LIKE :filtro_periodo";
                                $paramsRelIA[':filtro_periodo'] = "%[" . $periodoNome . "]%";
                            }
                        }
                        
                        $sqlRelIA .= " ORDER BY r.criado_em DESC";
                        $stmtRelIA = $pdo->prepare($sqlRelIA);
                        $stmtRelIA->execute($paramsRelIA);
                        $relatoriosIA = $stmtRelIA->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (!empty($relatoriosIA)): ?>
                        <div class="row">
                            <?php foreach ($relatoriosIA as $rel): ?>
                                <div class="col-12 mb-3">
                                    <div class="card border-0 shadow-sm" style="border-radius: 15px; border-left: 5px solid #8e44ad;">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="fw-bold text-primary mb-1">
                                                        <i class="bi bi-robot me-2"></i>Análise de IA da Turma
                                                    </h5>
                                                    <p class="text-muted small mb-0">
                                                        <i class="bi bi-person-circle me-1"></i> 
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
                            <i class="bi bi-robot text-muted opacity-25 d-block mb-3" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-secondary">Nenhuma análise de IA encontrada</h5>
                            <p class="text-muted">Não há análises de inteligência artificial para a turma selecionada com os filtros aplicados.</p>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 20px;">
                    <i class="bi bi-arrow-up-circle text-primary opacity-25 d-block mb-3" style="font-size: 64px;"></i>
                    <h5 class="fw-bold text-secondary">Selecione uma Turma</h5>
                    <p class="text-muted">Aguardando sua escolha para mostrar os alunos e relatórios.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .card-hover { transition: all 0.3s ease; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
