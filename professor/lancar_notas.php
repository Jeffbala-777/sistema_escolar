<?php
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/NotasModel.php';

$title = 'Lançar Notas';

$usuario = $_SESSION['usuario'];

$alunoId = (int) ($_GET['aluno_id'] ?? 0);
$turmaId = (int) ($_GET['turma_id'] ?? 0);
$disciplinaId = (int) ($_GET['disciplina_id'] ?? 0);
$anoLetivoId = (int) ($_GET['ano_letivo_id'] ?? 1);

$sql = "SELECT nome_completo FROM usuarios WHERE id = ? LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$alunoId]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $model = new NotaModel($pdo);

        $nota = (float) ($_POST['nota'] ?? 0);

        if ($nota < 0 || $nota > 10) {
            throw new Exception('A nota deve ser entre 0 e 10.');
        }

        $model->salvar([
            'escola_id' => $usuario['escola_id'],
            'aluno_id' => $alunoId,
            'disciplina_id' => $disciplinaId,
            'professor_id' => $usuario['id'],
            'ano_letivo_id' => $anoLetivoId,
            'periodo_id' => (int) ($_POST['periodo_id'] ?? 1),
            'tipo' => 'prova',
            'nota' => $nota,
            'observacao' => $_POST['observacao'] ?? ''
        ]);

        $mensagem = 'Nota lançada com sucesso.';

    } catch (Exception $e) {

        $erro = $e->getMessage();
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-4">

<?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
<?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

<div class="page-card p-4">

    <div class="page-title mb-4">
        Lançar Nota
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-success">
            <?= e($mensagem); ?>
        </div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= e($erro); ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Aluno</label>
            <input
                type="text"
                class="form-control"
                value="<?= e($aluno['nome_completo']); ?>"
                readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Período</label>

            <select name="periodo_id" class="form-select" required>
                <option value="1">1º Bimestre</option>
                <option value="2">2º Bimestre</option>
                <option value="3">3º Bimestre</option>
                <option value="4">4º Bimestre</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Nota</label>
            <input
                type="number"
                step="0.01"
                min="0"
                max="10"
                name="nota"
                class="form-control"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">Observação</label>
            <textarea
                name="observacao"
                class="form-control"></textarea>
        </div>

        <button class="btn btn-primary">
            Salvar Nota
        </button>

    </form>

</div>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>