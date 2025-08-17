-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 17, 2025 at 10:24 AM
-- Server version: 9.1.0
-- PHP Version: 8.1.31

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
CREATE DATABASE IF NOT EXISTS `lanternc_uberrapsy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci;
USE `lanternc_uberrapsy`;

-- --------------------------------------------------------

--
-- Table structure for table `list`
--

DROP TABLE IF EXISTS `list`;
CREATE TABLE IF NOT EXISTS `list` (
  `ListId` int NOT NULL AUTO_INCREMENT,
  `ListUrl` varchar(255) COLLATE utf8mb3_polish_ci DEFAULT NULL,
  `ListName` varchar(50) COLLATE utf8mb3_polish_ci NOT NULL,
  `ListDesc` text COLLATE utf8mb3_polish_ci NOT NULL,
  `ListCreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ListIntegrated` tinyint(1) NOT NULL,
  `ListPublic` bit(1) NOT NULL DEFAULT b'1',
  `ListOwnerId` int NOT NULL COMMENT 'Id of the user who created/owns the playlist',
  `btnRehearsal` bit(1) NOT NULL DEFAULT b'1',
  `btnDistinction` bit(1) NOT NULL DEFAULT b'1',
  `btnMemorial` bit(1) NOT NULL DEFAULT b'1',
  `btnXD` bit(1) NOT NULL DEFAULT b'1',
  `btnNotRap` bit(1) NOT NULL DEFAULT b'1',
  `btnDiscomfort` bit(1) NOT NULL DEFAULT b'1',
  `btnTop` bit(1) NOT NULL DEFAULT b'1',
  `btnNoGrade` bit(1) NOT NULL DEFAULT b'1',
  `btnUber` bit(1) NOT NULL DEFAULT b'1',
  `btnBelowTen` bit(1) NOT NULL DEFAULT b'0',
  `btnBelowNine` bit(1) NOT NULL DEFAULT b'0',
  `btnBelowEight` bit(1) NOT NULL DEFAULT b'0',
  `btnBelowSeven` bit(1) NOT NULL DEFAULT b'0',
  `btnBelowFour` bit(1) NOT NULL DEFAULT b'0',
  `btnDuoTen` bit(1) NOT NULL DEFAULT b'1',
  `btnVeto` bit(1) NOT NULL DEFAULT b'1',
  `btnBelowHalfSeven` bit(1) NOT NULL DEFAULT b'0',
  `btnBelowHalfEight` bit(1) NOT NULL DEFAULT b'0',
  `btnBelowHalfNine` bit(1) NOT NULL DEFAULT b'0',
  `btnDepA` bit(1) NOT NULL DEFAULT b'1',
  `ListActive` bit(1) NOT NULL DEFAULT b'1' COMMENT 'An inactive list is archived',
  PRIMARY KEY (`ListId`)
) ENGINE=MyISAM AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `LogId` int NOT NULL AUTO_INCREMENT,
  `UserId` int NOT NULL COMMENT 'User ID of who performed the action',
  `EntityType` varchar(13) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'Must be a valid database entity (user/song/playlist)',
  `EntityId` int NOT NULL COMMENT 'Id of the entity logged',
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reportId` int NOT NULL DEFAULT '0' COMMENT 'If a report was generated, its id will be attached here',
  PRIMARY KEY (`LogId`)
) ENGINE=MyISAM AUTO_INCREMENT=522 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `playlist_song`
--

