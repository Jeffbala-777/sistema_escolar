<?php
require_once __DIR__ . '/../app/middleware/admin.php';
require_once __DIR__ . '/../app/database/database.php';

$title = 'Configurações';
$usuario = $_SESSION['usuario'] ?? null;
$escola_id = $usuario['escola_id'] ?? 0;

if (!$escola_id) {
    $erro = 'Escola não definida na sessão.';
    require_once __DIR__ . '/../partials/header.php';
    echo '<div class="container p-4"><div class="alert alert-danger">' . e($erro) . '</div></div>';
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

// Lei de exemplo de configurações
$configs = $pdo->query("SELECT chave, valor FROM configuracoes WHERE escola_id = $escola_id")->fetchAll();

require_once __DIR__ . '/../partials/header.php';
?>
<div class="d-flex page-wrap">
  <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>
  <div class="content-area p-4">
    <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
    <div class="page-card p-4">
      <div class="page-title mb-3">Configurações da escola</div>

      <div class="alert alert-info small">
        Aqui você pode adicionar/editar configurações dinâmicas da escola via código PHP.
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-sm mb-0">
          <thead class="table-dark">
            <tr>
              <th>Chave</th>
              <th>Valor</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($configs as $c): ?>
              <tr>
                <td><?= e($c['chave']) ?></td>
                <td><?= e($c['valor']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>