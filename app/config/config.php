<?php
session_start();

require_once __DIR__ . '/conexao.php';

function gerarEmailPadrao($nome_completo) {
    $partes = preg_split('/\s+/', trim($nome_completo));
    $nome = strtolower($partes[0] ?? 'aluno');
    $sobrenome = strtolower($partes[1] ?? 'usuario');
    $random = rand(100, 999);
    return $nome . '.' . $sobrenome . '.' . $random . '@sistema.escolar.com';
}

function gerarSenhaAleatoria() {
    return bin2hex(random_bytes(4));
}

function e($valor) {
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}