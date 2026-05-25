<?php
// Protege acesso do admin
require_once __DIR__ . '/../app/middleware/verificar_admin.php';
$title = 'Dashboard Admin';
require_once __DIR__ . '/../partials/header.php'; // Topo
?>

<?php require_once __DIR__ . '/../partials/top_panel.php'; ?> <!-- Painel superior -->
<?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?> <!-- Menu lateral -->

<div class="main-content">
    <div class="cards-grid">
        <!-- Card de Alunos (Escolhe turma primeiro) -->
        <a href="alunos.php" style="text-decoration: none; color: inherit;">
            <div class="dashboard-card">
                <div class="circle-icon"><i class="bi bi-people"></i></div>
                <h5>Alunos</h5>
                <p>Gerencie alunos por turma.</p>
            </div>
        </a>

        <!-- Card de Professores -->
        <a href="professores.php" style="text-decoration: none; color: inherit;">
            <div class="dashboard-card">
                <div class="circle-icon"><i class="bi bi-mortarboard"></i></div>
                <h5>Professores</h5>
                <p>Gerencie os docentes.</p>
            </div>
        </a>

        <!-- Card de Turmas -->
        <a href="turmas.php" style="text-decoration: none; color: inherit;">
            <div class="dashboard-card">
                <div class="circle-icon"><i class="bi bi-grid"></i></div>
                <h5>Turmas</h5>
                <p>Organização das turmas.</p>
            </div>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> <!-- Rodape -->
