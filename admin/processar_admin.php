<?php
require_once __DIR__ . '/../app/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Erro de segurança!");
    }

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // AQUI A MÁGICA SEGURA
    $tipo = 'admin'; // Definindo como admin normal

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome_completo, email, senha, tipo, ativo) VALUES (?, ?, ?, ?, 1)");
    
    if ($stmt->execute([$nome, $email, $senha, $tipo])) {
        echo "Admin cadastrado com sucesso! <a href='dashboard.php'>Voltar</a>";
    } else {
        echo "Erro ao cadastrar.";
    }
}