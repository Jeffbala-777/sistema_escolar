<?php
declare(strict_types=1);

// Protege acesso do admin supremo
require_once __DIR__ . '/../app/middleware/verificar_admin_supremo.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/EscolaModel.php';
require_once __DIR__ . '/../app/models/AdminSupremoModel.php';

$usuarioModel = new UsuarioModel($pdo);
$escolaModel = new EscolaModel($pdo);
$adminModel = new AdminSupremoModel($pdo);

$escolas = $escolaModel->listarTodas();
$perfis = $adminModel->listarPerfis();

$nome = $email = $escola_id = $perfil_id = $senha = $erro = $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $escola_id = (int)($_POST['escola_id'] ?? 0);
    $perfil_id = (int)($_POST['perfil_id'] ?? 0);
    $senha = $_POST['senha'] ?? '';

    // E-mail Automático para Admin Supremo (Gera @adm.com ou @admin.edu.com)
    if ($email === '' && $nome !== '') {
        $partesNome = explode(' ', strtolower($nome));
        $primeiroNome = preg_replace('/[^a-z0-9]/', '', $partesNome[0]);
        $ultimoNome = count($partesNome) > 1 ? preg_replace('/[^a-z0-9]/', '', end($partesNome)) : rand(10, 99);
        
        // Se for perfil admin da escola (ID 2 geralmente)
        $email = $primeiroNome . '.' . $ultimoNome . '@admin.edu.com';
    }

    if ($nome === '' || $escola_id === 0 || $perfil_id === 0 || $senha === '') {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        $dados = [
            'escola_id' => $escola_id,
            'perfil_id' => $perfil_id,
            'nome_completo' => $nome,
            'email' => $email,
            'senha' => $senha,
            'ativo' => 1
        ];
        if ($usuarioModel->cadastrar($dados)) {
            $sucesso = "Usuário cadastrado com sucesso! E-mail: $email";
            $nome = $email = $senha = '';
        } else {
            $erro = 'Erro ao cadastrar usuário.';
        }
    }
}

$title = 'Cadastrar Usuário - Admin Supremo';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <div class="main-content flex-grow-1" style="background-color: #f5f5f5; min-height: 100vh;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
        <div class="p-4">
            <div class="card shadow-sm border-0 p-4" style="max-width: 800px; margin: 0 auto;">
                <h4 class="mb-4">Cadastrar Novo Usuário (Admin/Prof/Aluno)</h4>
                <?php if ($sucesso): ?><div class="alert alert-success"><?= $sucesso ?></div><?php endif; ?>
                <?php if ($erro): ?><div class="alert alert-danger"><?= $erro ?></div><?php endif; ?>

                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" value="<?= e($nome) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Escola Vinculada</label>
                            <select name="escola_id" class="form-select" required>
                                <option value="">-- Selecione a Escola --</option>
                                <?php foreach ($escolas as $e): ?>
                                    <option value="<?= $e['id'] ?>" <?= $escola_id == $e['id'] ? 'selected' : '' ?>><?= e($e['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cargo / Perfil</label>
                            <select name="perfil_id" class="form-select" required>
                                <option value="">-- Selecione o Perfil --</option>
                                <?php foreach ($perfis as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $perfil_id == $p['id'] ? 'selected' : '' ?>><?= e($p['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">E-mail (Vazio = automático)</label>
                            <input type="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="Ex: nome@admin.edu.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Senha de Acesso</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Salvar Usuário</button>
                        <a href="usuarios.php" class="btn btn-secondary">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
