<?php
// Ativa tipagem estrita para garantir seguranca no tratamento de dados
declare(strict_types=1);

// Protege o acesso garantindo que apenas alunos logados entrem na pagina
require_once __DIR__ . '/../app/middleware/verificar_aluno.php';
// Conexao central com o banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Model responsavel por buscar e organizar as notas do aluno
require_once __DIR__ . '/../app/models/NotasModel.php';
// Model responsavel por gerenciar os periodos letivos (Bimestres/Trimestres)
require_once __DIR__ . '/../app/models/PeriodoLetivoModel.php';
// Componente unificado de boletim
require_once __DIR__ . '/../app/components/BoletimComponent.php';

// Pega o ID do aluno logado na sessao
$alunoId = (int)$_SESSION['usuario']['id'];
// Pega o ID da escola vinculada ao aluno
$escolaId = (int)$_SESSION['usuario']['escola_id'];
// Define o ano letivo padrao como 1
$stmtAno = $pdo->prepare("
    SELECT ano_letivo_id
    FROM matriculas
    WHERE aluno_id = :aid
    AND status = 'ativa'
    LIMIT 1
");

$stmtAno->execute([
    ':aid' => $alunoId
]);

$anoLetivoId = (int) ($stmtAno->fetchColumn() ?: 1);

// Instancia os modelos de dados necessarios
$notasModel = new NotaModel($pdo);
$periodoModel = new PeriodoLetivoModel($pdo);

// Busca os periodos configurados para a escola no banco
$periodosBanco = $periodoModel->listarPorAno($anoLetivoId, $escolaId);

// Lógica de períodos fixos conforme pedido
$periodosParaExibir = [];
if (count($periodosBanco) > 0) {
    $periodosParaExibir = $periodosBanco;
} else {
    // Fallback: 4 bimestres
    for ($i = 1; $i <= 4; $i++) {
        $periodosParaExibir[] = ['id' => $i, 'nome' => $i . 'º Bimestre'];
    }
}

// Busca o boletim completo
$boletim = $notasModel->buscarNotasCompletasAluno($alunoId, $anoLetivoId);

// Busca informacoes da escola diretamente do banco
$stmtEscola = $pdo->prepare("SELECT nome, tipo_periodo FROM escolas WHERE id = :id LIMIT 1");
$stmtEscola->execute([':id' => $escolaId]);
$escolaInfo = $stmtEscola->fetch(PDO::FETCH_ASSOC);
$escolaNomeReal = $escolaInfo['nome'] ?? 'Minha Escola';
$tipoPeriodo = $escolaInfo['tipo_periodo'] ?? 'bimestral';

// Busca dados da matricula
$stmtMatricula = $pdo->prepare("SELECT t.nome as turma_nome, t.serie FROM matriculas m 
                                JOIN turmas t ON t.id = m.turma_id 
                                WHERE m.aluno_id = :aid AND m.status = 'ativa' LIMIT 1");
$stmtMatricula->execute([':aid' => $alunoId]);
$infoMatricula = $stmtMatricula->fetch(PDO::FETCH_ASSOC);

// Inicializa o componente de boletim
$boletimComponent = new BoletimComponent($pdo, $escolaId, $tipoPeriodo);

$title = 'Boletim Escolar';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../partials/aluno_menu.php'; ?>

    <main class="main-content flex-grow-1" style="background-color: #f8f9fa; min-height: 100vh;">
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>

        <div class="p-4">
            <div class="bg-white shadow-sm rounded p-4 mx-auto" style="max-width: 1200px;">
                
                <!-- Cabeçalho Informativo -->
                <?= $boletimComponent->renderizarCabecalhoInfo(
                    $escolaNomeReal,
                    $infoMatricula['turma_nome'] ?? '-',
                    $infoMatricula['serie'] ?? '-'
                ) ?>

                <!-- Tabela de Notas Unificada -->
                <?= $boletimComponent->renderizarTabela($boletim, $periodosParaExibir) ?>

            </div>
        </div>
    </main>
</div>

<?= BoletimComponent::renderizarCSS() ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
