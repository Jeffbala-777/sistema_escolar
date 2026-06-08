<?php

require_once __DIR__ . '/BaseModel.php';

class DisciplinaModel extends BaseModel
{
    public function listar(int $escolaId): array
    {
        $sql = "
            SELECT *
            FROM disciplinas
            WHERE escola_id = :escola_id
            ORDER BY nome ASC
        ";

        return $this->fetchAll($sql, [
            ':escola_id' => $escolaId
        ]);
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM disciplinas
            WHERE id = :id
            LIMIT 1
        ";

        return $this->fetch($sql, [
            ':id' => $id
        ]);
    }
}
?>