<?php
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';
require_once __DIR__ . '/../app/models/FaltaModel.php';
require_once __DIR__ . '/../app/models/AulaModel.php';

$ptdId = (int)($_GET['id'] ?? 0);
$dataSelecionada = $_GET['data'] ?? date('Y-m-d');
$professorId = $_SESSION['usuario']['id'];
$escolaId = $_SESSION['usuario']['escola_id'];

$ptdModel = new ProfessorTurmaDisciplinaModel($pdo);
$faltaModel = new FaltaModel($pdo);
$aulaModel = new AulaModel($pdo);

$turma = $ptdModel->buscarTurmaProfessor($ptdId, $professorId);
if (!$turma) {
    exit('Acesso negado ou turma não encontrada.');
}

$alunos = $ptdModel->listarAlunosTurma($turma['turma_id']);

// Busca ou cria a aula para esta data
$aulaId = $aulaModel->criarOuBuscar([
    'escola_id' => $escolaId,
    'ptd_id' => $ptdId,
    'data_aula' => $dataSelecionada
]);

$presencasAtuais = $faltaModel->buscarPresencasAula($aulaId);

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $presencasPost = $_POST['presenca'] ?? [];
    $sucesso = true;

    foreach ($alunos as $aluno) {
        $status = $presencasPost[$aluno['id']] ?? 'falta'; // Se não marcou, é falta (X vermelho)
        $res = $faltaModel->salvar([
            'escola_id' => $escolaId,
            'aluno_id' => $aluno['id'],
            'aula_id' => $aulaId,
            'status' => $status
        ]);
        if (!$res) $sucesso = false;
    }

    if ($sucesso) {
        $mensagem = 'Frequência salva com sucesso!';
        $tipo_mensagem = 'success';
        $presencasAtuais = $faltaModel->buscarPresencasAula($aulaId);
    } else {
        $mensagem = 'Erro ao salvar frequência.';
        $tipo_mensagem = 'danger';
    }
}

$title = 'Lançar Faltas - ' . $turma['turma_nome'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="dashboard-container">
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <main class="main-content">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="top-page">
            <h1>Chamada Diária</h1>
            <p><?= e($turma['turma_nome']) ?> • <?= e($turma['disciplina_nome']) ?></p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?>"><?= $mensagem ?></div>
        <?php endif; ?>

        <div class="page-card">
            <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-end;">
                <input type="hidden" name="id" value="<?= $ptdId ?>">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Data da Aula:</label>
                    <input type="date" name="data" value="<?= e($dataSelecionada) ?>" class="form-control" onchange="this.form.submit()">
                </div>
            </form>

            <form method="POST">
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">Nº</th>
                                <th>Nome do Aluno</th>
                                <th style="width: 150px; text-align: center;">Presença / Falta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($alunos as $aluno): 
                                $status = $presencasAtuais[$aluno['id']] ?? 'presente';
                            ?>
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;"><?= $i++ ?></td>
                                    <td style="vertical-align: middle;"><?= e($aluno['nome_completo']) ?></td>
                                    <td style="text-align: center;">
                                        <div class="presenca-toggle">
                                            <label class="toggle-item">
                                                <input type="radio" name="presenca[<?= $aluno['id'] ?>]" value="falta" <?= $status === 'falta' ? 'checked' : '' ?>>
                                                <span class="circle circle-red" title="Falta">X</span>
                                            </label>
                                            <label class="toggle-item">
                                                <input type="radio" name="presenca[<?= $aluno['id'] ?>]" value="presente" <?= $status === 'presente' ? 'checked' : '' ?>>
                                                <span class="circle circle-green" title="Presente">O</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="btn btn-primary">Salvar Chamada</button>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
    .presenca-toggle {
        display: flex;
        justify-content: center;
        gap: 15px;
    }
    .toggle-item {
        cursor: pointer;
        position: relative;
    }
    .toggle-item input {
        display: none;
    }
    .circle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 2px solid #ddd;
        font-weight: bold;
        transition: all 0.2s;
        color: #ddd;
    }
    .circle-green { border-color: #28a745; color: #28a745; }
    .circle-red { border-color: #dc3545; color: #dc3545; }

    .toggle-item input:checked + .circle-green {
        background-color: #28a745;
        color: white;
        border-color: #28a745;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
    }
    .toggle-item input:checked + .circle-red {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
        box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
    }
    .circle:hover {
        transform: scale(1.1);
    }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
