<?php

$paginaAtual = basename($_SERVER['PHP_SELF']);

function adminMenuAtivo($pagina)
{
    global $paginaAtual;

    return $paginaAtual === $pagina
        ? 'active-menu'
        : '';
}

?>

<div class="sidebar" id="sidebarMenu">

    <!-- <div class="sidebar-title">
        Administração
    </div> -->

    <?php if (isset($_SESSION['usuario'])): ?>
    <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= e($_SESSION['usuario']['nome_completo'] ?? '') ?></div>
        <div class="sidebar-user-email"><?= e($_SESSION['usuario']['email'] ?? '') ?></div>
    </div>
    <?php endif; ?>

    <a
        class="<?= adminMenuAtivo('dashboard.php') ?>"
        href="<?= base_url('admin/dashboard.php') ?>">

        <i class="bi bi-house-door"></i>
        Tela Principal

    </a>

    <a
        class="<?= adminMenuAtivo('selecionar_turma.php') ?> <?= adminMenuAtivo('desempenho.php') ?>"
        href="<?= base_url('admin/selecionar_turma.php') ?>">

        <i class="bi bi-graph-up-arrow"></i>
        Desempenho

    </a>

    <a
        class="<?= in_array($paginaAtual, ['relatorios.php', 'relatorios_turma.php', 'historico_relatorios.php']) ? 'active-menu' : '' ?>"
        href="<?= base_url('admin/relatorios_turma.php') ?>">

        <i class="bi bi-journal-text"></i>
        Relatórios

    </a>

    <a href="<?= base_url('public/logout.php') ?>">

        <i class="bi bi-box-arrow-right"></i>
        Sair

    </a>

</div>