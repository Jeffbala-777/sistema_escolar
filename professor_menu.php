<?php
// Ativa tipagem estrita para seguranca
declare(strict_types=1);

// Inclui a base do modelo para conexao com o banco
require_once __DIR__ . '/BaseModel.php';

class NotaModel extends BaseModel
{
    // Lista as notas de um aluno em uma disciplina especifica
    public function listarNotasAluno(int $alunoId, int $disciplinaId, int $anoLetivoId, int $escolaId): array {
        // SQL para buscar notas filtradas por aluno, disciplina, ano e escola
        $sql = "SELECT * FROM notas WHERE aluno_id = :aluno_id AND disciplina_id = :disciplina_id 
                AND ano_letivo_id = :ano_letivo_id AND escola_id = :escola_id ORDER BY periodo_id ASC";
        // Retorna todos os registros encontrados
        return $this->fetchAll($sql, [
            ':aluno_id' => $alunoId, ':disciplina_id' => $disciplinaId,
            ':ano_letivo_id' => $anoLetivoId, ':escola_id' => $escolaId
        ]);
    }

    // Salva ou atualiza uma nota no banco de dados
    public function salvar(array $dados): bool {
        // Verifica se ja existe uma nota para este aluno no mesmo periodo e disciplina
        $sqlCheck = "SELECT id FROM notas WHERE aluno_id = :aluno_id AND disciplina_id = :disciplina_id 
                    AND periodo_id = :periodo_id AND ano_letivo_id = :ano_letivo_id AND escola_id = :escola_id LIMIT 1";
        $existente = $this->fetch($sqlCheck, [
            ':aluno_id' => $dados['aluno_id'], ':disciplina_id' => $dados['disciplina_id'],
            ':periodo_id' => $dados['periodo_id'], ':ano_letivo_id' => $dados['ano_letivo_id'],
            ':escola_id' => $dados['escola_id']
        ]);

        // Se a nota ja existir, faz o update do valor
        if ($existente) {
            $sql = "UPDATE notas SET nota = :nota, professor_id = :professor_id, data_ultima_edicao = CURRENT_TIMESTAMP WHERE id = :id";
            return $this->execute($sql, [':nota' => $dados['nota'], ':professor_id' => $dados['professor_id'], ':id' => $existente['id']]);
        }

        // Se nao existir, insere um novo registro de nota
        $sql = "INSERT INTO notas (escola_id, aluno_id, disciplina_id, professor_id, ano_letivo_id, periodo_id, tipo, nota, observacao) 
                VALUES (:escola_id, :aluno_id, :disciplina_id, :professor_id, :ano_letivo_id, :periodo_id, :tipo, :nota, :observacao)";
        return $this->execute($sql, [
            ':escola_id' => $dados['escola_id'], ':aluno_id' => $dados['aluno_id'], ':disciplina_id' => $dados['disciplina_id'],
            ':professor_id' => $dados['professor_id'], ':ano_letivo_id' => $dados['ano_letivo_id'], ':periodo_id' => $dados['periodo_id'],
            ':tipo' => $dados['tipo'] ?? 'prova', ':nota' => $dados['nota'], ':observacao' => $dados['observacao'] ?? null
        ]);
    }

    // Busca todas as notas de uma turma para uma disciplina especifica
    public function buscarNotasTurma(int $turmaId, int $disciplinaId, int $anoLetivoId) {
        // SQL com JOIN para garantir que pegamos apenas alunos matriculados ativos
        $sql = "SELECT n.aluno_id, n.periodo_id, n.nota FROM notas n INNER JOIN matriculas m ON m.aluno_id = n.aluno_id 
                WHERE m.turma_id = :turma_id AND n.disciplina_id = :disciplina_id AND n.ano_letivo_id = :ano_letivo_id AND m.status = 'ativa'";
        // Executa a busca
        $resultados = $this->fetchAll($sql, [':turma_id' => $turmaId, ':disciplina_id' => $disciplinaId, ':ano_letivo_id' => $anoLetivoId]);
        $notasFormatadas = [];
        // Organiza o array por aluno e periodo para facilitar o uso na view
        foreach ($resultados as $row) { $notasFormatadas[$row['aluno_id']][$row['periodo_id']] = $row['nota']; }
        return $notasFormatadas;
    }

    // Calcula a media das notas de um aluno em uma disciplina
    public function calcularMediaAluno(int $alunoId, int $disciplinaId, int $anoLetivoId, int $escolaId): float {
        // SQL para tirar a media aritmetica das notas
        $sql = "SELECT AVG(nota) AS media FROM notas WHERE aluno_id = :aluno_id AND disciplina_id = :disciplina_id 
                AND ano_letivo_id = :ano_letivo_id AND escola_id = :escola_id";
        $resultado = $this->fetch($sql, [':aluno_id' => $alunoId, ':disciplina_id' => $disciplinaId, ':ano_letivo_id' => $anoLetivoId, ':escola_id' => $escolaId]);
        // Retorna a media arredondada com 2 casas decimais
        return round((float) ($resultado['media'] ?? 0), 2);
    }

