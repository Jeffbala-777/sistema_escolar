<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/BaseModel.php';

class AIModel extends BaseModel
{
    public function analisarDesempenho(array $dados, string $perfil, string $tipoPeriodo = 'bimestral', ?array $periodoSelecionado = null): string
    {
        $prompt = $this->construirPrompt($dados, $perfil, $tipoPeriodo, $periodoSelecionado);

        try {
            $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
            if (empty($apiKey)) {
                return '❌ GEMINI_API_KEY não configurada.';
            }

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=' . $apiKey;

            $payload = json_encode([
                'contents' => [[
                    'parts' => [[
                        'text' => "Você é um assistente pedagógico. Responda em português, curto e objetivo.

" . $prompt
                    ]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 220
                ]
            ], JSON_UNESCAPED_UNICODE);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT => 20
            ]);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $erro = curl_error($ch);
                curl_close($ch);
                return '❌ Erro CURL: ' . $erro;
            }

            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 429) {
                return '⚠️ Limite da API atingido. Aguarde alguns minutos e tente novamente.';
            }

            if ($httpCode === 401 || $httpCode === 403) {
                return '❌ Chave da API inválida ou sem permissão.';
            }

            if ($httpCode !== 200) {
                return '❌ Erro na API Gemini (HTTP ' . $httpCode . ').';
            }

