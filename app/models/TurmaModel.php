<?php

require_once __DIR__ . '/BaseModel.php';

// Model para gerenciar as Turmas das escolas
class TurmaModel extends BaseModel
{
    protected PDO $pdo; // Guarda conexao

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    // Lista todas as turmas de uma escola com o ano letivo
    public function listar(int $escolaId): array
    {
        $sql = "SELECT t.id, t.nome, t.serie, t.turno, t.capacidade, al.ano AS ano_nome 
                FROM turmas t 
                INNER JOIN anos_letivos al ON al.id = t.ano_letivo_id 
                WHERE t.escola_id = :escola_id ORDER BY t.nome ASC";
        return $this->fetchAll($sql, [':escola_id' => $escolaId]);
    }

    // Atalho para listar turmas (usado na filtragem de alunos)
    public function listarPorEscola(int $escolaId): array
    {
        return $this->listar($escolaId);
    }

    // Busca uma turma especifica pelo ID
    public function buscarPorId(int $id, int $escolaId): ?array
    {
        $sql = "SELECT t.*, al.ano AS ano_nome FROM turmas t 
                INNER JOIN anos_letivos al ON al.id = t.ano_letivo_id 
                WHERE t.id = :id AND t.escola_id = :escola_id LIMIT 1";
        return $this->fetch($sql, [':id' => $id, ':escola_id' => $escolaId]);
    }

    // Cria nova turma no banco
    public function cadastrar(array $dados): bool
    {
        $sql = "INSERT INTO turmas (escola_id, ano_letivo_id, nome, serie, turno, capacidade) 
                VALUES (:escola_id, :ano_letivo_id, :nome, :serie, :turno, :capacidade)";
        return $this->execute($sql, [
            ':escola_id' => $dados['escola_id'],
            ':ano_letivo_id' => $dados['ano_letivo_id'],
            ':nome' => trim($dados['nome']),
            ':serie' => trim($dados['serie']),
            ':turno' => trim($dados['turno']),
            ':capacidade' => (int) $dados['capacidade']
        ]);
    }

    // Atualiza dados da turma
    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE turmas SET ano_letivo_id = :ano_letivo_id, nome = :nome, serie = :serie, 
                turno = :turno, capacidade = :capacidade WHERE id = :id AND escola_id = :escola_id";
        return $this->execute($sql, [
            ':id' => $id,
            ':escola_id' => $dados['escola_id'],
            ':ano_letivo_id' => $dados['ano_letivo_id'],
            ':nome' => trim($dados['nome']),
            ':serie' => trim($dados['serie']),
            ':turno' => trim($dados['turno']),
            ':capacidade' => (int) $dados['capacidade']
        ]);
    }

    // Remove turma do banco
    public function excluir(int $id, int $escolaId): bool
    {
        $sql = "DELETE FROM turmas WHERE id = :id AND escola_id = :escola_id";
        return $this->execute($sql, [':id' => $id, ':escola_id' => $escolaId]);
    }
}

?>
