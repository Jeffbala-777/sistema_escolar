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
            return "Analise a turma utilizando EXCLUSIVAMENTE os dados do JSON.

Estrutura obrigatória:

📊 PERÍODO: {$periodoTxt}

🏫 TURMA: [Nome] | 📚 DISCIPLINA: [Disciplina]

📈 SITUAÇÃO GERAL: Classifique conforme a média geral (máximo 2 linhas).
- Abaixo de 15 = Crítica
- 15 a 19,9 = Atenção
- 20 a 24,9 = Regular
- 25 a 29,9 = Boa
- 30+ = Muito Boa

📋 INDICADORES:
Média Geral: X | Total de Faltas: X

CRITÉRIOS PARA CLASSIFICAÇÃO DE ALUNOS:

Se for 1º Trimestre:
- CRISE: Nota < 18
- REVISÃO: Nota entre 18 e 19,9
- BOM: Nota >= 20

Se for 2º Trimestre ou posterior:
- CRISE: Soma de notas < 36 (muito abaixo da média)
- REVISÃO: Soma de notas entre 36 e 39,9
- BOM: Soma de notas >= 40

🚨 ALUNOS EM CRISE:
Apenas os nomes separados por vírgula. Destaque se estão MUITO ABAIXO da média da turma. Se nenhum, escreva: Nenhum.

💳 ALUNOS PARA REVISÃO:
Apenas os nomes separados por vírgula. Se nenhum, escreva: Nenhum.

💵 ALUNOS COM BOM DESEMPENHO:
Apenas os nomes separados por vírgula. Se nenhum, escreva: Nenhum.

⚠️ ALUNOS COM FALTAS ALTAS (3 ou mais):
Apenas os nomes separados por vírgula. Se nenhum, escreva: Nenhum.

💡 PONTOS POSITIVOS:
Máximo 2 itens com 1-2 linhas cada.

⚠️ PONTOS DE ATENÇÃO:
Máximo 2 itens com 1-2 linhas cada.

🎯 SUGESTÕES PEDAGÓGICAS:
Máximo 3 ações práticas com 1-2 linhas cada (ex: reforço, atividades, acompanhamento).

📝 RESUMO:
Máximo 2 frases explicando a situação geral da turma.

REGRAS:
- Não inventar dados.
- Máximo 20 linhas.
- Linguagem clara, profissional e acessível.
- NOTA MÁXIMA: {$notaMaxima} pontos.
- Dar corpo ao texto: evitar linhas muito curtas, usar descrições breves mas completas.
- Manter foco: alunos em crise (nomes) rápidos, sugestões práticas e detalhadas.
- IMPORTANTE: Destacar alunos MUITO ABAIXO da média da turma com aviso claro.
- IMPORTANTE: No 2º trimestre e posteriores, usar SOMA de notas para classificação (36 pontos = média).

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

DISCIPLINA:
Exiba apenas o valor do campo disciplina no início da análise.

PERÍODO:
Utilize o período selecionado pelo sistema: {$periodoTxt}

ALUNO:
Exiba o valor do campo aluno.

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
                WHERE ptd.escola_id = :escola_id
                  AND ptd.ativo = 1
                  AND ptd.turma_id = :turma_id
                  AND ptd.disciplina_id = :did
                LIMIT 1";

        $t = $this->fetch($sql, [
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
