<?php
// app/ia/ResumoService.php

require_once __DIR__ . '/prompts.php';
require_once __DIR__ . '/../config/config.php';

class ResumoService {

    private $apiKey;
    private const GROQ_API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const GROQ_MODEL = 'llama-3.3-70b-versatile';

    public function __construct()
    {
        $this->apiKey = GROQ_API_KEY ?? null;

        if (!$this->apiKey) {
            throw new Exception("GROQ_API_KEY não configurada. Obtenha uma chave gratuita em: https://console.groq.com/keys");
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

        // 4. Chama o Groq
        $resumo = $this->chamarGroqAPI($prompt);
        
        return $resumo;
    }

    /**
     * Busca histórico no banco (ajuste conforme sua model)
     */
    private function buscarHistoricoAluno($aluno_id) {
        // Exemplo usando sua estrutura atual
        global $pdo; // se você estiver usando PDO no config

        $stmt = $pdo->prepare("
            SELECT data, professor, texto 
            FROM historico_aluno 
            WHERE aluno_id = ? 
            ORDER BY data DESC
        ");
        $stmt->execute([$aluno_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Chamada real para a API do Groq (compatível com OpenAI)
     */
    private function chamarGroqAPI($prompt)
    {
        $data = [
            'model' => self::GROQ_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Você é um assistente pedagógico especializado em análise de histórico escolar.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];

        $ch = curl_init(self::GROQ_API_URL);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Erro desconhecido';
            return "Erro Groq HTTP {$httpCode}: {$errorMessage}";
        }

        $result = json_decode($response, true);

        return $result['choices'][0]['message']['content']
            ?? 'Não foi possível gerar o resumo.';
    }
}
