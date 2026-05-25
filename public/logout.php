<?php

require_once __DIR__ . '/../app/config/config.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_unset();

session_destroy();

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

header('Location: ' . base_url('public/login.php'));
exit;
