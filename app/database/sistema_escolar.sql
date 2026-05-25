-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 04:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_escolar`
--

-- --------------------------------------------------------

--
-- Table structure for table `aluno_responsavel`
--

CREATE TABLE `aluno_responsavel` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `responsavel_id` int(10) UNSIGNED NOT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anos_letivos`
--

CREATE TABLE `anos_letivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `ano` year(4) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `inicio` date DEFAULT NULL,
  `fim` date DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anos_letivos`
--

INSERT INTO `anos_letivos` (`id`, `escola_id`, `ano`, `ativo`, `inicio`, `fim`, `criado_em`) VALUES
(1, 1, '2026', 1, NULL, NULL, '2026-05-19 02:03:32'),
(2, 2, '2026', 1, NULL, NULL, '2026-05-19 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `acao` varchar(120) NOT NULL,
  `tabela_afetada` varchar(60) DEFAULT NULL,
  `registro_id` int(10) UNSIGNED DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aulas`
--

CREATE TABLE `aulas` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `professor_turma_disciplina_id` int(10) UNSIGNED NOT NULL,
  `data_aula` date NOT NULL,
  `conteudo` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `boletins`
--

CREATE TABLE `boletins` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `media_geral` decimal(4,2) NOT NULL DEFAULT 0.00,
  `total_faltas` int(11) NOT NULL DEFAULT 0,
  `status_final` enum('aprovado','reprovado','em_analise') NOT NULL DEFAULT 'em_analise',
  `gerado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `chave` varchar(80) NOT NULL,
  `valor` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `escola_id`, `chave`, `valor`, `criado_em`) VALUES
