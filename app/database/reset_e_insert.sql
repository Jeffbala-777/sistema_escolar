-- SCRIPT DE RESET E INSERT COMPLETO
-- Limpa os dados e insere os usuários iniciais com hashes reais

SET FOREIGN_KEY_CHECKS = 0;

-- Limpar tabelas principais (RESET)
TRUNCATE TABLE `usuarios`;
TRUNCATE TABLE `escolas`;
TRUNCATE TABLE `perfis`;

SET FOREIGN_KEY_CHECKS = 1;

-- Inserir Perfis
INSERT INTO `perfis` (`id`, `nome`, `nivel`) VALUES
(1, 'admin_supremo', 100),
(2, 'admin', 80),
(3, 'professor', 50),
(4, 'aluno', 10);

-- Inserir Escola Inicial
INSERT INTO `escolas` (`id`, `nome`, `codigo`, `cnpj`, `cidade`, `estado`, `ativo`) VALUES
(1, 'Escola Central de Testes', 'ESC01', '00.000.000/0001-00', 'São Paulo', 'SP', 1);

-- INSERTS DE USUÁRIOS COM HASHES REAIS
-- Senhas: ADMIN123, ESCOLA123, PROF123, ALUNO123

-- 1. ADMIN SUPREMO (@adm.com)
INSERT INTO `usuarios` (`escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `cpf`, `telefone`, `ativo`) VALUES
(1, 1, 'Administrador Supremo', 'supremo@adm.com', '$2b$12$a.YyUBRP8Pqe9is1efhv2OwihYaIEJtbXSUwPjiz0HynbncuytRfi', '000.000.000-00', '(00) 00000-0000', 1);

-- 2. ADMINISTRADOR DE ESCOLA (@admin.edu.com)
INSERT INTO `usuarios` (`escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `cpf`, `telefone`, `ativo`) VALUES
(1, 2, 'Gestor Escolar', 'gestor@admin.edu.com', '$2b$12$GfPhHN6dvTn8ACXi9pCLiugvpMP/j4UAIk3/0rCPbEB/ia93ElJH6', '111.111.111-11', '(11) 11111-1111', 1);

-- 3. PROFESSOR (@prof.edu.com)
INSERT INTO `usuarios` (`escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `cpf`, `telefone`, `ativo`) VALUES
(1, 3, 'Professor Exemplo', 'professor@prof.edu.com', '$2b$12$71FDvEywhZuJKcco.SeTA.giotdknM4PRBq5h6GsjYBVF64WKu4g.', '222.222.222-22', '(22) 22222-2222', 1);

-- 4. ALUNO (@aluno.edu.com)
INSERT INTO `usuarios` (`escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `cpf`, `telefone`, `ativo`) VALUES
(1, 4, 'Aluno Exemplo', 'aluno@aluno.edu.com', '$2b$12$p.OEAorJAXg3EP8RUDWoJ.Zdo9qaLmLVkovGO6GTDR0BSMH1lLrCi', '333.333.333-33', '(33) 33333-3333', 1);
