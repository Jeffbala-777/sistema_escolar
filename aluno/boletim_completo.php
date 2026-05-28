<?php
// Ativa tipagem estrita para garantir seguranca no tratamento de dados
declare(strict_types=1);

// Protege o acesso garantindo que apenas alunos logados entrem na pagina
require_once __DIR__ . '/../app/middleware/verificar_aluno.php';
// Conexao central com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model responsavel por buscar e organizar as notas do aluno
require_once __DIR__ . '/../app/models/NotasModel.php';
// Model responsavel por gerenciar os periodos letivos (Bimestres/Trimestres)
require_once __DIR__ . '/../app/models/PeriodoLetivoModel.php';
// Model para faltas
require_once __DIR__ . '/../app/models/FaltaModel.php';

// Pega o ID do aluno logado na sessao
$alunoId = (int)$_SESSION['usuario']['id'];
// Pega o ID da escola vinculada ao aluno
$escolaId = (int)$_SESSION['usuario']['escola_id'];
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

// Instancia os modelos de dados necessarios
$notasModel = new NotaModel($pdo);
$periodoModel = new PeriodoLetivoModel($pdo);
$faltaModel = new FaltaModel($pdo);

// Busca os periodos configurados para a escola no banco
$periodosBanco = $periodoModel->listarPorAno($anoLetivoId, $escolaId);

// Lógica de períodos fixos conforme pedido
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

// Busca a frequencia mensal
$estatisticasFrequencia = $faltaModel->buscarEstatisticasFrequenciaMensal($alunoId, $anoLetivoId);
$diasFaltas = $faltaModel->buscarDiasFaltasMensais($alunoId, $anoLetivoId);

$mesesNomes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
$frequenciaGrafico = array_fill(1, 12, 100);
$totalAulasAno = 0;
$totalFaltasAno = 0;

foreach ($estatisticasFrequencia as $est) {
    if ($est['total_aulas'] > 0) {
        $freq = (($est['total_aulas'] - $est['total_faltas']) / $est['total_aulas']) * 100;
        $frequenciaGrafico[$est['mes']] = round($freq, 1);
        $totalAulasAno += $est['total_aulas'];
        $totalFaltasAno += $est['total_faltas'];
    }
}

$percentualGlobal = $totalAulasAno > 0 ? round((($totalAulasAno - $totalFaltasAno) / $totalAulasAno) * 100, 1) : 100;

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

$title = 'Boletim Completo';
require_once __DIR__ . '/../partials/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/aluno_menu.php'; ?>

    <main class="main-content flex-grow-1" style="background-color: #f8f9fa; min-height: 100vh;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <div class="bg-white shadow-sm rounded p-4 mx-auto" style="max-width: 1200px;">
                
                <!-- Cabecalho Informativo Conforme Imagem -->
                <div class="small mb-4 text-dark border-bottom pb-2">
                    <strong>Escola:</strong> <?= e($escolaNomeReal) ?> | 
                    <strong>Turma:</strong> <?= e($infoMatricula['turma_nome'] ?? '-') ?> | 
                    <strong>Ano de Escolaridade:</strong> <?= e($infoMatricula['serie'] ?? '-') ?> | 
                    <strong>Ano Escolar:</strong> <?= date('Y') ?>
                </div>

                <!-- Tabela de Notas Padronizada (COMPLETA) -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle text-center small">
                        <thead class="table-light">
                            <tr style="font-size: 0.65rem; background: #fdfdfd;">
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
                            
                            <!-- Linha de Total de Faltas -->
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

                <div class="small mb-5">
                    Situação final do aluno: <strong class="text-uppercase"><?= $percentualGlobal >= 75 ? 'APROVADO' : 'REPROVADO POR FALTA' ?></strong><br>
                    <span class="text-muted" style="font-size: 0.7rem;">* Etapa reaberta em <?= date('d/m/Y') ?></span>
                </div>

                <!-- Grafico de Frequencia -->
                <div class="text-center mb-5">
                    <h6 class="text-warning fw-bold small text-uppercase mb-4">Histórico de Frequência</h6>
                    <div style="height: 180px; width: 100%;">
                        <canvas id="freqChart"></canvas>
                    </div>
                </div>

                <!-- Detalhamento Mensal -->
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-sm text-center small" style="font-size: 0.7rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Mês</th>
                                <th>Dias Letivos</th>
                                <th>Aulas</th>
                                <th>Faltas</th>
                                <th>F.J.</th>
                                <th>Total</th>
                                <th>Freq.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mesesNomes as $idx => $nomeMes): 
                                $mesNum = $idx + 1;
                                $dadosMes = null;
                                foreach ($estatisticasFrequencia as $est) { if ($est['mes'] == $mesNum) { $dadosMes = $est; break; } }
                                $aulas = $dadosMes['total_aulas'] ?? 0;
                                $faltas = $dadosMes['total_faltas'] ?? 0;
                                $freq = $aulas > 0 ? round((($aulas - $faltas) / $aulas) * 100, 1) : 100;
                            ?>
                                <tr>
                                    <td class="fw-bold"><?= $nomeMes ?></td>
                                    <td>20</td>
                                    <td><?= $aulas ?></td>
                                    <td><?= $faltas ?></td>
                                    <td>0</td>
                                    <td><?= $faltas ?></td>
                                    <td class="fw-bold text-<?= $freq >= 75 ? 'success' : 'danger' ?>"><?= $freq ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Detalhamento de Datas das Faltas -->
                <div class="text-center">
                    <h6 class="text-warning fw-bold mb-3 small">Detalhes das Faltas</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-start small" style="font-size: 0.7rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Mês</th>
                                    <th>Dias - Faltas</th>
                                    <th>Dias - Faltas Justificadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mesesNomes as $idx => $nomeMes): 
                                    $mesNum = $idx + 1;
                                    $dias = isset($diasFaltas[$mesNum]) ? implode(', ', $diasFaltas[$mesNum]) : '';
                                ?>
                                    <tr>
                                        <td class="fw-bold"><?= $nomeMes ?></td>
                                        <td class="text-danger fw-bold small"><?= $dias ?></td>
                                        <td></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<script>
const ctx = document.getElementById('freqChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($mesesNomes) ?>,
        datasets: [{
            label: 'Frequência %',
            data: <?= json_encode(array_values($frequenciaGrafico)) ?>,
            borderColor: '#f39c12',
            backgroundColor: 'rgba(243, 156, 18, 0.1)',
            borderWidth: 2,
            fill: true,
            pointRadius: 3,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, max: 100, ticks: { font: { size: 10 } } },
            x: { ticks: { font: { size: 10 } } }
        },
        plugins: { legend: { display: false } }
    }
});
</script>

<style>
    .table-bordered td, .table-bordered th { border: 1px solid #e0e0e0 !important; }
    .table thead th { vertical-align: middle; font-weight: 600; color: #555; }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
