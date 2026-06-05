-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 06, 2026 at 12:47 AM
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
(1, 1, '2026', 1, NULL, NULL, '2026-05-29 16:55:55');

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

--
-- Dumping data for table `aulas`
--

INSERT INTO `aulas` (`id`, `escola_id`, `professor_turma_disciplina_id`, `data_aula`, `conteudo`, `observacao`, `criado_em`) VALUES
(1, 1, 12, '2026-05-30', NULL, NULL, '2026-05-30 17:24:51'),
(2, 1, 12, '2026-05-31', NULL, NULL, '2026-05-31 11:51:59'),
(3, 1, 12, '2026-06-01', NULL, NULL, '2026-06-01 11:42:01'),
(4, 1, 12, '2026-06-02', NULL, NULL, '2026-06-02 08:42:19'),
(5, 1, 12, '2026-06-03', NULL, NULL, '2026-06-02 08:42:26'),
(6, 1, 12, '2026-06-04', NULL, NULL, '2026-06-02 08:42:34'),
(7, 1, 12, '2026-04-05', NULL, NULL, '2026-06-03 18:32:50'),
(8, 1, 12, '2026-06-05', NULL, NULL, '2026-06-03 18:35:47'),
(9, 1, 15, '2026-06-05', NULL, NULL, '2026-06-05 09:51:41');

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
(11, 1, 'Portugues', NULL, 0, 1, '2026-05-29 16:55:55'),
(12, 1, 'Matematica', NULL, 0, 1, '2026-05-29 16:55:55'),
(13, 1, 'Historia', NULL, 0, 1, '2026-05-29 16:55:55'),
(14, 1, 'Geografia', NULL, 0, 1, '2026-05-29 16:55:56'),
(15, 1, 'Ciencias', NULL, 0, 1, '2026-05-29 16:55:56');

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
(1, 'SENAI Belo Horizonte', 'SENAI02', NULL, NULL, NULL, NULL, 'trimestral', '100', 'basico', NULL, 1, '2026-05-29 16:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `historico_desempenho`
--

CREATE TABLE `historico_desempenho` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `disciplina_id` int(10) UNSIGNED NOT NULL,
  `periodo_id` int(10) UNSIGNED NOT NULL,
  `media_final` decimal(5,2) DEFAULT 0.00,
  `total_faltas` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(25, 1, 8, 3, 1, 'MAT200', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(26, 1, 9, 3, 1, 'MAT201', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(27, 1, 10, 3, 1, 'MAT202', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(28, 1, 11, 3, 1, 'MAT203', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(29, 1, 12, 3, 1, 'MAT204', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(30, 1, 13, 3, 1, 'MAT205', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(31, 1, 14, 3, 1, 'MAT206', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(32, 1, 15, 3, 1, 'MAT207', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(33, 1, 16, 3, 1, 'MAT208', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(34, 1, 17, 3, 1, 'MAT209', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(35, 1, 18, 3, 1, 'MAT210', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(36, 1, 19, 3, 1, 'MAT211', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(37, 1, 20, 3, 1, 'MAT212', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(38, 1, 21, 3, 1, 'MAT213', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(39, 1, 22, 3, 1, 'MAT214', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(40, 1, 23, 3, 1, 'MAT215', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(41, 1, 24, 3, 1, 'MAT216', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(42, 1, 25, 3, 1, 'MAT217', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(43, 1, 26, 3, 1, 'MAT218', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56'),
(44, 1, 27, 3, 1, 'MAT219', '2026-01-10', 'ativa', NULL, '2026-05-29 16:55:56');

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
(1, 1, 8, 12, 4, 1, 8, 'prova', 30.00, NULL, '2026-05-31 08:53:32', '2026-06-03 18:42:56'),
(2, 1, 23, 12, 4, 1, 8, 'prova', 21.00, NULL, '2026-05-31 08:53:32', '2026-06-03 18:42:56'),
(3, 1, 11, 12, 4, 1, 8, 'prova', 19.00, NULL, '2026-05-31 08:53:32', '2026-06-03 18:42:56'),
(4, 1, 27, 12, 4, 1, 8, 'prova', 16.00, NULL, '2026-05-31 08:53:32', '2026-06-03 18:42:56'),
(5, 1, 16, 12, 4, 1, 8, 'prova', 18.00, NULL, '2026-05-31 08:53:32', '2026-06-03 18:42:56'),
(6, 1, 24, 12, 4, 1, 8, 'prova', 16.00, NULL, '2026-05-31 08:53:32', '2026-06-03 18:42:56'),
(7, 1, 25, 12, 4, 1, 8, 'prova', 19.00, NULL, '2026-05-31 08:53:32', '2026-06-03 18:42:56'),
(8, 1, 10, 12, 4, 1, 8, 'prova', 14.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(9, 1, 18, 12, 4, 1, 8, 'prova', 14.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(10, 1, 15, 12, 4, 1, 8, 'prova', 17.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(11, 1, 14, 12, 4, 1, 8, 'prova', 15.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(12, 1, 22, 12, 4, 1, 8, 'prova', 9.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(13, 1, 20, 12, 4, 1, 8, 'prova', 21.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(14, 1, 12, 12, 4, 1, 8, 'prova', 23.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(15, 1, 13, 12, 4, 1, 8, 'prova', 8.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(16, 1, 21, 12, 4, 1, 8, 'prova', 12.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(17, 1, 26, 12, 4, 1, 8, 'prova', 12.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(18, 1, 9, 12, 4, 1, 8, 'prova', 21.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(19, 1, 19, 12, 4, 1, 8, 'prova', 23.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(20, 1, 17, 12, 4, 1, 8, 'prova', 12.00, NULL, '2026-06-02 05:45:47', '2026-06-03 18:42:56'),
(21, 1, 8, 12, 4, 1, 9, 'prova', 27.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(22, 1, 23, 12, 4, 1, 9, 'prova', 17.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(23, 1, 11, 12, 4, 1, 9, 'prova', 20.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(24, 1, 27, 12, 4, 1, 9, 'prova', 15.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(25, 1, 16, 12, 4, 1, 9, 'prova', 19.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(26, 1, 10, 12, 4, 1, 9, 'prova', 17.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(27, 1, 18, 12, 4, 1, 9, 'prova', 12.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(28, 1, 15, 12, 4, 1, 9, 'prova', 16.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(29, 1, 14, 12, 4, 1, 9, 'prova', 16.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(30, 1, 22, 12, 4, 1, 9, 'prova', 11.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(31, 1, 20, 12, 4, 1, 9, 'prova', 18.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(32, 1, 12, 12, 4, 1, 9, 'prova', 19.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(33, 1, 13, 12, 4, 1, 9, 'prova', 16.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(34, 1, 24, 12, 4, 1, 9, 'prova', 18.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(35, 1, 21, 12, 4, 1, 9, 'prova', 17.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(36, 1, 26, 12, 4, 1, 9, 'prova', 11.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(37, 1, 9, 12, 4, 1, 9, 'prova', 17.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(38, 1, 19, 12, 4, 1, 9, 'prova', 16.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(39, 1, 25, 12, 4, 1, 9, 'prova', 17.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(40, 1, 17, 12, 4, 1, 9, 'prova', 19.00, NULL, '2026-06-03 15:42:56', '2026-06-03 18:42:56'),
(41, 1, 8, 11, 3, 1, 8, 'prova', 27.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(42, 1, 8, 11, 3, 1, 9, 'prova', 23.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(43, 1, 23, 11, 3, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(44, 1, 23, 11, 3, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(45, 1, 11, 11, 3, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(46, 1, 11, 11, 3, 1, 9, 'prova', 20.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(47, 1, 27, 11, 3, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(48, 1, 27, 11, 3, 1, 9, 'prova', 15.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(49, 1, 16, 11, 3, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(50, 1, 16, 11, 3, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(51, 1, 10, 11, 3, 1, 8, 'prova', 14.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(52, 1, 10, 11, 3, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(53, 1, 18, 11, 3, 1, 8, 'prova', 14.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(54, 1, 18, 11, 3, 1, 9, 'prova', 12.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(55, 1, 15, 11, 3, 1, 8, 'prova', 16.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(56, 1, 15, 11, 3, 1, 9, 'prova', 16.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(57, 1, 14, 11, 3, 1, 8, 'prova', 20.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(58, 1, 14, 11, 3, 1, 9, 'prova', 16.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(59, 1, 22, 11, 3, 1, 8, 'prova', 8.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(60, 1, 22, 11, 3, 1, 9, 'prova', 11.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(61, 1, 20, 11, 3, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:41:17', '2026-06-05 09:41:17'),
(62, 1, 20, 11, 3, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(63, 1, 12, 11, 3, 1, 8, 'prova', 23.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(64, 1, 12, 11, 3, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(65, 1, 13, 11, 3, 1, 8, 'prova', 8.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(66, 1, 13, 11, 3, 1, 9, 'prova', 16.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(67, 1, 24, 11, 3, 1, 8, 'prova', 16.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(68, 1, 24, 11, 3, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(69, 1, 21, 11, 3, 1, 8, 'prova', 12.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(70, 1, 21, 11, 3, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(71, 1, 26, 11, 3, 1, 8, 'prova', 12.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(72, 1, 26, 11, 3, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(73, 1, 9, 11, 3, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(74, 1, 9, 11, 3, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(75, 1, 19, 11, 3, 1, 8, 'prova', 23.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(76, 1, 19, 11, 3, 1, 9, 'prova', 16.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(77, 1, 25, 11, 3, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(78, 1, 25, 11, 3, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(79, 1, 17, 11, 3, 1, 8, 'prova', 12.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(80, 1, 17, 11, 3, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:41:18', '2026-06-05 09:41:18'),
(81, 1, 8, 13, 5, 1, 8, 'prova', 25.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(82, 1, 8, 13, 5, 1, 9, 'prova', 27.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(83, 1, 23, 13, 5, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(84, 1, 23, 13, 5, 1, 9, 'prova', 24.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(85, 1, 11, 13, 5, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(86, 1, 11, 13, 5, 1, 9, 'prova', 20.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(87, 1, 27, 13, 5, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(88, 1, 27, 13, 5, 1, 9, 'prova', 16.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(89, 1, 16, 13, 5, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(90, 1, 16, 13, 5, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(91, 1, 10, 13, 5, 1, 8, 'prova', 14.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(92, 1, 10, 13, 5, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(93, 1, 18, 13, 5, 1, 8, 'prova', 14.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(94, 1, 18, 13, 5, 1, 9, 'prova', 22.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(95, 1, 15, 13, 5, 1, 8, 'prova', 17.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(96, 1, 15, 13, 5, 1, 9, 'prova', 15.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(97, 1, 14, 13, 5, 1, 8, 'prova', 15.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(98, 1, 14, 13, 5, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(99, 1, 22, 13, 5, 1, 8, 'prova', 9.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(100, 1, 22, 13, 5, 1, 9, 'prova', 13.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(101, 1, 20, 13, 5, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(102, 1, 20, 13, 5, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(103, 1, 12, 13, 5, 1, 8, 'prova', 23.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(104, 1, 12, 13, 5, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(105, 1, 13, 13, 5, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(106, 1, 13, 13, 5, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(107, 1, 24, 13, 5, 1, 8, 'prova', 16.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(108, 1, 24, 13, 5, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(109, 1, 21, 13, 5, 1, 8, 'prova', 12.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(110, 1, 21, 13, 5, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(111, 1, 26, 13, 5, 1, 8, 'prova', 12.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(112, 1, 26, 13, 5, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(113, 1, 9, 13, 5, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(114, 1, 9, 13, 5, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(115, 1, 19, 13, 5, 1, 8, 'prova', 23.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(116, 1, 19, 13, 5, 1, 9, 'prova', 16.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(117, 1, 25, 13, 5, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(118, 1, 25, 13, 5, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(119, 1, 17, 13, 5, 1, 8, 'prova', 12.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(120, 1, 17, 13, 5, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:45:10', '2026-06-05 09:45:10'),
(121, 1, 8, 14, 6, 1, 8, 'prova', 28.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(122, 1, 8, 14, 6, 1, 9, 'prova', 25.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(123, 1, 23, 14, 6, 1, 8, 'prova', 27.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(124, 1, 23, 14, 6, 1, 9, 'prova', 24.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(125, 1, 11, 14, 6, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(126, 1, 11, 14, 6, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(127, 1, 27, 14, 6, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(128, 1, 27, 14, 6, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(129, 1, 16, 14, 6, 1, 8, 'prova', 20.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(130, 1, 16, 14, 6, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(131, 1, 10, 14, 6, 1, 8, 'prova', 16.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(132, 1, 10, 14, 6, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(133, 1, 18, 14, 6, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(134, 1, 18, 14, 6, 1, 9, 'prova', 24.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(135, 1, 15, 14, 6, 1, 8, 'prova', 17.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(136, 1, 15, 14, 6, 1, 9, 'prova', 20.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(137, 1, 14, 14, 6, 1, 8, 'prova', 16.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(138, 1, 14, 14, 6, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(139, 1, 22, 14, 6, 1, 8, 'prova', 17.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(140, 1, 22, 14, 6, 1, 9, 'prova', 23.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(141, 1, 20, 14, 6, 1, 8, 'prova', 23.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(142, 1, 20, 14, 6, 1, 9, 'prova', 20.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(143, 1, 12, 14, 6, 1, 8, 'prova', 24.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(144, 1, 12, 14, 6, 1, 9, 'prova', 21.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(145, 1, 13, 14, 6, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(146, 1, 13, 14, 6, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(147, 1, 24, 14, 6, 1, 8, 'prova', 22.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(148, 1, 24, 14, 6, 1, 9, 'prova', 22.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(149, 1, 21, 14, 6, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(150, 1, 21, 14, 6, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(151, 1, 26, 14, 6, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(152, 1, 26, 14, 6, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(153, 1, 9, 14, 6, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(154, 1, 9, 14, 6, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(155, 1, 19, 14, 6, 1, 8, 'prova', 14.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(156, 1, 19, 14, 6, 1, 9, 'prova', 15.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(157, 1, 25, 14, 6, 1, 8, 'prova', 20.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(158, 1, 25, 14, 6, 1, 9, 'prova', 23.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(159, 1, 17, 14, 6, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(160, 1, 17, 14, 6, 1, 9, 'prova', 21.00, NULL, '2026-06-05 06:50:59', '2026-06-05 09:50:59'),
(161, 1, 8, 15, 7, 1, 8, 'prova', 28.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(162, 1, 8, 15, 7, 1, 9, 'prova', 27.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(163, 1, 23, 15, 7, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(164, 1, 23, 15, 7, 1, 9, 'prova', 21.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(165, 1, 11, 15, 7, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(166, 1, 11, 15, 7, 1, 9, 'prova', 23.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(167, 1, 27, 15, 7, 1, 8, 'prova', 15.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(168, 1, 27, 15, 7, 1, 9, 'prova', 21.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(169, 1, 16, 15, 7, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(170, 1, 16, 15, 7, 1, 9, 'prova', 22.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(171, 1, 10, 15, 7, 1, 8, 'prova', 22.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(172, 1, 10, 15, 7, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(173, 1, 18, 15, 7, 1, 8, 'prova', 24.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(174, 1, 18, 15, 7, 1, 9, 'prova', 26.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(175, 1, 15, 15, 7, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(176, 1, 15, 15, 7, 1, 9, 'prova', 24.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(177, 1, 14, 15, 7, 1, 8, 'prova', 23.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(178, 1, 14, 15, 7, 1, 9, 'prova', 21.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(179, 1, 22, 15, 7, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(180, 1, 22, 15, 7, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(181, 1, 20, 15, 7, 1, 8, 'prova', 25.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(182, 1, 20, 15, 7, 1, 9, 'prova', 23.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(183, 1, 12, 15, 7, 1, 8, 'prova', 20.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(184, 1, 12, 15, 7, 1, 9, 'prova', 21.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(185, 1, 13, 15, 7, 1, 8, 'prova', 23.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(186, 1, 13, 15, 7, 1, 9, 'prova', 20.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(187, 1, 24, 15, 7, 1, 8, 'prova', 26.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(188, 1, 24, 15, 7, 1, 9, 'prova', 19.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(189, 1, 21, 15, 7, 1, 8, 'prova', 18.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(190, 1, 21, 15, 7, 1, 9, 'prova', 13.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(191, 1, 26, 15, 7, 1, 8, 'prova', 19.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(192, 1, 26, 15, 7, 1, 9, 'prova', 15.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(193, 1, 9, 15, 7, 1, 8, 'prova', 21.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(194, 1, 9, 15, 7, 1, 9, 'prova', 18.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(195, 1, 19, 15, 7, 1, 8, 'prova', 23.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(196, 1, 19, 15, 7, 1, 9, 'prova', 17.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(197, 1, 25, 15, 7, 1, 8, 'prova', 27.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(198, 1, 25, 15, 7, 1, 9, 'prova', 20.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(199, 1, 17, 15, 7, 1, 8, 'prova', 24.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01'),
(200, 1, 17, 15, 7, 1, 9, 'prova', 16.00, NULL, '2026-06-05 06:58:01', '2026-06-05 09:58:01');

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
(1, 'admin_supremo', 100, '2026-05-29 16:54:24'),
(2, 'admin', 80, '2026-05-29 16:54:24'),
(3, 'professor', 50, '2026-05-29 16:54:24'),
(4, 'aluno', 10, '2026-05-29 16:54:24');

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
(8, 1, 1, '1º Trimestre', '2026-01-01', '2026-04-30', 1, 1, '2026-05-29 16:55:55'),
(9, 1, 1, '2º Trimestre', '2026-05-01', '2026-08-31', 2, 1, '2026-05-29 16:55:55'),
(10, 1, 1, '3º Trimestre', '2026-09-01', '2026-12-31', 3, 1, '2026-05-29 16:55:55');

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

--
-- Dumping data for table `presencas`
--

INSERT INTO `presencas` (`id`, `escola_id`, `aula_id`, `aluno_id`, `status`, `observacao`, `criado_em`) VALUES
(1, 1, 4, 8, 'falta', NULL, '2026-06-02 08:42:23'),
(2, 1, 4, 23, 'presente', NULL, '2026-06-02 08:42:23'),
(3, 1, 4, 11, 'presente', NULL, '2026-06-02 08:42:23'),
(4, 1, 4, 27, 'presente', NULL, '2026-06-02 08:42:23'),
(5, 1, 4, 16, 'presente', NULL, '2026-06-02 08:42:23'),
(6, 1, 4, 10, 'presente', NULL, '2026-06-02 08:42:23'),
(7, 1, 4, 18, 'presente', NULL, '2026-06-02 08:42:23'),
(8, 1, 4, 15, 'presente', NULL, '2026-06-02 08:42:23'),
(9, 1, 4, 14, 'presente', NULL, '2026-06-02 08:42:23'),
(10, 1, 4, 22, 'falta', NULL, '2026-06-02 08:42:23'),
(11, 1, 4, 20, 'falta', NULL, '2026-06-02 08:42:23'),
(12, 1, 4, 12, 'presente', NULL, '2026-06-02 08:42:23'),
(13, 1, 4, 13, 'presente', NULL, '2026-06-02 08:42:23'),
(14, 1, 4, 24, 'presente', NULL, '2026-06-02 08:42:23'),
(15, 1, 4, 21, 'presente', NULL, '2026-06-02 08:42:23'),
(16, 1, 4, 26, 'presente', NULL, '2026-06-02 08:42:23'),
(17, 1, 4, 9, 'presente', NULL, '2026-06-02 08:42:23'),
(18, 1, 4, 19, 'presente', NULL, '2026-06-02 08:42:23'),
(19, 1, 4, 25, 'presente', NULL, '2026-06-02 08:42:23'),
(20, 1, 4, 17, 'presente', NULL, '2026-06-02 08:42:23'),
(21, 1, 5, 8, 'falta', NULL, '2026-06-02 08:42:29'),
(22, 1, 5, 23, 'presente', NULL, '2026-06-02 08:42:29'),
(23, 1, 5, 11, 'presente', NULL, '2026-06-02 08:42:29'),
(24, 1, 5, 27, 'presente', NULL, '2026-06-02 08:42:29'),
(25, 1, 5, 16, 'presente', NULL, '2026-06-02 08:42:29'),
(26, 1, 5, 10, 'presente', NULL, '2026-06-02 08:42:29'),
(27, 1, 5, 18, 'presente', NULL, '2026-06-02 08:42:29'),
(28, 1, 5, 15, 'presente', NULL, '2026-06-02 08:42:29'),
(29, 1, 5, 14, 'presente', NULL, '2026-06-02 08:42:29'),
(30, 1, 5, 22, 'falta', NULL, '2026-06-02 08:42:29'),
(31, 1, 5, 20, 'falta', NULL, '2026-06-02 08:42:29'),
(32, 1, 5, 12, 'presente', NULL, '2026-06-02 08:42:29'),
(33, 1, 5, 13, 'presente', NULL, '2026-06-02 08:42:29'),
(34, 1, 5, 24, 'presente', NULL, '2026-06-02 08:42:29'),
(35, 1, 5, 21, 'presente', NULL, '2026-06-02 08:42:29'),
(36, 1, 5, 26, 'presente', NULL, '2026-06-02 08:42:29'),
(37, 1, 5, 9, 'presente', NULL, '2026-06-02 08:42:29'),
(38, 1, 5, 19, 'presente', NULL, '2026-06-02 08:42:29'),
(39, 1, 5, 25, 'presente', NULL, '2026-06-02 08:42:29'),
(40, 1, 5, 17, 'presente', NULL, '2026-06-02 08:42:29'),
(41, 1, 6, 8, 'falta', NULL, '2026-06-02 08:42:36'),
(42, 1, 6, 23, 'presente', NULL, '2026-06-02 08:42:36'),
(43, 1, 6, 11, 'presente', NULL, '2026-06-02 08:42:36'),
(44, 1, 6, 27, 'presente', NULL, '2026-06-02 08:42:36'),
(45, 1, 6, 16, 'presente', NULL, '2026-06-02 08:42:36'),
(46, 1, 6, 10, 'presente', NULL, '2026-06-02 08:42:36'),
(47, 1, 6, 18, 'presente', NULL, '2026-06-02 08:42:36'),
(48, 1, 6, 15, 'presente', NULL, '2026-06-02 08:42:36'),
(49, 1, 6, 14, 'presente', NULL, '2026-06-02 08:42:36'),
(50, 1, 6, 22, 'falta', NULL, '2026-06-02 08:42:36'),
(51, 1, 6, 20, 'falta', NULL, '2026-06-02 08:42:36'),
(52, 1, 6, 12, 'presente', NULL, '2026-06-02 08:42:36'),
(53, 1, 6, 13, 'presente', NULL, '2026-06-02 08:42:36'),
(54, 1, 6, 24, 'presente', NULL, '2026-06-02 08:42:36'),
(55, 1, 6, 21, 'presente', NULL, '2026-06-02 08:42:36'),
(56, 1, 6, 26, 'presente', NULL, '2026-06-02 08:42:36'),
(57, 1, 6, 9, 'presente', NULL, '2026-06-02 08:42:36'),
(58, 1, 6, 19, 'presente', NULL, '2026-06-02 08:42:36'),
(59, 1, 6, 25, 'presente', NULL, '2026-06-02 08:42:36'),
(60, 1, 6, 17, 'presente', NULL, '2026-06-02 08:42:36'),
(61, 1, 7, 8, 'falta', NULL, '2026-06-03 18:32:53'),
(62, 1, 7, 23, 'presente', NULL, '2026-06-03 18:32:53'),
(63, 1, 7, 11, 'presente', NULL, '2026-06-03 18:32:53'),
(64, 1, 7, 27, 'presente', NULL, '2026-06-03 18:32:53'),
(65, 1, 7, 16, 'presente', NULL, '2026-06-03 18:32:53'),
(66, 1, 7, 10, 'presente', NULL, '2026-06-03 18:32:53'),
(67, 1, 7, 18, 'presente', NULL, '2026-06-03 18:32:53'),
(68, 1, 7, 15, 'presente', NULL, '2026-06-03 18:32:53'),
(69, 1, 7, 14, 'presente', NULL, '2026-06-03 18:32:53'),
(70, 1, 7, 22, 'presente', NULL, '2026-06-03 18:32:53'),
(71, 1, 7, 20, 'presente', NULL, '2026-06-03 18:32:53'),
(72, 1, 7, 12, 'presente', NULL, '2026-06-03 18:32:53'),
(73, 1, 7, 13, 'presente', NULL, '2026-06-03 18:32:53'),
(74, 1, 7, 24, 'presente', NULL, '2026-06-03 18:32:53'),
(75, 1, 7, 21, 'presente', NULL, '2026-06-03 18:32:53'),
(76, 1, 7, 26, 'presente', NULL, '2026-06-03 18:32:53'),
(77, 1, 7, 9, 'presente', NULL, '2026-06-03 18:32:53'),
(78, 1, 7, 19, 'presente', NULL, '2026-06-03 18:32:53'),
(79, 1, 7, 25, 'presente', NULL, '2026-06-03 18:32:53'),
(80, 1, 7, 17, 'presente', NULL, '2026-06-03 18:32:53'),
(81, 1, 3, 8, 'presente', NULL, '2026-06-03 18:35:36'),
(82, 1, 3, 23, 'presente', NULL, '2026-06-03 18:35:36'),
(83, 1, 3, 11, 'presente', NULL, '2026-06-03 18:35:36'),
(84, 1, 3, 27, 'presente', NULL, '2026-06-03 18:35:36'),
(85, 1, 3, 16, 'presente', NULL, '2026-06-03 18:35:36'),
(86, 1, 3, 10, 'presente', NULL, '2026-06-03 18:35:36'),
(87, 1, 3, 18, 'presente', NULL, '2026-06-03 18:35:36'),
(88, 1, 3, 15, 'presente', NULL, '2026-06-03 18:35:36'),
(89, 1, 3, 14, 'presente', NULL, '2026-06-03 18:35:36'),
(90, 1, 3, 22, 'falta', NULL, '2026-06-03 18:35:36'),
(91, 1, 3, 20, 'falta', NULL, '2026-06-03 18:35:36'),
(92, 1, 3, 12, 'presente', NULL, '2026-06-03 18:35:36'),
(93, 1, 3, 13, 'presente', NULL, '2026-06-03 18:35:36'),
(94, 1, 3, 24, 'presente', NULL, '2026-06-03 18:35:36'),
(95, 1, 3, 21, 'presente', NULL, '2026-06-03 18:35:36'),
(96, 1, 3, 26, 'presente', NULL, '2026-06-03 18:35:36'),
(97, 1, 3, 9, 'presente', NULL, '2026-06-03 18:35:36'),
(98, 1, 3, 19, 'presente', NULL, '2026-06-03 18:35:36'),
(99, 1, 3, 25, 'presente', NULL, '2026-06-03 18:35:36'),
(100, 1, 3, 17, 'presente', NULL, '2026-06-03 18:35:36'),
(101, 1, 8, 8, 'presente', NULL, '2026-06-03 18:35:50'),
(102, 1, 8, 23, 'presente', NULL, '2026-06-03 18:35:50'),
(103, 1, 8, 11, 'presente', NULL, '2026-06-03 18:35:50'),
(104, 1, 8, 27, 'presente', NULL, '2026-06-03 18:35:50'),
(105, 1, 8, 16, 'presente', NULL, '2026-06-03 18:35:50'),
(106, 1, 8, 10, 'presente', NULL, '2026-06-03 18:35:50'),
(107, 1, 8, 18, 'presente', NULL, '2026-06-03 18:35:50'),
(108, 1, 8, 15, 'presente', NULL, '2026-06-03 18:35:50'),
(109, 1, 8, 14, 'presente', NULL, '2026-06-03 18:35:50'),
(110, 1, 8, 22, 'falta', NULL, '2026-06-03 18:35:50'),
(111, 1, 8, 20, 'falta', NULL, '2026-06-03 18:35:50'),
(112, 1, 8, 12, 'presente', NULL, '2026-06-03 18:35:50'),
(113, 1, 8, 13, 'presente', NULL, '2026-06-03 18:35:50'),
(114, 1, 8, 24, 'presente', NULL, '2026-06-03 18:35:50'),
(115, 1, 8, 21, 'presente', NULL, '2026-06-03 18:35:50'),
(116, 1, 8, 26, 'presente', NULL, '2026-06-03 18:35:50'),
(117, 1, 8, 9, 'presente', NULL, '2026-06-03 18:35:50'),
(118, 1, 8, 19, 'presente', NULL, '2026-06-03 18:35:50'),
(119, 1, 8, 25, 'presente', NULL, '2026-06-03 18:35:50'),
(120, 1, 8, 17, 'presente', NULL, '2026-06-03 18:35:50'),
(121, 1, 9, 8, 'presente', NULL, '2026-06-05 09:51:45'),
(122, 1, 9, 23, 'presente', NULL, '2026-06-05 09:51:45'),
(123, 1, 9, 11, 'presente', NULL, '2026-06-05 09:51:45'),
(124, 1, 9, 27, 'presente', NULL, '2026-06-05 09:51:45'),
(125, 1, 9, 16, 'presente', NULL, '2026-06-05 09:51:45'),
(126, 1, 9, 10, 'presente', NULL, '2026-06-05 09:51:45'),
(127, 1, 9, 18, 'presente', NULL, '2026-06-05 09:51:45'),
(128, 1, 9, 15, 'presente', NULL, '2026-06-05 09:51:45'),
(129, 1, 9, 14, 'presente', NULL, '2026-06-05 09:51:45'),
(130, 1, 9, 22, 'presente', NULL, '2026-06-05 09:51:45'),
(131, 1, 9, 20, 'presente', NULL, '2026-06-05 09:51:45'),
(132, 1, 9, 12, 'presente', NULL, '2026-06-05 09:51:45'),
(133, 1, 9, 13, 'presente', NULL, '2026-06-05 09:51:45'),
(134, 1, 9, 24, 'presente', NULL, '2026-06-05 09:51:45'),
(135, 1, 9, 21, 'presente', NULL, '2026-06-05 09:51:45'),
(136, 1, 9, 26, 'presente', NULL, '2026-06-05 09:51:45'),
(137, 1, 9, 9, 'presente', NULL, '2026-06-05 09:51:45'),
(138, 1, 9, 19, 'presente', NULL, '2026-06-05 09:51:45'),
(139, 1, 9, 25, 'presente', NULL, '2026-06-05 09:51:45'),
(140, 1, 9, 17, 'presente', NULL, '2026-06-05 09:51:45');

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
(11, 1, 3, 3, 11, 1, NULL, 1, '2026-05-29 16:55:55'),
(12, 1, 4, 3, 12, 1, NULL, 1, '2026-05-29 16:55:55'),
(13, 1, 5, 3, 13, 1, NULL, 1, '2026-05-29 16:55:56'),
(14, 1, 6, 3, 14, 1, NULL, 1, '2026-05-29 16:55:56'),
(15, 1, 7, 3, 15, 1, NULL, 1, '2026-05-29 16:55:56');

-- --------------------------------------------------------

--
-- Table structure for table `relatorios_alunos`
--

CREATE TABLE `relatorios_alunos` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED DEFAULT NULL,
  `professor_id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `conteudo` text NOT NULL,
  `tipo` enum('professor','ia') NOT NULL DEFAULT 'professor',
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
  `turno` enum('manhã','tarde','noite','integral') NOT NULL DEFAULT 'manhã',
  `capacidade` int(11) NOT NULL DEFAULT 40,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `turmas`
--

INSERT INTO `turmas` (`id`, `escola_id`, `ano_letivo_id`, `nome`, `serie`, `turno`, `capacidade`, `ativo`, `criado_em`) VALUES
(3, 1, 1, 'DDS03', '1º Ano', 'tarde', 40, 1, '2026-05-29 16:55:55');

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
(1, NULL, 1, 'Admin Supremo', 'supremo@adm.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:54:24'),
(2, 1, 2, 'Amanda Costa', 'amanda.costa@admin.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:55'),
(3, 1, 3, 'Pedro Lima', 'pedro.lima78@prof.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:55'),
(4, 1, 3, 'Samuel Lopes', 'samuel.lopes78@prof.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:55'),
(5, 1, 3, 'Lorena Martins', 'lorena.martins65@prof.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(6, 1, 3, 'Beatriz Fernandes', 'beatriz.fernandes57@prof.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(7, 1, 3, 'Giovanna Lopes', 'giovanna.lopes37@prof.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(8, 1, 4, 'Arthur Gomes', 'arthur.gomes867@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(9, 1, 4, 'Rafael Alves', 'rafael.alves537@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(10, 1, 4, 'Enzo Rodrigues', 'enzo.rodrigues368@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(11, 1, 4, 'Daniel Lima', 'daniel.lima402@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(12, 1, 4, 'Julia Costa', 'julia.costa498@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(13, 1, 4, 'Lorena Pereira', 'lorena.pereira899@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(14, 1, 4, 'Gustavo Ferreira', 'gustavo.ferreira332@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(15, 1, 4, 'Guilherme Ferreira', 'guilherme.ferreira201@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(16, 1, 4, 'Enzo Martins', 'enzo.martins957@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(17, 1, 4, 'Vitor Souza', 'vitor.souza777@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(18, 1, 4, 'Giovanna Fernandes', 'giovanna.fernandes292@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(19, 1, 4, 'Samuel Silva', 'samuel.silva998@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(20, 1, 4, 'Joao Lopes', 'joao.lopes387@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(21, 1, 4, 'Manuela Santos', 'manuela.santos673@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(22, 1, 4, 'Joao Gomes', 'joao.gomes374@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(23, 1, 4, 'Bruno Alves', 'bruno.alves433@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(24, 1, 4, 'Luiza Costa', 'luiza.costa629@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(25, 1, 4, 'Samuel Vieira', 'samuel.vieira776@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(26, 1, 4, 'Pedro Soares', 'pedro.soares745@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56'),
(27, 1, 4, 'Daniel Rodrigues', 'daniel.rodrigues290@aluno.edu.com', '$2y$10$B4kI6ogrWg.R/M7ZHu2LWuN1eFn9n7iCqr8UTqri13rlEH4n7tkHm', NULL, NULL, NULL, NULL, 1, '2026-05-29 16:55:56');

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
-- Indexes for table `historico_desempenho`
--
ALTER TABLE `historico_desempenho`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_aluno_disc_periodo` (`aluno_id`,`disciplina_id`,`periodo_id`),
  ADD KEY `idx_escola` (`escola_id`),
  ADD KEY `idx_periodo` (`periodo_id`);

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
-- Indexes for table `relatorios_alunos`
--
ALTER TABLE `relatorios_alunos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rel_escola` (`escola_id`),
  ADD KEY `fk_rel_aluno` (`aluno_id`),
  ADD KEY `fk_rel_prof` (`professor_id`),
  ADD KEY `fk_rel_turma` (`turma_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `escolas`
--
ALTER TABLE `escolas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `historico_desempenho`
--
ALTER TABLE `historico_desempenho`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `perfis`
--
ALTER TABLE `perfis`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `presencas`
--
ALTER TABLE `presencas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `professor_turma_disciplina`
--
ALTER TABLE `professor_turma_disciplina`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `relatorios_alunos`
--
ALTER TABLE `relatorios_alunos`
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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
-- Constraints for table `relatorios_alunos`
--
ALTER TABLE `relatorios_alunos`
  ADD CONSTRAINT `fk_rel_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rel_escola` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rel_prof` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rel_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

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
