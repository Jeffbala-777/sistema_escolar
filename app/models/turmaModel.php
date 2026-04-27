<?php
class turmaModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listar(): array {
        $stmt = $this->pdo->query("SELECT * FROM turmas ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM turmas WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}