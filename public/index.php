<?php
require_once __DIR__ . '/../app/config/config.php';

if (isset($_SESSION['usuario'])) {
    $tipo = $_SESSION['usuario']['tipo'];
    if (in_array($tipo, ['admin', 'admin_supremo'], true)) {
        header('Location: /sistema_escolar/admin/dashboard.php');
    } elseif ($tipo === 'professor') {
        header('Location: /sistema_escolar/professor/dashboard.php');
    } elseif ($tipo === 'aluno') {
        header('Location: /sistema_escolar/aluno/dashboard.php');
    }
    exit;
}

$csrf_token = bin2hex(random_bytes(16));
$_SESSION['csrf_token'] = $csrf_token;
require_once __DIR__ . '/login.php';