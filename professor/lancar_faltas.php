<?php
require_once __DIR__ . '/../app/middleware/professor.php';
require_once __DIR__ . '/../app/models/faltaModel.php';

$title = 'Lançar Faltas';
$usuario = $_SESSION['usuario'];
$escola_id = $usuario['escola_id'];

$turma_id = $_GET['turma_id'] ?? 0;
$aluno_id = $_GET['aluno_id'] ?? 0;


require_once __DIR__ . '/../app/database/database.php';

$sql = "SELECT t.id, t.nome, t.serie, t.turno FROM turmas t
        WHERE t.id = :turma_id AND t.escola_id = :escola_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':turma_id', $turma_id, PDO::PARAM_INT);
$stmt->bindValue(':escola_id', $escola_id);
$stmt->execute();
$turma = $stmt->fetch();

$sql = "SELECT id, nome_completo FROM usuarios
        WHERE id = :aluno_id AND escola_id = :escola_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':aluno_id', $aluno_id, PDO::PARAM_INT);
$stmt->bindValue(':escola_id', $escola_id);
$stmt->execute();
$aluno = $stmt->fetch();

$ano_letivo_id = 1; // ajuste para o ano atual depois
$data_falta = $_POST['data_falta'] ?? '';
$erro = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_falta = trim($_POST['data_falta'] ?? '');
    $status = $_POST['status'] ?? 'falta';
    $obs = trim($_POST['observacao'] ?? '');

    if (empty($data_falta)) {
        $erro = 'Informe a data da falta.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_falta)) {
        $erro = 'Formato de data inválido.';
    } else {
        $sql = "INSERT INTO faltas
                (escola_id, aluno_id, turma_id, disciplina_id, ano_letivo_id, periodo_id, data_falta, status, observacao)
                VALUES (:escola_id, :aluno_id, :turma_id, NULL, :ano_letivo_id, NULL, :data_falta, :status, :observacao)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':escola_id', $escola_id);
        $stmt->bindValue(':aluno_id', $aluno_id, PDO::PARAM_INT);
        $stmt->bindValue(':turma_id', $turma_id, PDO::PARAM_INT);
        $stmt->bindValue(':ano_letivo_id', $ano_letivo_id, PDO::PARAM_INT);
        $stmt->bindValue(':data_falta', $data_falta);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':observacao', $obs);
        if ($stmt->execute()) {
            $mensagem = 'Falta lançada com sucesso.';
        } else {
            $erro = 'Erro ao salvar falta.';
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>
<div class="container py-4">
  <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
  <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

  <?php if (!$turma || !$aluno): ?>
    <div class="alert alert-danger">Dados inválidos ou acesso incorreto.</div>
  <?php else: ?>
    <div class="page-card p-4">
      <div class="page-title mb-3">Lançar Faltas – <?= e($aluno['nome_completo']) ?></div>
      <div class="small mb-3">
        Turma: <?= e($turma['nome']) ?> – <?= e($turma['serie']) ?> – <?= e($turma['turno']) ?>
      </div>

      <?php if ($mensagem): ?>
        <div class="alert alert-success mb-3"><?= e($mensagem) ?></div>
      <?php endif; ?>
      <?php if ($erro): ?>
        <div class="alert alert-danger mb-3"><?= e($erro) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">Data da falta</label>
            <input type="date" class="form-control" name="data_falta" value="<?= e($data_falta) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-control" name="status">
              <option value="falta" <?= $data_falta && (!isset($_POST['status']) || $_POST['status'] === 'falta') ? 'selected' : '' ?>>Falta</option>
              <option value="presente" <?= isset($_POST['status']) && $_POST['status'] === 'presente' ? 'selected' : '' ?>>Presente</option>
              <option value="justificada" <?= isset($_POST['status']) && $_POST['status'] === 'justificada' ? 'selected' : '' ?>>Justificada</option>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Observação</label>
          <textarea class="form-control" name="observacao" rows="3"><?= e($_POST['observacao'] ?? '') ?></textarea>
        </div>
        <div>
          <button type="submit" class="btn btn-primary">Salvar falta</button>
          <a class="btn btn-secondary" href="<?= base_url('professor/alunos_turma.php?turma_id=' . $turma_id) ?>">
            Voltar
          </a>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>