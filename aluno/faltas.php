<?php
declare(strict_types=1); // Forca tipagem estrita

// Verifica se o aluno esta logado
require_once __DIR__ . '/../app/middleware/verificar_aluno.php';
require_once __DIR__ . '/../app/database/database.php'; // Conexao banco
require_once __DIR__ . '/../app/models/FaltaModel.php'; // Model de faltas

$alunoId = $_SESSION['usuario']['id']; // ID do aluno
$escolaId = $_SESSION['usuario']['escola_id']; // ID da escola

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

$faltaModel = new FaltaModel($pdo); // Inicia model

// Busca estatisticas mensais e dias de faltas
$mesesNomes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
$estatisticas = $faltaModel->buscarEstatisticasFrequenciaMensal($alunoId, $anoLetivoId);
$diasFaltas = $faltaModel->buscarDiasFaltasMensais($alunoId, $anoLetivoId);

$title = 'Minha Frequência'; // Titulo da pagina
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/aluno_menu.php'; ?> <!-- Menu lateral -->

    <main class="main-content flex-grow-1" style="background-color: #f8f9fa; min-height: 100vh;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Topbar -->

        <div class="p-4">
            <div class="bg-white shadow-sm rounded p-4 mx-auto" style="max-width: 1100px;">
                
                <h4 class="fw-bold text-secondary text-center mb-4">Histórico de Frequência</h4>

                <!-- Tabela Mensal de Frequencia -->
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-sm text-center small">
                        <thead class="table-light">
                            <tr>
                                <th>Mês</th>
                                <th>Qnt. Aulas</th>
                                <th>Qnt. Faltas</th>
                                <th>Freq. %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mesesNomes as $idx => $nomeMes): 
                                $mesNum = $idx + 1;
                                $dadosMes = null;
                                foreach ($estatisticas as $est) { if ($est['mes'] == $mesNum) { $dadosMes = $est; break; } }
                                $aulas = $dadosMes['total_aulas'] ?? 0;
                                $faltas = $dadosMes['total_faltas'] ?? 0;
                                $freq = $aulas > 0 ? round((($aulas - $faltas) / $aulas) * 100, 1) : 100;
                            ?>
                                <tr>
                                    <td class="fw-bold"><?= $nomeMes ?></td>
                                    <td><?= $aulas ?></td> <!-- Total de aulas dadas -->
                                    <td><?= $faltas ?></td> <!-- Faltas registradas -->
                                    <td class="fw-bold text-<?= $freq >= 75 ? 'success' : 'danger' ?>"><?= $freq ?>%</td> <!-- Percentual -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Detalhes das Faltas (Datas) -->
                <h6 class="text-warning fw-bold mb-3 text-center">Detalhamento por Datas</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-start small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px;">Mês</th>
                                <th>Dias das Faltas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mesesNomes as $idx => $nomeMes): 
                                $mesNum = $idx + 1;
                                $dias = isset($diasFaltas[$mesNum]) ? implode(', ', $diasFaltas[$mesNum]) : 'Nenhuma falta';
                            ?>
                                <tr>
                                    <td class="fw-bold"><?= $nomeMes ?></td>
                                    <td class="<?= empty($dias) || $dias == 'Nenhuma falta' ? 'text-muted italic' : 'text-danger fw-bold' ?>">
                                        <?= $dias ?> <!-- Lista de dias que faltou -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape -->
