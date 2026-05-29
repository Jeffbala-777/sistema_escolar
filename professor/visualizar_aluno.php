<?php
// Protege o acesso garantindo que apenas professores logados entrem
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
// Conexao central com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model para consolidar notas e faltas do aluno
require_once __DIR__ . '/../app/models/NotasModel.php';
// Model para gerenciar os periodos letivos (Bimestres/Trimestres)
require_once __DIR__ . '/../app/models/PeriodoLetivoModel.php';
// Model para buscar dados do usuario/aluno
require_once __DIR__ . '/../app/models/UsuarioModel.php';

// Pega o ID do aluno e da turma via URL
$alunoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
// Pega o ID da escola do professor logado
$escolaId = (int)$_SESSION['usuario']['escola_id'];
// Instancia os modelos necessarios
$notasModel = new NotaModel($pdo);
$periodoModel = new PeriodoLetivoModel($pdo);
$usuarioModel = new UsuarioModel($pdo);

// Busca os dados do aluno para exibir no topo
$aluno = $usuarioModel->buscarPorId($alunoId);
// Se o aluno nao existir ou nao for da mesma escola, volta para a lista
if (!$aluno || (int)$aluno['escola_id'] !== $escolaId) {
    header('Location: minhas_turmas.php');
    exit;
}

