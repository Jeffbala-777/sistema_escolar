<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/database/database.php';

if (!isset($_SESSION['usuario'])) {
    redirect(base_url('public/login.php'));
}

$perfil = $_SESSION['usuario']['perfil_nome'] ?? '';
if (in_array($perfil, ['admin', 'admin_supremo'], true)) {
    redirect(base_url('admin/dashboard.php'));
}
if ($perfil === 'professor') {
    redirect(base_url('professor/dashboard.php'));
}
redirect(base_url('aluno/dashboard.php'));