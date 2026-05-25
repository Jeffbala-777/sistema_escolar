<?php

// Trava acesso do professor
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php'; // Banco
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php'; // Model prof
require_once __DIR__ . '/../app/models/NotasModel.php'; // Model notas
require_once __DIR__ . '/../app/models/PeriodoLetivoModel.php'; // Model periodos
require_once __DIR__ . '/../app/models/EscolaModel.php'; // Model escola

$ptdId = (int)($_GET['id'] ?? 0); // ID do vinculo
$professorId = $_SESSION['usuario']['id']; // ID logado
$escolaId = $_SESSION['usuario']['escola_id']; // ID da escola

$ptdModel = new ProfessorTurmaDisciplinaModel($pdo); // Inicia models
$notasModel = new NotaModel($pdo);
$periodoModel = new PeriodoLetivoModel($pdo);
$escolaModel = new EscolaModel($pdo);

$turma = $ptdModel->buscarTurmaProfessor($ptdId, $professorId); // Pega dados da turma
if (!$turma) { // Se nao achar turma
    exit('Acesso negado ou turma não encontrada.');
}

// Pega se a escola usa nota 10 ou 100
$notaMaxima = (float)($escolaModel->buscarConfiguracao($escolaId, 'nota_maxima') ?? 10);

$alunos = $ptdModel->listarAlunosTurma($turma['turma_id']); // Lista alunos
$periodos = $periodoModel->listarPorAno($turma['ano_letivo_id'], $escolaId); // Bimestres
$notasAtuais = $notasModel->buscarNotasTurma($turma['turma_id'], $turma['disciplina_id'], $turma['ano_letivo_id']); // Notas salvas

$mensagem = '';
$tipo_mensagem = '';

// Se salvar notas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notasPost = $_POST['notas'] ?? []; // Array de notas vindo do form
    $sucesso = true;

    foreach ($notasPost as $alunoId => $bimestres) {
        foreach ($bimestres as $periodoId => $valor) {
            if ($valor !== '') { // Se nao for vazio
                $valor = str_replace(',', '.', $valor); // Aceita virgula
                $valorFloat = (float)$valor;

                // Valida limite. Se errado, pula esse registro
                if ($valorFloat > $notaMaxima || $valorFloat < 0) {
                    $sucesso = false;
                    continue;
                }

                // Grava no banco
                $res = $notasModel->salvar([
                    'escola_id' => $escolaId,
                    'aluno_id' => $alunoId,
                    'disciplina_id' => $turma['disciplina_id'],
                    'professor_id' => $professorId,
                    'ano_letivo_id' => $turma['ano_letivo_id'],
                    'periodo_id' => $periodoId,
                    'nota' => (float)$valor,
                    'tipo' => 'prova'
                ]);
                if (!$res) $sucesso = false;
            }
        }
    }

    if ($sucesso) { // Deu tudo certo
        $mensagem = 'Notas salvas com sucesso!';
        $tipo_mensagem = 'success';
    } else { // Alguma nota tava errada
        $mensagem = 'Algumas notas inválidas foram ignoradas. As notas válidas foram salvas.';
        $tipo_mensagem = 'warning';
    }

    // Recarrega notas atualizadas
    $notasAtuais = $notasModel->buscarNotasTurma($turma['turma_id'], $turma['disciplina_id'], $turma['ano_letivo_id']);
}