DROP TABLE IF EXISTS `playlist_song`;
CREATE TABLE IF NOT EXISTS `playlist_song` (
  `id` int NOT NULL AUTO_INCREMENT,
  `songId` int NOT NULL,
  `listId` int NOT NULL,
  `SongGradeAdam` decimal(10,2) NOT NULL,
  `SongGradeChurchie` decimal(10,2) NOT NULL,
  `SongGradeOwner` decimal(10,2) NOT NULL,
  `SongComment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SongRehearsal` bit(1) NOT NULL DEFAULT b'0',
  `SongDistinction` bit(1) NOT NULL DEFAULT b'0',
  `SongMemorial` bit(1) NOT NULL DEFAULT b'0',
  `SongXD` bit(1) NOT NULL DEFAULT b'0',
  `SongNotRap` bit(1) NOT NULL DEFAULT b'0',
  `SongDiscomfort` bit(1) NOT NULL DEFAULT b'0',
  `SongTop` bit(1) NOT NULL DEFAULT b'0',
  `SongNoGrade` bit(1) NOT NULL DEFAULT b'0',
  `SongUber` bit(1) NOT NULL DEFAULT b'0',
  `SongBelow` bit(1) NOT NULL DEFAULT b'0',
  `SongBelTen` bit(1) NOT NULL DEFAULT b'0',
  `SongBelNine` bit(1) NOT NULL DEFAULT b'0',
  `SongBelEight` bit(1) NOT NULL DEFAULT b'0',
  `SongBelFour` bit(1) NOT NULL DEFAULT b'0',
  `SongDuoTen` bit(1) NOT NULL DEFAULT b'0',
  `SongVeto` bit(1) NOT NULL DEFAULT b'0',
  `SongBelHalfSeven` bit(1) NOT NULL DEFAULT b'0',
  `SongBelHalfEight` bit(1) NOT NULL DEFAULT b'0',
  `SongBelHalfNine` bit(1) NOT NULL DEFAULT b'0',
  `SongDepA` bit(1) NOT NULL DEFAULT b'0',
  `SongPlaylistItemsId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `SongVisible` bit(1) NOT NULL DEFAULT b'1',
  `SongDeleted` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  KEY `songId` (`songId`)
) ENGINE=MyISAM AUTO_INCREMENT=1939 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
CREATE TABLE IF NOT EXISTS `report` (
  `reportId` int NOT NULL AUTO_INCREMENT,
  `reportText` text CHARACTER SET utf8mb3 COLLATE utf8mb3_polish_ci NOT NULL,
  PRIMARY KEY (`reportId`)
) ENGINE=MyISAM AUTO_INCREMENT=513 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE IF NOT EXISTS `review` (
  `reviewId` int NOT NULL AUTO_INCREMENT,
  `reviewTitle` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reviewText` int NOT NULL,
  `reviewMusic` int NOT NULL,
  `reviewComp` int NOT NULL,
  `reviewUber` int NOT NULL,
  `reviewPartner` int NOT NULL,
  `reviewUnique` int NOT NULL,
  `reviewStyle` int NOT NULL,
  `reviewReflective` int NOT NULL,
  `reviewMotive` int NOT NULL,
  `reviewDate` date NOT NULL,
  `reviewInsertDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewActive` bit(1) NOT NULL DEFAULT b'1',
  `reviewTextContent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reviewSongId` int NOT NULL,
  `reviewUserId` int NOT NULL,
  PRIMARY KEY (`reviewId`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `song`
--

DROP TABLE IF EXISTS `song`;
CREATE TABLE IF NOT EXISTS `song` (
  `SongId` int NOT NULL AUTO_INCREMENT,
  `SongAddedBy` int NOT NULL COMMENT 'user id',
  `SongURL` varchar(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_polish_ci DEFAULT NULL COMMENT 'YouTube song ID',
  `SongThumbnailURL` varchar(255) COLLATE utf8mb3_polish_ci NOT NULL,
  `SongTitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SongChannelName` varchar(50) COLLATE utf8mb3_polish_ci NOT NULL,
  `SongReleaseYear` int NOT NULL,
  `SongGradeAdam` decimal(10,2) NOT NULL,
  `SongGradeChurchie` decimal(10,2) NOT NULL,
  `SongVisible` bit(1) NOT NULL DEFAULT b'1',
  `SongDeleted` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`SongId`)
) ENGINE=MyISAM AUTO_INCREMENT=9332 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song_award`
--

DROP TABLE IF EXISTS `song_award`;
CREATE TABLE IF NOT EXISTS `song_award` (
  `id` int NOT NULL AUTO_INCREMENT,
  `songId` int NOT NULL,
  `award` varchar(22) COLLATE utf8mb4_polish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song_rating`
--

DROP TABLE IF EXISTS `song_rating`;
CREATE TABLE IF NOT EXISTS `song_rating` (
  `id` int NOT NULL AUTO_INCREMENT,
  `songId` int NOT NULL,
  `userId` int NOT NULL,
  `songGrade` decimal(4,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rating` (`userId`,`songId`)
) ENGINE=MyISAM AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb3_polish_ci NOT NULL,
  `password` varchar(128) COLLATE utf8mb3_polish_ci NOT NULL,
  `role` varchar(10) COLLATE utf8mb3_polish_ci NOT NULL,
  `passwordResetKey` varchar(255) COLLATE utf8mb3_polish_ci DEFAULT NULL,
  `accountLocked` bit(1) NOT NULL DEFAULT b'0',
  `userScore` int NOT NULL DEFAULT '0',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_polish_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `role`, `passwordResetKey`, `accountLocked`, `userScore`, `createdAt`) VALUES
(1, 'RAPPAR', 'admin@uberrapsy.pl', '$2y$10$vfZ6VKeFebqf3ObRtTswIudK1MJE95Fxn4S/LO1QQAwZirmZih7JK', 'reviewer', NULL, b'0', 458, '2025-08-09 13:56:09'),
(22, 'tester', 'tester@gmail.com', '$2y$10$OyIkAwroCNRZWMJSA.6i2uEN.x2dYw.bNfap8GZ0WBNGzfoM1TsgC', 'user', NULL, b'0', 122, '2025-08-09 13:56:09'),
(21, 'Adam Macc', 'adam.machowczyk@gmail.com', '$2y$10$.B0h4kWRDCVGXsJJKW7AVeRaEXFfSdJpiYuqGkOAixU3Kf/bfotl6', 'user', NULL, b'0', 75, '2025-08-09 13:56:09');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
