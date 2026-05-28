<?php
declare(strict_types=1); // Ativa tipagem estrita

// Verifica se e admin supremo para permitir acesso
require_once __DIR__ . '/../app/middleware/verificar_admin_supremo.php';
// Conecta ao banco de dados
require_once __DIR__ . '/../app/database/database.php';
// Carrega os modelos necessarios
require_once __DIR__ . '/../app/models/UsuarioModel.php';
require_once __DIR__ . '/../app/models/EscolaModel.php';
require_once __DIR__ . '/../app/models/AdminSupremoModel.php';

$usuarioModel = new UsuarioModel($pdo); // Instancia model de usuarios
$escolaModel = new EscolaModel($pdo); // Instancia model de escolas
$adminSupremoModel = new AdminSupremoModel($pdo); // Instancia model do supremo

$acao = $_GET['acao'] ?? ''; // Pega acao da URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Pega ID do usuario
$filtro_escola = isset($_GET['escola_id']) ? (int)$_GET['escola_id'] : null; // Filtro de escola
$filtro_perfil = isset($_GET['perfil_id']) ? (int)$_GET['perfil_id'] : 2; // Filtro de perfil (padrao Admin)

$mensagem = ''; // Mensagem de feedback
$tipo_mensagem = ''; // Estilo da mensagem (success/danger)

// Processa delecao se houver POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao_post = $_POST['acao'] ?? '';
    if ($acao_post === 'deletar_permanente') {
        $id_del = (int)$_POST['id'];
        if ($adminSupremoModel->excluirUsuario($id_del)) {
            $mensagem = 'Usuário deletado com sucesso!';
            $tipo_mensagem = 'success';
        }
    }
}

// Busca dados para a tabela e filtros
$usuarios = $adminSupremoModel->listarUsuariosFiltrados($filtro_escola, $filtro_perfil);
$escolas = $escolaModel->listarTodas();
$perfis = $adminSupremoModel->listarPerfis();

$title = 'Gestão de Admins - Admin Supremo'; // Titulo da pagina
require_once __DIR__ . '/../partials/header.php'; // Topo padrao
?>

<div class="d-flex"> <!-- Container flexivel -->
    <?php require_once __DIR__ . '/sidebar.php'; ?> <!-- Menu lateral padronizado -->

    <div class="main-content flex-grow-1"> <!-- Conteudo principal -->
        <?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Topbar -->

        <div class="p-4"> <!-- Espacamento interno -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">Gestão de Administradores</h3> <!-- Titulo da secao -->
                <a href="cadastrar_usuarios.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-2"></i> Novo Admin</a> <!-- Link cadastro -->
            </div>

            <!-- Area de Filtros Estilizada -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Escola</label>
                            <!-- Atualiza pagina ao mudar escola -->
                            <select class="form-select form-select-sm" onchange="window.location.href='?escola_id='+this.value+'&perfil_id=<?= $filtro_perfil ?>'">
                                <option value="">Todas as Escolas</option>
                                <?php foreach ($escolas as $e): ?>
                                    <option value="<?= $e['id'] ?>" <?= $filtro_escola == $e['id'] ? 'selected' : '' ?>><?= e($e['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Cargo/Perfil</label>
                            <!-- Atualiza pagina ao mudar perfil -->
                            <select class="form-select form-select-sm" onchange="window.location.href='?perfil_id='+this.value+'<?= $filtro_escola ? '&escola_id='.$filtro_escola : '' ?>'">
                                <?php foreach ($perfis as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $filtro_perfil == $p['id'] ? 'selected' : '' ?>><?= e(perfil_label($p['nome'])) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="usuarios.php" class="btn btn-outline-secondary btn-sm w-100">Resetar</a> <!-- Limpa filtros -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerta Informativo -->
            <div class="alert alert-info py-2 small mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Dica:</strong> Gerencie aqui os gestores das escolas. Dados operacionais ficam em <strong>Ver Dados</strong>.
            </div>

            <!-- Feedback de acoes -->
            <?php if ($mensagem): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show"><?= $mensagem ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <!-- Tabela de Usuarios -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark"> <!-- Cabecalho escuro -->
                                <tr>
                                    <th class="px-4">ID</th>
                                    <th>Nome / Escola</th>
                                    <th>E-mail</th>
                                    <th>Senha (Hash)</th>
                                    <th>Telefone</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($usuarios) > 0): ?>
                                    <?php foreach ($usuarios as $u): ?> <!-- Loop de usuarios -->
                                        <tr>
                                            <td class="px-4"><?= $u['id'] ?></td> <!-- ID -->
                                            <td>
                                                <div class="fw-bold"><?= e($u['nome_completo']) ?></div> <!-- Nome -->
                                                <small class="text-muted"><?= e($u['escola_nome']) ?></small> <!-- Escola -->
                                            </td>
                                            <td><?= e($u['email']) ?></td> <!-- E-mail -->
                                            <td>
                                                <!-- Mostra inicio do hash da senha -->
                                                <code class="small text-muted" title="<?= e($u['senha']) ?>"><?= substr($u['senha'], 0, 10) ?>...</code>
                                            </td>
                                            <td><?= e($u['telefone'] ?? '-') ?></td> <!-- Telefone -->
                                            <td class="text-center">
                                                <div class="btn-group"> <!-- Botoes de acao -->
                                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Editar" onclick="alert('Função de edição será implementada em breve.')"><i class="bi bi-pencil"></i></button>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Deletar permanentemente?')">
                                                        <input type="hidden" name="acao" value="deletar_permanente">
                                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">Nenhum administrador encontrado.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape e scripts -->
