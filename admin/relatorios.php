<?php
require_once __DIR__ . '/../app/middleware/admin.php';
require_once __DIR__ . '/../app/database/database.php';

$title = 'Relatórios';
$usuario = $_SESSION['usuario'] ?? null;
$escola_id = $usuario['escola_id'] ?? 0;

if (!$escola_id) {
    $erro = 'Escola não definida na sessão.';
    require_once __DIR__ . '/../partials/header.php';
    echo '<div class="container p-4"><div class="alert alert-danger">' . e($erro) . '</div></div>';
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

// Filtros via GET
$ano_letivo_id = (int)($_GET['ano_letivo_id'] ?? 0);
$turma_id = (int)($_GET['turma_id'] ?? 0);

// Lista de anos letivos da escola (exemplo rápido)
$anos_letivos = $pdo->query("SELECT id, ano FROM anos_letivos WHERE escola_id = $escola_id ORDER BY ano DESC")->fetchAll();
if (empty($anos_letivos) && $ano_letivo_id === 0) {
    $erro = 'Nenhum ano letivo cadastrado.';
}

// Lista de turmas da escola (filtradas por ano se desejado)
$turmas = $pdo->query("SELECT id, nome, serie, turno FROM turmas WHERE escola_id = $escola_id AND ativo = 1 ORDER BY nome, serie")->fetchAll();

// Carrega turma pelo id para mostrar no título
$turma_nome = '';
if ($turma_id > 0) {
    $sql = "SELECT nome FROM turmas WHERE id = :turma_id AND escola_id = :escola_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':turma_id', $turma_id, PDO::PARAM_INT);
    $stmt->bindValue(':escola_id', $escola_id);
    $stmt->execute();
    $t = $stmt->fetch();
    $turma_nome = $t ? $t['nome'] : '';
}

// Se não houver POST/GET de filtros, pega o primeiro ano
if ($ano_letivo_id === 0 && !empty($anos_letivos)) {
    $ano_letivo_id = (int)$anos_letivos[0]['id'];
}

$relatorio = [];

// Só gera relatório se houver ano letivo válido
if ($ano_letivo_id > 0) {
    $sql_base = "SELECT m.turma_id, t.nome AS turma_nome, t.serie, t.turno
                 FROM matriculas m
                 JOIN turmas t ON t.id = m.turma_id
                 WHERE m.escola_id = :escola_id
                   AND m.ano_letivo_id = :ano_letivo_id
                 GROUP BY m.turma_id";
    $stmt = $pdo->prepare($sql_base);
    $stmt->bindValue(':escola_id', $escola_id);
    $stmt->bindValue(':ano_letivo_id', $ano_letivo_id, PDO::PARAM_INT);
    $stmt->execute();
    $turmas_rel = $stmt->fetchAll();

    foreach ($turmas_rel as $t) {
        // Se tiver turma_id filtrada, pula as demais
        if ($turma_id > 0 && $t['turma_id'] != $turma_id) {
            continue;
        }

        // Média geral de notas da turma no ano
        $sql_media = "SELECT AVG(n.nota) AS media_geral
                      FROM notas n
                      JOIN matriculas m ON m.aluno_id = n.aluno_id
                      WHERE m.turma_id = :turma_id
                        AND m.escola_id = :escola_id
                        AND m.ano_letivo_id = :ano_letivo_id";
        $stmt = $pdo->prepare($sql_media);
        $stmt->bindValue(':turma_id', $t['turma_id'], PDO::PARAM_INT);
        $stmt->bindValue(':escola_id', $escola_id);
        $stmt->bindValue(':ano_letivo_id', $ano_letivo_id, PDO::PARAM_INT);
        $stmt->execute();
        $row_media = $stmt->fetch();
        $media = $row_media ? $row_media['media_geral'] : 0.0;

        // Total de faltas na turma no ano
        $sql_faltas = "SELECT COUNT(*) AS total_faltas
                       FROM faltas f
                       WHERE f.turma_id = :turma_id
                         AND f.escola_id = :escola_id
                         AND f.ano_letivo_id = :ano_letivo_id
                         AND f.status = 'falta'";
        $stmt = $pdo->prepare($sql_faltas);
        $stmt->bindValue(':turma_id', $t['turma_id'], PDO::PARAM_INT);
        $stmt->bindValue(':escola_id', $escola_id);
        $stmt->bindValue(':ano_letivo_id', $ano_letivo_id, PDO::PARAM_INT);
        $stmt->execute();
        $row_faltas = $stmt->fetch();
        $total_faltas = $row_faltas ? (int)$row_faltas['total_faltas'] : 0;

        $relatorio[] = [
            'turma_nome'   => $t['turma_nome'],
            'turma_serie'  => $t['serie'] ?? '-',
            'turma_turno'  => $t['turno'],
            'media_geral'  => $media,
            'total_faltas' => $total_faltas,
        ];
    }
}

require_once __DIR__ . '/../partials/header.php';
?>
<div class="d-flex page-wrap">
  <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>
  <div class="content-area p-4">
    <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
    <div class="page-card p-4">
      <div class="page-title mb-3">Relatórios da escola</div>

      <!-- Filtros -->
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Ano Letivo</label>
          <select name="ano_letivo_id" class="form-control"
                  onchange="location.href='<?= base_url('admin/relatorios.php') ?>?ano_letivo_id='+this.value+'&turma_id=<?= $turma_id ?>';">
            <?php foreach ($anos_letivos as $a): ?>
              <option value="<?= $a['id'] ?>" <?= $a['id'] == $ano_letivo_id ? 'selected' : '' ?>>
                <?= e($a['ano']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Turma (opcional)</label>
          <select name="turma_id" class="form-control"
                  onchange="location.href='<?= base_url('admin/relatorios.php') ?>?ano_letivo_id=<?= $ano_letivo_id ?>&turma_id='+this.value;">
            <option value="0" <?= $turma_id == 0 ? 'selected' : '' ?>>Todas as turmas</option>
            <?php foreach ($turmas as $t): ?>
              <option value="<?= $t['id'] ?>" <?= $t['id'] == $turma_id ? 'selected' : '' ?>>
                <?= e($t['nome']) ?> (<?= e($t['serie'] ?? '-') ?> - <?= e($t['turno']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <?php if ($erro): ?>
        <div class="alert alert-danger"><?= e($erro) ?></div>
      <?php elseif (empty($relatorio)): ?>
        <div class="alert alert-info">
          Não há dados de notas/faltas para o ano letivo e turma escolhidos.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
              <tr>
                <th>Turma</th>
                <th>Série</th>
                <th>Turno</th>
                <th>Média geral</th>
                <th>Total de faltas</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($relatorio as $r): ?>
                <tr>
                  <td><?= e($r['turma_nome']) ?></td>
                  <td><?= e($r['turma_serie']) ?></td>
                  <td><?= e($r['turma_turno']) ?></td>
                  <td><?= number_format($r['media_geral'], 2) ?></td>
                  <td><?= e($r['total_faltas']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>