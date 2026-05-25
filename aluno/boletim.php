<?php
declare(strict_types=1); // Forca tipagem estrita

// Verifica se o aluno esta logado
require_once __DIR__ . '/../app/middleware/verificar_aluno.php';
require_once __DIR__ . '/../app/database/database.php'; // Conexao banco
require_once __DIR__ . '/../app/models/NotasModel.php'; // Model de notas
require_once __DIR__ . '/../app/models/PeriodoLetivoModel.php'; // Model de periodos

$alunoId = $_SESSION['usuario']['id']; // ID do aluno
$escolaId = $_SESSION['usuario']['escola_id']; // ID da escola
$anoLetivoId = 1; // Ano padrao

$notasModel = new NotaModel($pdo); // Inicia models
$periodoModel = new PeriodoLetivoModel($pdo);

// Busca periodos e boletim consolidado
$periodos = $periodoModel->listarPorAno($anoLetivoId, $escolaId);
$boletim = $notasModel->buscarNotasCompletasAluno($alunoId, $anoLetivoId);

$title = 'Meu Boletim'; // Titulo da pagina
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/aluno_menu.php'; ?> <!-- Menu lateral -->

    <main class="main-content flex-grow-1" style="background-color: #f8f9fa; min-height: 100vh;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Topbar -->

        <div class="p-4">
            <div class="bg-white shadow-sm rounded p-4 mx-auto" style="max-width: 1100px;">
                
                <h4 class="fw-bold text-secondary text-center mb-4">Notas por Disciplina</h4>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center small">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start py-3">Disciplina</th>
                                <?php foreach ($periodos as $p): ?>
                                    <th><?= e($p['nome']) ?></th> <!-- Nome do periodo (Bimestre/Trimestre) -->
                                <?php endforeach; ?>
                                <th>Média Final</th>
                                <th>Situação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($boletim as $disciplina => $dados): 
                                $soma = 0; $cont = 0;
                            ?>
                                <tr>
                                    <td class="text-start fw-bold"><?= e($disciplina) ?></td>
                                    <?php foreach ($periodos as $p): 
                                        $n = $dados[$p['id']]['nota'] ?? '-';
                                        if ($n !== '-') { $soma += (float)$n; $cont++; }
                                    ?>
                                        <td><?= $n ?></td> <!-- Nota do periodo -->
                                    <?php endforeach; ?>
                                    <?php $media = $cont > 0 ? round($soma / $cont, 1) : '-'; ?>
                                    <td class="fw-bold"><?= $media ?></td> <!-- Media calculada -->
                                    <td>
                                        <?php if ($media !== '-'): ?>
                                            <span class="badge bg-<?= $media >= 7 ? 'success' : 'warning' ?>">
                                                <?= $media >= 7 ? 'Aprovado' : 'Recuperação' ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
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
