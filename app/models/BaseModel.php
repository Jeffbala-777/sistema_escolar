<?php

// Classe pai de todos os Models
class BaseModel
{
    protected PDO $pdo; // Guarda a conexao

    // Recebe o banco ao ser criado
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Busca um unico registro
    protected function fetch(
        string $sql,
        array $params = []
    ): ?array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    // Busca varios registros
    protected function fetchAll(
        string $sql,
        array $params = []
    ): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca um unico valor (ex: ID ou Contagem)
    protected function fetchColumn(
        string $sql,
        array $params = []
    ) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Executa comando (Insert/Update/Delete)
    protected function execute(
        string $sql,
        array $params = []
    ): bool {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Pega o ID do ultimo item inserido
    protected function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}

?>
