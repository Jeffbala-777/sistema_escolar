<?php
// Ativa tipagem estrita para seguranca
declare(strict_types=1);

// Verifica se o usuario e administrador
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
// Conexao com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model para gerenciar turmas
require_once __DIR__ . '/../app/models/TurmaModel.php';
// Model para gerenciar usuarios/alunos
require_once __DIR__ . '/../app/models/UsuarioModel.php';

// Instancia os modelos necessarios
$turmaModel = new TurmaModel($pdo);
$usuarioModel = new UsuarioModel($pdo);

// Pega o ID da escola do administrador logado
$escolaId = (int)$_SESSION['usuario']['escola_id'];
// Lista todas as turmas vinculadas a esta escola
$turmas = $turmaModel->listarPorEscola($escolaId);

// Pega o ID da turma selecionada via URL (filtro)
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
// Inicializa array de alunos
$alunos = [];

// Se uma turma foi selecionada, busca os alunos dela
if ($turmaId > 0) {
    $alunos = $usuarioModel->listarPorTurma($turmaId); 
}

// Define o titulo da pagina
$title = 'Gestão de Alunos';
// Inclui o cabecalho padrao
require_once __DIR__ . '/../partials/header.php';
?>

<!-- Estrutura principal com menu lateral -->
<div class="d-flex">
    <!-- Sidebar do administrador -->
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Conteudo principal da pagina -->
    <div class="main-content flex-grow-1">
        <!-- Barra superior -->
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <!-- Cabecalho da secao -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-secondary">Gerenciar Alunos</h3>
                <!-- Botao para cadastrar novo aluno -->
                <a href="cadastrar_alunos.php" class="btn btn-primary btn-sm shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Novo Aluno
                </a>
            </div>

            <!-- Filtro de selecao de turma (Estilo Professor) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="GET" class="row align-items-end g-3">
                        <div class="col-md-9">
                            <label class="form-label fw-bold text-muted small text-uppercase">Selecione a Turma</label>
                            <!-- Dropdown de turmas com submissao automatica -->
                            <select name="turma_id" class="form-select form-select-lg border-0 bg-light" onchange="this.form.submit()">
                                <option value="">-- Escolha uma Turma para listar os alunos --</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $turmaId == $t['id'] ? 'selected' : '' ?>>
                                        <?= e($t['nome']) ?> (<?= e($t['serie']) ?> - <?= e($t['turno']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <!-- Botao manual de filtro -->
                            <button type="submit" class="btn btn-secondary btn-lg w-100 shadow-sm">
                                <i class="bi bi-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Exibicao dos alunos em formato de CARTOES (Design padronizado) -->
            <?php if ($turmaId > 0): ?>
                <div class="row g-4">
                    <?php foreach ($alunos as $a): ?>
                        <div class="col-md-6 col-lg-4">
                            <!-- Cartao do aluno -->
                            <div class="card border-0 shadow-sm h-100 card-hover">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <!-- Icone representativo do aluno -->
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                            <i class="bi bi-person-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <!-- Nome e Email do aluno -->
                                            <h6 class="mb-0 fw-bold"><?= e($a['nome_completo']) ?></h6>
                                            <small class="text-muted"><?= e($a['email']) ?></small>
                                        </div>
                                    </div>
                                    
                                    <!-- Info de contato -->
                                    <div class="mb-4">
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-telephone me-2"></i> <?= e($a['telefone'] ?? 'Não informado') ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-hash me-2"></i> ID: <?= $a['id'] ?>
                                        </div>
                                    </div>

                                    <!-- Botoes de acao (Visualizar Notas/Faltas e Editar) -->
                                    <div class="d-flex gap-2">
                                        <!-- Novo botao Visualizar (Redireciona para ver notas/faltas) -->
                                        <a href="visualizar_aluno.php?id=<?= $a['id'] ?>" class="btn btn-light btn-sm flex-grow-1 fw-bold text-primary">
                                            <i class="bi bi-eye me-1"></i> Visualizar
                                        </a>
                                        <!-- Botao de edicao de cadastro -->
                                        <a href="editar_aluno.php?id=<?= $a['id'] ?>" class="btn btn-outline-secondary btn-sm px-3">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Mensagem caso a turma esteja vazia -->
                    <?php if(empty($alunos)): ?>
                        <div class="col-12 text-center py-5">
                            <img src="../public/img/empty.svg" alt="Vazio" style="width: 150px;" class="mb-3 opacity-50">
                            <p class="text-muted">Nenhum aluno matriculado nesta turma ainda.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Mensagem inicial orientando a selecao -->
                <div class="text-center py-5 bg-white rounded shadow-sm">
                    <i class="bi bi-arrow-up-circle display-4 text-primary opacity-25 d-block mb-3"></i>
                    <h5 class="text-secondary">Escolha uma turma acima para começar</h5>
                    <p class="text-muted small">Você poderá visualizar o boletim e as faltas de cada aluno.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Estilo extra para o efeito de hover nos cartoes -->
<style>
.card-hover { transition: transform 0.2s, shadow 0.2s; }
.card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>

<!-- Inclui o rodape padrao -->
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
