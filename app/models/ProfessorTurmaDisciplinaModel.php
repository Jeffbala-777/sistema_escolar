<?php

class ProfessorTurmaDisciplinaModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarTurmasProfessor(
        int $professorId,
        int $escolaId
    )
    {
        $sql = "
            SELECT
                MIN(ptd.id) AS id,
                t.id AS turma_id,
                t.nome AS turma,
                t.serie,
                t.turno,
                GROUP_CONCAT(DISTINCT d.nome ORDER BY d.nome SEPARATOR ', ') AS disciplina,
                al.ano

            FROM professor_turma_disciplina ptd

            INNER JOIN turmas t
                ON t.id = ptd.turma_id

            INNER JOIN disciplinas d
                ON d.id = ptd.disciplina_id

            INNER JOIN anos_letivos al
                ON al.id = ptd.ano_letivo_id

            WHERE ptd.professor_id = ?
            AND ptd.escola_id = ?
            AND ptd.ativo = 1

            GROUP BY t.id, t.nome, t.serie, t.turno, al.id, al.ano
            ORDER BY t.nome ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $professorId,
            $escolaId
        ]);

        return $stmt->fetchAll();
    }

    public function buscarTurmaProfessor(
        int $ptdId,
        int $professorId
    )
    {
        $sql = "
            SELECT
                ptd.*,
                t.nome AS turma_nome,
                t.serie,
                t.turno,
                d.nome AS disciplina_nome

            FROM professor_turma_disciplina ptd

            INNER JOIN turmas t
                ON t.id = ptd.turma_id

            INNER JOIN disciplinas d
                ON d.id = ptd.disciplina_id

            WHERE ptd.id = ?
            AND ptd.professor_id = ?
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $ptdId,
            $professorId
        ]);

        return $stmt->fetch();
    }

    public function listarAlunosTurma(int $turmaId)
    {
        $sql = "
            SELECT
                u.id,
                u.nome_completo,
                m.numero_matricula

            FROM matriculas m

            INNER JOIN usuarios u
                ON u.id = m.aluno_id

            WHERE m.turma_id = ?
            AND m.status = 'ativa'

            ORDER BY u.nome_completo ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([$turmaId]);

        return $stmt->fetchAll();
    }
}