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
$stmtEscola = $pdo->prepare("SELECT nome FROM escolas WHERE id = :id LIMIT 1");
$stmtEscola->execute([':id' => $escolaId]);
$escolaNomeReal = $stmtEscola->fetchColumn() ?: 'Minha Escola';

// Busca dados da matricula
$stmtMatricula = $pdo->prepare("SELECT t.nome as turma_nome, t.serie FROM matriculas m 
                                JOIN turmas t ON t.id = m.turma_id 
                                WHERE m.aluno_id = :aid AND m.status = 'ativa' LIMIT 1");
$stmtMatricula->execute([':aid' => $alunoId]);
$infoMatricula = $stmtMatricula->fetch(PDO::FETCH_ASSOC);

$title = 'Visualizar Aluno - ' . $aluno['nome_completo'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-content flex-grow-1">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <div class="mb-4">
                <a href="alunos.php" class="btn btn-link text-decoration-none p-0 text-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para lista de alunos
                </a>
            </div>

            <div class="bg-white shadow-sm rounded p-4 mx-auto" style="max-width: 1200px;">
                
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
        </div>
    </div>
</div>

<style>
    .table-bordered td, .table-bordered th { border: 1px solid #e0e0e0 !important; }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
