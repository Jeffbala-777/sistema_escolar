<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';

$title = 'Turma';

$model = new ProfessorTurmaDisciplinaModel($pdo);

$professorId = $_SESSION['usuario']['id'];

$ptdId = (int) ($_GET['id'] ?? 0);

$turma = $model->buscarTurmaProfessor(
    $ptdId,
    $professorId
);

if (!$turma) {
    exit('Turma não encontrada.');
}

$alunos = $model->listarAlunosTurma(
    $turma['turma_id']
);

require_once __DIR__ . '/../partials/header.php';
?>

<div class="dashboard-container">

    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <main class="main-content">

        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="top-page">

            <h1>
                <?= e($turma['turma_nome']) ?>
            </h1>

            <p>
                <?= e($turma['disciplina_nome']) ?>
                •
                <?= e($turma['serie']) ?>
            </p>

        </div>

        <div class="tabs-container">

            <button class="tab-button active">
                Alunos
            </button>

            <button class="tab-button">
                Notas
            </button>

            <button class="tab-button">
                Presença
            </button>

            <button class="tab-button">
                Conteúdo
            </button>

        </div>

        <div class="table-responsive">

            <table class="table custom-table">

                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Aluno</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($alunos as $aluno): ?>

                        <tr>

                            <td>
                                <?= e($aluno['numero_matricula']) ?>
                            </td>

                            <td>
                                <?= e($aluno['nome_completo']) ?>
                            </td>

                            <td>

                                <button class="btn btn-primary btn-sm">
                                    Ver
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </main>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>