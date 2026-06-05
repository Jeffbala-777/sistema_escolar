<?php
require_once __DIR__ . '/../app/middleware/professor.php';
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';
require_once __DIR__ . '/../app/models/notaModel.php';

$title = 'Alunos da Turma';
$usuario = $_SESSION['usuario'];
$escola_id = $usuario['escola_id'];
$professor_id = $usuario['id'];

$turma_id = $_GET['turma_id'] ?? 0;
$ano_letivo_id = 1; // ajuste depois para ano atual real

require_once __DIR__ . '/../app/database/database.php';

// Turma
$sql = "SELECT t.nome, t.serie, t.turno FROM turmas t
        WHERE t.id = :turma_id AND t.escola_id = :escola_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':turma_id', $turma_id, PDO::PARAM_INT);
$stmt->bindValue(':escola_id', $escola_id);
$stmt->execute();
$turma = $stmt->fetch();

// Lista de alunos matriculados nesta turma
$sql = "SELECT u.id, u.nome_completo, u.cpf
        FROM matriculas m
        JOIN usuarios u ON u.id = m.aluno_id
        WHERE m.turma_id = :turma_id
          AND m.escola_id = :escola_id
          AND m.status = 'ativa'
        ORDER BY u.nome_completo";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':turma_id', $turma_id, PDO::PARAM_INT);
$stmt->bindValue(':escola_id', $escola_id);
$stmt->execute();
$alunos = $stmt->fetchAll();

// Disciplinas que o professor dá nessa turma
$sql = "SELECT d.id, d.nome FROM professor_turma_disciplina ptd
        JOIN disciplinas d ON d.id = ptd.disciplina_id
        WHERE ptd.turma_id = :turma_id
          AND ptd.professor_id = :professor_id
          AND ptd.escola_id = :escola_id
          AND ptd.ativo = 1";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':turma_id', $turma_id, PDO::PARAM_INT);
$stmt->bindValue(':professor_id', $professor_id, PDO::PARAM_INT);
$stmt->bindValue(':escola_id', $escola_id);
$stmt->execute();
$disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../partials/header.php';
?>
<div class="container py-4">
  <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
  <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

  <?php if (!$turma): ?>
    <div class="alert alert-danger">Turma não encontrada ou acesso inválido.</div>
  <?php else: ?>
    <div class="page-card p-4">
      <div class="page-title mb-3">
        <?= e($turma['nome']) ?> – <?= e($turma['serie']) ?> – <?= e($turma['turno']) ?>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Nome</th>
              <th>CPF</th>
              <th>Notas</th>
              <th>Faltas</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($alunos as $a): ?>
              <tr>
                <td><?= e($a['nome_completo']) ?></td>
                <td><?= e($a['cpf'] ?? '-') ?></td>
                <td>
                  <?php foreach ($disciplinas as $d): ?>
                    <a class="badge text-bg-primary"
                       href="<?= base_url('professor/lancar_notas.php?turma_id=' . $turma_id . '&aluno_id=' . $a['id'] . '&disciplina_id=' . $d['id']) ?>">
                      <?= e($d['nome']) ?>
                    </a>
                  <?php endforeach; ?>
                </td>
                <td>
                  <a class="badge text-bg-danger"
                     href="<?= base_url('professor/lancar_faltas.php?turma_id=' . $turma_id . '&aluno_id=' . $a['id']) ?>">
                    Faltas
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>