$title = 'Lançar Notas - ' . $turma['turma_nome'];
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="dashboard-container">
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?> <!-- Menu -->

    <main class="main-content">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Painel -->

        <div class="top-page">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="margin-bottom: 5px;"><?= e($_SESSION['usuario']['escola_nome'] ?? 'Escola Municipal') ?></h2>
                <h3 style="font-weight: normal; margin-top: 0;">Controle de Notas - <?= date('Y') ?></h3>
            </div>
            
            <!-- Info da turma -->
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 20px; font-size: 0.9rem;">
                <span><strong>Profº:</strong> <?= e($_SESSION['usuario']['nome_completo']) ?></span>
                <span><strong>Ano/Série:</strong> <?= e($turma['serie']) ?></span>
                <span><strong>Turno:</strong> <?= e(ucfirst($turma['turno'])) ?></span>
                <span><strong>Disciplina:</strong> <?= e($turma['disciplina_nome']) ?></span>
            </div>
        </div>

        <?php if ($mensagem): ?> <!-- Alerta -->
            <div class="alert alert-<?= $tipo_mensagem ?>"><?= $mensagem ?></div>
        <?php endif; ?>

        <div class="page-card" style="padding: 0; overflow-x: auto;">
            <form method="POST" id="form-notas">
                <table class="table table-bordered" style="margin-bottom: 0; border: 2px solid #333;">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th rowspan="2" style="width: 50px; text-align: center; vertical-align: middle; border: 1px solid #333;">Nº</th>
                            <th rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #333;">Nome do aluno</th>
                            <?php foreach ($periodos as $p): ?> <!-- Cabecalho periodos -->
                                <th colspan="2" style="text-align: center; border: 1px solid #333;"><?= e($p['nome']) ?></th>
                            <?php endforeach; ?>
                            <th rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #333; background-color: #eee;">Média Final</th>
                        </tr>
                        <tr>
                            <?php foreach ($periodos as $p): ?>
                                <th style="text-align: center; font-size: 0.8rem; border: 1px solid #333;">Nota</th>
                                <th style="text-align: center; font-size: 0.8rem; border: 1px solid #333; background-color: #f0f0f0;">Média</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        foreach ($alunos as $aluno): 
                            $soma = 0;
                            $cont = 0;
                        ?>
                            <tr>
                                <td style="text-align: center; border: 1px solid #333;"><?= $i++ ?></td>
                                <td style="border: 1px solid #333;"><?= e($aluno['nome_completo']) ?></td>
                                <?php foreach ($periodos as $p): 
                                    $nota = $notasAtuais[$aluno['id']][$p['id']] ?? '';
                                    if ($nota !== '') { $soma += (float)$nota; $cont++; }
                                ?>
                                    <td style="padding: 2px; border: 1px solid #333;">
                                        <!-- Campo de nota com validacao visual -->
                                        <input type="text"
                                               name="notas[<?= $aluno['id'] ?>][<?= $p['id'] ?>]"
                                               value="<?= $nota ?>"
                                               class="form-control form-control-sm nota-input"
                                               data-nota-maxima="<?= $notaMaxima ?>"
                                               style="text-align: center; border: none; padding: 2px;"
                                               placeholder="0.0"
                                               title="Nota máxima: <?= $notaMaxima ?>">
                                    </td>
                                    <td style="text-align: center; border: 1px solid #333; background-color: #f9f9f9; vertical-align: middle;">
                                        <?= $nota ?>
                                    </td>
                                <?php endforeach; ?>
                                <td style="text-align: center; border: 1px solid #333; font-weight: bold; background-color: #eee; vertical-align: middle;">
                                    <?php 
                                        if ($cont > 0) { // Calcula media
                                            $media = $soma / $cont;
                                            echo $notaMaxima > 10 ? round($media, 0) : round($media, 1);
                                        } else { echo '-'; }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding: 20px; text-align: right;">
                    <button type="submit" class="btn btn-primary">Salvar Todas as Notas</button>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
    /* Estilo do campo vermelho quando nota for invalida */
    .nota-input.nota-invalida {
        background-color: #ffe0e0 !important;
        border: 1.5px solid #e53935 !important;
        color: #b71c1c !important;
        box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.25) !important;
    }
</style>

<script>
(function () {
    // Valida se a nota esta no limite da escola
    function validarCampo(input) {
        var raw = input.value.trim().replace(',', '.');
        var notaMaxima = parseFloat(input.dataset.notaMaxima);

        if (raw === '') { // Se vazio, ta ok
            input.classList.remove('nota-invalida');
            return;
        }

        var valor = parseFloat(raw);
        // Se passar do limite ou nao for numero, fica vermelho
        if (isNaN(valor) || valor < 0 || valor > notaMaxima) {
            input.classList.add('nota-invalida');
        } else {
            input.classList.remove('nota-invalida');
        }
    }

    // Ativa validacao ao digitar
    document.querySelectorAll('.nota-input').forEach(function (input) {
        validarCampo(input); // Checa ao carregar
        input.addEventListener('input', function () { validarCampo(this); });
        input.addEventListener('change', function () { validarCampo(this); });
    });

    // Bloqueia salvar se tiver algo vermelho
    document.getElementById('form-notas').addEventListener('submit', function (e) {
        var invalidos = document.querySelectorAll('.nota-input.nota-invalida');
        if (invalidos.length > 0) {
            e.preventDefault();
            alert('Existem notas inválidas (em vermelho). Corrija antes de salvar!');
            invalidos[0].focus();
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape -->