    // FUNCAO PRINCIPAL: Busca o boletim completo com notas e faltas vinculadas por data automaticamente
    public function buscarNotasCompletasAluno(int $alunoId, int $anoLetivoId) {
        // 1. Busca todas as notas lancadas para o aluno
        $sql = "SELECT d.nome as disciplina_nome, n.periodo_id, n.nota, n.disciplina_id FROM notas n 
                INNER JOIN disciplinas d ON d.id = n.disciplina_id WHERE n.aluno_id = :aluno_id 
                AND n.ano_letivo_id = :ano_letivo_id ORDER BY d.nome ASC, n.periodo_id ASC";
        $resultados = $this->fetchAll($sql, [':aluno_id' => $alunoId, ':ano_letivo_id' => $anoLetivoId]);

        // 2. Busca faltas vinculadas automaticamente aos periodos pela data da aula
        $faltasMap = [];
        // Verifica se as colunas de data existem na tabela de periodos letivos
        $checkCol = $this->fetch("SHOW COLUMNS FROM periodos_letivos LIKE 'data_inicio'");
        
        if ($checkCol) { // Se as colunas de data existirem, executa a vinculacao automatica
            try {
                // SQL que une presencas com aulas e periodos letivos baseando-se no intervalo de datas
                $sqlFaltas = "SELECT a.disciplina_id, pl.id as periodo_id, 
                              SUM(CASE WHEN p.status = 'falta' THEN 1 ELSE 0 END) as total_faltas,
                              SUM(CASE WHEN p.status = 'falta' AND p.justificada = 1 THEN 1 ELSE 0 END) as total_justificadas
                              FROM presencas p 
                              INNER JOIN aulas a ON a.id = p.aula_id 
                              INNER JOIN periodos_letivos pl ON a.data_aula BETWEEN pl.data_inicio AND pl.data_fim 
                              WHERE p.aluno_id = :aluno_id AND a.ano_letivo_id = :ano_letivo_id 
                              GROUP BY a.disciplina_id, pl.id";
                $faltasRes = $this->fetchAll($sqlFaltas, [':aluno_id' => $alunoId, ':ano_letivo_id' => $anoLetivoId]);
                // Organiza as faltas encontradas por disciplina e periodo
                foreach ($faltasRes as $f) { 
                    $faltasMap[$f['disciplina_id']][$f['periodo_id']] = [
                        'faltas' => $f['total_faltas'],
                        'justificadas' => $f['total_justificadas']
                    ]; 
                }
            } catch (Exception $e) { /* Protecao contra erros de SQL */ }
        }

        // 3. Busca todas as disciplinas que o aluno deve cursar (mesmo as sem nota)
        $sqlDisc = "SELECT DISTINCT d.id, d.nome FROM disciplinas d 
                    INNER JOIN professor_turma_disciplina ptd ON ptd.disciplina_id = d.id 
                    INNER JOIN matriculas m ON m.turma_id = ptd.turma_id 
                    WHERE m.aluno_id = :aluno_id AND m.status = 'ativa' AND ptd.ano_letivo_id = :ano_letivo_id";
        $todasDisciplinas = $this->fetchAll($sqlDisc, [':aluno_id' => $alunoId, ':ano_letivo_id' => $anoLetivoId]);

        // Inicializa o boletim com as disciplinas fixas
        $boletim = [];
        foreach ($todasDisciplinas as $d) { 
            $boletim[$d['nome']] = ['id' => $d['id']]; 
        }

        // Preenche o boletim com as notas encontradas
        foreach ($resultados as $row) {
            if (!isset($boletim[$row['disciplina_nome']])) {
                $boletim[$row['disciplina_nome']] = ['id' => $row['disciplina_id']];
            }
            $boletim[$row['disciplina_nome']][$row['periodo_id']]['nota'] = $row['nota'];
        }
    
        // Mescla as faltas automáticas no boletim final
        foreach ($boletim as $nome => &$dados) {
            if (!isset($dados['id'])) continue;
            $did = $dados['id'];
            foreach ($faltasMap[$did] ?? [] as $pid => $info) {
                if (!isset($dados[$pid])) $dados[$pid] = ['nota' => '-'];
                $dados[$pid]['faltas'] = $info['faltas'];
                $dados[$pid]['faltas_justificadas'] = $info['justificadas'];
            }
        }
        // Retorna o boletim completo e organizado
        return $boletim;
    }
}
?>
