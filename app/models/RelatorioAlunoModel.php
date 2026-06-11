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
            $colunasParaNull = ['aluno_id', 'professor_id'];

            foreach ($colunasParaNull as $coluna) {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM `relatorios_alunos` LIKE '{$coluna}'");
                $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

                if ($column && isset($column['Null']) && $column['Null'] === 'NO') {
                    $tipo = $column['Type'] ?? 'INT(10) UNSIGNED';
                    $this->pdo->exec("ALTER TABLE `relatorios_alunos` MODIFY `{$coluna}` {$tipo} NULL");
                }
            }

            $stmt = $this->pdo->query("SHOW COLUMNS FROM `relatorios_alunos` LIKE 'tipo'");
            $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

            if (!$column) {
                $this->pdo->exec("ALTER TABLE `relatorios_alunos` ADD COLUMN `tipo` VARCHAR(20) NULL AFTER `conteudo`");
            }
        } catch (Throwable $e) {
        }
    }

    private function hasTipoColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn === null) {
            try {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM `relatorios_alunos` LIKE 'tipo'");
                $hasColumn = (bool)$stmt->fetch();
            } catch (Throwable $e) {
                $hasColumn = false;
            }
        }
        return $hasColumn;
    }

    public function listarPorAluno(int $alunoId, int $turmaId, ?string $tipo = null, ?int $professorId = null): array
    {
        $sql = "SELECT r.*, u.nome_completo as professor_nome
                FROM relatorios_alunos r
                INNER JOIN usuarios u ON u.id = r.professor_id
                WHERE r.aluno_id = :aluno_id AND r.turma_id = :turma_id";

        $params = [
            ':aluno_id' => $alunoId,
            ':turma_id' => $turmaId
        ];

        if ($professorId) {
            $sql .= " AND r.professor_id = :professor_id";
            $params[':professor_id'] = $professorId;
        }

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
        try {
            $hasTipo = $this->hasTipoColumn();

            $sql = "INSERT INTO relatorios_alunos (
                        escola_id, aluno_id, professor_id, turma_id, conteudo"
                        . ($hasTipo ? ", tipo" : "") .
                   ") VALUES (
                        :escola_id, :aluno_id, :professor_id, :turma_id, :conteudo"
                        . ($hasTipo ? ", :tipo" : "") .
                   ")";

            $stmt = $this->pdo->prepare($sql);

            $escolaId = isset($dados['escola_id']) ? (int)$dados['escola_id'] : null;
            $alunoId = isset($dados['aluno_id']) && $dados['aluno_id'] !== '' ? (int)$dados['aluno_id'] : null;
            $professorId = isset($dados['professor_id']) && $dados['professor_id'] !== '' ? (int)$dados['professor_id'] : null;
            $turmaId = isset($dados['turma_id']) ? (int)$dados['turma_id'] : null;
            $conteudo = isset($dados['conteudo']) ? trim((string)$dados['conteudo']) : '';
            $tipo = $hasTipo ? (string)($dados['tipo'] ?? 'professor') : null;

            $stmt->bindValue(':escola_id', $escolaId, PDO::PARAM_INT);

            if ($alunoId === null) {
                $stmt->bindValue(':aluno_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':aluno_id', $alunoId, PDO::PARAM_INT);
            }

            if ($professorId === null) {
                $stmt->bindValue(':professor_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':professor_id', $professorId, PDO::PARAM_INT);
            }

            $stmt->bindValue(':turma_id', $turmaId, PDO::PARAM_INT);
            $stmt->bindValue(':conteudo', $conteudo, PDO::PARAM_STR);

            if ($hasTipo) {
                $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            }

            return $stmt->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function buscarUltimoPorAluno($alunoId, $turmaId, $professorId = null)
    {
        $sql = "
            SELECT *
            FROM relatorios_alunos
            WHERE aluno_id = :aluno_id
            AND turma_id = :turma_id
        ";

        $params = [
            ':aluno_id' => $alunoId,
            ':turma_id' => $turmaId
        ];

        if ($professorId) {
            $sql .= " AND professor_id = :professor_id";
            $params[':professor_id'] = $professorId;
        }

        $sql .= " ORDER BY criado_em DESC LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

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