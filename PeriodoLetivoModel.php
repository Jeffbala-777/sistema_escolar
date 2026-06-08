<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {

    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/database.php';


header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');


if (
    !isset($_SESSION['usuario']) ||
    !is_array($_SESSION['usuario'])
) {

    session_unset();
    session_destroy();

    header('Location: ' . base_url('public/login.php'));
    exit;
}

$usuario = $_SESSION['usuario'];

if (
    empty($usuario['id']) ||
    empty($usuario['nome_completo'])
) {

    session_unset();
    session_destroy();

    header('Location: ' . base_url('public/login.php'));
    exit;
}

$perfil = $usuario['perfil_nome']
    ?? $usuario['tipo']
    ?? null;

if ($perfil !== null) {

    $_SESSION['usuario']['perfil_nome'] = $perfil;
}