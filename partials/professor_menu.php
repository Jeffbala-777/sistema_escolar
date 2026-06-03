<?php

$paginaAtual = basename($_SERVER['PHP_SELF']);

function professorMenuAtivo($pagina)
{
    global $paginaAtual;

    return $paginaAtual === $pagina
        ? 'active-menu'
        : '';
}

?>

<div class="sidebar" id="sidebarMenu">

    <!-- <div class="sidebar-title">
        Professor
    </div> -->

    <?php if (isset($_SESSION['usuario'])): ?>
    <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= e($_SESSION['usuario']['nome_completo'] ?? '') ?></div>
        <div class="sidebar-user-email"><?= e($_SESSION['usuario']['email'] ?? '') ?></div>
    </div>
    <?php endif; ?>

    <a
        class="<?= professorMenuAtivo('dashboard.php') ?>"
        href="<?= base_url('professor/dashboard.php') ?>">

        <i class="bi bi-house-door"></i>
        Tela Principal

    </a>

    <a
        class="<?= professorMenuAtivo('minhas_turmas.php') ?>"
        href="<?= base_url('professor/minhas_turmas.php') ?>">

        <i class="bi bi-grid"></i>
        Minhas Turmas

    </a>

    <a
        class="<?= professorMenuAtivo('selecionar_turma.php') || professorMenuAtivo('desempenho.php') || professorMenuAtivo('relatorios_turma.php') ? 'active-menu' : '' ?>"
        href="<?= base_url('professor/selecionar_turma.php') ?>">

        <i class="bi bi-graph-up-arrow"></i>
        Desempenho

    </a>



    <a href="<?= base_url('public/logout.php') ?>">

        <i class="bi bi-box-arrow-right"></i>
        Sair

    </a>

</div>