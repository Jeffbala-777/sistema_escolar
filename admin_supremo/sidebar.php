<?php
// Define a pagina atual para marcar o menu ativo
$paginaAtual = basename($_SERVER['PHP_SELF']);

// Funcao para verificar se o link do menu esta ativo
function adminSupremoMenuAtivo($pagina) {
    global $paginaAtual; // Pega a variavel global
    return $paginaAtual === $pagina ? 'active-menu' : ''; // Retorna a classe CSS se for igual
}
?>

<!-- Container principal da sidebar com ID para o toggle mobile -->
<div class="sidebar" id="sidebarMenu">
    
    <!-- Titulo da sidebar -->
    <div class="sidebar-title">
        Admin Supremo
    </div>

    <!-- Informacoes do usuario logado se existirem -->
    <?php if (isset($_SESSION['usuario'])): ?>
    <div class="sidebar-user-info">
        <!-- Nome do administrador supremo -->
        <div class="sidebar-user-name"><?= e($_SESSION['usuario']['nome_completo'] ?? '') ?></div>
        <!-- E-mail do administrador supremo -->
        <div class="sidebar-user-email"><?= e($_SESSION['usuario']['email'] ?? '') ?></div>
    </div>
    <?php endif; ?>

    <!-- Link para o Dashboard Inicial -->
    <a class="<?= adminSupremoMenuAtivo('dashboard.php') ?>" href="dashboard.php">
        <i class="bi bi-speedometer2"></i>
        <span>Início</span>
    </a>
    
    <!-- Link para Gestao de Escolas -->
    <a class="<?= adminSupremoMenuAtivo('escolas.php') ?>" href="escolas.php">
        <i class="bi bi-building"></i>
        <span>Escolas</span>
    </a>

    <!-- Link para Gestao de Administradores das Escolas -->
    <a class="<?= adminSupremoMenuAtivo('usuarios.php') ?>" href="usuarios.php">
        <i class="bi bi-person-badge"></i>
        <span>Admins Escolas</span>
    </a>

    <!-- Link para Monitoramento de Dados Global -->
    <a class="<?= adminSupremoMenuAtivo('visualizar_dados.php') ?>" href="visualizar_dados.php">
        <i class="bi bi-search"></i>
        <span>Ver Dados</span>
    </a>

    <!-- Link para Logs de Auditoria e Seguranca -->
    <a class="<?= adminSupremoMenuAtivo('logs.php') ?>" href="logs.php">
        <i class="bi bi-shield-lock"></i>
        <span>Logs</span>
    </a>

    <!-- Link para sair do sistema -->
    <a href="../public/logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Sair</span>
    </a>

</div>
