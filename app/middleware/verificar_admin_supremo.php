<?php
// Middleware para seguranca do Admin Supremo
require_once __DIR__ . '/../config/config.php';

// Se nao estiver logado, vai pro login
if (!usuario_logado()) {
    redirect(base_url('public/login.php'));
}

// Se nao for supremo, destroi sessao e vai pro login
if (perfil_nome() !== 'admin_supremo') {
    session_unset();
    session_destroy();
    redirect(base_url('public/login.php'));
}

$tempoLimite = 60 * 60 * 2; // 2 horas de limite

// Se passou do tempo de inatividade, desloga
if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso']) > $tempoLimite) {
    session_unset();
    session_destroy();
    redirect(base_url('public/login.php'));
}

// Atualiza o tempo do ultimo acesso
$_SESSION['ultimo_acesso'] = time();
