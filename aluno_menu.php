<?php

require_once __DIR__ . '/BaseModel.php';

class BoletimModel extends BaseModel
{
    public function gerarBoletim(
        int $alunoId,
        int $anoLetivoId,
        int $escolaId
    ): array {

        $sql = "
            SELECT
                d.nome AS disciplina,
                AVG(n.nota) AS media

            FROM notas n

            INNER JOIN disciplinas d
                ON d.id = n.disciplina_id

            WHERE n.aluno_id = :aluno_id
            AND n.ano_letivo_id = :ano_letivo_id
            AND n.escola_id = :escola_id

            GROUP BY d.nome

            ORDER BY d.nome ASC
        ";

        return $this->fetchAll($sql, [
            ':aluno_id' => $alunoId,
            ':ano_letivo_id' => $anoLetivoId,
            ':escola_id' => $escolaId
        ]);
    }
}
?>