            $data = json_decode((string)$response, true);
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? '❌ Gemini não retornou conteúdo.';

        } catch (Throwable $e) {
            return '❌ Erro: ' . $e->getMessage();
        }
    }

    private function construirPrompt(array $dados, string $perfil, string $tipoPeriodo = 'bimestral', ?array $periodoSelecionado = null): string
    {
        $dadosCompactos = $this->compactarDados($dados);
        $periodoInfo = $periodoSelecionado ? "Período: {$periodoSelecionado['nome']}" : 'Todos os períodos';

        if ($perfil === 'professor') {
            return "Dados do professor ({$periodoInfo}): " . json_encode($dadosCompactos, JSON_UNESCAPED_UNICODE) . ". Analise notas, faltas, alunos em risco e dê 3 sugestões curtas.";
        }

        return "Dados da escola ({$periodoInfo}): " . json_encode($dadosCompactos, JSON_UNESCAPED_UNICODE) . ". Resuma o panorama, críticos e um insight curto.";
    }

    private function compactarDados(array $dados): array
    {
        if (isset($dados['desempenho_disciplinas'])) {
            return [
                'disciplinas' => array_map(static function ($d) {
                    return [
                        'disc' => $d['disciplina'] ?? ($d['disc'] ?? ''),
                        'media' => round((float)($d['media'] ?? 0), 1)
                    ];
                }, $dados['desempenho_disciplinas']),
                'sem_notas' => array_slice($dados['alunos_sem_notas'] ?? [], 0, 5),
                'sem_presenca' => array_slice($dados['alunos_sem_presenca'] ?? [], 0, 5),
                'criticos_faltas' => array_slice($dados['alunos_criticos_faltas'] ?? [], 0, 5),
                'total' => (int)($dados['total_alunos_analisados'] ?? ($dados['total'] ?? 0))
            ];
        }

        $resumo = [];
        if (isset($dados[0]['turma'])) {
            foreach ($dados as $t) {
                $resumo[] = [
                    't' => $t['turma'] ?? '',
                    'd' => $t['disciplina'] ?? '',
                    'm' => round((float)($t['media_geral'] ?? 0), 1),
                    'r' => count($t['alunos_em_risco'] ?? [])
                ];
            }
            return $resumo;
        }

        return [
            'total' => (int)($dados['total'] ?? 0),
            'criticos' => array_slice($dados['criticos'] ?? [], 0, 5)
        ];
    }

    public function coletarDadosProfessor(int $professorId, int $escolaId, ?int $turmaId = null, ?int $periodoId = null): array
    {
        $periodo = null;
        if ($periodoId) {
            $periodo = $this->fetch("SELECT data_inicio, data_fim, nome FROM periodos_letivos WHERE id = ?", [$periodoId]);
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
            $sqlAlunos = "SELECT u.nome_completo,
                            (SELECT COUNT(*) FROM notas WHERE aluno_id = u.id AND disciplina_id = :disc_id1" . ($periodoId ? " AND periodo_id = :p1" : "") . ") AS qtd_notas,
                            (SELECT AVG(nota) FROM notas WHERE aluno_id = u.id AND disciplina_id = :disc_id2" . ($periodoId ? " AND periodo_id = :p2" : "") . ") AS media,
                            (SELECT COUNT(*) FROM presencas p INNER JOIN aulas a ON a.id = p.aula_id WHERE p.aluno_id = u.id AND a.professor_turma_disciplina_id = :ptd_id1" . ($periodo && !empty($periodo['data_inicio']) && !empty($periodo['data_fim']) ? " AND a.data_aula BETWEEN :d1 AND :d2" : "") . ") AS qtd_presencas,
                            (SELECT COUNT(*) FROM presencas p INNER JOIN aulas a ON a.id = p.aula_id WHERE p.aluno_id = u.id AND a.professor_turma_disciplina_id = :ptd_id2 AND p.status = 'falta'" . ($periodo && !empty($periodo['data_inicio']) && !empty($periodo['data_fim']) ? " AND a.data_aula BETWEEN :d3 AND :d4" : "") . ") AS total_faltas
                          FROM usuarios u
                          INNER JOIN matriculas m ON m.aluno_id = u.id
                          WHERE m.turma_id = :turma_id
                            AND m.status = 'ativa'";

            $pAlunos = [
                ':disc_id1' => $t['disciplina_id'],
                ':disc_id2' => $t['disciplina_id'],
                ':ptd_id1' => $t['id'],
                ':ptd_id2' => $t['id'],
                ':turma_id' => $t['turma_id']
            ];

            if ($periodoId) {
                $pAlunos[':p1'] = $periodoId;
                $pAlunos[':p2'] = $periodoId;
                if ($periodo && !empty($periodo['data_inicio']) && !empty($periodo['data_fim'])) {
                    $pAlunos[':d1'] = $periodo['data_inicio'];
                    $pAlunos[':d2'] = $periodo['data_fim'];
                    $pAlunos[':d3'] = $periodo['data_inicio'];
                    $pAlunos[':d4'] = $periodo['data_fim'];
                }
            }

            $alunos = $this->fetchAll($sqlAlunos, $pAlunos);
            $semNotas = [];
            $semPresenca = [];
            $emRisco = [];
            $alunosAtencao = [];
            $medias = [];

            foreach ($alunos as $a) {
                if ((int)$a['qtd_notas'] === 0) $semNotas[] = $a['nome_completo'];
                if ((int)$a['qtd_presencas'] === 0) $semPresenca[] = $a['nome_completo'];

                if ($a['media'] !== null) {
                    $m = (float)$a['media'];
                    $medias[] = $m;
                    if ($m < 6.0) {
                        $emRisco[] = $a['nome_completo'] . ' (Média: ' . round($m, 1) . ')';
                        $alunosAtencao[] = ['nome_completo' => $a['nome_completo'], 'media' => round($m, 1)];
                    }
                }

                if ((int)$a['total_faltas'] > 10) {
                    $emRisco[] = $a['nome_completo'] . ' (' . $a['total_faltas'] . ' faltas)';
                }
            }

            $analise[] = [
                'ptd_id' => $t['id'],
                'disciplina_id' => $t['disciplina_id'],
                'turma' => $t['turma'],
                'disciplina' => $t['disciplina'],
                'alunos_sem_notas' => $semNotas,
                'alunos_sem_presenca' => $semPresenca,
                'alunos_em_risco' => array_values(array_unique($emRisco)),
                'alunos_atencao' => $alunosAtencao,
                'media_geral' => count($medias) ? round(array_sum($medias) / count($medias), 1) : 0
            ];
        }

        return $analise;
    }

    public function coletarDadosAdmin(int $escolaId, ?int $turmaId = null, ?int $periodoId = null): array
{
    $periodo = null;
    if ($periodoId) {
        $periodo = $this->fetch("SELECT data_inicio, data_fim, nome FROM periodos_letivos WHERE id = ?", [$periodoId]);
    }

    $sqlDisciplinas = "
        SELECT d.id, d.nome AS disciplina,
               COALESCE(AVG(n.nota), 0) AS media
        FROM disciplinas d
        INNER JOIN professor_turma_disciplina ptd ON ptd.disciplina_id = d.id
        LEFT JOIN notas n
               ON n.disciplina_id = d.id
              AND n.escola_id = ?
              " . ($periodoId ? " AND n.periodo_id = ? " : "") . "
        WHERE ptd.turma_id = ?
          AND ptd.escola_id = ?
          AND ptd.ativo = 1
        GROUP BY d.id, d.nome
        ORDER BY d.nome ASC
    ";

    $paramsDisc = [$escolaId];
    if ($periodoId) {
        $paramsDisc[] = $periodoId;
    }
    $paramsDisc[] = $turmaId;
    $paramsDisc[] = $escolaId;

    $disciplinas = $this->fetchAll($sqlDisciplinas, $paramsDisc);

    $sqlAlunos = "
        SELECT u.id, u.nome_completo
        FROM usuarios u
        INNER JOIN matriculas m ON m.aluno_id = u.id
        WHERE u.escola_id = ?
          AND m.status = 'ativa'
          AND m.turma_id = ?
    ";
    $alunos = $this->fetchAll($sqlAlunos, [$escolaId, $turmaId]);

    $semNotas = [];
    $semPresenca = [];
    $criticos = [];

    foreach ($alunos as $aluno) {
        $paramsNotas = [$aluno['id']];
        $sqlNotas = "SELECT COUNT(*) FROM notas WHERE aluno_id = ?";

        if ($periodoId) {
            $sqlNotas .= " AND periodo_id = ?";
            $paramsNotas[] = $periodoId;
        }

        $sqlNotas .= " AND disciplina_id IN (
            SELECT disc_id FROM (
                SELECT ptd.disciplina_id AS disc_id
                FROM professor_turma_disciplina ptd
                WHERE ptd.turma_id = ? AND ptd.escola_id = ? AND ptd.ativo = 1
            ) x
        )";

        $paramsNotas[] = $turmaId;
        $paramsNotas[] = $escolaId;

        $totalNotas = (int)$this->fetchColumn($sqlNotas, $paramsNotas);

        $sqlPres = "
            SELECT COUNT(p.id)
            FROM presencas p
            INNER JOIN aulas a ON a.id = p.aula_id
            INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
            WHERE p.aluno_id = ?
              AND ptd.turma_id = ?
              AND ptd.escola_id = ?
              AND ptd.ativo = 1
        ";
        $paramsPres = [$aluno['id'], $turmaId, $escolaId];

        if ($periodo && !empty($periodo['data_inicio']) && !empty($periodo['data_fim'])) {
            $sqlPres .= " AND a.data_aula BETWEEN ? AND ?";
            $paramsPres[] = $periodo['data_inicio'];
            $paramsPres[] = $periodo['data_fim'];
        }

        $totalPresencas = (int)$this->fetchColumn($sqlPres, $paramsPres);

        $sqlFaltas = "
            SELECT COUNT(p.id)
            FROM presencas p
            INNER JOIN aulas a ON a.id = p.aula_id
            INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
            WHERE p.aluno_id = ?
              AND p.status = 'falta'
              AND ptd.turma_id = ?
              AND ptd.escola_id = ?
              AND ptd.ativo = 1
        ";
        $paramsFaltas = [$aluno['id'], $turmaId, $escolaId];

        if ($periodo && !empty($periodo['data_inicio']) && !empty($periodo['data_fim'])) {
            $sqlFaltas .= " AND a.data_aula BETWEEN ? AND ?";
            $paramsFaltas[] = $periodo['data_inicio'];
            $paramsFaltas[] = $periodo['data_fim'];
        }

        $totalFaltas = (int)$this->fetchColumn($sqlFaltas, $paramsFaltas);

        if ($totalNotas === 0) {
            $semNotas[] = $aluno['nome_completo'];
        }

        if ($totalPresencas === 0) {
            $semPresenca[] = $aluno['nome_completo'];
        }

        if ($totalFaltas > 10) {
            $criticos[] = [
                'nome_completo' => $aluno['nome_completo'],
                'total_faltas' => $totalFaltas
            ];
        }
    }

    return [
        'desempenho_disciplinas' => $disciplinas,
        'alunos_sem_notas' => $semNotas,
        'alunos_sem_presenca' => $semPresenca,
        'alunos_criticos_faltas' => $criticos,
        'total_alunos_analisados' => count($alunos)
    ];
    }
}