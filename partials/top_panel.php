<?php

if (!isset($_SESSION)) {
    session_start();
}

$usuario = $_SESSION['usuario'] ?? [];

$nome = $usuario['nome_completo'] ?? 'Usuário';
$perfil = $usuario['perfil_nome'] ?? '';

?>

<div class="topbar">

    <div class="topbar-left">

        <button
            type="button"
            class="menu-toggle"
            id="menuToggle"
            aria-label="Abrir menu"
        >
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-logo">
            Sistema Escolar
        </div>

    </div>

    <div class="topbar-right">

        <div class="topbar-user-info">

            <div class="topbar-user-name">
                <?= e($nome) ?>
            </div>

            <div class="topbar-user-role">
                <?= e(perfil_label($perfil)) ?>
            </div>

        </div>

        <div class="topbar-avatar">
            <i class="bi bi-person-fill"></i>
        </div>

    </div>

</div>