<?php

// app/ia/ResumoService.php

require_once __DIR__ . '/prompts.php';
require_once __DIR__ . '/../config/config.php'; // ajuste o caminho se necessário

class ResumoService {

    private $apiKey;
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo;
        $this->apiKey = GEMINI_API_KEY ?? null; // Pegando da config
        if (!$this->apiKey) {
            throw new Exception("Gemini API Key não configurada.");
        }
        if (!$this->pdo) {
            throw new Exception("Conexão com banco de dados não fornecida.");
        }
    }

    /**
     * Gera resumo do histórico do aluno
     */
    public function gerarResumo($aluno_id) {
        // 1. Busca o histórico do aluno
        $historicos = $this->buscarHistoricoAluno($aluno_id);

        if (empty($historicos)) {
            return "Nenhum registro encontrado no histórico deste aluno.";
        }

        // 2. Monta o texto completo do histórico
        $textoHistorico = "";
        foreach ($historicos as $h) {
            $textoHistorico .= "Data: " . $h['data'] . "\n";
            $textoHistorico .= "Professor: " . $h['professor'] . "\n";
            $textoHistorico .= "Observação: " . $h['texto'] . "\n\n";
        }

        // 3. Pega o prompt
        $prompt = getPromptResumoAluno($textoHistorico);

        // 4. Chama o Gemini
        $resumo = $this->chamarGemini($prompt);

        return $resumo;
    }

    /**
     * Busca histórico no banco (ajuste conforme sua model)
     */
    private function buscarHistoricoAluno($aluno_id) {
        if (!$this->pdo) {
            throw new Exception("Conexão com banco de dados não disponível.");
        }

        $stmt = $this->pdo->prepare("
            SELECT data, professor, texto 
            FROM historico_aluno 
            WHERE aluno_id = ? 
            ORDER BY data DESC
        ");
        $stmt->execute([$aluno_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Chamada real para a API do Gemini
     */
    private function chamarGemini($prompt) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 1000,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return "Erro ao conectar com o Gemini. Código: " . $httpCode;
        }

        $result = json_decode($response, true);

        // Extrai o texto da resposta
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Não foi possível gerar o resumo.";
    }
}