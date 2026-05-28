-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 28, 2026 at 05:26 PM
-- Server version: 10.4.34-MariaDB
-- PHP Version: 8.2.29

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
(10, 10, '2026', 1, NULL, NULL, '2026-05-28 14:07:14'),
(20, 20, '2026', 1, NULL, NULL, '2026-05-28 14:07:14');

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

--
-- Dumping data for table `disciplinas`
--

INSERT INTO `disciplinas` (`id`, `escola_id`, `nome`, `codigo`, `carga_horaria`, `ativo`, `criado_em`) VALUES
(101, 10, 'Matemática', 'MAT', 0, 1, '2026-05-28 14:07:14'),
(102, 10, 'Português', 'POR', 0, 1, '2026-05-28 14:07:14'),
(103, 10, 'História', 'HIS', 0, 1, '2026-05-28 14:07:14'),
(104, 10, 'Geografia', 'GEO', 0, 1, '2026-05-28 14:07:14'),
(105, 10, 'Ciências', 'CIE', 0, 1, '2026-05-28 14:07:14'),
(201, 20, 'Matemática', 'MAT', 0, 1, '2026-05-28 14:07:14'),
(202, 20, 'Português', 'POR', 0, 1, '2026-05-28 14:07:14'),
(203, 20, 'Física', 'FIS', 0, 1, '2026-05-28 14:07:14'),
(204, 20, 'Química', 'QUI', 0, 1, '2026-05-28 14:07:14'),
(205, 20, 'Biologia', 'BIO', 0, 1, '2026-05-28 14:07:14');

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
  `tipo_periodo` enum('bimestral','trimestral','semestral') DEFAULT 'bimestral',
  `escala_nota` enum('10','100') DEFAULT '10',
  `plano` enum('basico','pro','premium') DEFAULT 'basico',
  `expiracao` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `escolas`
--

INSERT INTO `escolas` (`id`, `nome`, `codigo`, `cnpj`, `cidade`, `estado`, `logo`, `tipo_periodo`, `escala_nota`, `plano`, `expiracao`, `ativo`, `criado_em`) VALUES
(10, 'Colégio Santa Maria', 'CSM01', '10.200.300/0001-10', 'Vitória', 'ES', NULL, 'bimestral', '10', 'basico', NULL, 1, '2026-05-28 14:07:14'),
(20, 'Instituto Dom Bosco', 'IDB02', '20.300.400/0001-20', 'Belo Horizonte', 'MG', NULL, 'trimestral', '100', 'basico', NULL, 1, '2026-05-28 14:07:14');

-- --------------------------------------------------------

--
-- Table structure for table `logs_auditoria`
--

CREATE TABLE `logs_auditoria` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED DEFAULT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `tabela` varchar(100) DEFAULT NULL,
  `registro_id` int(10) UNSIGNED DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
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

--
-- Dumping data for table `matriculas`
--

