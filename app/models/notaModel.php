<?php
class notaModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function salvarOuAtualizar(int $aluno_id, int $professor_id, string $materia, int $bimestre, float $nota): bool {
        $sql = "INSERT INTO notas (aluno_id, professor_id, materia, nota, bimestre)
                VALUES (:aluno_id, :professor_id, :materia, :nota, :bimestre)
                ON DUPLICATE KEY UPDATE
                    professor_id = VALUES(professor_id),
                    nota = VALUES(nota),
                    data_ultima_edicao = CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'aluno_id' => $aluno_id,
            'professor_id' => $professor_id,
            'materia' => $materia,
            'nota' => $nota,
            'bimestre' => $bimestre
        ]);
    }

    public function getNotasAluno(int $aluno_id): array {
        $stmt = $this->pdo->prepare("SELECT * FROM notas WHERE aluno_id = :aluno_id ORDER BY materia, bimestre");
        $stmt->execute(['aluno_id' => $aluno_id]);
        return $stmt->fetchAll();
    }
}