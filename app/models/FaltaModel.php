<?php

require_once __DIR__ . '/BaseModel.php';

class FaltaModel extends BaseModel
{
    public function listarPorAluno(
        int $alunoId,
        int $disciplinaId
    ): array {

        $sql = "
            SELECT *
            FROM presencas
            WHERE aluno_id = :aluno_id
            AND disciplina_id = :disciplina_id
            ORDER BY data_aula DESC
        ";

        return $this->fetchAll($sql, [
            ':aluno_id' => $alunoId,
            ':disciplina_id' => $disciplinaId
        ]);
    }

    public function salvar(array $dados): bool
    {
        // Verifica se já existe registro de presença para este aluno nesta aula
        $sqlCheck = "SELECT id FROM presencas WHERE aluno_id = :aluno_id AND aula_id = :aula_id LIMIT 1";
        $existente = $this->fetch($sqlCheck, [
            ':aluno_id' => $dados['aluno_id'],
            ':aula_id' => $dados['aula_id']
        ]);

        if ($existente) {
            $sql = "UPDATE presencas SET status = :status WHERE id = :id";
            return $this->execute($sql, [
                ':status' => $dados['status'],
                ':id' => $existente['id']
            ]);
        }

        $sql = "
            INSERT INTO presencas (
                escola_id,
                aluno_id,
                aula_id,
                status
            ) VALUES (
                :escola_id,
                :aluno_id,
                :aula_id,
                :status
            )
        ";

        return $this->execute($sql, [
            ':escola_id' => $dados['escola_id'],
            ':aluno_id' => $dados['aluno_id'],
            ':aula_id' => $dados['aula_id'],
            ':status' => $dados['status']
        ]);
    }

    public function buscarPresencasAula(int $aulaId)
    {
        $sql = "SELECT aluno_id, status FROM presencas WHERE aula_id = :aula_id";
        $resultados = $this->fetchAll($sql, [':aula_id' => $aulaId]);
        
        $presencas = [];
        foreach ($resultados as $row) {
            $presencas[$row['aluno_id']] = $row['status'];
        }
        return $presencas;
    }

    public function buscarEstatisticasFrequenciaMensal(int $alunoId, int $anoLetivoId)
    {
        $sql = "
            SELECT 
                MONTH(a.data_aula) as mes,
                COUNT(p.id) as total_aulas,
                SUM(CASE WHEN p.status = 'falta' THEN 1 ELSE 0 END) as total_faltas
            FROM presencas p
            INNER JOIN aulas a ON a.id = p.aula_id
            INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
            WHERE p.aluno_id = :aluno_id
            AND ptd.ano_letivo_id = :ano_letivo_id
            GROUP BY MONTH(a.data_aula)
            ORDER BY mes ASC
        ";

        return $this->fetchAll($sql, [
            ':aluno_id' => $alunoId,
            ':ano_letivo_id' => $anoLetivoId
        ]);
    }

    public function buscarDiasFaltasMensais(int $alunoId, int $anoLetivoId)
    {
        $sql = "
            SELECT 
                MONTH(a.data_aula) as mes,
                DAY(a.data_aula) as dia
            FROM presencas p
            INNER JOIN aulas a ON a.id = p.aula_id
            INNER JOIN professor_turma_disciplina ptd ON ptd.id = a.professor_turma_disciplina_id
            WHERE p.aluno_id = :aluno_id
            AND ptd.ano_letivo_id = :ano_letivo_id
            AND p.status = 'falta'
            ORDER BY mes ASC, dia ASC
        ";

        $resultados = $this->fetchAll($sql, [
            ':aluno_id' => $alunoId,
            ':ano_letivo_id' => $anoLetivoId
        ]);

        $faltasPorMes = [];
        foreach ($resultados as $row) {
            $faltasPorMes[$row['mes']][] = $row['dia'];
        }
        return $faltasPorMes;
    }
}
?>