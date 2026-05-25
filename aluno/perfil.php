<?php
require_once __DIR__ . '/../app/middleware/aluno.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';

$title = 'Meu Perfil';
$usuario = $_SESSION['usuario'];
$escola_id = $usuario['escola_id'];
$aluno_id = $usuario['id'];

// Aqui depois você pode buscar endereço, dados completos, etc.
// Por enquanto só exibe o básico vindo da sessão
$cpf = $usuario['cpf'] ?? '-';
$telefone = $usuario['telefone'] ?? '-';
$nascimento = $usuario['nascimento'] ?? '-';

require_once __DIR__ . '/../partials/header.php';
?>
<div class="container py-4">
  <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
  <?php require_once __DIR__ . '/../partials/aluno_menu.php'; ?>

  <div class="page-card p-4">
    <div class="page-title mb-3">Meu Perfil</div>

    <div class="row mb-3">
      <div class="col-md-4"><strong>Nome:</strong></div>
      <div class="col-md-8"><?= e($usuario['nome_completo']) ?></div>
    </div>
    <div class="row mb-3">
      <div class="col-md-4"><strong>E‑mail:</strong></div>
      <div class="col-md-8"><?= e($usuario['email']) ?></div>
    </div>
    <div class="row mb-3">
      <div class="col-md-4"><strong>CPF:</strong></div>
      <div class="col-md-8"><?= e($cpf) ?></div>
    </div>
    <div class="row mb-3">
      <div class="col-md-4"><strong>Telefone:</strong></div>
      <div class="col-md-8"><?= e($telefone) ?></div>
    </div>
    <div class="row mb-3">
      <div class="col-md-4"><strong>Data de nascimento:</strong></div>
      <div class="col-md-8"><?= e($nascimento === '-' ? '-' : date('d/m/Y', strtotime($nascimento))) ?></div>
    </div>
    <div class="row mb-3">
      <div class="col-md-4"><strong>Escola:</strong></div>
      <div class="col-md-8">Escola <?= e($escola_id) ?></div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>