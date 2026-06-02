<?php
require_once 'app/config/config.php';
require_once 'app/database/database.php';
require_once 'app/ia/ResumoService.php';

try {
    $service = new ResumoService();
    $resumo = $service->gerarResumo(1); // teste com ID de algum aluno
    echo "<pre>" . htmlspecialchars($resumo) . "</pre>";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}