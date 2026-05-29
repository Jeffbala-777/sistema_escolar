<?php
declare(strict_types=1); // Forca tipagem estrita

// Protege acesso do aluno
require_once __DIR__ . '/../app/middleware/verificar_aluno.php';
require_once __DIR__ . '/../app/database/database.php'; // Conexao banco
require_once __DIR__ . '/../app/models/NotasModel.php'; // Notas e Faltas consolidadas
require_once __DIR__ . '/../app/models/FaltaModel.php'; // Estatisticas mensais
require_once __DIR__ . '/../app/models/PeriodoLetivoModel.php'; // Bimestres/Trimestres

$alunoId = $_SESSION['usuario']['id']; // ID do aluno logado
$escolaId = $_SESSION['usuario']['escola_id']; // ID da escola
$anoLetivoId = 1; // Padrao do sistema

$notasModel = new NotaModel($pdo); // Inicia models
$faltaModel = new FaltaModel($pdo);
$periodoModel = new PeriodoLetivoModel($pdo);

$periodos = $periodoModel->listarPorAno($anoLetivoId, $escolaId); // Busca bimestres
$boletim = $notasModel->buscarNotasCompletasAluno($alunoId, $anoLetivoId); // Dados da tabela principal
$estatisticasFrequencia = $faltaModel->buscarEstatisticasFrequenciaMensal($alunoId, $anoLetivoId); // Dados mensais
$diasFaltas = $faltaModel->buscarDiasFaltasMensais($alunoId, $anoLetivoId); // Datas das faltas

// Preparar dados para o grafico e calculos globais
$mesesNomes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
$frequenciaGrafico = array_fill(1, 12, 100); // Inicia meses com 100%
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

// Percentual de frequencia do ano todo
$percentualGlobal = $totalAulasAno > 0 ? round((($totalAulasAno - $totalFaltasAno) / $totalAulasAno) * 100, 1) : 100;

