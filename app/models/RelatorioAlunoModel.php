<?php

require_once __DIR__ . '/BaseModel.php';

class RelatorioAlunoModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function listarPorAluno(int $alunoId, int $turmaId, ?string $tipo = null): array
    {
        $sql = "SELECT r.*, u.nome_completo as professor_nome 
                FROM relatorios_alunos r
                INNER JOIN usuarios u ON u.id = r.professor_id
                WHERE r.aluno_id = :aluno_id AND r.turma_id = :turma_id";
        
        $params = [
            ':aluno_id' => $alunoId,
            ':turma_id' => $turmaId
        ];

        if ($tipo) {
            $sql .= " AND r.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $sql .= " ORDER BY r.criado_em DESC";
        
        return $this->fetchAll($sql, $params);
    }

    public function listarPorTurma(int $turmaId, int $escolaId, ?string $tipo = null): array
    {
        $sql = "SELECT r.*, p.nome_completo as professor_nome, a.nome_completo as aluno_nome 
                FROM relatorios_alunos r
                INNER JOIN usuarios p ON p.id = r.professor_id
                INNER JOIN usuarios a ON a.id = r.aluno_id
                WHERE r.turma_id = :turma_id AND r.escola_id = :escola_id";
        
        $params = [
            ':turma_id' => $turmaId,
            ':escola_id' => $escolaId
        ];

        if ($tipo) {
            $sql .= " AND r.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $sql .= " ORDER BY r.criado_em DESC";

        return $this->fetchAll($sql, $params);
    }

    public function adicionar(array $dados): bool
    {
        $sql = "INSERT INTO relatorios_alunos (escola_id, aluno_id, professor_id, turma_id, conteudo, tipo) 
                VALUES (:escola_id, :aluno_id, :professor_id, :turma_id, :conteudo, :tipo)";
        return $this->execute($sql, [
            ':escola_id' => $dados['escola_id'],
            ':aluno_id' => $dados['aluno_id'],
            ':professor_id' => $dados['professor_id'],
            ':turma_id' => $dados['turma_id'],
            ':conteudo' => $dados['conteudo'],
            ':tipo' => $dados['tipo'] ?? 'professor'
        ]);
    }

    public function excluir(int $id, int $professorId): bool
    {
        $sql = "DELETE FROM relatorios_alunos WHERE id = :id AND professor_id = :professor_id";
        return $this->execute($sql, [
            ':id' => $id,
            ':professor_id' => $professorId
        ]);
    }
}
