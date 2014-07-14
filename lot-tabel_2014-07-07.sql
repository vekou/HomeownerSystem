-- phpMyAdmin SQL Dump
-- version 4.2.3
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 07, 2014 at 10:13 AM
-- Server version: 5.5.38
-- PHP Version: 5.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `homeowner`
--

-- --------------------------------------------------------

--
-- Table structure for table `lot`
--
-- Creation: Jul 07, 2014 at 01:34 AM
-- Last update: Jul 07, 2014 at 05:37 AM
--

DROP TABLE IF EXISTS `lot`;
CREATE TABLE `lot` (
`id` int(10) unsigned NOT NULL COMMENT 'Lot ID',
  `code` varchar(255) NOT NULL COMMENT 'Lot Code (e.g. 1P2-L1B4)',
  `homeowner` int(11) NOT NULL COMMENT 'Homeowner (FK)',
  `dateacquired` date NOT NULL COMMENT 'Date Acquired',
  `lotsize` decimal(10,0) NOT NULL COMMENT 'Lot Size',
  `housenumber` varchar(10) NOT NULL COMMENT 'House Number',
  `street` varchar(100) NOT NULL COMMENT 'Street Name',
  `lot` varchar(10) NOT NULL COMMENT 'Lot No.',
  `block` varchar(10) NOT NULL COMMENT 'Block Number',
  `phase` varchar(64) NOT NULL COMMENT 'Phase',
  `numberinhousehold` int(11) NOT NULL COMMENT 'Number in Household',
  `caretaker` varchar(512) NOT NULL COMMENT 'Name of Caretaker',
  `dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date Added to System',
  `user` int(11) NOT NULL COMMENT 'Added by User (FK)',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is the lot active?'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='House Lot Information' AUTO_INCREMENT=11 ;

--
-- RELATIONS FOR TABLE `lot`:
--   `homeowner`
--       `homeowner` -> `id`
--   `user`
--       `user` -> `id`
--

--
-- Truncate table before insert `lot`
--

TRUNCATE TABLE `lot`;
--
-- Dumping data for table `lot`
--

INSERT INTO `lot` (`id`, `code`, `homeowner`, `dateacquired`, `lotsize`, `housenumber`, `street`, `lot`, `block`, `phase`, `numberinhousehold`, `caretaker`, `dateadded`, `user`, `active`) VALUES
(1, 'P1L1B1-2345', 1, '2014-06-01', '111', '1', 'First St.', '1', '1', '1', 1, '', '2014-06-30 02:26:13', 1, 1),
(2, 'P1L2B2-3453', 1, '2014-06-30', '123', '21', 'Second St.', '2', '2', '1', 2, '', '2014-06-30 02:48:46', 1, 1),
(3, 'P1L2B1-452151', 1, '2014-07-09', '200', '50', 'Greenhills', '2', '1', '1', 8, '', '2014-07-01 03:31:45', 1, 1),
(4, 'P1L3B1-54545', 2, '2014-01-01', '400', '5', 'Ilang-ilang', '3', '1', '1', 3, '', '2014-07-03 03:43:04', 1, 1),
(5, 'P1L2B3-12345', 0, '0000-00-00', '100', '13', 'Empire', '5', '5', '2', 0, '', '2014-07-03 09:00:46', 1, 1),
(6, 'P5L5B5-12345', 6, '2012-06-12', '100', '13', 'Empire St.', '5', '5', '5', 100, '', '2014-07-03 09:04:07', 1, 1),
(7, 'P1L1B1-8888', 0, '2009-07-16', '80', '2', 'Malunggay', '444', '4', '2', 2, '', '2014-07-04 10:50:41', 1, 1),
(8, 'P1L1B1-9999', 0, '0000-00-00', '300', '34', 'Emerald', '4', '23', '1', 0, '', '2014-07-05 07:29:21', 1, 1),
(9, 'jaylotnum', 0, '0000-00-00', '54', '52', 'market view', '65', '12', '4', 0, '', '2014-07-07 05:35:30', 1, 1),
(10, 'jaylotnum', 1, '0000-00-00', '54', '52', 'market view', '65', '12', '4', 0, '', '2014-07-07 05:35:32', 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lot`
--
ALTER TABLE `lot`
 ADD PRIMARY KEY (`id`), ADD KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lot`
--
ALTER TABLE `lot`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Lot ID',AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
