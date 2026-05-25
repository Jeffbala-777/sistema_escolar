<?php
declare(strict_types=1); // Forca tipagem estrita

require_once __DIR__ . '/BaseModel.php'; // Base para conexao PDO

class NotaModel extends BaseModel
{
    // Lista notas de um aluno em uma disciplina especifica
    public function listarNotasAluno(int $alunoId, int $disciplinaId, int $anoLetivoId, int $escolaId): array {
        $sql = "SELECT * FROM notas WHERE aluno_id = :aluno_id AND disciplina_id = :disciplina_id 
                AND ano_letivo_id = :ano_letivo_id AND escola_id = :escola_id ORDER BY periodo_id ASC";
        return $this->fetchAll($sql, [
            ':aluno_id' => $alunoId, ':disciplina_id' => $disciplinaId,
            ':ano_letivo_id' => $anoLetivoId, ':escola_id' => $escolaId
        ]);
    }

    // Salva ou atualiza uma nota no banco
    public function salvar(array $dados): bool {
        $sqlCheck = "SELECT id FROM notas WHERE aluno_id = :aluno_id AND disciplina_id = :disciplina_id 
                    AND periodo_id = :periodo_id AND ano_letivo_id = :ano_letivo_id AND escola_id = :escola_id LIMIT 1";
        $existente = $this->fetch($sqlCheck, [
            ':aluno_id' => $dados['aluno_id'], ':disciplina_id' => $dados['disciplina_id'],
            ':periodo_id' => $dados['periodo_id'], ':ano_letivo_id' => $dados['ano_letivo_id'],
            ':escola_id' => $dados['escola_id']
        ]);

        if ($existente) { // Se ja existe, atualiza
            $sql = "UPDATE notas SET nota = :nota, professor_id = :professor_id, data_ultima_edicao = CURRENT_TIMESTAMP WHERE id = :id";
            return $this->execute($sql, [':nota' => $dados['nota'], ':professor_id' => $dados['professor_id'], ':id' => $existente['id']]);
        }

        // Se nao existe, insere novo registro
        $sql = "INSERT INTO notas (escola_id, aluno_id, disciplina_id, professor_id, ano_letivo_id, periodo_id, tipo, nota, observacao) 
                VALUES (:escola_id, :aluno_id, :disciplina_id, :professor_id, :ano_letivo_id, :periodo_id, :tipo, :nota, :observacao)";
        return $this->execute($sql, [
            ':escola_id' => $dados['escola_id'], ':aluno_id' => $dados['aluno_id'], ':disciplina_id' => $dados['disciplina_id'],
            ':professor_id' => $dados['professor_id'], ':ano_letivo_id' => $dados['ano_letivo_id'], ':periodo_id' => $dados['periodo_id'],
            ':tipo' => $dados['tipo'] ?? 'prova', ':nota' => $dados['nota'], ':observacao' => $dados['observacao'] ?? null
        ]);
    }

    // Busca notas de toda uma turma para uma disciplina
    public function buscarNotasTurma(int $turmaId, int $disciplinaId, int $anoLetivoId) {
        $sql = "SELECT n.aluno_id, n.periodo_id, n.nota FROM notas n INNER JOIN matriculas m ON m.aluno_id = n.aluno_id 
                WHERE m.turma_id = :turma_id AND n.disciplina_id = :disciplina_id AND n.ano_letivo_id = :ano_letivo_id AND m.status = 'ativa'";
        $resultados = $this->fetchAll($sql, [':turma_id' => $turmaId, ':disciplina_id' => $disciplinaId, ':ano_letivo_id' => $anoLetivoId]);
        $notasFormatadas = [];
        foreach ($resultados as $row) { $notasFormatadas[$row['aluno_id']][$row['periodo_id']] = $row['nota']; }
        return $notasFormatadas;
    }

