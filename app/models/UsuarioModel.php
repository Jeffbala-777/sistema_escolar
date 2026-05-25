<?php

require_once __DIR__ . '/BaseModel.php';

// Model para gerenciar todos os usuarios (Supremo, Admin, Prof, Aluno)
class UsuarioModel extends BaseModel
{
    protected PDO $pdo; // Guarda conexao

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    // Busca usuario pelo ID unico
    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT u.*, p.nome AS perfil_nome FROM usuarios u 
                INNER JOIN perfis p ON p.id = u.perfil_id 
                WHERE u.id = :id LIMIT 1";
        return $this->fetch($sql, [':id' => $id]);
    }

    // Busca usuario pelo e-mail (usado no login)
    public function buscarPorEmail(string $email): ?array
    {
        $sql = "SELECT u.*, p.nome AS perfil_nome FROM usuarios u 
                INNER JOIN perfis p ON p.id = u.perfil_id 
                WHERE u.email = :email LIMIT 1";
        return $this->fetch($sql, [':email' => trim($email)]);
    }

    // Faz o login do usuario conferindo senha
    public function autenticar(string $email, string $senha): ?array
    {
        $usuario = $this->buscarPorEmail($email);
        if (!$usuario) { return null; }
        if ((int) $usuario['ativo'] !== 1) { return null; } // Bloqueia se inativo
        if (!password_verify($senha, $usuario['senha'])) { return null; } // Senha errada
        unset($usuario['senha']); // Remove senha por seguranca
        return $usuario;
    }

    // Lista todos os usuarios de uma escola
    public function listar(int $escolaId): array
    {
        $sql = "SELECT u.id, u.nome_completo, u.email, u.telefone, u.cpf, u.ativo, p.nome AS perfil_nome 
                FROM usuarios u INNER JOIN perfis p ON p.id = u.perfil_id 
                WHERE u.escola_id = :escola_id ORDER BY u.nome_completo ASC";
        return $this->fetchAll($sql, [':escola_id' => $escolaId]);
    }

    // Lista usuarios filtrando por tipo (aluno, prof, etc)
    public function listarPorPerfil(int $escolaId, string $perfilNome): array
    {
        $sql = "SELECT u.id, u.nome_completo, u.email, u.telefone, u.cpf, u.nascimento, u.ativo, p.nome AS perfil_nome 
                FROM usuarios u INNER JOIN perfis p ON p.id = u.perfil_id 
                WHERE u.escola_id = :escola_id AND p.nome = :perfil_nome ORDER BY u.nome_completo ASC";
        return $this->fetchAll($sql, [':escola_id' => $escolaId, ':perfil_nome' => $perfilNome]);
    }

    // Lista alunos vinculados a uma turma
    public function listarPorTurma(int $turmaId): array
    {
        $sql = "SELECT u.* FROM usuarios u 
                JOIN matriculas m ON m.aluno_id = u.id 
                WHERE m.turma_id = :tid AND u.ativo = 1 
                ORDER BY u.nome_completo ASC";
        return $this->fetchAll($sql, [':tid' => $turmaId]);
    }

    // Atalhos para listagens especificas
    public function listarProfessores(int $escolaId): array { return $this->listarPorPerfil($escolaId, 'professor'); }
    public function listarAlunos(int $escolaId): array { return $this->listarPorPerfil($escolaId, 'aluno'); }
    public function listarAdmins(int $escolaId): array { return $this->listarPorPerfil($escolaId, 'admin'); }

    // Cria novo usuario no banco com LOGICA DE EMAIL AUTOMATICO
    public function cadastrar(array $dados): bool
    {
        $nome = trim($dados['nome_completo']);
        $email = trim($dados['email'] ?? '');

        // Se o email estiver vazio, gera automaticamente baseado no cargo
        if ($email === '') {
            $partes = explode(' ', strtolower($nome));
            $userPart = preg_replace('/[^a-z0-9]/', '', $partes[0]) . '.' . (count($partes) > 1 ? preg_replace('/[^a-z0-9]/', '', end($partes)) : rand(10, 99));
            
            // Pega o nome do perfil para definir o dominio
            $stmt = $this->pdo->prepare("SELECT nome FROM perfis WHERE id = :pid");
            $stmt->execute([':pid' => $dados['perfil_id']]);
            $perfilNome = $stmt->fetchColumn();

            if ($perfilNome === 'aluno') $email = $userPart . '@aluno.edu.com';
            elseif ($perfilNome === 'professor') $email = $userPart . '@prof.edu.com';
            elseif ($perfilNome === 'admin') $email = $userPart . '@admin.edu.com';
            else $email = $userPart . '@escola.com';
        }

        $sql = "INSERT INTO usuarios (escola_id, perfil_id, nome_completo, email, senha, cpf, telefone, nascimento, ativo) 
                VALUES (:escola_id, :perfil_id, :nome_completo, :email, :senha, :cpf, :telefone, :nascimento, 1)";
        
        return $this->execute($sql, [
            ':escola_id' => $dados['escola_id'],
            ':perfil_id' => $dados['perfil_id'],
            ':nome_completo' => $nome,
            ':email' => $email,
            ':senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
            ':cpf' => $dados['cpf'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':nascimento' => $dados['nascimento'] ?? null
        ]);
    }

    // Atualiza dados do usuario
    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE usuarios SET perfil_id = :perfil_id, nome_completo = :nome_completo, email = :email, cpf = :cpf, 
                telefone = :telefone, nascimento = :nascimento, ativo = :ativo WHERE id = :id";
        return $this->execute($sql, [
            ':id' => $id,
            ':perfil_id' => $dados['perfil_id'],
            ':nome_completo' => trim($dados['nome_completo']),
            ':email' => trim($dados['email']),
            ':cpf' => $dados['cpf'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':nascimento' => $dados['nascimento'] ?? null,
            ':ativo' => $dados['ativo']
        ]);
    }

    // Muda apenas a senha
    public function atualizarSenha(int $id, string $senha): bool
    {
        $sql = "UPDATE usuarios SET senha = :senha WHERE id = :id";
        return $this->execute($sql, [':id' => $id, ':senha' => password_hash($senha, PASSWORD_DEFAULT)]);
    }

    // Desativa acesso do usuario
    public function desativar(int $id): bool
    {
        $sql = "UPDATE usuarios SET ativo = 0 WHERE id = :id";
        return $this->execute($sql, [':id' => $id]);
    }
}
