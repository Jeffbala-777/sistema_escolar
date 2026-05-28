<?php

$paginaAtual = basename($_SERVER['PHP_SELF']);

function alunoMenuAtivo($pagina)
{
    global $paginaAtual;

    return $paginaAtual === $pagina
        ? 'active-menu'
        : '';
}

?>

<div class="sidebar" id="sidebarMenu">

    <!-- <div class="sidebar-title">
        Aluno
    </div> -->

    <?php if (isset($_SESSION['usuario'])): ?>
    <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= e($_SESSION['usuario']['nome_completo'] ?? '') ?></div>
        <div class="sidebar-user-email"><?= e($_SESSION['usuario']['email'] ?? '') ?></div>
    </div>
    <?php endif; ?>

    <a
        class="<?= alunoMenuAtivo('dashboard.php') ?>"
        href="<?= base_url('aluno/dashboard.php') ?>">

        <i class="bi bi-house-door"></i>
        Tela Principal

    </a>

    <a
        class="<?= alunoMenuAtivo('boletim.php') ?>"
        href="<?= base_url('aluno/boletim.php') ?>">

        <i class="bi bi-journal-text"></i>
        Boletim

    </a>

    <a
        class="<?= alunoMenuAtivo('faltas.php') ?>"
        href="<?= base_url('aluno/faltas.php') ?>">

        <i class="bi bi-calendar-check"></i>
        Frequência

    </a>

    <a href="<?= base_url('public/logout.php') ?>">

        <i class="bi bi-box-arrow-right"></i>
        Sair

    </a>

</div>