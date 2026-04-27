<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: /sistema_escolar/public/index.php');
    exit;
}