INSERT INTO `matriculas` (`id`, `escola_id`, `aluno_id`, `turma_id`, `ano_letivo_id`, `numero_matricula`, `data_matricula`, `status`, `observacao`, `criado_em`) VALUES
(1, 10, 1201, 101, 10, 'MAT1001', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(2, 10, 1202, 101, 10, 'MAT1002', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(3, 10, 1203, 101, 10, 'MAT1003', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(4, 10, 1204, 101, 10, 'MAT1004', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(5, 10, 1205, 101, 10, 'MAT1005', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(6, 10, 1206, 101, 10, 'MAT1006', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(7, 10, 1207, 101, 10, 'MAT1007', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(8, 10, 1208, 101, 10, 'MAT1008', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(9, 10, 1209, 101, 10, 'MAT1009', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(10, 10, 1210, 101, 10, 'MAT1010', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(11, 10, 1211, 101, 10, 'MAT1011', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(12, 10, 1212, 101, 10, 'MAT1012', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(13, 10, 1213, 101, 10, 'MAT1013', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(14, 10, 1214, 101, 10, 'MAT1014', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(15, 10, 1215, 101, 10, 'MAT1015', '2026-01-10', 'ativa', NULL, '2026-05-28 14:07:14'),
(16, 20, 2201, 201, 20, 'IDB2001', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(17, 20, 2202, 201, 20, 'IDB2002', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(18, 20, 2203, 201, 20, 'IDB2003', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(19, 20, 2204, 201, 20, 'IDB2004', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(20, 20, 2205, 201, 20, 'IDB2005', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(21, 20, 2206, 201, 20, 'IDB2006', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(22, 20, 2207, 201, 20, 'IDB2007', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(23, 20, 2208, 201, 20, 'IDB2008', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(24, 20, 2209, 201, 20, 'IDB2009', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(25, 20, 2210, 201, 20, 'IDB2010', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(26, 20, 2211, 201, 20, 'IDB2011', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(27, 20, 2212, 201, 20, 'IDB2012', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(28, 20, 2213, 201, 20, 'IDB2013', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(29, 20, 2214, 201, 20, 'IDB2014', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15'),
(30, 20, 2215, 201, 20, 'IDB2015', '2026-01-12', 'ativa', NULL, '2026-05-28 14:07:15');

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
  `tipo` enum('prova','trabalho','recuperacao','final','media') NOT NULL DEFAULT 'prova',
  `nota` decimal(4,2) NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `data_lancamento` datetime NOT NULL DEFAULT current_timestamp(),
  `data_ultima_edicao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notas`
--

INSERT INTO `notas` (`id`, `escola_id`, `aluno_id`, `disciplina_id`, `professor_id`, `ano_letivo_id`, `periodo_id`, `tipo`, `nota`, `observacao`, `data_lancamento`, `data_ultima_edicao`) VALUES
(1, 10, 1201, 101, 1101, 10, 1, 'prova', 2.40, NULL, '2026-05-28 12:25:03', '2026-05-28 15:25:03');

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
(1, 'admin_supremo', 100, '2026-05-28 13:42:48'),
(2, 'admin', 80, '2026-05-28 13:42:48'),
(3, 'professor', 50, '2026-05-28 13:42:48'),
(4, 'aluno', 10, '2026-05-28 13:42:48');

-- --------------------------------------------------------

--
-- Table structure for table `periodos_letivos`
--

CREATE TABLE `periodos_letivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `ano_letivo_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(30) NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `ordem` tinyint(4) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `periodos_letivos`
--

INSERT INTO `periodos_letivos` (`id`, `escola_id`, `ano_letivo_id`, `nome`, `data_inicio`, `data_fim`, `ordem`, `ativo`, `criado_em`) VALUES
(1, 10, 10, '1º Bimestre', NULL, NULL, 1, 1, '2026-05-28 14:07:14'),
(2, 10, 10, '2º Bimestre', NULL, NULL, 2, 1, '2026-05-28 14:07:14'),
(3, 10, 10, '3º Bimestre', NULL, NULL, 3, 1, '2026-05-28 14:07:14'),
(4, 10, 10, '4º Bimestre', NULL, NULL, 4, 1, '2026-05-28 14:07:14'),
(5, 20, 20, '1º Trimestre', NULL, NULL, 1, 1, '2026-05-28 14:07:14'),
(6, 20, 20, '2º Trimestre', NULL, NULL, 2, 1, '2026-05-28 14:07:14'),
(7, 20, 20, '3º Trimestre', NULL, NULL, 3, 1, '2026-05-28 14:07:14');

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

--
-- Dumping data for table `professor_turma_disciplina`
--

INSERT INTO `professor_turma_disciplina` (`id`, `escola_id`, `professor_id`, `turma_id`, `disciplina_id`, `ano_letivo_id`, `periodo_id`, `ativo`, `criado_em`) VALUES
(1, 10, 1101, 101, 101, 10, NULL, 1, '2026-05-28 14:07:14'),
(2, 10, 1101, 101, 105, 10, NULL, 1, '2026-05-28 14:07:14'),
(3, 10, 1102, 101, 102, 10, NULL, 1, '2026-05-28 14:07:14'),
(4, 10, 1102, 101, 103, 10, NULL, 1, '2026-05-28 14:07:14'),
(5, 10, 1102, 101, 104, 10, NULL, 1, '2026-05-28 14:07:14'),
(6, 20, 2101, 201, 201, 20, NULL, 1, '2026-05-28 14:07:14'),
(7, 20, 2101, 201, 203, 20, NULL, 1, '2026-05-28 14:07:14'),
(8, 20, 2101, 201, 204, 20, NULL, 1, '2026-05-28 14:07:14'),
(9, 20, 2102, 201, 202, 20, NULL, 1, '2026-05-28 14:07:14'),
(10, 20, 2102, 201, 205, 20, NULL, 1, '2026-05-28 14:07:14');

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
  `turno` enum('manhã','tarde','noite','integral') NOT NULL DEFAULT 'manhã',
  `capacidade` int(11) NOT NULL DEFAULT 40,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `turmas`
--

INSERT INTO `turmas` (`id`, `escola_id`, `ano_letivo_id`, `nome`, `serie`, `turno`, `capacidade`, `ativo`, `criado_em`) VALUES
(101, 10, 10, '9º Ano A', '9ª Série', 'manhã', 40, 1, '2026-05-28 14:07:14'),
(201, 20, 20, '3º Ano Médio', '3ª Série', 'tarde', 40, 1, '2026-05-28 14:07:14');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED DEFAULT NULL,
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
(1, NULL, 1, 'Administrador Supremo', 'supremo@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 1, '2026-05-28 13:42:48'),
(1001, 10, 2, 'Ricardo Oliveira', 'admin.santamaria@admin.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1101, 10, 3, 'Carlos Eduardo Santos', 'carlos.prof@prof.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1102, 10, 3, 'Fernanda Souza', 'fernanda.prof@prof.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1201, 10, 4, 'Alice Ferreira Lima', 'alice.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1202, 10, 4, 'Bruno Henrique Costa', 'bruno.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1203, 10, 4, 'Camila Rocha', 'camila.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1204, 10, 4, 'Daniel Alves', 'daniel.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1205, 10, 4, 'Eduarda Mendes', 'eduarda.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1206, 10, 4, 'Felipe Augusto', 'felipe.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1207, 10, 4, 'Gabriela Neves', 'gabriela.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1208, 10, 4, 'Heitor Braga', 'heitor.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1209, 10, 4, 'Isadora Martins', 'isadora.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1210, 10, 4, 'João Pedro Silva', 'joao.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1211, 10, 4, 'Kaiky Oliveira', 'kaiky.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1212, 10, 4, 'Larissa Vieira', 'larissa.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1213, 10, 4, 'Murilo Gomes', 'murilo.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1214, 10, 4, 'Nicole Castro', 'nicole.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(1215, 10, 4, 'Otávio Rezende', 'otavio.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2001, 20, 2, 'Patrícia Lima', 'admin.dombosco@admin.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2101, 20, 3, 'Roberto Carlos', 'roberto.prof@prof.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2102, 20, 3, 'Mariana Peixoto', 'mariana.prof@prof.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2201, 20, 4, 'André Luiz Silva', 'andre.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2202, 20, 4, 'Beatriz Xavier', 'beatriz.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2203, 20, 4, 'Caio Moreira', 'caio.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2204, 20, 4, 'Débora Freitas', 'debora.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2205, 20, 4, 'Elias Júnio', 'elias.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2206, 20, 4, 'Flávia Alessandra', 'flavia.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2207, 20, 4, 'Gustavo Henrique', 'gustavo.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2208, 20, 4, 'Heloísa Helena', 'heloisa.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2209, 20, 4, 'Igor Guimarães', 'igor.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2210, 20, 4, 'Júlia Roberta', 'julia.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2211, 20, 4, 'Kevin Willian', 'kevin.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2212, 20, 4, 'Luana Piovani', 'luana.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2213, 20, 4, 'Matheus Solano', 'matheus.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2214, 20, 4, 'Nayara Azevedo', 'nayara.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14'),
(2215, 20, 4, 'Paulo Gustavo', 'paulo.aluno@aluno.edu.com', '$2y$10$RyHUoJhPqKjFGqsB5OzmVeizKYGYuRTd4JWP9cHn7IHDkPZ1ZewaW', NULL, NULL, NULL, NULL, 1, '2026-05-28 14:07:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aluno_responsavel`
--
ALTER TABLE `aluno_responsavel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_aluno_resp` (`escola_id`,`aluno_id`,`responsavel_id`),
  ADD KEY `fk_ar_aluno` (`aluno_id`),
  ADD KEY `fk_ar_resp` (`responsavel_id`);

--
-- Indexes for table `anos_letivos`
--
ALTER TABLE `anos_letivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ano_escola` (`escola_id`,`ano`);

--
-- Indexes for table `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aulas_escola` (`escola_id`),
  ADD KEY `fk_aulas_ptd` (`professor_turma_disciplina_id`);

--
-- Indexes for table `boletins`
--
ALTER TABLE `boletins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bol_escola` (`escola_id`),
  ADD KEY `fk_bol_aluno` (`aluno_id`),
  ADD KEY `fk_bol_ano` (`ano_letivo_id`);

--
-- Indexes for table `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_conf_escola` (`escola_id`);

--
-- Indexes for table `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_disciplinas_escola` (`escola_id`);

--
-- Indexes for table `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_end_escola` (`escola_id`),
  ADD KEY `fk_end_usuario` (`usuario_id`);

--
-- Indexes for table `escolas`
--
ALTER TABLE `escolas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_logs_escola` (`escola_id`),
  ADD KEY `fk_logs_usuario` (`usuario_id`);

--
-- Indexes for table `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_mat_aluno_ano` (`aluno_id`,`ano_letivo_id`),
  ADD KEY `fk_mat_escola` (`escola_id`),
  ADD KEY `fk_mat_turma` (`turma_id`),
  ADD KEY `fk_mat_ano` (`ano_letivo_id`);

--
-- Indexes for table `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nota_unica` (`aluno_id`,`disciplina_id`,`periodo_id`,`ano_letivo_id`,`escola_id`,`tipo`),
  ADD KEY `fk_notas_escola` (`escola_id`),
  ADD KEY `fk_notas_disc` (`disciplina_id`),
  ADD KEY `fk_notas_prof` (`professor_id`),
  ADD KEY `fk_notas_ano` (`ano_letivo_id`),
  ADD KEY `fk_notas_periodo` (`periodo_id`);

--
-- Indexes for table `perfis`
--
ALTER TABLE `perfis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_periodos_escola` (`escola_id`),
  ADD KEY `fk_periodos_ano` (`ano_letivo_id`);

--
-- Indexes for table `presencas`
--
ALTER TABLE `presencas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_pres_aula_aluno` (`aula_id`,`aluno_id`),
  ADD KEY `fk_pres_escola` (`escola_id`),
  ADD KEY `fk_pres_aluno` (`aluno_id`);

--
-- Indexes for table `professor_turma_disciplina`
--
ALTER TABLE `professor_turma_disciplina`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ptd_vinculo` (`professor_id`,`turma_id`,`disciplina_id`,`ano_letivo_id`),
  ADD KEY `fk_ptd_escola` (`escola_id`),
  ADD KEY `fk_ptd_turma` (`turma_id`),
  ADD KEY `fk_ptd_disc` (`disciplina_id`),
  ADD KEY `fk_ptd_ano` (`ano_letivo_id`);

--
-- Indexes for table `responsaveis`
--
ALTER TABLE `responsaveis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_resp_escola` (`escola_id`);

--
-- Indexes for table `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_turmas_escola` (`escola_id`),
  ADD KEY `fk_turmas_ano` (`ano_letivo_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unico` (`email`),
  ADD KEY `fk_usuarios_perfil` (`perfil_id`),
  ADD KEY `fk_usuarios_escola` (`escola_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disciplinas`
--
ALTER TABLE `disciplinas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `escolas`
--
ALTER TABLE `escolas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `perfis`
--
ALTER TABLE `perfis`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `presencas`
--
ALTER TABLE `presencas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `professor_turma_disciplina`
--
ALTER TABLE `professor_turma_disciplina`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `responsaveis`
--
ALTER TABLE `responsaveis`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2216;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aluno_responsavel`
--
ALTER TABLE `aluno_responsavel`
  ADD CONSTRAINT `fk_ar_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ar_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ar_resp` FOREIGN KEY (`responsavel_id`) REFERENCES `responsaveis` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `anos_letivos`
--
ALTER TABLE `anos_letivos`
  ADD CONSTRAINT `fk_anos_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `fk_aulas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_aulas_ptd` FOREIGN KEY (`professor_turma_disciplina_id`) REFERENCES `professor_turma_disciplina` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `boletins`
--
ALTER TABLE `boletins`
  ADD CONSTRAINT `fk_bol_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bol_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bol_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD CONSTRAINT `fk_conf_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD CONSTRAINT `fk_disciplinas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enderecos`
--
ALTER TABLE `enderecos`
  ADD CONSTRAINT `fk_end_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_end_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD CONSTRAINT `fk_logs_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `fk_mat_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mat_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mat_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mat_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `fk_notas_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notas_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notas_disc` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notas_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_letivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notas_prof` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  ADD CONSTRAINT `fk_periodos_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_periodos_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `presencas`
--
ALTER TABLE `presencas`
  ADD CONSTRAINT `fk_pres_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pres_aula` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pres_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `professor_turma_disciplina`
--
ALTER TABLE `professor_turma_disciplina`
  ADD CONSTRAINT `fk_ptd_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ptd_disc` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ptd_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ptd_prof` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ptd_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `responsaveis`
--
ALTER TABLE `responsaveis`
  ADD CONSTRAINT `fk_resp_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `turmas`
--
ALTER TABLE `turmas`
  ADD CONSTRAINT `fk_turmas_ano` FOREIGN KEY (`ano_letivo_id`) REFERENCES `anos_letivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_turmas_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuarios_perfil` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
