<?php

require_once __DIR__ . '/BaseModel.php';

class AulaModel extends BaseModel
{
    public function criarOuBuscar(array $dados)
    {
        $sqlCheck = "SELECT id FROM aulas WHERE professor_turma_disciplina_id = :ptd_id AND data_aula = :data_aula LIMIT 1";
        $existente = $this->fetch($sqlCheck, [
            ':ptd_id' => $dados['ptd_id'],
            ':data_aula' => $dados['data_aula']
        ]);

        if ($existente) {
            return (int)$existente['id'];
        }

        $sql = "INSERT INTO aulas (escola_id, professor_turma_disciplina_id, data_aula) VALUES (:escola_id, :ptd_id, :data_aula)";
        $this->execute($sql, [
            ':escola_id' => $dados['escola_id'],
            ':ptd_id' => $dados['ptd_id'],
            ':data_aula' => $dados['data_aula']
        ]);

        return (int)$this->lastInsertId();
    }

    public function buscarPorPtdEData(int $ptdId, string $data)
    {
        $sql = "SELECT * FROM aulas WHERE professor_turma_disciplina_id = :ptd_id AND data_aula = :data_aula LIMIT 1";
        return $this->fetch($sql, [
            ':ptd_id' => $ptdId,
            ':data_aula' => $data
        ]);
    }
}
?>
