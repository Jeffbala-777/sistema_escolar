<?php
class usuarioModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getByEmail(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function getById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function insert(array $dados): bool {
        $sql = "INSERT INTO usuarios (nome_completo, email, senha, tipo, turma_id, ativo)
                VALUES (:nome_completo, :email, :senha, :tipo, :turma_id, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($dados);
    }

    public function listarTurmas(): array {
        $stmt = $this->pdo->query("SELECT * FROM turmas ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function listarProfessores(): array {
        $stmt = $this->pdo->query("SELECT id, nome_completo FROM usuarios WHERE tipo = 'professor' AND ativo = 1 ORDER BY nome_completo");
        return $stmt->fetchAll();
    }

    public function listarAlunosPorTurma(int $turma_id): array {
        $stmt = $this->pdo->prepare("SELECT id, nome_completo, email FROM usuarios WHERE tipo = 'aluno' AND turma_id = :turma_id AND ativo = 1 ORDER BY nome_completo");
        $stmt->execute(['turma_id' => $turma_id]);
        return $stmt->fetchAll();
    }
}