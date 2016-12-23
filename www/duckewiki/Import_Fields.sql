-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 03, 2016 at 09:30 AM
-- Server version: 5.5.49-0+deb8u1
-- PHP Version: 5.6.20-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ppsp`
--

-- --------------------------------------------------------

--
-- Table structure for table `Import_Fields`
--

CREATE TABLE IF NOT EXISTS `Import_Fields` (
`id` int(10) NOT NULL,
  `BRAHMS` text COLLATE utf8_unicode_ci,
  `CLASS` text COLLATE utf8_unicode_ci,
  `ORDEM` text COLLATE utf8_unicode_ci,
  `DEFINICAO` text COLLATE utf8_unicode_ci,
  `FieldsToPut` text COLLATE utf8_unicode_ci,
  `NamesToMatch` text COLLATE utf8_unicode_ci,
  `TabelaParaPor` text COLLATE utf8_unicode_ci,
  `LocalityFields` int(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Import_Fields`
--

INSERT INTO `Import_Fields` (`BRAHMS`, `CLASS`, `ORDEM`, `DEFINICAO`, `FieldsToPut`, `NamesToMatch`, `TabelaParaPor`, `LocalityFields`) VALUES ('BIBKEY', 'Genérico', '3.5', 'Bibkey da referência bibliográfica - para variáveis de usuário', NULL, NULL, 'Especimenes;Plantas', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Import_Fields`
--
ALTER TABLE `Import_Fields`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Import_Fields`
--
ALTER TABLE `Import_Fields`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=54;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
