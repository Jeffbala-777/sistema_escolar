<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';

$professorId = $_SESSION['usuario']['id'];
$escolaId = $_SESSION['usuario']['escola_id'];
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;

$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);
$turmaModel = new TurmaModel($pdo);

// Validar acesso à turma
$turmasProfessor = $ptdModel->listarTurmasProfessor($professorId, $escolaId);
$turmaAtual = null;
foreach ($turmasProfessor as $tp) {
    if ((int)$tp['turma_id'] === $turmaId) {
        $turmaAtual = $tp;
        break;
    }
}

if (!$turmaAtual) {
    header('Location: selecionar_turma.php');
    exit;
}

// Buscar faltas gerais da turma por mês
$sqlFaltasTurma = "
    SELECT 
        DATE_FORMAT(a.data_aula, '%m/%Y') as mes,
        COUNT(p.id) as total_faltas
    FROM presencas p
    INNER JOIN aulas a ON a.id = p.aula_id
    INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
    WHERE ptd.turma_id = :turma_id
    AND p.status = 'falta'
    AND p.escola_id = :escola_id
    GROUP BY DATE_FORMAT(a.data_aula, '%m/%Y')
    ORDER BY a.data_aula DESC
";

$stmtFaltasTurma = $pdo->prepare($sqlFaltasTurma);
$stmtFaltasTurma->execute([':turma_id' => $turmaId, ':escola_id' => $escolaId]);
$faltasPorMesTurma = $stmtFaltasTurma->fetchAll();

// Buscar alunos da turma com suas faltas
$sqlAlunosFaltas = "
    SELECT 
        u.id,
        u.nome_completo,
        COUNT(p.id) as total_faltas
    FROM usuarios u
    INNER JOIN matriculas m ON m.aluno_id = u.id
    INNER JOIN presencas p ON p.aluno_id = u.id
    INNER JOIN aulas a ON a.id = p.aula_id
    INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
    WHERE m.turma_id = :turma_id
    AND ptd.turma_id = :turma_id_2
    AND p.status = 'falta'
    AND p.escola_id = :escola_id
    AND m.status = 'ativa'
    GROUP BY u.id, u.nome_completo
    ORDER BY total_faltas DESC
";

$stmtAlunosFaltas = $pdo->prepare($sqlAlunosFaltas);
$stmtAlunosFaltas->execute([':turma_id' => $turmaId, ':turma_id_2' => $turmaId, ':escola_id' => $escolaId]);
$alunosComFaltas = $stmtAlunosFaltas->fetchAll();

$title = 'Controle de Faltas - ' . $turmaAtual['turma'];
require_once __DIR__ . '/../partials/header.php';
?>

<?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
<?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

