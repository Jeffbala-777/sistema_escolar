<?php
// Funcoes globais de ajuda para o sistema todo

// Retorna a URL base do site
if (!function_exists('base_url')) {
    function base_url(string $url = ''): string {
        return rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
    }
}

// Retorna a URL da aplicacao
if (!function_exists('app_url')) {
    function app_url(string $url = ''): string {
        return rtrim(APP_URL, '/') . '/' . ltrim($url, '/');
    }
}

// Redireciona para uma URL e para o script
if (!function_exists('redirect')) {
    function redirect(string $url): never {
        if (!headers_sent()) { header('Location: ' . $url); }
        exit;
    }
}

// Limpa texto para evitar ataques XSS
if (!function_exists('e')) {
    function e(string|null|int|float $valor): string {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// Verifica se usuario esta logado
if (!function_exists('usuario_logado')) {
    function usuario_logado(): bool {
        return isset($_SESSION['usuario']);
    }
}

// Pega dados do usuario logado
if (!function_exists('usuario')) {
    function usuario(): ?array {
        return $_SESSION['usuario'] ?? null;
    }
}

// Pega apenas o ID do usuario logado
if (!function_exists('usuario_id')) {
    function usuario_id(): int {
        return (int) ($_SESSION['usuario']['id'] ?? 0);
    }
}

// Pega o ID da escola do usuario logado
if (!function_exists('escola_id')) {
    function escola_id(): int {
        return (int) ($_SESSION['usuario']['escola_id'] ?? 0);
    }
}

// Pega o nome do perfil do usuario logado
if (!function_exists('perfil_nome')) {
    function perfil_nome(): string {
        return $_SESSION['usuario']['perfil_nome'] ?? '';
    }
}

// Alias para perfil_nome
if (!function_exists('perfil_usuario')) {
    function perfil_usuario(): string {
        return perfil_nome();
    }
}

// Verifica se e administrador
if (!function_exists('is_admin')) {
    function is_admin(): bool {
        return in_array(perfil_nome(), ['admin', 'admin_supremo'], true);
    }
}

// Verifica se e professor
if (!function_exists('is_professor')) {
    function is_professor(): bool {
        return perfil_nome() === 'professor';
    }
}

// Verifica se e aluno
if (!function_exists('is_aluno')) {
    function is_aluno(): bool {
        return perfil_nome() === 'aluno';
    }
}

// Valida se o e-mail pertence a um dominio permitido por cargo
if (!function_exists('dominio_permitido')) {
    function dominio_permitido(string $email, ?string $perfil = null): bool {
        $email = trim(strtolower($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { return false; }
        $dominiosPorPerfil = [
            'admin_supremo' => 'adm.com',
            'admin' => 'admin.edu.com',
            'professor' => 'prof.edu.com',
            'aluno' => 'aluno.edu.com'
        ];
        $partes = explode('@', $email);
        $dominio = strtolower(end($partes));
        if ($perfil !== null && $perfil !== '') {
            return isset($dominiosPorPerfil[$perfil]) && $dominio === $dominiosPorPerfil[$perfil];
        }
        return in_array($dominio, array_values($dominiosPorPerfil), true);
    }
}

// Gera token CSRF para seguranca de formularios
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
        return $_SESSION['csrf_token'];
    }
}

// Gera input oculto com o token CSRF
if (!function_exists('csrf_input')) {
    function csrf_input(): string {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

// Valida se o token enviado e igual ao da sessao
if (!function_exists('validar_csrf')) {
    function validar_csrf(): bool {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { return false; }
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}

// Desloga o usuario e limpa a sessao
if (!function_exists('logout')) {
    function logout(): never {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_unset();
        session_destroy();
        redirect(base_url('public/login.php'));
    }
}

// Traduz o nome do perfil para exibicao amigavel
if (!function_exists('perfil_label')) {
    function perfil_label(string $perfil): string {
        return match ($perfil) {
            'admin' => 'Administrador',
            'admin_supremo' => 'Administrador Supremo',
            'professor' => 'Professor',
            'aluno' => 'Aluno',
            default => 'Usuário'
        };
    }
}
?>