    // Calcula a media aritmetica das notas do aluno
    public function calcularMediaAluno(int $alunoId, int $disciplinaId, int $anoLetivoId, int $escolaId): float {
        $sql = "SELECT AVG(nota) AS media FROM notas WHERE aluno_id = :aluno_id AND disciplina_id = :disciplina_id 
                AND ano_letivo_id = :ano_letivo_id AND escola_id = :escola_id";
        $resultado = $this->fetch($sql, [':aluno_id' => $alunoId, ':disciplina_id' => $disciplinaId, ':ano_letivo_id' => $anoLetivoId, ':escola_id' => $escolaId]);
        return round((float) ($resultado['media'] ?? 0), 2);
    }

    // Busca boletim completo (Notas e Faltas por periodo)
    public function buscarNotasCompletasAluno(int $alunoId, int $anoLetivoId) {
        // Busca notas por disciplina e periodo
        $sql = "SELECT d.nome as disciplina_nome, n.periodo_id, n.nota, n.disciplina_id FROM notas n 
                INNER JOIN disciplinas d ON d.id = n.disciplina_id WHERE n.aluno_id = :aluno_id 
                AND n.ano_letivo_id = :ano_letivo_id ORDER BY d.nome ASC, n.periodo_id ASC";
        $resultados = $this->fetchAll($sql, [':aluno_id' => $alunoId, ':ano_letivo_id' => $anoLetivoId]);

        // Faltas por periodo (Inicia vazio)
        $faltasMap = [];

        // Verifica se as colunas de data existem antes de rodar a query complexa
        $checkCol = $this->fetch("SHOW COLUMNS FROM periodos_letivos LIKE 'data_inicio'");
        
        if ($checkCol) { // Se as colunas existem, roda a query de faltas por periodo
            try {
                $sqlFaltas = "SELECT ptd.disciplina_id, pl.id as periodo_id, COUNT(p.id) as total_faltas 
                              FROM presencas p INNER JOIN aulas a ON a.id = p.aula_id 
                              INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id 
                              INNER JOIN periodos_letivos pl ON a.data_aula BETWEEN pl.data_inicio AND pl.data_fim 
                              WHERE p.aluno_id = :aluno_id AND ptd.ano_letivo_id = :ano_letivo_id AND p.status = 'falta' 
                              GROUP BY ptd.disciplina_id, pl.id";
                $faltasRes = $this->fetchAll($sqlFaltas, [':aluno_id' => $alunoId, ':ano_letivo_id' => $anoLetivoId]);
                foreach ($faltasRes as $f) { $faltasMap[$f['disciplina_id']][$f['periodo_id']] = $f['total_faltas']; }
            } catch (Exception $e) { /* Falha silenciosa se algo der errado na query */ }
        }

        // Busca disciplinas do aluno
        $sqlDisc = "SELECT DISTINCT d.id, d.nome FROM disciplinas d 
                    INNER JOIN professor_turma_disciplina ptd ON ptd.disciplina_id = d.id 
                    INNER JOIN matriculas m ON m.turma_id = ptd.turma_id 
                    WHERE m.aluno_id = :aluno_id AND m.status = 'ativa'";
        $todasDisciplinas = $this->fetchAll($sqlDisc, [':aluno_id' => $alunoId]);

        $boletim = [];
        foreach ($todasDisciplinas as $d) { $boletim[$d['nome']] = ['id' => $d['id']]; }

        foreach ($resultados as $row) { $boletim[$row['disciplina_nome']][$row['periodo_id']] = ['nota' => $row['nota']]; }

        // Mescla faltas no boletim
        foreach ($boletim as $nome => &$dados) {
            if (!isset($dados['id'])) continue;
            $did = $dados['id'];
            foreach ($faltasMap[$did] ?? [] as $pid => $qtd) {
                if (!isset($dados[$pid])) $dados[$pid] = ['nota' => '-'];
                $dados[$pid]['faltas'] = $qtd;
            }
        }
        return $boletim;
    }
}
?>
