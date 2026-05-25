SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- 1. Inserir a Escola
INSERT INTO `escolas` (`id`, `nome`, `codigo`, `cnpj`, `cidade`, `estado`, `ativo`) VALUES
(3, 'Centro Educacional Nova Era', 'CENE01', '12.345.678/0001-90', 'São Paulo', 'SP', 1);

-- 2. Inserir Ano Letivo
INSERT INTO `anos_letivos` (`id`, `escola_id`, `ano`, `ativo`) VALUES
(3, 3, '2026', 1);

-- 3. Inserir Bimestres
INSERT INTO `periodos_letivos` (`escola_id`, `ano_letivo_id`, `nome`, `ordem`, `ativo`) VALUES
(3, 3, '1º Bimestre', 1, 1), (3, 3, '2º Bimestre', 2, 1), (3, 3, '3º Bimestre', 3, 1), (3, 3, '4º Bimestre', 4, 1);

INSERT INTO `usuarios` (`escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `cpf`, `telefone`, `ativo`) VALUES
(1, 1, 'Administrador Supremo', 'supremo@adm.com', '$2b$12$a.YyUBRP8Pqe9is1efhv2OwihYaIEJtbXSUwPjiz0HynbncuytRfi', '000.000.000-00', '(00) 00000-0000', 1);

-- 4. Inserir 2 Administradores (Senha: 123456)
INSERT INTO `usuarios` (`id`, `escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `ativo`) VALUES
(100, 3, 2, 'Admin Principal CENE', 'admin1@admin.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(101, 3, 2, 'Admin Auxiliar CENE', 'admin2@admin.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1);

-- 5. Inserir Disciplinas
INSERT INTO `disciplinas` (`id`, `escola_id`, `nome`, `ativo`) VALUES
(10, 3, 'Matemática', 1), (11, 3, 'Português', 1), (12, 3, 'História', 1);

-- 6. Inserir 2 Professores (Senha: 123456)
INSERT INTO `usuarios` (`id`, `escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `ativo`) VALUES
(150, 3, 3, 'Prof. Marcos Silva', 'marcos.prof@prof.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(151, 3, 3, 'Profª. Julia Costa', 'julia.prof@prof.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1);

-- 7. Inserir a Turma
INSERT INTO `turmas` (`id`, `escola_id`, `ano_letivo_id`, `nome`, `serie`, `turno`, `capacidade`) VALUES
(10, 3, 3, '3ºM01-EM-TVC', '3ª Série - Ensino Médio', 'manhã', 40);

-- 8. Vincular Professores
INSERT INTO `professor_turma_disciplina` (`escola_id`, `professor_id`, `turma_id`, `disciplina_id`, `ano_letivo_id`, `ativo`) VALUES
(3, 150, 10, 10, 3, 1), (3, 151, 10, 11, 3, 1), (3, 150, 10, 12, 3, 1);

-- 9. Inserir Alunos (Senha: 123456)
INSERT INTO `usuarios` (`id`, `escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `ativo`) VALUES
(201, 3, 4, 'Ana Beatriz Silva', 'ana.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(202, 3, 4, 'Bruno Oliveira', 'bruno.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(203, 3, 4, 'Carla Mendes', 'carla.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(204, 3, 4, 'Diego Ferreira', 'diego.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(205, 3, 4, 'Elena Santos', 'elena.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(206, 3, 4, 'Fábio Lima', 'fabio.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(207, 3, 4, 'Giovanna Rocha', 'giovanna.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(208, 3, 4, 'Henrique Souza', 'henrique.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(209, 3, 4, 'Isabela Costa', 'isabela.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1),
(210, 3, 4, 'João Vitor Pereira', 'joao.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', 1);

-- 10. Matricular Alunos
INSERT INTO `matriculas` (`escola_id`, `aluno_id`, `turma_id`, `ano_letivo_id`, `numero_matricula`, `data_matricula`, `status`) VALUES
(3, 201, 10, 3, '2026001', '2026-01-15', 'ativa'), (3, 202, 10, 3, '2026002', '2026-01-15', 'ativa'), (3, 203, 10, 3, '2026003', '2026-01-15', 'ativa'), (3, 204, 10, 3, '2026004', '2026-01-15', 'ativa'), (3, 205, 10, 3, '2026005', '2026-01-15', 'ativa'), (3, 206, 10, 3, '2026006', '2026-01-15', 'ativa'), (3, 207, 10, 3, '2026007', '2026-01-15', 'ativa'), (3, 208, 10, 3, '2026008', '2026-01-15', 'ativa'), (3, 209, 10, 3, '2026009', '2026-01-15', 'ativa'), (3, 210, 10, 3, '2026010', '2026-01-15', 'ativa');

COMMIT;
