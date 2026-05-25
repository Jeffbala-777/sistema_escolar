<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

// Model para gerenciar as Escolas (Tenants)
class EscolaModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    // Busca escola pelo ID unico
    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT * FROM escolas WHERE id = :id LIMIT 1";
        return $this->fetch($sql, [':id' => $id]);
    }

    // Busca escola pelo codigo interno
    public function buscarPorCodigo(string $codigo): ?array
    {
        $sql = "SELECT * FROM escolas WHERE codigo = :codigo LIMIT 1";
        return $this->fetch($sql, [':codigo' => $codigo]);
    }

    // Lista todas as escolas do sistema
    public function listarTodas(): array
    {
        $sql = "SELECT * FROM escolas ORDER BY nome ASC";
        return $this->fetchAll($sql);
    }

    // Cadastra nova escola no banco
    public function cadastrar(array $dados): bool
    {
        $sql = "INSERT INTO escolas (nome, codigo, cnpj, cidade, estado, logo, ativo) 
                VALUES (:nome, :codigo, :cnpj, :cidade, :estado, :logo, :ativo)";
        return $this->execute($sql, [
            ':nome' => trim($dados['nome']),
            ':codigo' => trim($dados['codigo']),
            ':cnpj' => $dados['cnpj'] ?? null,
            ':cidade' => $dados['cidade'] ?? null,
            ':estado' => $dados['estado'] ?? null,
            ':logo' => $dados['logo'] ?? null,
            ':ativo' => $dados['ativo'] ?? 1
        ]);
    }

    // Atualiza dados de uma escola
    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE escolas SET nome = :nome, codigo = :codigo, cnpj = :cnpj, cidade = :cidade, 
                estado = :estado, logo = :logo, ativo = :ativo WHERE id = :id";
        return $this->execute($sql, [
            ':id' => $id,
            ':nome' => trim($dados['nome']),
            ':codigo' => trim($dados['codigo']),
            ':cnpj' => $dados['cnpj'] ?? null,
            ':cidade' => $dados['cidade'] ?? null,
            ':estado' => $dados['estado'] ?? null,
            ':logo' => $dados['logo'] ?? null,
            ':ativo' => $dados['ativo']
        ]);
    }

    // Remove escola do sistema
    public function excluir(int $id): bool
    {
        $sql = "DELETE FROM escolas WHERE id = :id";
        return $this->execute($sql, [':id' => $id]);
    }

    // Salva configuracao da escola (ex: nota maxima)
    public function salvarConfiguracao(int $escolaId, string $chave, string $valor): bool
    {
        $sql = "INSERT INTO configuracoes (escola_id, chave, valor) VALUES (:escola_id, :chave, :valor) 
                ON DUPLICATE KEY UPDATE valor = :valor2";
        return $this->execute($sql, [
            ':escola_id' => $escolaId,
            ':chave' => $chave,
            ':valor' => $valor,
            ':valor2' => $valor
        ]);
    }

    // Pega valor de uma configuracao da escola
    public function buscarConfiguracao(int $escolaId, string $chave): ?string
    {
        $sql = "SELECT valor FROM configuracoes WHERE escola_id = :escola_id AND chave = :chave LIMIT 1";
        $res = $this->fetch($sql, [':escola_id' => $escolaId, ':chave' => $chave]);
        return $res['valor'] ?? null;
    }
}