// Busca o ano letivo ativo do aluno via matricula
$stmtAno = $pdo->prepare("
    SELECT ano_letivo_id
    FROM matriculas
    WHERE aluno_id = :aid
    AND status = 'ativa'
    LIMIT 1
");
$stmtAno->execute([':aid' => $alunoId]);
$anoLetivoId = (int) ($stmtAno->fetchColumn() ?: 1);

// Busca os periodos configurados para a escola
$periodosBanco = $periodoModel->listarPorAno($anoLetivoId, $escolaId);

// Lógica de períodos fixos (Fallback para 4 bimestres se não houver no banco)
$periodos = [];
if (count($periodosBanco) > 0) {
    $periodos = $periodosBanco;
} else {
    for ($i = 1; $i <= 4; $i++) {
        $periodos[] = ['id' => $i, 'nome' => $i . 'º Bimestre'];
    }
}
// Busca o boletim completo do aluno (O model ja garante que as materias aparecam mesmo sem notas)
$boletim = $notasModel->buscarNotasCompletasAluno($alunoId, $anoLetivoId);

// Define o titulo da pagina
$title = 'Boletim do Aluno - ' . $aluno['nome_completo'];
// Inclui o cabecalho padrao
require_once __DIR__ . '/../partials/header.php';
?>

<!-- Estrutura principal com menu lateral -->
<div class="d-flex page-wrap">

    <!-- Menu lateral do professor -->
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <!-- Area de conteudo da pagina -->
    <div class="content-area p-4">

        <!-- Painel superior com nome e cargo -->
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <!-- Botao para voltar para a lista de alunos da turma -->
            <div class="mb-4">
                <a href="meus_alunos.php?turma_id=<?= $turmaId ?>" class="btn btn-link text-decoration-none p-0 text-secondary fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para os alunos da turma
                </a>
            </div>

            <!-- Cabecalho com informacoes do aluno -->
            <div class="bg-white shadow-sm rounded p-4 mb-4" style="border-left: 5px solid #3498db;">
                <div class="d-flex align-items-center">
                    <!-- Icone de perfil -->
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                    <div>
                        <!-- Nome completo e ID do aluno -->
                        <h4 class="fw-bold mb-1 text-secondary"><?= e($aluno['nome_completo']) ?></h4>
                        <p class="text-muted mb-0 small text-uppercase fw-bold">ID do Aluno: <?= $aluno['id'] ?> • Ano Letivo: <?= date('Y') ?></p>
                    </div>
                </div>
            </div>

            <!-- TABELA DE NOTAS (Design profissional SEGES/Sedu) -->
            <div class="bg-white shadow-sm rounded p-4 mb-4">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-journal-check fs-4 text-primary me-2"></i>
                    <h5 class="fw-bold text-secondary mb-0">Quadro de Notas por Disciplina</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center small">
                        <thead class="table-light">
                            <tr>
                                <!-- Cabecalho fixo das materias -->
                                <th class="text-start py-3 px-3">Disciplinas Disponíveis</th>
                                <!-- Gera colunas para cada periodo (Bimestre/Trimestre) -->
                                <?php foreach ($periodos as $p): ?>
                                    <th style="min-width: 100px;"><?= e($p['nome']) ?></th>
                                <?php endforeach; ?>
                                <th class="bg-primary bg-opacity-10" style="min-width: 100px;">Média Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Percorre cada disciplina (O Model garante que as materias fixas aparecam)
                            foreach ($boletim as $disciplina => $dados): 
                                $somaNotas = 0;
                                $contNotas = 0;
                            ?>
                                <tr>
                                    <!-- Nome da disciplina em destaque -->
                                    <td class="text-start fw-bold px-3 text-uppercase small"><?= e($disciplina) ?></td>
                                    <?php foreach ($periodos as $p): 
                                        // Pega a nota ou coloca '-' se nao houver nota lancada
                                        $n = $dados[$p['id']]['nota'] ?? '-';
                                        if ($n !== '-') { $somaNotas += (float)$n; $contNotas++; }
                                    ?>
                                        <td class="fw-bold <?= $n !== '-' && (float)$n < 6 ? 'text-danger' : '' ?>"><?= $n ?></td>
                                    <?php endforeach; ?>
                                    <!-- Calcula a media final da disciplina -->
                                    <?php $media = $contNotas > 0 ? round($somaNotas / $contNotas, 1) : '-'; ?>
                                    <td class="fw-bold text-primary bg-primary bg-opacity-10"><?= $media ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABELA DE FALTAS (Design profissional SEGES/Sedu) -->
            <div class="bg-white shadow-sm rounded p-4">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-calendar-check fs-4 text-danger me-2"></i>
                    <h5 class="fw-bold text-secondary mb-0">Controle de Frequência por Período</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center small">
                        <thead class="table-light">
                            <tr>
                                <!-- Cabecalho fixo das materias -->
                                <th class="text-start py-3 px-3">Disciplinas Disponíveis</th>
                                <!-- Gera colunas para cada periodo (Bimestre/Trimestre) -->
                                <?php foreach ($periodos as $p): ?>
                                    <th style="min-width: 100px;"><?= e($p['nome']) ?></th>
                                <?php endforeach; ?>
                                <th class="bg-danger bg-opacity-10" style="min-width: 100px;">Total Faltas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Percorre cada disciplina para listar as faltas
                            foreach ($boletim as $disciplina => $dados): 
                                $totalFaltas = 0;
                            ?>
                                <tr>
                                    <!-- Nome da disciplina em destaque -->
                                    <td class="text-start fw-bold px-3 text-uppercase small"><?= e($disciplina) ?></td>
                                    <?php foreach ($periodos as $p): 
                                        // Pega as faltas ou coloca '0' se nao houver registro
                                        $f = (int)($dados[$p['id']]['faltas'] ?? 0);
                                        $totalFaltas += $f;
                                    ?>
                                        <td class="<?= $f > 0 ? 'text-danger fw-bold' : 'text-muted' ?>"><?= $f ?></td>
                                    <?php endforeach; ?>
                                    <!-- Exibe o total de faltas acumuladas no ano -->
                                    <td class="fw-bold text-danger bg-danger bg-opacity-10"><?= $totalFaltas ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Estilo CSS para bordas das tabelas -->
<style>
    .table-bordered td, .table-bordered th { border: 1px solid #dee2e6 !important; }
</style>

<!-- Inclui o rodape padrao -->
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
