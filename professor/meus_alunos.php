<?php
// Protege o acesso garantindo que apenas professores logados entrem
require_once __DIR__ . '/../app/middleware/verificar_professor.php';
// Inclui o model para gerenciar as relacoes entre professor, turma e disciplina
require_once __DIR__ . '/../app/models/ProfessorTurmaDisciplinaModel.php';
// Inclui o model para gerenciar as notas
require_once __DIR__ . '/../app/models/NotasModel.php';
// Conexao central com o banco de dados
require_once __DIR__ . '/../app/database/database.php';

// Define o titulo da pagina para o cabecalho
$title = 'Meus Alunos - Turma';
// Pega os dados do professor logado na sessao
$usuario = $_SESSION['usuario'];
// ID da escola vinculada ao professor
$escola_id = $usuario['escola_id'];
// ID do proprio professor
$professor_id = $usuario['id'];

// Pega o ID da turma via parametro GET (URL)
$turma_id = $_GET['turma_id'] ?? 0;
// Define o ano letivo padrao como 1
$ano_letivo_id = 1;

// Busca os dados basicos da turma para exibir no topo
$sql = "SELECT t.id, t.nome, t.serie, t.turno FROM turmas t
        WHERE t.id = :turma_id AND t.escola_id = :escola_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':turma_id', $turma_id, PDO::PARAM_INT);
$stmt->bindValue(':escola_id', $escola_id);
$stmt->execute();
$turma = $stmt->fetch();

// Lista todos os alunos matriculados e ativos nesta turma especifica
$sql = "SELECT u.id, u.nome_completo, u.cpf, u.email
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

// Inclui o cabecalho padrao do sistema
require_once __DIR__ . '/../partials/header.php';
?>

<!-- Estrutura principal com menu lateral -->
<div class="d-flex page-wrap">

    <!-- Menu lateral do professor -->
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <!-- Area de conteudo da pagina -->
    <div class="content-area p-4">

        <!-- Painel superior com nome e cargo -->
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="page-card p-4">

            <!-- Verifica se a turma existe para o professor -->
            <?php if (!$turma): ?>

                <!-- Mensagem de erro caso a turma seja invalida -->
                <div class="alert alert-danger">Turma não encontrada ou acesso inválido.</div>

            <?php else: ?>

                <!-- Cabecalho com informacoes da turma selecionada -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold text-secondary mb-1">
                            <?= e($turma['nome']) ?> – <?= e($turma['serie']) ?> – <?= e($turma['turno']) ?>
                        </h4>
                        <p class="text-muted small mb-0">Gerenciamento e visualização individual de alunos</p>
                    </div>
                    <!-- Botao para voltar para a lista de turmas -->
                    <a href="minhas_turmas.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>

                <!-- Lista de Alunos em formato de CARTOES (Design padronizado conforme pedido) -->
                <div class="row g-4">
                    <?php foreach ($alunos as $aluno): ?>
                        <div class="col-md-6 col-lg-4">
                            <!-- Cartao individual do aluno -->
                            <div class="card border-0 shadow-sm h-100 card-hover" style="border-radius: 15px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <!-- Avatar representativo -->
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                            <i class="bi bi-person-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <!-- Nome e CPF do aluno -->
                                            <h6 class="mb-0 fw-bold"><?= e($aluno['nome_completo']) ?></h6>
                                            <small class="text-muted"><?= e($aluno['cpf'] ?? 'Sem CPF') ?></small>
                                        </div>
                                    </div>
                                    
                                    <!-- Informacoes adicionais -->
                                    <div class="mb-4">
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-envelope me-2"></i> <?= e($aluno['email'] ?? 'E-mail não informado') ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-hash me-2"></i> Matrícula: <?= $aluno['id'] ?>
                                        </div>
                                    </div>

                                    <!-- Botao Visualizar (Redireciona para ver notas e faltas) -->
                                    <a href="visualizar_aluno.php?id=<?= $aluno['id'] ?>&turma_id=<?= $turma_id ?>" class="btn btn-primary btn-sm w-100 fw-bold py-2 shadow-sm" style="border-radius: 10px;">
                                        <i class="bi bi-eye me-1"></i> Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Mensagem caso nao existam alunos na turma -->
                    <?php if(empty($alunos)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-people display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3">Nenhum aluno matriculado nesta turma.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<!-- Estilo CSS extra para efeito de hover nos cartoes -->
<style>
.card-hover { transition: all 0.3s ease; }
.card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
</style>

<!-- Inclui o rodape padrao -->
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
