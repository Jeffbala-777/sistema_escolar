<?php

require_once __DIR__ . '/BaseModel.php';

class RelatorioAlunoModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->checkAndFixSchema();
    }

    private function checkAndFixSchema(): void
    {
        try {
            // Verifica se aluno_id permite NULL
            $stmt = $this->pdo->query("SHOW COLUMNS FROM `relatorios_alunos` LIKE 'aluno_id'");
            $column = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($column && $column['Null'] === 'NO') {
                // Se for NOT NULL, tenta alterar para permitir NULL
                // Primeiro removemos a FK temporariamente para garantir a alteração se necessário, 
                // mas geralmente o MySQL permite mudar de NOT NULL para NULL sem dropar a FK.
                $this->pdo->exec("ALTER TABLE `relatorios_alunos` MODIFY `aluno_id` int(10) UNSIGNED NULL");
            }
        } catch (Exception $e) {
            // Ignora erros de esquema para não travar a aplicação
        }
    }

    private function hasTipoColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn === null) {
            try {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM `relatorios_alunos` LIKE 'tipo'");
                $hasColumn = (bool)$stmt->fetch();
            } catch (Exception $e) {
                $hasColumn = false;
            }
        }
        return $hasColumn;
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

        if ($tipo && $this->hasTipoColumn()) {
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
                LEFT JOIN usuarios a ON a.id = r.aluno_id
                WHERE r.turma_id = :turma_id AND r.escola_id = :escola_id";
        
        $params = [
            ':turma_id' => $turmaId,
            ':escola_id' => $escolaId
        ];

        if ($tipo && $this->hasTipoColumn()) {
            $sql .= " AND r.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $sql .= " ORDER BY r.criado_em DESC";

        return $this->fetchAll($sql, $params);
    }

    public function adicionar(array $dados): bool
    {
        $hasTipo = $this->hasTipoColumn();
        
        $sql = "INSERT INTO relatorios_alunos (escola_id, aluno_id, professor_id, turma_id, conteudo" . ($hasTipo ? ", tipo" : "") . ") 
                VALUES (:escola_id, :aluno_id, :professor_id, :turma_id, :conteudo" . ($hasTipo ? ", :tipo" : "") . ")";
        
        $params = [
            ':escola_id' => $dados['escola_id'],
            ':aluno_id' => $dados['aluno_id'],
            ':professor_id' => $dados['professor_id'],
            ':turma_id' => $dados['turma_id'],
            ':conteudo' => $dados['conteudo']
        ];

        if ($hasTipo) {
            $params[':tipo'] = $dados['tipo'] ?? 'professor';
        }

        return $this->execute($sql, $params);
    }
    
    public function buscarUltimoPorAluno($alunoId, $turmaId)
    {
    $sql = "
        SELECT *
        FROM relatorios_alunos
        WHERE aluno_id = :aluno_id
        AND turma_id = :turma_id
        ORDER BY criado_em DESC
        LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':aluno_id' => $alunoId,
        ':turma_id' => $turmaId
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT r.*, u.nome_completo as professor_nome 
                FROM relatorios_alunos r
                INNER JOIN usuarios u ON u.id = r.professor_id
                WHERE r.id = :id LIMIT 1";
        return $this->fetch($sql, [':id' => $id]);
    }

    public function atualizar(int $id, string $conteudo, int $professorId): bool
    {
        $sql = "UPDATE relatorios_alunos SET conteudo = :conteudo WHERE id = :id AND professor_id = :professor_id";
        return $this->execute($sql, [
            ':id' => $id,
            ':conteudo' => $conteudo,
            ':professor_id' => $professorId
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
