-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 26, 2021 at 11:17 PM
-- Server version: 8.0.21
-- PHP Version: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lanternc_uberrapsy`
--

-- --------------------------------------------------------

--
-- Table structure for table `list`
--

DROP TABLE IF EXISTS `list`;
CREATE TABLE IF NOT EXISTS `list` (
  `ListId` int NOT NULL AUTO_INCREMENT,
  `ListUrl` varchar(255) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `ListName` varchar(50) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `ListDesc` text CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `ListCreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ListActive` tinyint(1) NOT NULL,
  PRIMARY KEY (`ListId`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Dumping data for table `list`
--

INSERT INTO `list` (`ListId`, `ListUrl`, `ListName`, `ListDesc`, `ListCreatedAt`, `ListActive`) VALUES
(4, 'PLkIbfiOcITXpSEuD0Gwr6Fz_WYZs781lE', 'Uber Rapsy - Akademia S4', 'Akademia 4', '2021-09-16 15:13:01', 1),
(5, 'PLkIbfiOcITXrwOxl2z1w6GmDBaTomytiM', 'Uber Rapsy - Główna Lista', 'Uber', '2021-09-16 15:13:50', 1),
(16, 'PLkIbfiOcITXoeEjCiGOlOYUUp7-FwbqPy', 'Uber Rapsy - Akademia S5', '100 nut', '2021-08-01 11:50:10', 1);

-- --------------------------------------------------------

--
-- Table structure for table `song`
--

DROP TABLE IF EXISTS `song`;
CREATE TABLE IF NOT EXISTS `song` (
  `SongId` int NOT NULL AUTO_INCREMENT,
  `SongURL` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `SongThumbnailURL` varchar(255) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `SongTitle` text CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `SongPlaylistItemsId` varchar(255) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `SongGradeAdam` decimal(10,2) NOT NULL,
  `SongGradeChurchie` decimal(10,2) NOT NULL,
  `ListId` int NOT NULL,
  PRIMARY KEY (`SongId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `role` varchar(10) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `passwordResetKey` varchar(255) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `password`, `role`, `passwordResetKey`) VALUES
(0, 'admin@uberrapsy.pl', '$2y$10$vfZ6VKeFebqf3ObRtTswIudK1MJE95Fxn4S/LO1QQAwZirmZih7JK', 'reviewer', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
