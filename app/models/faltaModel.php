<?php
class faltaModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function registrar(int $aluno_id, int $turma_id, string $status = 'falta', ?string $data_falta = null): bool {
        $data_falta = $data_falta ?: date('Y-m-d');

        $sql = "INSERT INTO faltas (aluno_id, turma_id, data_falta, status)
                VALUES (:aluno_id, :turma_id, :data_falta, :status)
                ON DUPLICATE KEY UPDATE
                    turma_id = VALUES(turma_id),
                    status = VALUES(status)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'aluno_id' => $aluno_id,
            'turma_id' => $turma_id,
            'data_falta' => $data_falta,
            'status' => $status
        ]);
    }

    public function contarFaltasAluno(int $aluno_id): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM faltas WHERE aluno_id = :aluno_id AND status = 'falta'");
        $stmt->execute(['aluno_id' => $aluno_id]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }
}