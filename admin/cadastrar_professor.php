<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';

$title = 'Cadastrar Professor';
$usuario = $_SESSION['usuario'] ?? null;
$escola_id = $usuario['escola_id'] ?? 0;

// Trava se nao tiver escola na sessao
if (!$escola_id) {
    require_once __DIR__ . '/../partials/header.php';
    echo '<div class="container p-4"><div class="alert alert-danger">Escola não definida.</div></div>';
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

$usuarioModel = new UsuarioModel($pdo);
$stmt = $pdo->prepare("SELECT id FROM perfis WHERE nome = 'professor' LIMIT 1");
$stmt->execute();
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);
$perfil_id = $perfil['id'] ?? 0;

$nome = $email = $cpf = $telefone = $nascimento = $erro = $mensagem = '';

// Se enviou o form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $nascimento = trim($_POST['nascimento'] ?? '');
    $senha1 = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha_confirm'] ?? '';

    // E-mail Automático: se vazio, gera um @prof.edu.com
    if ($email === '' && $nome !== '') {
        $partesNome = explode(' ', strtolower($nome));
        $primeiroNome = preg_replace('/[^a-z0-9]/', '', $partesNome[0]);
        $ultimoNome = count($partesNome) > 1 ? preg_replace('/[^a-z0-9]/', '', end($partesNome)) : rand(10, 99);
        $email = $primeiroNome . '.' . $ultimoNome . '@prof.edu.com';
    }

    if ($nome === '') {
        $erro = 'Nome é obrigatório.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif ($usuarioModel->buscarPorEmail($email)) {
        $erro = 'E-mail já cadastrado.';
    } elseif ($senha1 !== $senha2) {
        $erro = 'As senhas não conferem.';
    } elseif (strlen($senha1) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres.';
    } else {
        $dados = [
            'escola_id' => $escola_id,
            'perfil_id' => $perfil_id,
            'nome_completo' => $nome,
            'email' => $email,
            'senha' => $senha1,
            'cpf' => $cpf,
            'telefone' => $telefone,
            'nascimento' => $nascimento
        ];
        if ($usuarioModel->cadastrar($dados)) {
            $mensagem = "Professor cadastrado com sucesso! E-mail: $email";
            $nome = $email = $cpf = $telefone = $nascimento = '';
        } else {
            $erro = 'Erro ao cadastrar professor.';
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="content-area p-4 flex-grow-1">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
        <div class="page-card p-4 shadow-sm bg-white rounded">
            <h4 class="mb-4">Cadastrar Novo Professor</h4>
            <?php if ($mensagem): ?><div class="alert alert-success"><?= e($mensagem) ?></div><?php endif; ?>
            <?php if ($erro): ?><div class="alert alert-danger"><?= e($erro) ?></div><?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nome completo</label>
                        <input type="text" name="nome" class="form-control" value="<?= e($nome) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Data de nascimento</label>
                        <input type="date" name="nascimento" class="form-control" value="<?= e($nascimento) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">E-mail (Vazio = automático)</label>
                        <input type="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="Ex: nome@prof.edu.com">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">CPF</label>
                        <input type="text" name="cpf" class="form-control" value="<?= e($cpf) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="<?= e($telefone) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Confirmar senha</label>
                        <input type="password" name="senha_confirm" class="form-control" required>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Cadastrar Professor</button>
                    <a href="professores.php" class="btn btn-secondary">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