<div class="main-content" style="background-color: #f5f5f5; min-height: 100vh; padding: 20px;">

    <div class="container-faltas" style="max-width: 1200px; margin: 0 auto;">
        
        <div class="mb-4">
            <a href="selecionar_turma.php" class="btn btn-link text-decoration-none p-0 text-secondary fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Voltar para seleção de turma
            </a>
        </div>

        <!-- Cabeçalho -->
        <div class="bg-white shadow-sm rounded-3 p-4 mb-4 border-bottom border-3 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3">
                        <i class="bi bi-calendar-x fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Controle de Faltas</h4>
                        <p class="text-muted mb-0">Turma: <span class="fw-bold"><?= e($turmaAtual['turma']) ?></span> | Disciplina: <span class="fw-bold"><?= e($turmaAtual['disciplina']) ?></span></p>
                    </div>
                </div>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="viewMode" id="viewGeral" value="geral" checked>
                    <label class="btn btn-outline-warning" for="viewGeral">
                        <i class="bi bi-graph-up me-1"></i> Visão Geral
                    </label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="viewAlunos" value="alunos">
                    <label class="btn btn-outline-warning" for="viewAlunos">
                        <i class="bi bi-people me-1"></i> Por Aluno
                    </label>
                </div>
            </div>
        </div>

        <!-- Visão Geral (Faltas da Turma) -->
        <div id="geralView">
            <div class="row g-4">
                <!-- Gráfico Geral -->
                <div class="col-lg-8">
                    <div class="bg-white shadow-sm rounded-3 p-4">
                        <h5 class="fw-bold text-secondary mb-4"><i class="bi bi-bar-chart me-2"></i> Faltas por Mês - Turma</h5>
                        <?php if (!empty($faltasPorMesTurma)): ?>
                            <canvas id="chartFaltasTurma" style="max-height: 300px;"></canvas>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-graph-up fs-1 d-block mb-2"></i>
                                <p>Nenhuma falta registrada nesta turma.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumo de Faltas -->
                <div class="col-lg-4">
                    <div class="bg-white shadow-sm rounded-3 p-4">
                        <h5 class="fw-bold text-secondary mb-4"><i class="bi bi-info-circle me-2"></i> Resumo</h5>
                        
                        <div class="mb-3 p-3 bg-light rounded-3 text-center">
                            <small class="text-muted d-block fw-bold mb-1">Total de Faltas</small>
                            <h3 class="fw-bold text-warning mb-0">
                                <?php 
                                $totalFaltasTurma = array_sum(array_column($faltasPorMesTurma, 'total_faltas'));
                                echo $totalFaltasTurma;
                                ?>
                            </h3>
                        </div>

                        <div class="mb-3 p-3 bg-light rounded-3 text-center">
                            <small class="text-muted d-block fw-bold mb-1">Total de Alunos</small>
                            <h3 class="fw-bold text-primary mb-0">
                                <?php 
                                $sqlTotalAlunos = "
                                    SELECT COUNT(DISTINCT m.aluno_id) as total
                                    FROM matriculas m
                                    WHERE m.turma_id = :turma_id
                                    AND m.status = 'ativa'
                                    AND m.escola_id = :escola_id
                                ";
                                $stmtTotal = $pdo->prepare($sqlTotalAlunos);
                                $stmtTotal->execute([':turma_id' => $turmaId, ':escola_id' => $escolaId]);
                                $totalAlunos = $stmtTotal->fetch()['total'] ?? 0;
                                echo $totalAlunos;
                                ?>
                            </h3>
                        </div>

                        <div class="p-3 bg-light rounded-3 text-center">
                            <small class="text-muted d-block fw-bold mb-1">Média de Faltas/Aluno</small>
                            <h3 class="fw-bold text-secondary mb-0">
                                <?php 
                                echo $totalAlunos > 0 ? round($totalFaltasTurma / $totalAlunos, 1) : '0';
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visão Por Aluno -->
        <div id="alunosView" style="display: none;">
            <div class="bg-white shadow-sm rounded-3 p-4">
                <h5 class="fw-bold text-secondary mb-4"><i class="bi bi-people me-2"></i> Faltas por Aluno</h5>
                
                <?php if (!empty($alunosComFaltas)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Aluno</th>
                                    <th class="text-center">Total de Faltas</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alunosComFaltas as $aluno): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <span class="fw-bold"><?= e($aluno['nome_completo']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger bg-opacity-10 text-danger fw-bold fs-6">
                                            <?= $aluno['total_faltas'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning" onclick="mostrarGraficoAluno(<?= $aluno['id'] ?>, '<?= e($aluno['nome_completo']) ?>')">
                                            <i class="bi bi-graph-up me-1"></i> Gráfico
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                        <p>Nenhum aluno com faltas registradas.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal para Gráfico do Aluno -->
        <div class="modal fade" id="modalGraficoAluno" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="nomeAlunoModal"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <canvas id="chartFaltasAluno" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
    // Alternar entre visão geral e por aluno
    document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('geralView').style.display = this.value === 'geral' ? 'block' : 'none';
            document.getElementById('alunosView').style.display = this.value === 'alunos' ? 'block' : 'none';
        });
    });

    // Gráfico de Faltas da Turma
    <?php if (!empty($faltasPorMesTurma)): ?>
    const ctxTurma = document.getElementById('chartFaltasTurma').getContext('2d');
    new Chart(ctxTurma, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($f) => $f['mes'], $faltasPorMesTurma)) ?>,
            datasets: [{
                label: 'Faltas',
                data: <?= json_encode(array_map(fn($f) => (int)$f['total_faltas'], $faltasPorMesTurma)) ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.5)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 2,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    <?php endif; ?>

    // Função para mostrar gráfico do aluno
    function mostrarGraficoAluno(alunoId, nomeAluno) {
        document.getElementById('nomeAlunoModal').textContent = nomeAluno;
        
        // Buscar dados de faltas do aluno por mês
        fetch('app/helpers/faltas_aluno.php?aluno_id=' + alunoId + '&turma_id=' + <?= $turmaId ?>, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            const modal = new bootstrap.Modal(document.getElementById('modalGraficoAluno'));
            
            // Destruir gráfico anterior se existir
            if (window.chartAlunoInstance) {
                window.chartAlunoInstance.destroy();
            }
            
            const ctxAluno = document.getElementById('chartFaltasAluno').getContext('2d');
            window.chartAlunoInstance = new Chart(ctxAluno, {
                type: 'line',
                data: {
                    labels: data.meses,
                    datasets: [{
                        label: 'Faltas por Mês',
                        data: data.faltas,
                        borderColor: 'rgba(255, 193, 7, 1)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: 'rgba(255, 193, 7, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
            
            modal.show();
        })
        .catch(error => {
            alert('Erro ao carregar dados do aluno');
            console.error(error);
        });
    }
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
