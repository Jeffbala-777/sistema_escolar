<?php

declare(strict_types=1);

// Carrega funcoes auxiliares e seguranca
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';

// Nomes e caminhos globais do sistema
define('APP_NAME', 'Sistema Escolar');
define('APP_ENV', 'local'); // Ambiente local
define('DEBUG_MODE', true); // Mostra erros em desenvolvimento
define('BASE_URL', '/sistema_escolar'); // Pasta no servidor
define('APP_URL', 'http://localhost/sistema_escolar'); // URL completa
define('SESSION_NAME', 'ERP_ESCOLAR_SESSION'); // Nome da sessao

// ==========================================
// Chave da API Groq (Gratuita)
// ==========================================

define('GROQ_API_KEY', '');

// Configura fuso horario e codificacao
date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

// Configura exibicao de erros baseado no modo DEBUG
if (DEBUG_MODE) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

// Inicia buffer de saida
if (!ob_get_level()) { ob_start(); }

// Força HTTPS se nao estiver em ambiente local
if (APP_ENV !== 'local' && empty($_SERVER['HTTPS'])) {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $redirect);
    exit;
}

// Configura e inicia a sessao de forma segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    if (!empty($_SERVER['HTTPS'])) { ini_set('session.cookie_secure', '1'); }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Seguranca de sessao: regenera ID apos 30 minutos
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif ((time() - $_SESSION['created']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Expira sessao apos 4 horas de inatividade
if (isset($_SESSION['ultimo_acesso'])) {
    $tempo_inativo = time() - $_SESSION['ultimo_acesso'];
    if ($tempo_inativo > 14400) {
        session_unset();
        session_destroy();
        header('Location: ' . rtrim(BASE_URL, '/') . '/public/login.php');
        exit;
    }
}
$_SESSION['ultimo_acesso'] = time();

// Headers de seguranca do navegador
if (!headers_sent()) {
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self';");
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    header('Cache-Control: private, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Funcoes globais de utilidade
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never {
        if (!headers_sent()) { header('Location: ' . $url); }
        exit;
    }
}

// Atalhos para dados do usuario logado
if (!function_exists('usuario_id')) {
    function usuario_id(): int { return (int) ($_SESSION['usuario']['id'] ?? 0); }
}

if (!function_exists('escola_id')) {
    function escola_id(): int { return (int) ($_SESSION['usuario']['escola_id'] ?? 0); }
}

// Controle de tokens de seguranca (CSRF)
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['_csrf_token'])) { $_SESSION['_csrf_token'] = bin2hex(random_bytes(32)); }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input(): string {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('validar_csrf')) {
    function validar_csrf(): bool {
        $token = $_POST['_token'] ?? '';
        return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }
}
?>