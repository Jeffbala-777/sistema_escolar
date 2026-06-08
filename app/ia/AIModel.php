<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/BaseModel.php';

class AIModel extends BaseModel
{
    private const GROQ_API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const GROQ_MODEL = 'llama-3.3-70b-versatile';
    private const NOTA_MAXIMA = 40;
    private const MEDIA_APROVACAO = 24;

    public function analisarDesempenho(array $dados, string $perfil, string $tipoPeriodo = 'bimestral', ?array $periodoSelecionado = null): string
    {
        $prompt = $this->construirPrompt($dados, $perfil, $tipoPeriodo, $periodoSelecionado);

        try {
            $apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
            if (empty($apiKey)) {
                return '❌ GROQ_API_KEY não configurada.';
            }

            $payload = json_encode([
                'model' => self::GROQ_MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um especialista em análise pedagógica institucional. Sua escrita deve ser clara, objetiva, profissional e adequada ao ambiente escolar.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.2,
                'max_tokens' => 2000
            ], JSON_UNESCAPED_UNICODE);

            $ch = curl_init(self::GROQ_API_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_TIMEOUT => 60
            ]);

            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return "❌ Erro na API (HTTP $httpCode)";
            }

            $data = json_decode((string)$response, true);
            return trim($data['choices'][0]['message']['content'] ?? '❌ Resposta vazia.');
        } catch (Throwable $e) {
            return '❌ Erro: ' . $e->getMessage();
        }
    }

    private function construirPrompt(array $dados, string $perfil, string $tipoPeriodo = 'bimestral', ?array $periodoSelecionado = null): string
    {
        $dadosCompactos = $this->compactarDados($dados);
        $periodoTxt = $periodoSelecionado ? $periodoSelecionado['nome'] : 'Todos os Períodos';
        $notaMaxima = self::NOTA_MAXIMA;
        $mediaAprovacao = self::MEDIA_APROVACAO;

        if ($perfil === 'professor') {
            return "Você é um analista pedagógico institucional.

Analise toda a turma utilizando exclusivamente os dados fornecidos no JSON.

Use os dados exatamente como estão. Não invente nomes, notas, faltas ou períodos.

REGRAS DE CLASSIFICAÇÃO DA TURMA:
- Média geral abaixo de 15 = Crítica.
- Média geral de 15 a 19,9 = Atenção.
- Média geral de 20 a 24,9 = Regular.
- Média geral de 25 a 29,9 = Boa.
- Média geral de 30 a 34,9 = Muito Boa.
- Média geral de 35 a 40 = Excelente.

Não classifique uma turma com média geral 17 como Boa.
Não classifique uma turma com média geral abaixo de 20 como Boa.

A resposta deve seguir EXATAMENTE esta estrutura:

📊 PERÍODO
Informar o período analisado e a turma.
PERÍODO SELECIONADO: {$periodoTxt}

🏫 TURMA
Nome da turma.

📈 SITUAÇÃO GERAL
Classificar a turma conforme a média geral e os dados de faltas.

📋 INDICADORES DA TURMA
Média Geral: X
Total de Faltas: X

📉 COMPARAÇÃO COM PERÍODOS ANTERIORES
Informar:
- evolução da média
- aumento ou redução das faltas
- tendência observada

🎯 PRINCIPAIS PONTOS POSITIVOS
Máximo de 3 itens.

⚠️ PRINCIPAIS PONTOS DE ATENÇÃO
Máximo de 3 itens.

🚨 ALUNOS QUE NECESSITAM ATENÇÃO
Liste TODOS os alunos que atendam a pelo menos um destes critérios:
- média abaixo de {$mediaAprovacao}
- 3 ou mais faltas
- sem nota
- queda significativa de desempenho entre períodos

Para cada aluno, mostre:
• Nome
Nota: X
Faltas: X
Motivo: explique objetivamente.

Não omita alunos críticos.
Se houver 5, liste 5.
Se houver 20, liste 20.
Não limite a quantidade.

Se não houver alunos críticos:
(Não foram identificados alunos em situação de risco)

💡 RECOMENDAÇÃO PEDAGÓGICA
Máximo de 3 frases.

📝 CONCLUSÃO
Resumo final em uma frase.

REGRAS:
- Não inventar dados.
- Não criar estatísticas fictícias.
- Não listar todos os alunos, apenas os críticos.
- Linguagem profissional e objetiva.
- Máximo de 25 linhas.
- NOTA MÁXIMA DO SISTEMA: {$notaMaxima} pontos.

DADOS EM JSON:
" . json_encode($dadosCompactos, JSON_UNESCAPED_UNICODE);
        }

        if ($perfil === 'aluno') {
            return "Você é um analista pedagógico escolar.

Analise EXCLUSIVAMENTE os dados recebidos no JSON.

Utilize obrigatoriamente os valores presentes nos campos:
- aluno
- disciplina
- media_aluno
- total_faltas
- historico

Nunca invente nomes, notas, faltas ou períodos.

NOTA MÁXIMA DO SISTEMA: {$notaMaxima} pontos
MÉDIA DE APROVAÇÃO: {$mediaAprovacao} pontos

ESTRUTURA OBRIGATÓRIA:

PERÍODO:
Utilize o período selecionado pelo sistema: {$periodoTxt}

ALUNO:
Exiba o valor do campo aluno.

DISCIPLINA:
Exiba o valor do campo disciplina.

SITUAÇÃO GERAL:
Classifique utilizando a escala 0-{$notaMaxima}:
- {$this->calculaPercentual(90)} a {$notaMaxima} = Excelente
- {$this->calculaPercentual(80)} a {$this->calculaPercentual(89.9)} = Muito Bom
- {$mediaAprovacao} a {$this->calculaPercentual(79.9)} = Bom
- {$this->calculaPercentual(50)} a {$this->calculaPercentual(79.9)} = Atenção
- Abaixo de {$this->calculaPercentual(50)} = Crítico

Use o valor de media_aluno.
Se media_aluno for maior que {$notaMaxima}, use {$notaMaxima} como limite máximo.

INDICADORES ATUAIS:
Nota Média: usar media_aluno
Faltas: usar total_faltas

COMPARAÇÃO COM PERÍODOS ANTERIORES:
Use exclusivamente os valores presentes em historico.
Compare os períodos existentes.
Exemplos:
- A nota aumentou de 20 para 24.
- A nota caiu de 35 para 25.
- O desempenho permaneceu estável.

Nunca escreva 'sem dados' se existir histórico.

EVOLUÇÃO POSITIVA:
Identifique:
- aumento de notas
- manutenção de notas altas (acima de {$mediaAprovacao})
- ausência de faltas
- melhora contínua

Caso não exista:
Não foram identificados avanços relevantes.

SITUAÇÃO CRÍTICA:
Identifique:
- nota abaixo da média mínima ({$mediaAprovacao})
- queda contínua de desempenho
- excesso de faltas

Caso não exista:
Não há pontos críticos identificados.

RECOMENDAÇÃO:
No máximo 1 frase.

CONCLUSÃO:
No máximo 1 frase.

REGRAS:
- Não utilizar emojis.
- Não criar introduções.
- Não criar parágrafos longos.
- Máximo de 12 linhas.
- Utilizar apenas os dados recebidos.
- Nunca inventar informações.
- Se nota for maior que {$notaMaxima}, capte para {$notaMaxima}.

DADOS EM JSON:
" . json_encode($dadosCompactos, JSON_UNESCAPED_UNICODE);
        }

        return 'Resumo: ' . json_encode($dadosCompactos, JSON_UNESCAPED_UNICODE);
    }

    private function calculaPercentual(float $percentual): float
    {
        return round(($percentual / 100) * self::NOTA_MAXIMA, 1);
    }

    private function compactarDados(array $dados): array
    {
        if (isset($dados[0]['disciplina'])) {
            $turma = $dados[0]['turma'] ?? '';
            $disciplina = $dados[0]['disciplina'] ?? '';
            $mediaGeral = $dados[0]['media_geral'] ?? 0;
            $alunosCriticos = $dados[0]['alunos_criticos'] ?? [];
            $totalFaltas = 0;
            foreach ($alunosCriticos as $a) {
                $totalFaltas += (int)($a['faltas'] ?? 0);
            }

            return [[
                'turma' => $turma,
                'disciplina' => $disciplina,
                'media_geral' => $mediaGeral,
                'total_faltas' => $totalFaltas,
                'alunos_criticos' => $alunosCriticos
            ]];
        }

        if (isset($dados['aluno_nome'])) {
            $mediaValidada = $dados['media_aluno'];
            if (is_numeric($mediaValidada) && (float)$mediaValidada > self::NOTA_MAXIMA) {
                $mediaValidada = self::NOTA_MAXIMA;
            }

            $historicoFonte = $dados['historico_notas'] ?? [];
            $historicoValidado = array_map(function ($h) {
                if (isset($h['nota']) && is_numeric($h['nota']) && $h['nota'] !== 'Sem nota') {
                    $nota = (float)$h['nota'];
                    if ($nota > self::NOTA_MAXIMA) {
                        $h['nota'] = self::NOTA_MAXIMA;
                    }
                }
                return $h;
            }, $historicoFonte);

            $historicoReal = array_values(array_filter($historicoValidado, function ($h) {
                return isset($h['nota']) && $h['nota'] !== 'Sem nota';
            }));

            return [
                'aluno' => $dados['aluno_nome'],
                'disciplina' => $dados['disciplina_analisada'],
                'nota_maxima' => self::NOTA_MAXIMA,
                'media_aprovacao' => self::MEDIA_APROVACAO,
                'media_aluno' => $mediaValidada !== null ? round((float)$mediaValidada, 1) : 'Sem nota',
                'total_faltas' => $dados['total_faltas'],
                'historico' => $historicoReal
            ];
        }

        return $dados;
    }

    public function coletarDadosAluno(int $alunoId, int $escolaId, ?int $turmaId = null, ?int $periodoId = null, ?int $disciplinaId = null): array
    {
        $alunoNome = $this->fetchColumn("SELECT nome_completo FROM usuarios WHERE id = :aid LIMIT 1", [':aid' => $alunoId]);
        $disciplinaNome = $this->fetchColumn("SELECT nome FROM disciplinas WHERE id = :did", [':did' => $disciplinaId]);

        $sqlHistorico = "SELECT pl.nome AS periodo, pl.ordem AS ordem_periodo, COALESCE(n.nota, 'Sem nota') AS nota
                         FROM periodos_letivos pl
                         INNER JOIN anos_letivos al ON al.id = pl.ano_letivo_id
                         LEFT JOIN notas n ON n.periodo_id = pl.id
                                           AND n.aluno_id = :aid
                                           AND n.disciplina_id = :did
                         WHERE al.escola_id = :eid
                           AND al.ativo = 1";

        $paramsHistorico = [
            ':aid' => $alunoId,
            ':did' => $disciplinaId,
            ':eid' => $escolaId
        ];

        if ($periodoId) {
            $sqlHistorico .= " AND pl.id = :pid";
            $paramsHistorico[':pid'] = $periodoId;
        }

        $sqlHistorico .= " ORDER BY pl.ordem ASC";
        $historico = $this->fetchAll($sqlHistorico, $paramsHistorico);

        $ptdId = $this->fetchColumn(
            "SELECT id FROM professor_turma_disciplina WHERE turma_id = :tid AND disciplina_id = :did AND ativo = 1 LIMIT 1",
            [':tid' => $turmaId, ':did' => $disciplinaId]
        );

        $sqlFaltas = "SELECT COUNT(p.id)
                      FROM presencas p
                      INNER JOIN aulas a ON a.id = p.aula_id
                      WHERE p.aluno_id = :aid
                        AND p.status = 'falta'
                        AND a.professor_turma_disciplina_id = :ptdid";

        $paramsFaltas = [
            ':aid' => $alunoId,
            ':ptdid' => $ptdId
        ];

        if ($periodoId) {
            $periodo = $this->fetch("SELECT data_inicio, data_fim FROM periodos_letivos WHERE id = :pid", [':pid' => $periodoId]);
            if ($periodo) {
                $sqlFaltas .= " AND a.data_aula BETWEEN :d1 AND :d2";
                $paramsFaltas[':d1'] = $periodo['data_inicio'];
                $paramsFaltas[':d2'] = $periodo['data_fim'];
            }
        }

        $totalFaltas = (int)$this->fetchColumn($sqlFaltas, $paramsFaltas);

        $sqlMedia = "SELECT AVG(nota) FROM notas WHERE aluno_id = :aid AND disciplina_id = :did";
        $paramsMedia = [':aid' => $alunoId, ':did' => $disciplinaId];
        if ($periodoId) {
            $sqlMedia .= " AND periodo_id = :pid";
            $paramsMedia[':pid'] = $periodoId;
        }

        $media = $this->fetchColumn($sqlMedia, $paramsMedia);

        return [
            'aluno_nome' => $alunoNome,
            'disciplina_analisada' => $disciplinaNome,
            'media_aluno' => $media !== null ? round((float)$media, 1) : 'Sem nota',
            'total_faltas' => $totalFaltas,
            'historico_notas' => $historico
        ];
    }

    public function coletarDadosProfessor(int $professorId, int $escolaId, ?int $turmaId = null, ?int $periodoId = null, ?int $disciplinaId = null): array
{
    $sql = "SELECT ptd.id, ptd.turma_id, ptd.disciplina_id, t.nome AS turma, d.nome AS disciplina
            FROM professor_turma_disciplina ptd
            INNER JOIN turmas t ON t.id = ptd.turma_id
            INNER JOIN disciplinas d ON d.id = ptd.disciplina_id
            WHERE ptd.professor_id = :prof_id
              AND ptd.escola_id = :escola_id
              AND ptd.ativo = 1
              AND ptd.turma_id = :turma_id
              AND ptd.disciplina_id = :did
            LIMIT 1";

    $t = $this->fetch($sql, [
        ':prof_id' => $professorId,
        ':escola_id' => $escolaId,
        ':turma_id' => $turmaId,
        ':did' => $disciplinaId
    ]);

    if (!$t) {
        return [];
    }

    $params = [
        ':tid' => $turmaId,
        ':did_geral' => $disciplinaId
    ];

    $sqlMediaPeriodo = 'NULL';
    if ($periodoId) {
        $sqlMediaPeriodo = "(SELECT n.nota
                             FROM notas n
                             WHERE n.aluno_id = u.id
                               AND n.disciplina_id = :did_periodo
                               AND n.periodo_id = :pid
                             LIMIT 1)";
        $params[':did_periodo'] = $disciplinaId;
        $params[':pid'] = $periodoId;
    }

    $sqlFaltasPeriodo = '';
    if ($periodoId) {
        $periodo = $this->fetch(
            "SELECT data_inicio, data_fim FROM periodos_letivos WHERE id = :pid",
            [':pid' => $periodoId]
        );

        if ($periodo && $periodo['data_inicio'] && $periodo['data_fim']) {
            $sqlFaltasPeriodo = " AND a.data_aula BETWEEN :d1 AND :d2";
            $params[':d1'] = $periodo['data_inicio'];
            $params[':d2'] = $periodo['data_fim'];
        }
    }

    $sqlAlunos = "SELECT u.nome_completo,
                    $sqlMediaPeriodo AS media,
                    (SELECT AVG(n2.nota)
                     FROM notas n2
                     WHERE n2.aluno_id = u.id
                       AND n2.disciplina_id = :did_geral) AS media_geral,
                    (SELECT COUNT(p.id)
                     FROM presencas p
                     INNER JOIN aulas a ON a.id = p.aula_id
                     WHERE p.aluno_id = u.id
                       AND a.professor_turma_disciplina_id = :ptd
                       AND p.status = 'falta'
                       $sqlFaltasPeriodo) AS faltas
                  FROM usuarios u
                  INNER JOIN matriculas m ON m.aluno_id = u.id
                  WHERE m.turma_id = :tid
                    AND u.ativo = 1";

    $params[':ptd'] = $t['id'];

    $alunos = $this->fetchAll($sqlAlunos, $params);

    $mediaTurma = 0;
    $cont = 0;
    $detalhes = [];

    foreach ($alunos as $a) {
        $valorMedia = $periodoId ? $a['media'] : $a['media_geral'];

        if ($valorMedia !== null && $valorMedia !== 'Sem nota') {
            if (is_numeric($valorMedia) && (float)$valorMedia > self::NOTA_MAXIMA) {
                $valorMedia = self::NOTA_MAXIMA;
            }
            $mediaTurma += (float)$valorMedia;
            $cont++;
        }

        $detalhes[] = [
            'nome' => $a['nome_completo'],
            'media' => $a['media'] ?? 'Sem nota',
            'media_geral' => $a['media_geral'] ?? 0,
            'faltas' => (int)$a['faltas']
        ];
    }

    $alunosCriticos = array_values(array_filter($detalhes, function ($a) {
        $media = $a['media'];
        $faltas = (int)$a['faltas'];
        $mediaGeral = $a['media_geral'];

        return (is_numeric($media) && (float)$media < self::MEDIA_APROVACAO)
            || (is_numeric($mediaGeral) && (float)$mediaGeral < self::MEDIA_APROVACAO)
            || $faltas >= 3
            || !is_numeric($media)
            || $media === 'Sem nota';
    }));

    return [[
        'turma' => $t['turma'],
        'disciplina' => $t['disciplina'],
        'media_geral' => $cont > 0 ? round($mediaTurma / $cont, 1) : 0,
        'alunos_criticos' => $alunosCriticos
    ]];
}
}