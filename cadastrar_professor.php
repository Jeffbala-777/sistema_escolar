<?php
declare(strict_types=1);

// Protege acesso do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php'; // Banco
require_once __DIR__ . '/../app/helpers/functions.php'; // Helpers
require_once __DIR__ . '/../app/models/UsuarioModel.php'; // Model usuarios

$title = 'Cadastrar Aluno';
$escola_id = (int)($_SESSION['usuario']['escola_id'] ?? 0);

// Busca ID do perfil 'aluno'
$stmt = $pdo->prepare("SELECT id FROM perfis WHERE nome = 'aluno' LIMIT 1");
$stmt->execute();
$perfil = $stmt->fetch();
$perfil_id = (int)($perfil['id'] ?? 0);

$nome = '';
$email = '';
$cpf = '';
$telefone = '';
$nascimento = '';
$erro = '';
$sucesso = '';

// Se enviou o formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $nascimento = trim($_POST['nascimento'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha_confirm = $_POST['senha_confirm'] ?? '';

    // Lógica de E-mail Automático: se o campo e-mail estiver vazio, gera um baseado no nome
    if ($email === '' && $nome !== '') {
        $partesNome = explode(' ', strtolower($nome));
        $primeiroNome = preg_replace('/[^a-z0-9]/', '', $partesNome[0]);
        $ultimoNome = count($partesNome) > 1 ? preg_replace('/[^a-z0-9]/', '', end($partesNome)) : rand(10, 99);
        $email = $primeiroNome . '.' . $ultimoNome . '@aluno.edu.com';
    }

    // Validações
    if ($nome === '') {
        $erro = 'Informe o nome completo.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif ($senha !== $senha_confirm) {
        $erro = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve possuir no mínimo 6 caracteres.';
    } else {
        $usuarioModel = new UsuarioModel($pdo);
        if ($usuarioModel->buscarPorEmail($email)) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            // Salva no banco
            $dados = [
                'escola_id' => $escola_id,
                'perfil_id' => $perfil_id,
                'nome_completo' => $nome,
                'email' => $email,
                'senha' => $senha,
                'cpf' => $cpf,
                'telefone' => $telefone,
                'nascimento' => $nascimento
            ];
            
            if ($usuarioModel->cadastrar($dados)) {
                $sucesso = "Aluno cadastrado com sucesso! E-mail gerado: $email";
                $nome = $email = $cpf = $telefone = $nascimento = ''; // Limpa campos
            } else {
                $erro = 'Erro ao cadastrar aluno.';
            }
        }
    }
}

require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?> <!-- Menu -->

    <div class="content-area p-4 flex-grow-1">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="page-card p-4 shadow-sm bg-white rounded">
            <h4 class="mb-4">Cadastrar Novo Aluno</h4>

            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= e($sucesso); ?></div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= e($erro); ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off" id="form-cadastro">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nome completo</label>
                        <input type="text" name="nome" id="nome-aluno" class="form-control" value="<?= e($nome); ?>" required placeholder="Ex: João Silva">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Data de nascimento</label>
                        <input type="date" name="nascimento" class="form-control" value="<?= e($nascimento); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">E-mail (Deixe vazio para gerar automático)</label>
                        <input type="email" name="email" id="email-aluno" class="form-control" value="<?= e($email); ?>" placeholder="nome@aluno.edu.com">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">CPF</label>
                        <input type="text" name="cpf" class="form-control" value="<?= e($cpf); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="<?= e($telefone); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Senha</label>
                        <input type="password" name="senha" class="form-control" required placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Confirmar senha</label>
                        <input type="password" name="senha_confirm" class="form-control" required>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Cadastrar Aluno
                    </button>
                    <a href="alunos.php" class="btn btn-secondary">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script simples para sugerir o email enquanto digita o nome
document.getElementById('nome-aluno').addEventListener('input', function() {
    let emailField = document.getElementById('email-aluno');
    if (emailField.value === '' || emailField.dataset.auto === 'true') {
        let nome = this.value.trim().toLowerCase();
        if (nome.length > 3) {
            let partes = nome.split(' ');
            let sugerido = partes[0].replace(/[^a-z0-9]/g, '') + '.' + (partes.length > 1 ? partes[partes.length-1].replace(/[^a-z0-9]/g, '') : 'aluno') + '@aluno.edu.com';
            emailField.placeholder = 'Sugerido: ' + sugerido;
            emailField.dataset.auto = 'true';
        }
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
