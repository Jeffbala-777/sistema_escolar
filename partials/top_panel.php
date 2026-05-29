<?php
// Inicia a sessao se ainda nao estiver ativa para acessar dados do usuario
if (!isset($_SESSION)) {
    session_start();
}

// Recupera o array de dados do usuario armazenado na sessao
$usuario = $_SESSION['usuario'] ?? [];

// Define o nome completo do usuario ou um valor padrao se nao existir
$nome = $usuario['nome_completo'] ?? 'Usuário';
// Recupera o nome do perfil/cargo do usuario logado
$perfil = $usuario['perfil_nome'] ?? '';

?>

<!-- Estrutura da barra superior do sistema -->
<div class="topbar">

    <!-- Area esquerda da barra superior -->
    <div class="topbar-left">

        <!-- Botao hamburguer para abrir/fechar o menu lateral -->
        <button
            type="button"
            class="menu-toggle"
            id="menuToggle"
            aria-label="Abrir menu"
        >
            <i class="bi bi-list"></i>
        </button>



    </div>

    <!-- Area direita da barra superior com info do usuario -->
    <div class="topbar-right">

        <!-- Bloco que agrupa o nome e o cargo do usuario -->
        <div class="topbar-user-info">

            <!-- Exibe o nome do usuario logado -->
            <div class="topbar-user-name">
                <?= e($nome) ?>
            </div>

            <div class="topbar-user-role" style="font-size: 0.75rem; color: rgba(255,255,255,0.8); text-align: right;">
                <?= e(perfil_label($perfil)) ?>
            </div>

        </div>

        <!-- Icone circular de avatar do usuario -->
        <div class="topbar-avatar">
            <i class="bi bi-person-fill"></i>
        </div>

    </div>

</div>
