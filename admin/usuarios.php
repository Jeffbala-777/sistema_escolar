<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';

$title = 'Usuários';

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

$perfil_nome = trim(
    $_GET['perfil'] ?? ''
);

$usuarioModel = new UsuarioModel($pdo);

if ($perfil_nome === 'professor') {

    $usuarios = $usuarioModel->listarProfessores(
        $escola_id
    );

} elseif ($perfil_nome === 'aluno') {

    $usuarios = $usuarioModel->listarAlunos(
        $escola_id
    );

} else {

    $usuarios = $usuarioModel->listar(
        $escola_id
    );
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">

    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="content-area p-4">

        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="page-card p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div class="page-title">

                    Usuários da Escola

                </div>

                <a
                    href="<?= base_url('admin/cadastrar_usuario.php'); ?>"
                    class="btn btn-primary btn-sm">

                    <i class="bi bi-plus-circle"></i>
                    Novo Usuário

                </a>

            </div>

            <form
                method="GET"
                class="row g-3 mb-4">

                <div class="col-md-4">

                    <label class="form-label">

                        Filtrar por perfil

                    </label>

                    <select
                        name="perfil"
                        class="form-select">

                        <option value="">
                            Todos
                        </option>

                        <option
                            value="admin"
                            <?= $perfil_nome === 'admin'
                                ? 'selected'
                                : ''; ?>>

                            Administradores

                        </option>

                        <option
                            value="professor"
                            <?= $perfil_nome === 'professor'
                                ? 'selected'
                                : ''; ?>>

                            Professores

                        </option>

                        <option
                            value="aluno"
                            <?= $perfil_nome === 'aluno'
                                ? 'selected'
                                : ''; ?>>

                            Alunos

                        </option>

                    </select>

                </div>

                <div class="col-md-2 d-flex align-items-end">

                    <button
                        type="submit"
                        class="btn btn-dark w-100">

                        Filtrar

                    </button>

                </div>

            </form>

            <?php if (empty($usuarios)): ?>

                <div class="alert alert-info">

                    Nenhum usuário encontrado.

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
                                    Perfil
                                </th>

                                <th>
                                    E-mail
                                </th>

                                <th>
                                    CPF
                                </th>

                                <th>
                                    Telefone
                                </th>

                                <th width="140">
                                    Status
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($usuarios as $u): ?>

                                <tr>

                                    <td>

                                        <?= e(
                                            $u['nome_completo']
                                        ); ?>

                                    </td>

                                    <td>

                                        <span class="badge bg-primary">

                                            <?= e(
                                                ucfirst(
                                                    $u['perfil'] ??
                                                    $u['perfil_nome'] ??
                                                    '-'
                                                )
                                            ); ?>

                                        </span>

                                    </td>

                                    <td>

                                        <?= e(
                                            $u['email']
                                        ); ?>

                                    </td>

                                    <td>

                                        <?= e(
                                            $u['cpf'] ?? '-'
                                        ); ?>

                                    </td>

                                    <td>

                                        <?= e(
                                            $u['telefone'] ?? '-'
                                        ); ?>

                                    </td>

                                    <td>

                                        <?php if (
                                            (int) ($u['ativo'] ?? 0) === 1
                                        ): ?>

                                            <span class="badge bg-success">

                                                Ativo

                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">

                                                Inativo

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