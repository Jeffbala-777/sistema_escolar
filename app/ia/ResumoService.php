<?php
// app/ia/ResumoService.php

require_once __DIR__ . '/prompts.php';
require_once __DIR__ . '/../config/config.php';

class ResumoService {
    private $apiKey;

    public function __construct() {
        $this->apiKey = GEMINI_API_KEY ?? null;
        if(!$this->apiKey) {
            throw new Exception("Chave de API do Gemini não configurada.");
        }
    }

    public function gerarResumo($aluno_id) {
        $registros = $this->buscarRegistrosAluno($aluno_id);

        if(empty($registros)){
            return "Nenhum registro de observação encontrado para este aluno.";
        }

        $textoHistorico = "";
        foreach($registros as $r) {
            $textoHistorico .= "Data: " . ($r['data'] ?? 'Não informada') . "\n";
            $textoHistorico .= "Tipo: " . $r['tipo'] . "\n";
            $textoHistorico .= "Observação: " . $r['texto'] . "\n";
        }

        $prompt = getPromptResumoAluno($textoHistorico);

        return $this->chamarGemini($prompt);
    }

    /**
     * Busca observações de várias tabelas
     */

    private function buscarRegistrosAluno($aluno_id){
        global $pdo;

        $registros = [];

        // 1. Observação das notas. vai tomando
        $stmt = $pdo->prepare("
        SELECT data_lancamento AS data, 'Nota' AS tipo, observacao AS texto
        FROM notas
        WHERE aluno_id = ? AND observacao IS NOT NULL AND observacao != ''
        ORDER BY data_lancamento DESC
        ");
        $stmt->execute([$aluno_id]);
        $registros = array_merge($registros, $stmt->fetchAll(PDO::FETCH_ASSOC));

        // 2. Observação das Presenças
        $stmt = $pdo->prepare("
        SELECT criado_em AS data, 'Presença' AS tipo, observacao AS texto
        FROM presencas
        WHERE aluno_id = ? AND observacao IS NOT NULL AND observacao != ''
        ORDER BY criado_em DESC
        ");
        $stmt->execute([$aluno_id]);
        $registros = array_merge($registros, $stmt->fetchAll(PDO::FETCH_ASSOC));

        return $registros;
    }

    private function chamarGemini($prompt) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

        $data = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 1000
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode !== 200) {
            return "Erro na chamada à API Gemini (Código $httpCode)";
        }

        $result = json_decode($response, true);
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Erro no resumo";
    }
}