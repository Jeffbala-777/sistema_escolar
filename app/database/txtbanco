CREATE DATABASE `sistema_escolar` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sistema_escolar`;

CREATE TABLE `perfis` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(30) NOT NULL,
  `nivel` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `escolas` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `cidade` varchar(80) DEFAULT NULL,
  `estado` varchar(30) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `tipo_periodo` enum('bimestral','trimestral','semestral') DEFAULT 'bimestral',
  `escala_nota` enum('10','100') DEFAULT '10',
  `plano` enum('basico','pro','premium') DEFAULT 'basico',
  `expiracao` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `anos_letivos` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `ano` year(4) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `inicio` date DEFAULT NULL,
  `fim` date DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ano_escola` (`escola_id`,`ano`),
  CONSTRAINT `fk_anos_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `periodos_letivos` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(30) NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `ordem` tinyint(4) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_periodos_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_periodos_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED DEFAULT NULL,
  `perfil_id` int(10) UNSIGNED NOT NULL,
  `nome_completo` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unico` (`email`),
  CONSTRAINT `fk_usuarios_perfil` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`),
  CONSTRAINT `fk_usuarios_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `turmas` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(50) NOT NULL,
  `serie` varchar(30) DEFAULT NULL,
  `turno` enum('manhã','tarde','noite','integral') NOT NULL DEFAULT 'manhã',
  `capacidade` int(11) NOT NULL DEFAULT 40,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_turmas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_turmas_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `disciplinas` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `carga_horaria` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_disciplinas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `matriculas` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `numero_matricula` varchar(30) NOT NULL,
  `data_matricula` date NOT NULL,
  `status` enum('ativa','transferida','cancelada','concluida') NOT NULL DEFAULT 'ativa',
  `observacao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mat_aluno_ano` (`aluno_id`,`ano_letivo_id`),
  CONSTRAINT `fk_mat_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mat_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mat_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mat_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `professor_turma_disciplina` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `professor_id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `disciplina_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `periodo_id` int(10) UNSIGNED DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ptd_vinculo` (`professor_id`,`turma_id`,`disciplina_id`,`ano_letivo_id`),
  CONSTRAINT `fk_ptd_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptd_prof` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptd_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptd_disc` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptd_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `aulas` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `professor_turma_disciplina_id` int(10) UNSIGNED NOT NULL,
  `data_aula` date NOT NULL,
  `conteudo` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_aulas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aulas_ptd` FOREIGN KEY (`professor_turma_disciplina_id`) REFERENCES `professor_turma_disciplina` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `presencas` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aula_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `status` enum('presente','falta','justificada') NOT NULL DEFAULT 'falta',
  `observacao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pres_aula_aluno` (`aula_id`,`aluno_id`),
  CONSTRAINT `fk_pres_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pres_aula` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pres_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notas` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `disciplina_id` int(10) UNSIGNED NOT NULL,
  `professor_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `periodo_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('prova','trabalho','recuperacao','final','media') NOT NULL DEFAULT 'prova',
  `nota` decimal(4,2) NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `data_lancamento` datetime NOT NULL DEFAULT current_timestamp(),
  `data_ultima_edicao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nota_unica` (`aluno_id`,`disciplina_id`,`periodo_id`,`ano_letivo_id`,`escola_id`,`tipo`),
  CONSTRAINT `fk_notas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notas_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notas_disc` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notas_prof` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notas_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notas_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_letivos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs_auditoria` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED DEFAULT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `tabela` varchar(100) DEFAULT NULL,
  `registro_id` int(10) UNSIGNED DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_logs_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `responsaveis` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(120) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_resp_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `aluno_responsavel` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `responsavel_id` int(10) UNSIGNED NOT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_aluno_resp` (`escola_id`,`aluno_id`,`responsavel_id`),
  CONSTRAINT `fk_ar_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_resp` FOREIGN KEY (`responsavel_id`) REFERENCES `responsaveis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `enderecos` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `cep` varchar(15) DEFAULT NULL,
  `logradouro` varchar(120) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(80) DEFAULT NULL,
  `bairro` varchar(80) DEFAULT NULL,
  `cidade` varchar(80) DEFAULT NULL,
  `estado` varchar(30) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_end_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_end_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `configuracoes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `chave` varchar(80) NOT NULL,
  `valor` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_conf_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `boletins` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `media_geral` decimal(4,2) NOT NULL DEFAULT 0.00,
  `total_faltas` int(11) NOT NULL DEFAULT 0,
  `status_final` enum('aprovado','reprovado','em_analise') NOT NULL DEFAULT 'em_analise',
  `gerado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_bol_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bol_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bol_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `perfis` (`id`, `nome`, `nivel`) VALUES (1, 'admin_supremo', 100), (2, 'admin', 80), (3, 'professor', 50), (4, 'aluno', 10);
INSERT INTO `usuarios` (`perfil_id`, `nome_completo`, `email`, `senha`, `ativo`) VALUES (1, 'Administrador Supremo', 'supremo@adm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
