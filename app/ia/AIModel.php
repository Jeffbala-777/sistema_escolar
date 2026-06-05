<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/BaseModel.php';

class AIModel extends BaseModel
{
    private const GROQ_API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const GROQ_MODEL = 'llama-3.3-70b-versatile';
    
    public function analisarDesempenho(array $dados, string $perfil, string $tipoPeriodo = 'bimestral', ?array $periodoSelecionado = null): string
    {
        $prompt = $this->construirPrompt($dados, $perfil, $tipoPeriodo, $periodoSelecionado);

        try {
            $apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
            if (empty($apiKey)) {
                return '❌ GROQ_API_KEY não configurada. Obtenha uma chave gratuita em: https://console.groq.com/keys';
            }

            $payload = json_encode([
                'model' => self::GROQ_MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um especialista em gestão escolar. Siga rigorosamente a estrutura enviada pelo usuário. Não utilize Markdown. Não altere nomes de seções. Não omita alunos. Não invente dados. Utilize somente as informações recebidas.'
                                          ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 1500,
                'presence_penalty' => 0,
                'frequency_penalty' => 0
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
            if (curl_errno($ch)) {
                $erro = curl_error($ch);
                curl_close($ch);
                return '❌ Erro CURL: ' . $erro;
            }

            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseBody = (string)$response;
            curl_close($ch);

            if ($httpCode !== 200) {
                $errorData = json_decode($responseBody, true);
                $errorMessage = $errorData['error']['message'] ?? 'Erro desconhecido';
                
                if ($httpCode === 429) {
                    return '⚠️ Limite da Groq atingido. Tente novamente em alguns minutos.';
                }
                if ($httpCode === 401) {
                    return '❌ Chave da Groq inválida. Obtenha uma nova em: https://console.groq.com/keys';
                }
                return "❌ Erro Groq (HTTP $httpCode): $errorMessage";
            }

            $data = json_decode($responseBody, true);
            $texto = $data['choices'][0]['message']['content'] ?? '';
            
            return !empty($texto) ? trim($texto) : '❌ Groq retornou uma resposta vazia.';
    
        } catch (Throwable $e) {
            return '❌ Erro: ' . $e->getMessage();
        }
    }

    private function construirPrompt(array $dados, string $perfil, string $tipoPeriodo = 'bimestral', ?array $periodoSelecionado = null): string
    {
        $dadosCompactos = $this->compactarDados($dados);
        $periodoInfo = $periodoSelecionado ? "Período: {$periodoSelecionado['nome']}" : 'Todos os períodos';

        if ($perfil === 'professor') {
            $prompt = "Você é um especialista em gestão escolar, coordenação pedagógica, avaliação educacional e acompanhamento do desempenho estudantil.

Sua função é analisar exclusivamente os dados recebidos e gerar relatórios pedagógicos claros, objetivos, profissionais e fáceis de interpretar.

REGRAS OBRIGATÓRIAS

- Utilize apenas os dados fornecidos.
- Nunca invente informações.
- Nunca invente alunos.
- Nunca invente notas.
- Nunca invente faltas.
- Não utilize Markdown.
- Não utilize emojis.
- Não altere a ordem das seções.
- Utilize português do Brasil.
- Seja objetivo e profissional.
- Evite textos excessivamente longos.
- Priorize informações que exigem ação.
- Utilize os nomes exatamente como recebidos.
- Considere evolução entre períodos quando houver dados anteriores.
- Considere notas e frequência na análise.
- Não criar recomendações individuais para cada aluno.
- Não repetir informações já apresentadas em seções anteriores.

ESTRUTURA OBRIGATÓRIA

[TODAS AS DISCIPLINAS OU DISCIPLINA ESPECÍFICA] - [PERÍODO ANALISADO]

RESUMO RÁPIDO

Média da turma:
Quantidade de alunos:
Alunos abaixo da meta:
Alunos acima da meta:
Situação geral:

ALUNOS QUE NECESSITAM ATENÇÃO

Listar apenas alunos abaixo da meta.

Formato:

Nome
Nota: XX

Nome
Nota: XX

Nome
Nota: XX

Resumo do grupo:

Criar apenas um resumo geral explicando os principais problemas encontrados entre os alunos listados.

ALUNOS COM DESTAQUE POSITIVO

Listar apenas alunos com melhor desempenho.

Formato:

Nome
Nota: XX

Nome
Nota: XX

Nome
Nota: XX

Resumo do grupo:

Criar apenas um resumo geral destacando os pontos positivos encontrados.

FREQUÊNCIA DA TURMA

Total de faltas registradas:

Alunos com maior número de faltas:

Nome - XX faltas
Nome - XX faltas
Nome - XX faltas

Resumo da frequência:

Explicar de forma resumida como as faltas podem estar impactando o desempenho da turma.

EVOLUÇÃO DOS ALUNOS

Caso existam dados anteriores:

- Principais evoluções positivas.
- Principais quedas de desempenho.
- Tendência geral da turma.

Caso não existam dados anteriores:

Informar que não há histórico suficiente para comparação.

IMPACTOS IDENTIFICADOS

Apontar objetivamente:

- Principais dificuldades observadas.
- Possíveis impactos na aprendizagem.
- Riscos pedagógicos identificados.

ANÁLISE PEDAGÓGICA

Produzir uma análise profissional e objetiva contendo:

- Desempenho geral da turma.
- Relação entre notas e frequência.
- Evolução dos alunos.
- Principais pontos positivos.
- Principais pontos de atenção.
- Situação pedagógica geral.

A análise deve ser detalhada, porém sem textos excessivamente longos.

RECOMENDAÇÕES

Professor:

- No máximo 3 recomendações objetivas.

Coordenação Pedagógica:

- No máximo 3 recomendações objetivas.

Responsáveis:

- No máximo 3 recomendações objetivas.

DADOS PARA ANÁLISE:

{DADOS_JSON}";
            $prompt .= json_encode($dadosCompactos, JSON_UNESCAPED_UNICODE);

            return $prompt;
                    }

        return "Dados da escola ({$periodoInfo}): " . json_encode($dadosCompactos, JSON_UNESCAPED_UNICODE) . ". Resuma o panorama, críticos e um insight curto.";
    }

    private function compactarDados(array $dados): array
    {
        if (isset($dados[0]['disciplina'])) {
            return array_map(function($item) {
                return [
                    'turma' => $item['turma'],
                    'disciplina' => $item['disciplina'],
                    'media_turma' => $item['media_geral'],
                    'detalhes_alunos' => array_map(function($aluno) {
                        return [
                            'nome' => $aluno['nome'],
                            'media' => $aluno['media'],
                            'faltas' => $aluno['faltas'],
                            'evolucao' => $aluno['evolucao'] ?? 'N/A'
                        ];
                    }, $item['alunos_detalhes'] ?? [])
                ];
            }, $dados);
        }

        if (isset($dados['desempenho_disciplinas'])) {
            return [
                'disciplinas' => array_map(static function ($d) {
                    return [
                        'disc' => $d['disciplina'] ?? ($d['disc'] ?? ''),
                        'media' => round((float)($d['media'] ?? 0), 1)
                    ];
                }, $dados['desempenho_disciplinas']),
                'criticos_faltas' => $dados['alunos_criticos_faltas'] ?? [],
                'baixo_desempenho' => $dados['alunos_baixo_desempenho'] ?? []
            ];
        }

        return $dados;
    }

    public function coletarDadosProfessor(int $professorId, int $escolaId, ?int $turmaId = null, ?int $periodoId = null): array
    {
        $periodo = null;
        if ($periodoId) {
            $periodo = $this->fetch("SELECT id, data_inicio, data_fim, nome, ordem FROM periodos_letivos WHERE id = :pid", [':pid' => $periodoId]);
        }

        $sql = "SELECT ptd.id, ptd.turma_id, ptd.disciplina_id, t.nome AS turma, d.nome AS disciplina
                FROM professor_turma_disciplina ptd
                INNER JOIN turmas t ON t.id = ptd.turma_id
                INNER JOIN disciplinas d ON d.id = ptd.disciplina_id
                WHERE ptd.professor_id = :prof_id
                  AND ptd.escola_id = :escola_id
                  AND ptd.ativo = 1";

        $params = [':prof_id' => $professorId, ':escola_id' => $escolaId];
        if ($turmaId) {
            $sql .= " AND ptd.turma_id = :turma_id";
            $params[':turma_id'] = $turmaId;
        }

        $turmas = $this->fetchAll($sql, $params);
        
        $analise = [];

        foreach ($turmas as $t) {
            $sqlAlunos = "SELECT u.id, u.nome_completo,
                            (SELECT n.nota FROM notas n WHERE n.aluno_id = u.id AND n.disciplina_id = :disc_id AND n.periodo_id = :periodo_id LIMIT 1) AS media_atual,
                            (SELECT COUNT(p.id) FROM presencas p INNER JOIN aulas a ON a.id = p.aula_id WHERE p.aluno_id = u.id AND a.professor_turma_disciplina_id = :ptd_id AND p.status = 'falta' " . ($periodo ? " AND a.data_aula BETWEEN :d1 AND :d2" : "") . ") AS total_faltas
                          FROM usuarios u
                          INNER JOIN matriculas m ON m.aluno_id = u.id
                          WHERE m.turma_id = :turma_id
                            AND m.status = 'ativa'";

            $pAlunos = [
                ':disc_id' => $t['disciplina_id'],
                ':periodo_id' => $periodoId,
                ':ptd_id' => $t['id'],
                ':turma_id' => $t['turma_id']
            ];
            
            if ($periodo) {
                $pAlunos[':d1'] = $periodo['data_inicio'];
                $pAlunos[':d2'] = $periodo['data_fim'];
            }

            $alunos = $this->fetchAll($sqlAlunos, $pAlunos);
            $alunosDetalhes = [];
            $medias = [];

            foreach ($alunos as $a) {
                $evolucao = "Primeiro período registrado";
                if ($periodo && $periodo['ordem'] > 1) {
                    $sqlAnterior = "SELECT id FROM periodos_letivos WHERE escola_id = :eid AND ordem = :ordem AND ano_letivo_id = (SELECT ano_letivo_id FROM periodos_letivos WHERE id = :curr_pid) LIMIT 1";
                    $periodoAnteriorId = $this->fetchColumn($sqlAnterior, [':eid' => $escolaId, ':ordem' => $periodo['ordem'] - 1, ':curr_pid' => $periodoId]);
                    
                    if ($periodoAnteriorId) {
                        $mediaAnterior = $this->fetchColumn("SELECT nota FROM notas WHERE aluno_id = :aid AND disciplina_id = :did AND periodo_id = :pid", [':aid' => $a['id'], ':did' => $t['disciplina_id'], ':pid' => $periodoAnteriorId]);
                        if ($mediaAnterior !== null) {
                            $diff = (float)$a['media_atual'] - (float)$mediaAnterior;
                            $evolucao = ($diff >= 0 ? "Aumentou " : "Caiu ") . abs(round($diff, 1)) . " pontos em relação ao período anterior (Média anterior: ".round((float)$mediaAnterior, 1).")";
                        }
                    }
                }
                    
                $m = $a['media_atual'] !== null ? round((float)$a['media_atual'], 1) : 0;
                if ($a['media_atual'] !== null) $medias[] = $m;

                $alunosDetalhes[] = [
                    'nome' => $a['nome_completo'],
                    'media' => $a['media_atual'] !== null ? $m : 'Sem nota',
                    'faltas' => (int)$a['total_faltas'],
                    'evolucao' => $evolucao
                ];
            }
            
            usort($alunosDetalhes, function ($a, $b) {
            $mediaA = is_numeric($a['media']) ? (float)$a['media'] : 999;
            $mediaB = is_numeric($b['media']) ? (float)$b['media'] : 999;

            return $mediaA <=> $mediaB;
        });
    
            $analise[] = [
                'ptd_id' => $t['id'],
                'disciplina_id' => $t['disciplina_id'],
                'turma' => $t['turma'],
                'disciplina' => $t['disciplina'],
                'alunos_detalhes' => $alunosDetalhes,
                'media_geral' => count($medias) ? round(array_sum($medias) / count($medias), 1) : 0
            ];
        }

        return $analise;
    }

    public function coletarDadosAdmin(int $escolaId, ?int $turmaId = null, ?int $periodoId = null): array
    {
        $periodo = null;
        if ($periodoId) {
            $periodo = $this->fetch("SELECT id, data_inicio, data_fim, nome, ordem FROM periodos_letivos WHERE id = :pid", [':pid' => $periodoId]);
        }

        $sqlDisciplinas = "
            SELECT d.id, d.nome AS disciplina,
                   COALESCE(AVG(n.nota), 0) AS media
            FROM disciplinas d
            INNER JOIN professor_turma_disciplina ptd ON ptd.disciplina_id = d.id
            LEFT JOIN notas n ON n.disciplina_id = d.id AND n.escola_id = :eid1 " . ($periodoId ? " AND n.periodo_id = :pid1 " : "") . "
            WHERE ptd.turma_id = :tid AND ptd.escola_id = :eid2 AND ptd.ativo = 1
            GROUP BY d.id, d.nome ORDER BY d.nome ASC
        ";
        
        $paramsDisc = [':eid1' => $escolaId, ':tid' => $turmaId, ':eid2' => $escolaId];
        if ($periodoId) $paramsDisc[':pid1'] = $periodoId;
        $disciplinas = $this->fetchAll($sqlDisciplinas, $paramsDisc);

        $sqlAlunos = "
            SELECT u.id, u.nome_completo
            FROM usuarios u
            INNER JOIN matriculas m ON m.aluno_id = u.id
            WHERE u.escola_id = :eid AND m.status = 'ativa' AND m.turma_id = :tid
        ";
        $alunos = $this->fetchAll($sqlAlunos, [':eid' => $escolaId, ':tid' => $turmaId]);

        $baixoDesempenho = [];
        $criticosFaltas = [];

        foreach ($alunos as $aluno) {
            $sqlMedia = "SELECT AVG(nota) FROM notas WHERE aluno_id = :aid " . ($periodoId ? " AND periodo_id = :pid" : "");
            $paramsMedia = [':aid' => $aluno['id']];
            if ($periodoId) $paramsMedia[':pid'] = $periodoId;
            $mediaAluno = $this->fetchColumn($sqlMedia, $paramsMedia);

            $sqlFaltas = "SELECT COUNT(p.id) FROM presencas p INNER JOIN aulas a ON a.id = p.aula_id WHERE p.aluno_id = :aid AND p.status = 'falta' " . ($periodo ? " AND a.data_aula BETWEEN :d1 AND :d2" : "");
            $paramsFaltas = [':aid' => $aluno['id']];
            if ($periodo) {
                $paramsFaltas[':d1'] = $periodo['data_inicio'];
                $paramsFaltas[':d2'] = $periodo['data_fim'];
            }
            
            $stmtF = $this->pdo->prepare($sqlFaltas);
            $stmtF->execute($paramsFaltas);
            $totalFaltas = (int)$stmtF->fetchColumn();

            if ($mediaAluno !== null && (float)$mediaAluno < 60) {
                $baixoDesempenho[] = ['nome' => $aluno['nome_completo'], 'media' => round((float)$mediaAluno, 1)];
            }
            if ($totalFaltas > 5) {
                $criticosFaltas[] = ['nome' => $aluno['nome_completo'], 'faltas' => $totalFaltas];
            }
        }

        return [
            'desempenho_disciplinas' => $disciplinas,
            'alunos_baixo_desempenho' => $baixoDesempenho,
            'alunos_criticos_faltas' => $criticosFaltas,
            'total_alunos_analisados' => count($alunos)
        ];
    }
}