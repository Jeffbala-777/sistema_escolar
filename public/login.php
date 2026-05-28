<?php

declare(strict_types=1);

// Carrega configuracoes e banco
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';

// Limpa cache do navegador para seguranca
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Se ja estiver logado, manda pro dashboard certo
if (isset($_SESSION['usuario'])) {
    $perfil = $_SESSION['usuario']['perfil_nome'] ?? '';
    switch ($perfil) {
        case 'admin':
            redirect(base_url('/admin/dashboard.php'));
            break;
        case 'admin_supremo':
            redirect(base_url('/admin_supremo/dashboard.php'));
            break;
        case 'professor':
            redirect(base_url('/professor/dashboard.php'));
            break;
        case 'aluno':
            redirect(base_url('/aluno/dashboard.php'));
            break;
        default:
            session_unset();
            session_destroy();
            break;
    }
}

// Cria token de seguranca (CSRF)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inicia contadores de tentativa de login
if (!isset($_SESSION['login_tentativas'])) { $_SESSION['login_tentativas'] = 0; }
if (!isset($_SESSION['login_bloqueio'])) { $_SESSION['login_bloqueio'] = 0; }

$erro = '';
$email = '';
$senha = '';

// Se enviou o formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se estiver bloqueado por muitas tentativas
    if (time() < $_SESSION['login_bloqueio']) {
        $tempo = $_SESSION['login_bloqueio'] - time();
        $erro = 'Muitas tentativas. Aguarde ' . $tempo . ' segundos.';
    } elseif (
        // Valida token de seguranca
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $erro = 'Sessão inválida. Atualize a página.';
    } else {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $senha = trim($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            $erro = 'Preencha todos os campos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail inválido.';
        } elseif (!dominio_permitido($email)) { // Valida se o email e do sistema
            $erro = 'Domínio de e-mail inválido. Use @adm.com, @admin.edu.com, @prof.edu.com ou @aluno.edu.com.';
        } else {
            $usuarioModel = new UsuarioModel($pdo);
            $user = $usuarioModel->autenticar($email, $senha); // Tenta logar

            if (!$user) {
                $_SESSION['login_tentativas']++;
                if ($_SESSION['login_tentativas'] >= 5) { // Bloqueia apos 5 erros
                    $_SESSION['login_bloqueio'] = time() + 60;
                    $_SESSION['login_tentativas'] = 0;
                }
                sleep(1); // Anti-forca bruta
                $erro = 'E-mail ou senha incorretos.';
            } else {
                // Sucesso no login
                $_SESSION['login_tentativas'] = 0;
                $_SESSION['login_bloqueio'] = 0;
                session_regenerate_id(true); // Nova sessao por seguranca

                // Busca o nome da escola para salvar na sessao
                $stmtEscola = $pdo->prepare("SELECT nome FROM escolas WHERE id = :id LIMIT 1");
                $stmtEscola->execute([':id' => $user['escola_id']]);
                $escolaNome = $stmtEscola->fetchColumn();

                // Salva dados na sessao
                $_SESSION['usuario'] = [
                    'id' => (int) $user['id'],
                    'escola_id' => (int) $user['escola_id'],
                    'escola_nome' => $escolaNome ?: 'Minha Escola',
                    'nome_completo' => $user['nome_completo'],
                    'perfil_nome' => $user['perfil_nome'],
                    'email' => $user['email']
                ];

                $_SESSION['usuario_id'] = (int) $user['id'];
                $_SESSION['escola_id'] = (int) $user['escola_id'];
                $_SESSION['usuario_nome'] = $user['nome_completo'];
                $_SESSION['perfil_nome'] = $user['perfil_nome'];
                $_SESSION['ultimo_acesso'] = time();

                // Redireciona conforme o perfil
                switch ($user['perfil_nome']) {
                    case 'admin':
                        redirect(base_url('admin/dashboard.php'));
                        break;
                    case 'admin_supremo':
                        redirect(base_url('admin_supremo/dashboard.php'));
                        break;
                    case 'professor':
                        redirect(base_url('professor/dashboard.php'));
                        break;
                    case 'aluno':
                        redirect(base_url('aluno/dashboard.php'));
                        break;
                    default:
                        session_unset();
                        session_destroy();
                        $erro = 'Perfil inválido.';
                        break;
                }
            }
        }
    }
}

require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="page-card p-4 shadow-sm" style="max-width:420px;width:100%;">
        <div class="text-center mb-4">
            <!-- Logo ou Titulo aqui -->
        </div>

        <?php if ($erro): ?> <!-- Alerta de erro -->
            <div class="alert alert-danger">
                <?= e($erro); ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <!-- Token de seguranca oculto -->
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">

            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="Digite seu e-mail" value="<?= e($email); ?>" maxlength="120" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" minlength="6" maxlength="120" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape -->
