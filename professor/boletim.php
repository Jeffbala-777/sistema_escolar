<?php

require_once __DIR__ . '/../app/middleware/verificar_professor.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';

$title = 'Boletim da Turma';

$usuario = $_SESSION['usuario'];

$escola_id = (int) $usuario['escola_id'];
$professor_id = (int) $usuario['id'];

$ano_letivo_id = 1;

$turma_id = (int) ($_GET['turma_id'] ?? 0);

$erro = '';

$sql = "
    SELECT
        t.id,
        t.nome,
        t.serie,
        t.turno

    FROM turmas t

    WHERE t.id = :turma_id
    AND t.escola_id = :escola_id

    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ':turma_id',
    $turma_id,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':escola_id',
    $escola_id,
    PDO::PARAM_INT
);

$stmt->execute();

$turma = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "
    SELECT
        d.id,
        d.nome

    FROM professor_turma_disciplina ptd

    INNER JOIN disciplinas d
        ON d.id = ptd.disciplina_id

    WHERE ptd.turma_id = :turma_id
    AND ptd.professor_id = :professor_id
    AND ptd.escola_id = :escola_id
    AND ptd.ativo = 1

    ORDER BY d.nome ASC
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ':turma_id',
    $turma_id,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':professor_id',
    $professor_id,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':escola_id',
    $escola_id,
    PDO::PARAM_INT
);

$stmt->execute();

$disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($turma && empty($disciplinas)) {

    $erro = 'Você não possui acesso a esta turma.';
}

$periodos = [
    [
        'id' => 1,
        'nome' => '1º Bimestre'
    ],
    [
        'id' => 2,
        'nome' => '2º Bimestre'
    ],
    [
        'id' => 3,
        'nome' => '3º Bimestre'
    ],
    [
        'id' => 4,
        'nome' => '4º Bimestre'
    ]
];

$sql = "
    SELECT
        u.id,
        u.nome_completo,
        m.numero_matricula

    FROM matriculas m

    INNER JOIN usuarios u
        ON u.id = m.aluno_id

    WHERE m.turma_id = :turma_id
    AND m.escola_id = :escola_id
    AND m.status = 'ativa'

    ORDER BY u.nome_completo ASC
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ':turma_id',
    $turma_id,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':escola_id',
    $escola_id,
    PDO::PARAM_INT
);

$stmt->execute();

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-4">

    <?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
    <?php require_once __DIR__ . '/../partials/professor_menu.php'; ?>

    <?php if (!$turma): ?>

        <div class="alert alert-danger">
            Turma não encontrada.
        </div>

    <?php elseif ($erro): ?>

        <div class="alert alert-warning">
            <?= e($erro); ?>
        </div>

    <?php else: ?>

        <div class="page-card p-4">

            <div class="page-title mb-4">

                Boletim —
                <?= e($turma['nome']); ?>
                —
                <?= e($turma['serie']); ?>
                —
                <?= e($turma['turno']); ?>

            </div>

            <div class="table-responsive">

                <table class="table table-striped table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>
                                Aluno
                            </th>

                            <?php foreach ($disciplinas as $disciplina): ?>

                                <th>
                                    <?= e($disciplina['nome']); ?>
                                </th>

                            <?php endforeach; ?>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($alunos as $aluno): ?>

                            <tr>

                                <td>
                                    <?= e($aluno['nome_completo']); ?>
                                </td>

                                <?php foreach ($disciplinas as $disciplina): ?>

                                    <td>

                                        <?php

                                        $sqlNotas = "
                                            SELECT
                                                periodo_id,
                                                nota

                                            FROM notas

                                            WHERE aluno_id = :aluno_id
                                            AND disciplina_id = :disciplina_id
                                            AND escola_id = :escola_id
                                            AND ano_letivo_id = :ano_letivo_id
                                        ";

                                        $stmtNotas = $pdo->prepare($sqlNotas);

                                        $stmtNotas->execute([
                                            ':aluno_id' => $aluno['id'],
                                            ':disciplina_id' => $disciplina['id'],
                                            ':escola_id' => $escola_id,
                                            ':ano_letivo_id' => $ano_letivo_id
                                        ]);

                                        $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

                                        $medias = [];

                                        foreach ($notas as $nota) {

                                            $periodo = (int) $nota['periodo_id'];

                                            if (!isset($medias[$periodo])) {
                                                $medias[$periodo] = [];
                                            }

                                            $medias[$periodo][] = (float) $nota['nota'];
                                        }

                                        $notasExibidas = [];

                                        foreach ($periodos as $periodo) {

                                            $idPeriodo = $periodo['id'];

                                            if (
                                                isset($medias[$idPeriodo]) &&
                                                count($medias[$idPeriodo]) > 0
                                            ) {

                                                $media =
                                                    array_sum($medias[$idPeriodo]) /
                                                    count($medias[$idPeriodo]);

                                                $notasExibidas[] = number_format(
                                                    $media,
                                                    1,
                                                    ',',
                                                    '.'
                                                );

                                            } else {

                                                $notasExibidas[] = '-';
                                            }
                                        }

                                        echo implode(' | ', $notasExibidas);

                                        ?>

                                    </td>

                                <?php endforeach; ?>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <div class="mt-4">

                <a
                    href="<?= base_url('professor/dashboard.php'); ?>"
                    class="btn btn-secondary btn-sm">

                    Voltar

                </a>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>