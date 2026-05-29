<?php

// Carrega as configuracoes globais
require_once __DIR__ . '/../config/config.php';

try {
    // Tenta criar a conexao PDO diretamente
    $pdo = new PDO(
        'mysql:host=localhost;dbname=sistema_escolar;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostra erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna array
            PDO::ATTR_EMULATE_PREPARES   => false, // Seguranca extra
        ]
    );

} catch (PDOException $e) {
    // Se falhar, avisa e para o sistema
    exit('Erro de conexão com o banco.');
}
