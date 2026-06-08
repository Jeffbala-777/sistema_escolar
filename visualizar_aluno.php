<?php
declare(strict_types=1);

// Protege acesso do admin da escola
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/database/database.php'; // Banco
require_once __DIR__ . '/../app/models/TurmaModel.php'; // Turmas
require_once __DIR__ . '/../app/models/UsuarioModel.php'; // Usuarios
require_once __DIR__ . '/../app/models/DisciplinaModel.php'; // Disciplinas

$turmaModel = new TurmaModel($pdo); // Inicia models
$usuarioModel = new UsuarioModel($pdo);
$disciplinaModel = new DisciplinaModel($pdo);

$escolaId = (int)$_SESSION['usuario']['escola_id']; // ID da escola logada
$turmas = $turmaModel->listarPorEscola($escolaId); // Lista turmas
$disciplinas = $disciplinaModel->listar($escolaId); // Lista disciplinas

$mensagem = $erro = ''; // Feedback para usuario

// Processa o vinculo quando enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? ''; // aluno ou professor
    $turmaId = (int)($_POST['turma_id'] ?? 0);
    $usuarioId = (int)($_POST['usuario_id'] ?? 0);

    if ($tipo === 'aluno') { // Logica para vincular Aluno (1 aluno -> 1 turma)
        // Remove vinculos antigos do aluno nesta escola (limpa matricula atual)
        $stmt = $pdo->prepare("DELETE FROM matriculas WHERE aluno_id = :aid");
        $stmt->execute([':aid' => $usuarioId]);
        
        // Cria novo vinculo de matricula
        $stmt = $pdo->prepare("INSERT INTO matriculas (aluno_id, turma_id, data_matricula) VALUES (:aid, :tid, NOW())");
        if ($stmt->execute([':aid' => $usuarioId, ':tid' => $turmaId])) {
            $mensagem = "Aluno vinculado com sucesso!";
        } else {
            $erro = "Erro ao vincular aluno.";
        }
    } elseif ($tipo === 'professor') { // Logica para vincular Professor (1 professor -> N turmas)
        $disciplinaId = (int)($_POST['disciplina_id'] ?? 0);
        // Verifica se ja existe esse vinculo exato
        $stmt = $pdo->prepare("SELECT id FROM professor_turma_disciplina WHERE professor_id = :pid AND turma_id = :tid AND disciplina_id = :did");
        $stmt->execute([':pid' => $usuarioId, ':tid' => $turmaId, ':did' => $disciplinaId]);
        
        if ($stmt->fetch()) {
            $erro = "Este professor já está vinculado a esta turma e disciplina.";
        } else {
            // Insere novo vinculo para o professor
            $stmt = $pdo->prepare("INSERT INTO professor_turma_disciplina (professor_id, turma_id, disciplina_id, ano_letivo_id) VALUES (:pid, :tid, :did, (SELECT id FROM anos_letivos WHERE ativo = 1 LIMIT 1))");
            if ($stmt->execute([':pid' => $usuarioId, ':tid' => $turmaId, ':did' => $disciplinaId])) {
                $mensagem = "Professor vinculado com sucesso!";
            } else {
                $erro = "Erro ao vincular professor.";
            }
        }
    }
}

// Busca listas para os selects do formulario
$alunosDisponiveis = $usuarioModel->listarAlunos($escolaId);
$professoresDisponiveis = $usuarioModel->listarProfessores($escolaId);

$title = 'Vincular Alunos e Professores';
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?> <!-- Menu lateral -->

    <div class="main-content flex-grow-1">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Painel superior -->

        <div class="p-4">
            <h3 class="mb-4">Gerenciar Vínculos</h3>

            <?php if ($mensagem): ?><div class="alert alert-success"><?= $mensagem ?></div><?php endif; ?>
            <?php if ($erro): ?><div class="alert alert-danger"><?= $erro ?></div><?php endif; ?>

            <div class="row g-4">
                <!-- Form Aluno -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white">Vincular Aluno à Turma</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="tipo" value="aluno">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Selecione o Aluno</label>
                                    <select name="usuario_id" class="form-select" required>
                                        <option value="">-- Escolha o Aluno --</option>
                                        <?php foreach ($alunosDisponiveis as $a): ?>
                                            <option value="<?= $a['id'] ?>"><?= e($a['nome_completo']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Selecione a Turma</label>
                                    <select name="turma_id" class="form-select" required>
                                        <option value="">-- Escolha a Turma --</option>
                                        <?php foreach ($turmas as $t): ?>
                                            <option value="<?= $t['id'] ?>"><?= e($t['nome']) ?> (<?= e($t['serie']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Vincular Aluno</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Form Professor -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-success text-white">Vincular Professor à Turma</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="tipo" value="professor">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Selecione o Professor</label>
                                    <select name="usuario_id" class="form-select" required>
                                        <option value="">-- Escolha o Professor --</option>
                                        <?php foreach ($professoresDisponiveis as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= e($p['nome_completo']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Selecione a Turma</label>
                                    <select name="turma_id" class="form-select" required>
                                        <option value="">-- Escolha a Turma --</option>
                                        <?php foreach ($turmas as $t): ?>
                                            <option value="<?= $t['id'] ?>"><?= e($t['nome']) ?> (<?= e($t['serie']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Selecione a Disciplina</label>
                                    <select name="disciplina_id" class="form-select" required>
                                        <option value="">-- Escolha a Disciplina --</option>
                                        <?php foreach ($disciplinas as $d): ?>
                                            <option value="<?= $d['id'] ?>"><?= e($d['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Vincular Professor</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape -->
