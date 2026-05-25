<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/models/DisciplinaModel.php';

$title = 'Disciplinas';

$usuario = $_SESSION['usuario'] ?? null;

$escola_id = (int) ($usuario['escola_id'] ?? 0);

if (!$escola_id) {

    require_once __DIR__ . '/../partials/header.php';

    echo '
        <div class="container p-4">
            <div class="alert alert-danger">
                Escola não encontrada na sessão.
            </div>
        </div>
    ';

    require_once __DIR__ . '/../partials/footer.php';

    exit;
}

/*
|--------------------------------------------------------------------------
| DISCIPLINAS
|--------------------------------------------------------------------------
*/

$disciplinaModel = new DisciplinaModel($pdo);

$disciplinas = $disciplinaModel->listar(
    $escola_id
);

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">

    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">

        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="page-card p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div class="page-title">

                    Disciplinas da Escola

                </div>

                <a
                    href="<?= base_url('admin/cadastrar_disciplina.php'); ?>"
                    class="btn btn-primary btn-sm">

                    <i class="bi bi-plus-circle"></i>
                    Nova Disciplina

                </a>

            </div>

            <?php if (empty($disciplinas)): ?>

                <div class="alert alert-info">

                    Nenhuma disciplina cadastrada.

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    Nome
                                </th>

                                <th>
                                    Código
                                </th>

                                <th>
                                    Carga Horária
                                </th>

                                <th width="120">
                                    Status
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($disciplinas as $disciplina): ?>

                                <tr>

                                    <td>

                                        <?= e(
                                            $disciplina['nome']
                                        ); ?>

                                    </td>

                                    <td>

                                        <?= e(
                                            $disciplina['codigo'] ?? '-'
                                        ); ?>

                                    </td>

                                    <td>

                                        <?= e(
                                            $disciplina['carga_horaria'] ?? 0
                                        ); ?>

                                        horas

                                    </td>

                                    <td>

                                        <?php if (
                                            (int) (
                                                $disciplina['ativo'] ?? 1
                                            ) === 1
                                        ): ?>

                                            <span class="badge bg-success">

                                                Ativa

                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">

                                                Inativa

                                            </span>

                                        <?php endif; ?>

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