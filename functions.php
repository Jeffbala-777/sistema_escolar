<?php

// Dados de acesso ao banco
$host   = 'localhost';
$dbname = 'sistema_escolar';
$user   = 'root';
$pass   = '';

// Opcoes de comportamento do banco
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostra erros
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna array simples
    PDO::ATTR_EMULATE_PREPARES   => false, // Seguranca extra
];

try {
    // Cria a conexao PDO
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        $options
    );

} catch (PDOException $e) {
    // Se der erro, para o site e avisa
    http_response_code(500);
    exit('Erro ao conectar ao banco de dados.');
}
