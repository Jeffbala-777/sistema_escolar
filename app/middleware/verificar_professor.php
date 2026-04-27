<?php
require_once __DIR__ . '/verificar_login.php';

if (($_SESSION['usuario']['tipo'] ?? '') !== 'professor') {
    header('Location: /sistema_escolar/public/index.php');
    exit;
}