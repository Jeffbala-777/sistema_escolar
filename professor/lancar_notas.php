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

// Busca escala de nota da escola
$sqlEscola = "SELECT escala_nota FROM escolas WHERE id = ? LIMIT 1";
$stmtEscola = $pdo->prepare($sqlEscola);
$stmtEscola->execute([$usuario['escola_id']]);
$escola = $stmtEscola->fetch(PDO::FETCH_ASSOC);
$escalaNota = (float)($escola['escala_nota'] ?? 10);

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $model = new NotaModel($pdo);

        $nota = (float) ($_POST['nota'] ?? 0);

        if ($nota < 0 || $nota > $escalaNota) {
            throw new Exception("A nota deve ser entre 0 e {$escalaNota}.");
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
                <?php 
                require_once __DIR__ . '/../app/models/PeriodoLetivoModel.php';
                $periodoModel = new PeriodoLetivoModel($pdo);
                $periodos = $periodoModel->listarPorAno($anoLetivoId, $usuario['escola_id']);
                
                if (empty($periodos)) {
                    // Fallback para 4 bimestres se não houver períodos cadastrados
                    for ($i = 1; $i <= 4; $i++) {
                        echo "<option value=\"$i\">{$i}º Bimestre</option>";
                    }
                } else {
                    foreach ($periodos as $p) {
                        echo "<option value=\"{$p['id']}\">" . e($p['nome']) . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Nota (0-<?= $escalaNota ?>)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                max="<?= $escalaNota ?>"
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