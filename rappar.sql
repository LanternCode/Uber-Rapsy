-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 07, 2025 at 12:05 PM
-- Server version: 10.6.22-MariaDB-cll-lve
-- PHP Version: 8.2.29

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
CREATE DATABASE IF NOT EXISTS `lanternc_uberrapsy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `lanternc_uberrapsy`;

-- --------------------------------------------------------

--
-- Table structure for table `list`
--

CREATE TABLE IF NOT EXISTS `list` (
  `ListId` int(11) NOT NULL AUTO_INCREMENT,
  `ListUrl` varchar(255) DEFAULT NULL,
  `ListName` varchar(50) NOT NULL,
  `ListDesc` mediumtext NOT NULL,
  `ListCreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `ListIntegrated` tinyint(1) NOT NULL,
  `ListPublic` bit(1) NOT NULL DEFAULT b'1',
  `ListOwnerId` int(11) NOT NULL COMMENT 'Id of the user who created/owns the playlist',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `LogId` int(11) NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL COMMENT 'User ID of who performed the action',
  `EntityType` varchar(13) NOT NULL COMMENT 'Must be a valid database entity (user/song/playlist)',
  `EntityId` int(11) NOT NULL COMMENT 'Id of the entity logged',
  `Description` text NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `reportId` int(11) NOT NULL DEFAULT 0 COMMENT 'If a report was generated, its id will be attached here',
  PRIMARY KEY (`LogId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playlist_song`
--

CREATE TABLE IF NOT EXISTS `playlist_song` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `songId` int(11) NOT NULL,
  `listId` int(11) NOT NULL,
  `SongGradeAdam` decimal(10,2) NOT NULL,
  `SongGradeChurchie` decimal(10,2) NOT NULL,
  `SongGradeOwner` decimal(10,2) NOT NULL,
  `SongComment` text NOT NULL,
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
  `SongPlaylistItemsId` varchar(255) DEFAULT NULL,
  `SongVisible` bit(1) NOT NULL DEFAULT b'1',
  `SongDeleted` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  KEY `songId` (`songId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE IF NOT EXISTS `report` (
  `reportId` int(10) NOT NULL AUTO_INCREMENT,
  `reportText` mediumtext NOT NULL,
  PRIMARY KEY (`reportId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE IF NOT EXISTS `review` (
  `reviewId` int(11) NOT NULL AUTO_INCREMENT,
  `reviewTitle` varchar(120) NOT NULL,
  `reviewText` int(2) NOT NULL,
  `reviewMusic` int(2) NOT NULL,
  `reviewComp` int(11) NOT NULL,
  `reviewUber` int(11) NOT NULL,
  `reviewPartner` int(11) NOT NULL,
  `reviewUnique` int(11) NOT NULL,
  `reviewStyle` int(11) NOT NULL,
  `reviewReflective` int(11) NOT NULL,
  `reviewMotive` int(11) NOT NULL,
  `reviewDate` date NOT NULL,
  `reviewInsertDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewActive` bit(1) NOT NULL DEFAULT b'1',
  `reviewTextContent` text NOT NULL,
  `reviewSongId` int(11) NOT NULL,
  `reviewUserId` int(11) NOT NULL,
  PRIMARY KEY (`reviewId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song`
--

CREATE TABLE IF NOT EXISTS `song` (
  `SongId` int(11) NOT NULL AUTO_INCREMENT,
  `SongAddedBy` int(11) NOT NULL COMMENT 'user id',
  `SongURL` varchar(11) DEFAULT NULL COMMENT 'YouTube song ID',
  `SongThumbnailURL` varchar(255) NOT NULL,
  `SongTitle` text NOT NULL,
  `SongChannelName` varchar(50) NOT NULL,
  `SongReleaseYear` int(11) NOT NULL,
  `SongGradeAdam` decimal(10,2) NOT NULL,
  `SongGradeChurchie` decimal(10,2) NOT NULL,
  `SongVisible` bit(1) NOT NULL DEFAULT b'1',
  `SongDeleted` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`SongId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song_award`
--

CREATE TABLE IF NOT EXISTS `song_award` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `songId` int(11) NOT NULL,
  `award` varchar(22) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song_rating`
--

CREATE TABLE IF NOT EXISTS `song_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `songId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `songGrade` decimal(2,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rating` (`userId`,`songId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(22) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(128) NOT NULL,
  `role` varchar(10) NOT NULL,
  `passwordResetKey` varchar(255) DEFAULT NULL,
  `accountLocked` bit(1) NOT NULL DEFAULT b'0',
  `userScore` int(11) NOT NULL DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
