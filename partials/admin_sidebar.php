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

    <div class="sidebar-title">
        Administração
    </div>

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

    <!-- Link para gestao de alunos (antigo usuarios) -->
    <a
        class="<?= adminMenuAtivo('alunos.php') ?>"
        href="<?= base_url('admin/alunos.php') ?>">

        <i class="bi bi-people"></i>
        Alunos

    </a>

    <a
        class="<?= adminMenuAtivo('professores.php') ?>"
        href="<?= base_url('admin/professores.php') ?>">

        <i class="bi bi-mortarboard"></i>
        Professores

    </a>

    <a
        class="<?= adminMenuAtivo('turmas.php') ?>"
        href="<?= base_url('admin/turmas.php') ?>">

        <i class="bi bi-grid"></i>
        Turmas

    </a>

    <a
        class="<?= adminMenuAtivo('disciplinas.php') ?>"
        href="<?= base_url('admin/disciplinas.php') ?>">

        <i class="bi bi-book"></i>
        Disciplinas

    </a>

    <!-- Link para vincular alunos e professores -->
    <a
        class="<?= adminMenuAtivo('vinculos.php') ?>"
        href="<?= base_url('admin/vinculos.php') ?>">

        <i class="bi bi-link-45deg"></i>
        Vínculos

    </a>

    <a href="<?= base_url('public/logout.php') ?>">

        <i class="bi bi-box-arrow-right"></i>
        Sair

    </a>

</div>