(1, 1, 'total_periodos_padrao', '4', '2026-05-19 02:03:32'),
(2, 2, 'total_periodos_padrao', '4', '2026-05-19 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `disciplinas`
--

CREATE TABLE `disciplinas` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `carga_horaria` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enderecos`
--

CREATE TABLE `enderecos` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `cep` varchar(15) DEFAULT NULL,
  `logradouro` varchar(120) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(80) DEFAULT NULL,
  `bairro` varchar(80) DEFAULT NULL,
  `cidade` varchar(80) DEFAULT NULL,
  `estado` varchar(30) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `escolas`
--

CREATE TABLE `escolas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `cidade` varchar(80) DEFAULT NULL,
  `estado` varchar(30) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `plano` enum('basico','pro','premium') DEFAULT 'basico',
  `expiracao` date DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `escolas`
--

INSERT INTO `escolas` (`id`, `nome`, `codigo`, `cnpj`, `cidade`, `estado`, `logo`, `ativo`, `criado_em`) VALUES
(1, 'Escola Exemplo 1', 'ESC01', NULL, NULL, NULL, NULL, 1, '2026-05-19 02:03:32'),
(2, 'Escola Exemplo 2', 'ESC02', NULL, NULL, NULL, NULL, 1, '2026-05-19 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `faltas`
--

CREATE TABLE `faltas` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `disciplina_id` int(10) UNSIGNED DEFAULT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `periodo_id` int(10) UNSIGNED DEFAULT NULL,
  `data_falta` date NOT NULL,
  `status` enum('presente','falta','justificada') NOT NULL DEFAULT 'falta',
  `observacao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `numero_matricula` varchar(30) NOT NULL,
  `data_matricula` date NOT NULL,
  `status` enum('ativa','transferida','cancelada','concluida') NOT NULL DEFAULT 'ativa',
  `observacao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notas`
--

CREATE TABLE `notas` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `disciplina_id` int(10) UNSIGNED NOT NULL,
  `professor_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `periodo_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('prova','trabalho','recuperacao','final') NOT NULL DEFAULT 'prova',
  `nota` decimal(4,2) NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `data_lancamento` datetime NOT NULL DEFAULT current_timestamp(),
  `data_ultima_edicao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perfis`
--

CREATE TABLE `perfis` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(30) NOT NULL,
  `nivel` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `perfis`
--

INSERT INTO `perfis` (`id`, `nome`, `nivel`, `criado_em`) VALUES
(1, 'admin_supremo', 100, '2026-05-19 02:03:32'),
(2, 'admin', 80, '2026-05-19 02:03:32'),
(3, 'professor', 50, '2026-05-19 02:03:32'),
(4, 'aluno', 10, '2026-05-19 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `periodos_letivos`
--

CREATE TABLE `periodos_letivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(30) NOT NULL,
  `ordem` tinyint(4) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `periodos_letivos`
--

INSERT INTO `periodos_letivos` (`id`, `escola_id`, `ano_letivo_id`, `nome`, `ordem`, `ativo`, `criado_em`) VALUES
(1, 1, 1, '1º bimestre', 1, 1, '2026-05-19 02:03:32'),
(2, 2, 2, '1º bimestre', 1, 1, '2026-05-19 02:03:32'),
(3, 1, 1, '2º bimestre', 2, 1, '2026-05-19 02:03:32'),
(4, 2, 2, '2º bimestre', 2, 1, '2026-05-19 02:03:32'),
(5, 1, 1, '3º bimestre', 3, 1, '2026-05-19 02:03:32'),
(6, 2, 2, '3º bimestre', 3, 1, '2026-05-19 02:03:32'),
(7, 1, 1, '4º bimestre', 4, 1, '2026-05-19 02:03:32'),
(8, 2, 2, '4º bimestre', 4, 1, '2026-05-19 02:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `presencas`
--

CREATE TABLE `presencas` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aula_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `status` enum('presente','falta','justificada') NOT NULL DEFAULT 'falta',
  `observacao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professor_turma_disciplina`
--

CREATE TABLE `professor_turma_disciplina` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `professor_id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `disciplina_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `periodo_id` int(10) UNSIGNED DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `responsaveis`
--

CREATE TABLE `responsaveis` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(120) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `turmas`
--

CREATE TABLE `turmas` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(50) NOT NULL,
  `serie` varchar(30) DEFAULT NULL,
  `turno` enum('manhã','tarde','noite') NOT NULL DEFAULT 'manhã',
  `capacidade` int(11) NOT NULL DEFAULT 40,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `perfil_id` int(10) UNSIGNED NOT NULL,
  `nome_completo` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `cpf`, `telefone`, `nascimento`, `foto`, `ativo`, `criado_em`) VALUES
(1, 1, 2, 'Administrador Escola A', 'admin@admin.edu.com', '$2y$10$EXEMPLO_DE_HASH', '999.999.999-99', '(11) 1111-1111', NULL, NULL, 1, '2026-05-19 02:23:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aluno_responsavel`
--
ALTER TABLE `aluno_responsavel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_aluno_resp` (`escola_id`,`aluno_id`,`responsavel_id`),
  ADD KEY `idx_ar_escola` (`escola_id`),
  ADD KEY `idx_ar_aluno` (`aluno_id`),
  ADD KEY `idx_ar_responsavel` (`responsavel_id`);

--
-- Indexes for table `anos_letivos`
--
ALTER TABLE `anos_letivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ano_escola` (`escola_id`,`ano`),
  ADD KEY `idx_anos_escola` (`escola_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_escola` (`escola_id`),
  ADD KEY `idx_audit_usuario` (`usuario_id`);

--
-- Indexes for table `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aulas_escola` (`escola_id`),
  ADD KEY `idx_aulas_ptd` (`professor_turma_disciplina_id`);

--
-- Indexes for table `boletins`
--
ALTER TABLE `boletins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_boletim` (`escola_id`,`aluno_id`,`ano_letivo_id`),
  ADD KEY `idx_boletins_escola` (`escola_id`),
  ADD KEY `idx_boletins_aluno` (`aluno_id`),
  ADD KEY `idx_boletins_ano` (`ano_letivo_id`);

--
-- Indexes for table `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_config` (`escola_id`,`chave`),
  ADD KEY `idx_config_escola` (`escola_id`);

--
-- Indexes for table `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_disciplina` (`escola_id`,`nome`),
  ADD KEY `idx_disc_escola` (`escola_id`);

--
-- Indexes for table `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_end_escola` (`escola_id`),
  ADD KEY `idx_end_usuario` (`usuario_id`);

--
-- Indexes for table `escolas`
--
ALTER TABLE `escolas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Indexes for table `faltas`
--
ALTER TABLE `faltas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_falta` (`escola_id`,`aluno_id`,`disciplina_id`,`ano_letivo_id`,`data_falta`),
  ADD KEY `idx_faltas_escola` (`escola_id`),
  ADD KEY `idx_faltas_aluno` (`aluno_id`),
  ADD KEY `idx_faltas_turma` (`turma_id`),
  ADD KEY `idx_faltas_ano` (`ano_letivo_id`),
  ADD KEY `fk_faltas_disc` (`disciplina_id`),
  ADD KEY `fk_faltas_periodo` (`periodo_id`);

--
-- Indexes for table `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_matricula` (`escola_id`,`numero_matricula`),
  ADD UNIQUE KEY `uk_matricula_aluno` (`escola_id`,`aluno_id`,`turma_id`,`ano_letivo_id`),
  ADD KEY `idx_mat_escola` (`escola_id`),
  ADD KEY `idx_mat_aluno` (`aluno_id`),
  ADD KEY `idx_mat_turma` (`turma_id`),
  ADD KEY `idx_mat_ano` (`ano_letivo_id`);

--
-- Indexes for table `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nota` (`escola_id`,`aluno_id`,`disciplina_id`,`ano_letivo_id`,`periodo_id`,`tipo`),
  ADD KEY `idx_notas_escola` (`escola_id`),
  ADD KEY `idx_notas_aluno` (`aluno_id`),
  ADD KEY `idx_notas_disciplina` (`disciplina_id`),
  ADD KEY `idx_notas_professor` (`professor_id`),
  ADD KEY `idx_notas_ano` (`ano_letivo_id`),
  ADD KEY `fk_notas_periodo` (`periodo_id`);

--
-- Indexes for table `perfis`
--
ALTER TABLE `perfis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_periodo` (`escola_id`,`ano_letivo_id`,`ordem`),
  ADD KEY `idx_periodos_escola` (`escola_id`),
  ADD KEY `idx_periodos_ano` (`ano_letivo_id`);

--
-- Indexes for table `presencas`
--
ALTER TABLE `presencas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_presenca` (`escola_id`,`aula_id`,`aluno_id`),
  ADD KEY `idx_pres_escola` (`escola_id`),
  ADD KEY `idx_pres_aula` (`aula_id`),
  ADD KEY `idx_pres_aluno` (`aluno_id`);

--
-- Indexes for table `professor_turma_disciplina`
--
ALTER TABLE `professor_turma_disciplina`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_prof_turma_disc` (`escola_id`,`professor_id`,`turma_id`,`disciplina_id`,`ano_letivo_id`),
  ADD KEY `idx_ptd_escola` (`escola_id`),
  ADD KEY `idx_ptd_professor` (`professor_id`),
  ADD KEY `idx_ptd_turma` (`turma_id`),
  ADD KEY `idx_ptd_disciplina` (`disciplina_id`),
  ADD KEY `idx_ptd_ano` (`ano_letivo_id`),
  ADD KEY `fk_ptd_periodo` (`periodo_id`);

--
-- Indexes for table `responsaveis`
--
ALTER TABLE `responsaveis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `idx_resp_escola` (`escola_id`);

--
-- Indexes for table `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_turma` (`escola_id`,`ano_letivo_id`,`nome`,`turno`),
  ADD KEY `idx_turmas_escola` (`escola_id`),
  ADD KEY `idx_turmas_ano` (`ano_letivo_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `idx_usuarios_escola` (`escola_id`),
  ADD KEY `idx_usuarios_perfil` (`perfil_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aluno_responsavel`
--
ALTER TABLE `aluno_responsavel`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `anos_letivos`
--
ALTER TABLE `anos_letivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `boletins`
--
ALTER TABLE `boletins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `disciplinas`
--
ALTER TABLE `disciplinas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `escolas`
--
ALTER TABLE `escolas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faltas`
--
ALTER TABLE `faltas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perfis`
--
ALTER TABLE `perfis`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `presencas`
--
ALTER TABLE `presencas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `professor_turma_disciplina`
--
ALTER TABLE `professor_turma_disciplina`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `responsaveis`
--
ALTER TABLE `responsaveis`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aluno_responsavel`
--
ALTER TABLE `aluno_responsavel`
  ADD CONSTRAINT `fk_ar_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ar_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ar_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `responsaveis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `anos_letivos`
--
ALTER TABLE `anos_letivos`
  ADD CONSTRAINT `fk_anos_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_audit_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `fk_aulas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aulas_ptd` FOREIGN KEY (`professor_turma_disciplina_id`) REFERENCES `professor_turma_disciplina` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `boletins`
--
ALTER TABLE `boletins`
  ADD CONSTRAINT `fk_boletins_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_boletins_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_boletins_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD CONSTRAINT `fk_config_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD CONSTRAINT `fk_disc_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enderecos`
--
ALTER TABLE `enderecos`
  ADD CONSTRAINT `fk_end_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_end_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faltas`
--
ALTER TABLE `faltas`
  ADD CONSTRAINT `fk_faltas_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_faltas_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_faltas_disc` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_faltas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_faltas_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_letivos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_faltas_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `fk_mat_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mat_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mat_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mat_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `fk_notas_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notas_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notas_disc` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notas_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_letivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notas_prof` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  ADD CONSTRAINT `fk_periodos_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_periodos_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `presencas`
--
ALTER TABLE `presencas`
  ADD CONSTRAINT `fk_pres_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pres_aula` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pres_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `professor_turma_disciplina`
--
ALTER TABLE `professor_turma_disciplina`
  ADD CONSTRAINT `fk_ptd_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ptd_disc` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ptd_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ptd_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_letivos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ptd_prof` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ptd_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `responsaveis`
--
ALTER TABLE `responsaveis`
  ADD CONSTRAINT `fk_resp_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `turmas`
--
ALTER TABLE `turmas`
  ADD CONSTRAINT `fk_turmas_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turmas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_perfil` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- --------------------------------------------------------

--
-- Table structure for table `logs_auditoria`
--

CREATE TABLE IF NOT EXISTS `logs_auditoria` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `acao` varchar(100) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip_origem` varchar(45) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
