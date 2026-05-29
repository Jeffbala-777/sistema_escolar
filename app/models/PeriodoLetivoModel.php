<?php

require_once __DIR__ . '/BaseModel.php';

class PeriodoLetivoModel extends BaseModel
{
    public function listarPorAno(int $anoLetivoId, int $escolaId)
    {
        $sql = "SELECT * FROM periodos_letivos WHERE ano_letivo_id = :ano_letivo_id AND escola_id = :escola_id ORDER BY ordem ASC";
        return $this->fetchAll($sql, [
            ':ano_letivo_id' => $anoLetivoId,
            ':escola_id' => $escolaId
        ]);
    }
}
?>
