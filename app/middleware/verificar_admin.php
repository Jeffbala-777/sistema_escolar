<?php
require_once __DIR__ . '/verificar_login.php';

$tipo = $_SESSION['usuario']['tipo'] ?? '';
if (!in_array($tipo, ['admin', 'admin_supremo'], true)) {
    header('Location: /sistema_escolar/public/index.php');
    exit;
}