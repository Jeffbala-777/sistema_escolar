<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';

$title = 'Turmas';

$usuario = $_SESSION['usuario'];

$escola_id = (int) $usuario['escola_id'];

$model = new TurmaModel($pdo);

$turmas = $model->listar($escola_id);

require_once __DIR__ . '/../partials/header.php';

?>

<div class="d-flex page-wrap">

    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">

        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="page-card p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div class="page-title">
                    Turmas da Escola
                </div>

                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    onclick="alert('Função de cadastro de turma será implementada em breve.')">

                    Nova Turma

                </button>

            </div>

            <?php if (empty($turmas)): ?>

                <div class="alert alert-info">
                    Nenhuma turma cadastrada.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-dark">

                            <tr>
                                <th>Nome</th>
                                <th>Série</th>
                                <th>Turno</th>
                                <th>Ano Letivo</th>
                                <th>Capacidade</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($turmas as $turma): ?>

                                <tr>

                                    <td>
                                        <?= e($turma['nome']) ?>
                                    </td>

                                    <td>
                                        <?= e($turma['serie']) ?>
                                    </td>

                                    <td>
                                        <?= e($turma['turno']) ?>
                                    </td>

                                    <td>
                                        <?= e($turma['ano_nome']) ?>
                                    </td>

                                    <td>
                                        <?= e($turma['capacidade']) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>