<?php
declare(strict_types=1);

// Importa a base do model
require_once __DIR__ . '/BaseModel.php';

// Model para funcoes exclusivas do Admin Supremo
class AdminSupremoModel extends BaseModel
{
    // Construtor: inicia conexao com o banco via PDO
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    // Lista usuarios filtrando por escola e cargo para o Supremo
    public function listarUsuariosFiltrados(?int $escolaId = null, ?int $perfilId = null): array
    {
        $where = []; // Array para guardar condicoes do WHERE
        $params = []; // Array para guardar valores dos parametros

        // Se informou escola, adiciona no filtro
        if ($escolaId) {
            $where[] = "u.escola_id = :escola_id";
            $params[':escola_id'] = $escolaId;
        }

        // Se informou perfil, adiciona no filtro
        if ($perfilId) {
            $where[] = "u.perfil_id = :perfil_id";
            $params[':perfil_id'] = $perfilId;
        }

        // Monta a string WHERE se houver filtros
        $whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        // Query que traz dados do usuario, perfil e nome da escola
        $sql = "SELECT u.*, p.nome AS perfil_nome, e.nome AS escola_nome 
                FROM usuarios u 
                INNER JOIN perfis p ON p.id = u.perfil_id 
                INNER JOIN escolas e ON e.id = u.escola_id 
                $whereSql ORDER BY e.nome ASC, u.nome_completo ASC";

        // Executa e retorna todos os resultados
        return $this->fetchAll($sql, $params);
    }

    // Pega contagens globais para o Dashboard do Supremo
    public function buscarKpisSaaS(): array
    {
        return [
            // Conta quantas escolas estao com status ativo
            'escolas_ativas' => (int)$this->fetchColumn("SELECT COUNT(*) FROM escolas WHERE ativo = 1"),
            // Conta quantos alunos ativos existem no sistema todo
            'total_alunos' => (int)$this->fetchColumn("SELECT COUNT(*) FROM usuarios WHERE perfil_id = 4 AND ativo = 1")
        ];
    }

    // Grava uma acao no log de auditoria do sistema
    public function registrarLog(int $usuarioId, string $acao, string $detalhes): bool
    {
        $sql = "INSERT INTO logs_auditoria (usuario_id, acao, detalhes, ip_origem) VALUES (:uid, :acao, :detalhes, :ip)";
        return $this->execute($sql, [
            ':uid' => $usuarioId,
            ':acao' => $acao,
            ':detalhes' => $detalhes,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' // Salva IP do usuario
        ]);
    }

    // Lista os logs mais recentes para exibir no dashboard ou tela de logs
    public function listarLogsRecentes(int $limite = 10): array
    {
        $sql = "SELECT l.*, u.nome_completo FROM logs_auditoria l 
                JOIN usuarios u ON u.id = l.usuario_id 
                ORDER BY l.data_criacao DESC LIMIT :limite";
        return $this->fetchAll($sql, [':limite' => $limite]);
    }

    // Lista todos os perfis/cargos disponiveis no sistema
    public function listarPerfis(): array
    {
        return $this->fetchAll("SELECT * FROM perfis ORDER BY nivel DESC");
    }

    // Exclui um usuario permanentemente do banco de dados
    public function excluirUsuario(int $id): bool
    {
        return $this->execute("DELETE FROM usuarios WHERE id = :id", [':id' => $id]);
    }
}
