<?php
require_once __DIR__ . '/../app/middleware/verificar_aluno.php';
require_once __DIR__ . '/../app/database/database.php';
require_once __DIR__ . '/../app/models/TurmaModel.php';

$alunoId = $_SESSION['usuario']['id'];
$escolaId = $_SESSION['usuario']['escola_id'];

// Buscar informações da matrícula/turma atual do aluno
$sql = "
    SELECT 
        t.nome as turma_nome,
        t.serie,
        al.ano as ano_escolar
    FROM matriculas m
    INNER JOIN turmas t ON t.id = m.turma_id
    INNER JOIN anos_letivos al ON al.id = m.ano_letivo_id
    WHERE m.aluno_id = :aluno_id
    AND m.escola_id = :escola_id
    AND m.status = 'ativa'
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':aluno_id' => $alunoId, ':escola_id' => $escolaId]);
$matricula = $stmt->fetch();

$title = 'Boletins Escolares';
require_once __DIR__ . '/../partials/header.php';
?>

<?php require_once __DIR__ . '/../partials/top_panel.php'; ?>
<?php require_once __DIR__ . '/../partials/aluno_menu.php'; ?>

<div class="main-content dashboard-aluno-mobile" style="background-color: #f5f5f5; min-height: 100vh; padding: 10px;">

    <div class="container-boletim" style="max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        
        <h2 style="text-align: center; color: #456b82; font-weight: 400; margin-bottom: 25px;">Boletins Escolares</h2>

        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 25px; font-size: 0.9rem; border-left: 5px solid #f5c6cb;">
            <strong style="display: block; margin-bottom: 5px;">Importante:</strong>
            - Etapas em aberto estão suscetíveis de serem alteradas a qualquer momento<br>
            - Etapas fechadas podem ser alteradas somente com autorização pedagógica
        </div>

        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table" style="font-size: 0.85rem; border-collapse: collapse; width: 100%; min-width: 600px;">
                <thead>
                    <tr style="border-bottom: 2px solid #eee; color: #333; text-align: left;">
                        <th style="padding: 12px 8px; font-weight: 600;">Escola</th>
                        <th style="padding: 12px 8px; font-weight: 600;">Turma</th>
                        <th style="padding: 12px 8px; font-weight: 600;">Ano de Escolaridade</th>
                        <th style="padding: 12px 8px; font-weight: 600;">Ano Escolar</th>
                        <th style="padding: 12px 8px; font-weight: 600; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($matricula): ?>
                    <tr style="border-bottom: 1px solid #eee; color: #666;">
                        <td style="padding: 15px 8px;"><?= e($_SESSION['usuario']['escola_nome'] ?? 'Minha Escola') ?></td>
                        <td style="padding: 15px 8px;"><?= e($matricula['turma_nome']) ?></td>
                        <td style="padding: 15px 8px;"><?= e($matricula['serie']) ?></td>
                        <td style="padding: 15px 8px;"><?= e($matricula['ano_escolar']) ?></td>
                        <td style="padding: 15px 8px; text-align: center;">
                            <a href="<?= base_url('aluno/boletim_completo.php') ?>" 
                               style="background: #eee; border: 1px solid #ccc; color: #666; padding: 5px 15px; border-radius: 3px; text-decoration: none; font-size: 0.75rem; transition: all 0.2s;"
                               onmouseover="this.style.background='#e0e0e0'" 
                               onmouseout="this.style.background='#eee'">
                                Visualizar
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: #999;">Nenhuma matrícula ativa encontrada.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Número da versão removido -->

    </div>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>