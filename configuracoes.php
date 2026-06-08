<?php

require_once __DIR__ . '/../app/middleware/verificar_admin.php';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/DisciplinaModel.php';

$title = 'Cadastrar Professor';
$usuario = $_SESSION['usuario'] ?? null;
$escola_id = $usuario['escola_id'] ?? 0;

// Trava se nao tiver escola na sessao
if (!$escola_id) {
    require_once __DIR__ . '/../partials/header.php';
    echo '<div class="container p-4"><div class="alert alert-danger">Escola não definida.</div></div>';
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

$usuarioModel = new UsuarioModel($pdo);
$disciplinaModel = new DisciplinaModel($pdo);

// Busca ID do perfil professor
$stmt = $pdo->prepare("SELECT id FROM perfis WHERE nome = 'professor' LIMIT 1");
$stmt->execute();
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);
$perfil_id = $perfil['id'] ?? 0;

// Busca disciplinas existentes
$disciplinas_existentes = $disciplinaModel->listar($escola_id);

$nome = $email = $cpf = $telefone = $nascimento = $erro = $mensagem = '';
$disciplinas_selecionadas = [];

// Processa POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $nascimento = trim($_POST['nascimento'] ?? '');
    $senha1 = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha_confirm'] ?? '';
    $nova_disciplina = trim($_POST['nova_disciplina'] ?? '');
    $disciplinas_selecionadas = $_POST['disciplinas'] ?? [];

    // E-mail Automático: se vazio, gera um @prof.edu.com
    if ($email === '' && $nome !== '') {
        $partesNome = explode(' ', strtolower($nome));
        $primeiroNome = preg_replace('/[^a-z0-9]/', '', $partesNome[0]);
        $ultimoNome = count($partesNome) > 1 ? preg_replace('/[^a-z0-9]/', '', end($partesNome)) : rand(10, 99);
        $email = $primeiroNome . '.' . $ultimoNome . '@prof.edu.com';
    }

    // Processa nova disciplina se informada
    if ($nova_disciplina !== '') {
        // Valida se ja existe disciplina com o mesmo nome (case-insensitive)
        $stmt = $pdo->prepare("SELECT id FROM disciplinas WHERE escola_id = :escola_id AND LOWER(nome) = LOWER(:nome) LIMIT 1");
        $stmt->execute([':escola_id' => $escola_id, ':nome' => $nova_disciplina]);
        $disc_existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($disc_existente) {
            $erro = "Disciplina '{$nova_disciplina}' já existe (validação case-insensitive).";
        } else {
            // Insere nova disciplina
            $stmt = $pdo->prepare("INSERT INTO disciplinas (escola_id, nome, ativo) VALUES (:escola_id, :nome, 1)");
            if ($stmt->execute([':escola_id' => $escola_id, ':nome' => $nova_disciplina])) {
                $nova_disc_id = $pdo->lastInsertId();
                $disciplinas_selecionadas[] = (string)$nova_disc_id;
                // Recarrega lista de disciplinas
                $disciplinas_existentes = $disciplinaModel->listar($escola_id);
                $mensagem_disc = "Disciplina '{$nova_disciplina}' criada com sucesso!";
            } else {
                $erro = "Erro ao criar nova disciplina.";
            }
        }
    }

    // Processa exclusão de disciplina se solicitado
    if (isset($_POST['excluir_disciplina'])) {
        $disc_id = (int)($_POST['excluir_disciplina'] ?? 0);
        if ($disc_id > 0) {
            // Verifica se há vínculos ativos
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM professor_turma_disciplina WHERE disciplina_id = :did AND ativo = 1");
            $stmt->execute([':did' => $disc_id]);
            $vinculos = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($vinculos['total'] > 0) {
                $erro = "Não é possível excluir disciplina com vínculos ativos.";
            } else {
                // Exclui a disciplina
                $stmt = $pdo->prepare("DELETE FROM disciplinas WHERE id = :did AND escola_id = :escola_id");
                if ($stmt->execute([':did' => $disc_id, ':escola_id' => $escola_id])) {
                    $mensagem = "Disciplina excluída com sucesso!";
                    // Recarrega lista
                    $disciplinas_existentes = $disciplinaModel->listar($escola_id);
                    // Remove da seleção se estava lá
                    $disciplinas_selecionadas = array_filter($disciplinas_selecionadas, function($d) use ($disc_id) {
                        return (int)$d !== $disc_id;
                    });
                } else {
                    $erro = "Erro ao excluir disciplina.";
                }
            }
        }
    }

    // Se não houver erro, continua com cadastro do professor
    if ($erro === '' && !isset($_POST['excluir_disciplina']) && $nova_disciplina === '') {
        if ($nome === '') {
            $erro = 'Nome é obrigatório.';
        } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail inválido.';
        } elseif ($usuarioModel->buscarPorEmail($email)) {
            $erro = 'E-mail já cadastrado.';
        } elseif ($senha1 !== $senha2) {
            $erro = 'As senhas não conferem.';
        } elseif (strlen($senha1) < 6) {
            $erro = 'A senha deve ter no mínimo 6 caracteres.';
        } else {
            // Cadastra o professor
            $dados = [
                'escola_id' => $escola_id,
                'perfil_id' => $perfil_id,
                'nome_completo' => $nome,
                'email' => $email,
                'senha' => $senha1,
                'cpf' => $cpf,
                'telefone' => $telefone,
                'nascimento' => $nascimento
            ];
            
            if ($usuarioModel->cadastrar($dados)) {
                $professor_id = $pdo->lastInsertId();
                
                // Se selecionou disciplinas, vincula ao professor
                if (!empty($disciplinas_selecionadas)) {
                    // Busca uma turma padrão para vincular (primeira turma ativa)
                    $stmt = $pdo->prepare("SELECT id FROM turmas WHERE escola_id = :escola_id LIMIT 1");
                    $stmt->execute([':escola_id' => $escola_id]);
                    $turma = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($turma) {
                        $turma_id = $turma['id'];
                        $ano_letivo_stmt = $pdo->prepare("SELECT id FROM anos_letivos WHERE ativo = 1 LIMIT 1");
                        $ano_letivo_stmt->execute();
                        $ano_letivo = $ano_letivo_stmt->fetch(PDO::FETCH_ASSOC);
                        $ano_letivo_id = $ano_letivo['id'] ?? 1;

                        foreach ($disciplinas_selecionadas as $disc_id) {
                            $disc_id = (int)$disc_id;
                            // Verifica se já existe vínculo
                            $check_stmt = $pdo->prepare("SELECT id FROM professor_turma_disciplina WHERE professor_id = :pid AND turma_id = :tid AND disciplina_id = :did");
                            $check_stmt->execute([':pid' => $professor_id, ':tid' => $turma_id, ':did' => $disc_id]);
                            
                            if (!$check_stmt->fetch()) {
                                // Insere vínculo
                                $insert_stmt = $pdo->prepare("INSERT INTO professor_turma_disciplina (escola_id, professor_id, turma_id, disciplina_id, ano_letivo_id, ativo) VALUES (:escola_id, :pid, :tid, :did, :ano_letivo_id, 1)");
                                $insert_stmt->execute([
                                    ':escola_id' => $escola_id,
                                    ':pid' => $professor_id,
                                    ':tid' => $turma_id,
                                    ':did' => $disc_id,
                                    ':ano_letivo_id' => $ano_letivo_id
                                ]);
                            }
                        }
                    }
                }
                
                $mensagem = "Professor cadastrado com sucesso! E-mail: $email";
                $nome = $email = $cpf = $telefone = $nascimento = '';
                $disciplinas_selecionadas = [];
            } else {
                $erro = 'Erro ao cadastrar professor.';
            }
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex page-wrap">
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="content-area p-4 flex-grow-1">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
        <div class="page-card p-4 shadow-sm bg-white rounded">
            <h4 class="mb-4">Cadastrar Novo Professor</h4>
            <?php if ($mensagem): ?><div class="alert alert-success"><?= e($mensagem) ?></div><?php endif; ?>
            <?php if ($erro): ?><div class="alert alert-danger"><?= e($erro) ?></div><?php endif; ?>
            <?php if (isset($mensagem_disc)): ?><div class="alert alert-info"><?= e($mensagem_disc) ?></div><?php endif; ?>

            <form method="POST" autocomplete="off">
                <!-- Dados do Professor -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nome completo</label>
                        <input type="text" name="nome" class="form-control" value="<?= e($nome) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Data de nascimento</label>
                        <input type="date" name="nascimento" class="form-control" value="<?= e($nascimento) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">E-mail (Vazio = automático)</label>
                        <input type="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="Ex: nome@prof.edu.com">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">CPF</label>
                        <input type="text" name="cpf" class="form-control" value="<?= e($cpf) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="<?= e($telefone) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Confirmar senha</label>
                        <input type="password" name="senha_confirm" class="form-control" required>
                    </div>
                </div>

                <!-- Seção de Disciplinas -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Disciplinas do Professor</h6>
                    </div>
                    <div class="card-body">
                        <!-- Lista de Disciplinas Existentes -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Disciplinas Cadastradas</label>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                <?php if (!empty($disciplinas_existentes)): ?>
                                    <?php foreach ($disciplinas_existentes as $disc): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                            <div class="form-check flex-grow-1">
                                                <input class="form-check-input" type="checkbox" name="disciplinas[]" 
                                                       value="<?= $disc['id'] ?>" id="disc_<?= $disc['id'] ?>"
                                                       <?= in_array((string)$disc['id'], $disciplinas_selecionadas) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="disc_<?= $disc['id'] ?>">
                                                    <?= e($disc['nome']) ?>
                                                </label>
                                            </div>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir disciplina?');">
                                                <input type="hidden" name="excluir_disciplina" value="<?= $disc['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Nenhuma disciplina cadastrada.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Criar Nova Disciplina -->
                        <div>
                            <label class="form-label fw-bold">Criar Nova Disciplina</label>
                            <div class="input-group">
                                <input type="text" name="nova_disciplina" class="form-control" 
                                       placeholder="Nome da nova disciplina">
                                <button class="btn btn-outline-primary" type="submit" name="criar_disciplina">
                                    <i class="bi bi-plus-circle me-1"></i> Adicionar
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Nomes duplicados (ex: Matemática e matemática) serão bloqueados automaticamente.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Cadastrar Professor</button>
                    <a href="professores.php" class="btn btn-secondary">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