$title = 'Boletim Escolar Detalhado'; // Titulo da aba
require_once __DIR__ . '/../partials/header.php'; // Topo padrao
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Biblioteca de graficos -->

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/aluno_menu.php'; ?> <!-- Menu do aluno -->

    <main class="main-content flex-grow-1" style="background-color: #f8f9fa; min-height: 100vh;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Topbar -->

        <div class="p-4">
            <div class="bg-white shadow-sm rounded p-4 mx-auto" style="max-width: 1100px;">
                
                <!-- Cabecalho Estilo SEGES -->
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-secondary">Boletins Escolares</h4>
                    <div class="alert alert-danger py-2 px-3 text-start small border-start border-4 border-danger">
                        <strong>Importante:</strong><br>
                        - Etapas em aberto estão suscetíveis de serem alteradas a qualquer momento<br>
                        - Etapas fechadas podem ser alteradas somente com autorização pedagógica
                    </div>
                    <div class="border-top pt-2 small fw-bold text-muted mt-3">
                        Escola: <?= e($_SESSION['usuario']['escola_nome'] ?? 'Escola Municipal') ?> | Ano: <?= date('Y') ?>
                    </div>
                </div>

                <!-- Tabela de Resultados (Notas e Faltas) -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle text-center small">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="text-start py-3">Áreas de Conhecimento / Disciplinas</th>
                                <?php foreach ($periodos as $p): ?>
                                    <th colspan="3"><?= e($p['nome']) ?></th>
                                <?php endforeach; ?>
                                <th rowspan="2">AP FINAL</th>
                                <th rowspan="2">RECUP.</th>
                                <th rowspan="2">RES FINAL</th>
                            </tr>
                            <tr>
                                <?php foreach ($periodos as $p): ?>
                                    <th style="font-size: 0.7rem;">NOTA</th>
                                    <th style="font-size: 0.7rem;">FALTA</th>
                                    <th style="font-size: 0.7rem;">F.J.</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $faltasPorPeriodo = array_fill_keys(array_column($periodos, 'id'), 0);
                            foreach ($boletim as $disciplina => $dados): 
                                $somaNotas = 0;
                                $contNotas = 0;
                            ?>
                                <tr>
                                    <td class="text-start fw-bold text-uppercase"><?= e($disciplina) ?></td>
                                    <?php foreach ($periodos as $p): 
                                        $n = $dados[$p['id']]['nota'] ?? '-';
                                        $f = $dados[$p['id']]['faltas'] ?? 0;
                                        if ($n !== '-') { $somaNotas += (float)$n; $contNotas++; }
                                        $faltasPorPeriodo[$p['id']] += (int)$f;
                                    ?>
                                        <td><?= $n ?></td> <!-- Nota do periodo -->
                                        <td><?= $f > 0 ? $f : '0' ?></td> <!-- Faltas no periodo -->
                                        <td>0</td> <!-- Faltas Justificadas (FJ) -->
                                    <?php endforeach; ?>
                                    <?php $media = $contNotas > 0 ? round($somaNotas / $contNotas, 1) : '-'; ?>
                                    <td class="fw-bold"><?= $media ?></td> <!-- Aproveitamento Final -->
                                    <td>--</td> <!-- Recuperacao Final -->
                                    <td class="fw-bold"><?= $media ?></td> <!-- Resultado Final -->
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light fw-bold">
                                <td class="text-start">TOTAL DE FALTAS</td>
                                <?php foreach ($periodos as $p): ?>
                                    <td>--</td>
                                    <td><?= $faltasPorPeriodo[$p['id']] ?></td> <!-- Soma faltas periodo -->
                                    <td>0</td>
                                <?php endforeach; ?>
                                <td>--</td>
                                <td>--</td>
                                <td class="bg-warning bg-opacity-10"><?= $totalFaltasAno ?></td> <!-- Total faltas ano -->
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="fw-bold mb-5 small">
                    Situação final do aluno: 
                    <span class="text-<?= $percentualGlobal >= 75 ? 'success' : 'danger' ?>">
                        <?= $percentualGlobal >= 75 ? 'APROVADO' : 'REPROVADO POR FALTA' ?>
                    </span>
                </div>

                <!-- Grafico de Frequencia -->
                <div class="mb-5 text-center">
                    <h6 class="text-warning fw-bold mb-4">Histórico de Frequência</h6>
                    <div style="height: 200px; width: 100%;">
                        <canvas id="freqChart"></canvas> <!-- Canvas para o Chart.js -->
                    </div>
                </div>

                <!-- Tabela Mensal de Frequencia -->
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-sm text-center small">
                        <thead class="table-light">
                            <tr>
                                <th>Mês</th>
                                <th>Qnt. Dias Letivos</th>
                                <th>Qnt. Aulas</th>
                                <th>Qnt. Faltas</th>
                                <th>Qnt. F.J.</th>
                                <th>Total Faltas</th>
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
                                    <td>20</td> <!-- Simulado (Media de dias letivos) -->
                                    <td><?= $aulas ?></td> <!-- Total de aulas dadas no mes -->
                                    <td><?= $faltas ?></td> <!-- Faltas do aluno -->
                                    <td>0</td> <!-- Justificadas -->
                                    <td><?= $faltas ?></td> <!-- Total de faltas -->
                                    <td><?= $freq ?>%</td> <!-- Porcentagem de presenca -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Detalhes das Faltas (Datas) -->
                <div class="text-center">
                    <h6 class="text-warning fw-bold mb-3">Detalhes das Faltas</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-start small">
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
                                        <td><?= $dias ?></td> <!-- Lista de dias que faltou -->
                                        <td></td> <!-- Lista de dias justificados -->
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
// Script para gerar o grafico de linha da frequencia
const ctx = document.getElementById('freqChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($mesesNomes) ?>,
        datasets: [{
            label: 'Frequência %',
            data: <?= json_encode(array_values($frequenciaGrafico)) ?>,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, max: 100, ticks: { callback: function(value) { return value + '%'; } } }
        },
        plugins: { legend: { display: false } }
    }
});
</script>

<style>
    /* Estilos extras para bordas e cores do boletim */
    .table-bordered td, .table-bordered th { border: 1px solid #dee2e6 !important; }
    .main-content { transition: all 0.3s; }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape padrao -->
