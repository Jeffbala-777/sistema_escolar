<?php

require_once __DIR__ . '/../config/config.php';

if (!usuario_logado()) {

    redirect(
        base_url('public/login.php')
    );
}

if (!is_aluno()) {

    session_unset();

    session_destroy();

    redirect(
        base_url('public/login.php')
    );
}

$tempoLimite = 60 * 60 * 2;

if (
    isset($_SESSION['ultimo_acesso']) &&
    (time() - $_SESSION['ultimo_acesso']) > $tempoLimite
) {

    session_unset();

    session_destroy();

    redirect(
        base_url('public/login.php')
    );
}

$_SESSION['ultimo_acesso'] = time();