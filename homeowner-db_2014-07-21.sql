-- phpMyAdmin SQL Dump
-- version 4.2.3
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 21, 2014 at 06:30 PM
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
CREATE DATABASE IF NOT EXISTS `homeowner` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `homeowner`;

DELIMITER $$
--
-- Functions
--
DROP FUNCTION IF EXISTS `getArrears`$$
CREATE DEFINER=`root`@`127.0.0.1` FUNCTION `getArrears`(`amt` FLOAT, `i` FLOAT, `m` INT) RETURNS float
    DETERMINISTIC
    COMMENT 'Computes the arrears.'
BEGIN
    DECLARE ir FLOAT;
    DECLARE j INT;
    
    SET ir = 0;
    SET j = 0;
    
    WHILE j<m DO
        SET ir = ir + (POWER(i,j)*amt);
        SET j = j+1;
    END WHILE;
    
    RETURN ir;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `charges`
--
-- Creation: Jul 19, 2014 at 12:58 PM
--

DROP TABLE IF EXISTS `charges`;
CREATE TABLE `charges` (
`id` int(10) unsigned NOT NULL COMMENT 'Charge ID',
  `lot` int(10) unsigned NOT NULL COMMENT 'Lot ID for Lot Dues',
  `homeowner` int(10) unsigned NOT NULL COMMENT 'Homeowner ID',
  `description` varchar(512) NOT NULL COMMENT 'Charge Description',
  `amount` float NOT NULL COMMENT 'Amount to be Paid',
  `dateposted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date Posted',
  `uid` int(10) unsigned NOT NULL COMMENT 'User who added the charges',
  `ledgerid` int(10) unsigned NOT NULL COMMENT 'Ledger ID if paid',
  `amountpaid` float NOT NULL COMMENT 'Amount paid, to check partial payment'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Table of Charges' AUTO_INCREMENT=95 ;

--
-- Truncate table before insert `charges`
--

TRUNCATE TABLE `charges`;
--
-- Dumping data for table `charges`
--

INSERT INTO `charges` (`id`, `lot`, `homeowner`, `description`, `amount`, `dateposted`, `uid`, `ledgerid`, `amountpaid`) VALUES
(1, 1, 1, 'P1L1B1-2345 due for May 2014', 444, '2014-05-01 15:04:47', 1, 3, 444),
(2, 2, 1, 'P1L2B2-3453 due for May 2014', 492, '2014-05-01 15:04:47', 1, 3, 492),
(3, 3, 1, 'P1L2B1-452151 due for May 2014', 800, '2014-05-01 15:04:47', 1, 3, 800),
(4, 4, 2, 'P1L3B1-54545 due for May 2014', 1600, '2014-05-01 15:04:47', 1, 0, 0),
(5, 5, 4, 'P1L2B3-12345 due for May 2014', 400, '2014-05-01 15:04:47', 1, 0, 0),
(6, 6, 6, 'P5L5B5-12345 due for May 2014', 400, '2014-05-01 15:04:47', 1, 0, 0),
(7, 10, 1, 'jaylotnum due for May 2014', 216, '2014-05-01 15:04:47', 1, 2, 216),
(8, 11, 16, '17856 due for May 2014', 1156, '2014-05-01 15:04:47', 1, 0, 0),
(9, 12, 17, 'P2L97K30 due for May 2014', 480, '2014-05-01 15:04:47', 1, 0, 0),
(10, 13, 18, 'MVS11 due for May 2014', 0, '2014-05-01 15:04:47', 1, 0, 0),
(11, 14, 30, 'MVS Lot.1 due for May 2014', 400, '2014-05-01 15:04:47', 1, 0, 0),
(12, 15, 19, 'MVS Lot.2 due for May 2014', 320, '2014-05-01 15:04:47', 1, 0, 0),
(13, 16, 21, 'MVS Lot.3 due for May 2014', 360, '2014-05-01 15:04:47', 1, 0, 0),
(14, 17, 22, 'MVS Lot.4 due for May 2014', 300, '2014-05-01 15:04:47', 1, 0, 0),
(15, 18, 24, 'MVS Lot.5 due for May 2014', 400, '2014-05-01 15:04:47', 1, 0, 0),
(16, 19, 25, 'MVS Lot.6 due for May 2014', 600, '2014-05-01 15:04:47', 1, 0, 0),
(17, 20, 26, 'MVS Lot.7 due for May 2014', 440, '2014-05-01 15:04:47', 1, 0, 0),
(18, 21, 28, 'MVS Lot.8 due for May 2014', 640, '2014-05-01 15:04:47', 1, 0, 0),
(19, 22, 29, 'MVS Lot.9 due for May 2014', 640, '2014-05-01 15:04:47', 1, 0, 0),
(20, 1, 1, 'P1L1B1-2345 interest for Jun 2014', 44.4, '2014-06-01 15:06:42', 1, 0, 0),
(21, 1, 1, 'P1L1B1-2345 due for Jun 2014', 444, '2014-06-01 15:06:42', 1, 0, 0),
(22, 2, 1, 'P1L2B2-3453 interest for Jun 2014', 49.2, '2014-06-01 15:06:42', 1, 0, 0),
(23, 2, 1, 'P1L2B2-3453 due for Jun 2014', 492, '2014-06-01 15:06:42', 1, 0, 0),
(24, 3, 1, 'P1L2B1-452151 interest for Jun 2014', 80, '2014-06-01 15:06:42', 1, 0, 0),
(25, 3, 1, 'P1L2B1-452151 due for Jun 2014', 800, '2014-06-01 15:06:42', 1, 0, 0),
(26, 4, 2, 'P1L3B1-54545 interest for Jun 2014', 160, '2014-06-01 15:06:42', 1, 0, 0),
(27, 4, 2, 'P1L3B1-54545 due for Jun 2014', 1600, '2014-06-01 15:06:42', 1, 0, 0),
(28, 5, 4, 'P1L2B3-12345 interest for Jun 2014', 40, '2014-06-01 15:06:42', 1, 0, 0),
(29, 5, 4, 'P1L2B3-12345 due for Jun 2014', 400, '2014-06-01 15:06:42', 1, 0, 0),
(30, 6, 6, 'P5L5B5-12345 interest for Jun 2014', 40, '2014-06-01 15:06:42', 1, 0, 0),
(31, 6, 6, 'P5L5B5-12345 due for Jun 2014', 400, '2014-06-01 15:06:42', 1, 0, 0),
(32, 10, 1, 'jaylotnum interest for Jun 2014', 21.6, '2014-06-01 15:06:42', 1, 2, 21.6),
(33, 10, 1, 'jaylotnum due for Jun 2014', 216, '2014-06-01 15:06:42', 1, 2, 216),
(34, 11, 16, '17856 interest for Jun 2014', 115.6, '2014-06-01 15:06:42', 1, 0, 0),
(35, 11, 16, '17856 due for Jun 2014', 1156, '2014-06-01 15:06:42', 1, 0, 0),
(36, 12, 17, 'P2L97K30 interest for Jun 2014', 48, '2014-06-01 15:06:42', 1, 0, 0),
(37, 12, 17, 'P2L97K30 due for Jun 2014', 480, '2014-06-01 15:06:42', 1, 0, 0),
(38, 13, 18, 'MVS11 due for Jun 2014', 0, '2014-06-01 15:06:42', 1, 0, 0),
(39, 14, 30, 'MVS Lot.1 interest for Jun 2014', 40, '2014-06-01 15:06:42', 1, 0, 0),
(40, 14, 30, 'MVS Lot.1 due for Jun 2014', 400, '2014-06-01 15:06:42', 1, 0, 0),
(41, 15, 19, 'MVS Lot.2 interest for Jun 2014', 32, '2014-06-01 15:06:42', 1, 0, 0),
(42, 15, 19, 'MVS Lot.2 due for Jun 2014', 320, '2014-06-01 15:06:42', 1, 0, 0),
(43, 16, 21, 'MVS Lot.3 interest for Jun 2014', 36, '2014-06-01 15:06:42', 1, 0, 0),
(44, 16, 21, 'MVS Lot.3 due for Jun 2014', 360, '2014-06-01 15:06:42', 1, 0, 0),
(45, 17, 22, 'MVS Lot.4 interest for Jun 2014', 30, '2014-06-01 15:06:42', 1, 0, 0),
(46, 17, 22, 'MVS Lot.4 due for Jun 2014', 300, '2014-06-01 15:06:42', 1, 0, 0),
(47, 18, 24, 'MVS Lot.5 interest for Jun 2014', 40, '2014-06-01 15:06:42', 1, 0, 0),
(48, 18, 24, 'MVS Lot.5 due for Jun 2014', 400, '2014-06-01 15:06:42', 1, 0, 0),
(49, 19, 25, 'MVS Lot.6 interest for Jun 2014', 60, '2014-06-01 15:06:42', 1, 0, 0),
(50, 19, 25, 'MVS Lot.6 due for Jun 2014', 600, '2014-06-01 15:06:42', 1, 0, 0),
(51, 20, 26, 'MVS Lot.7 interest for Jun 2014', 44, '2014-06-01 15:06:42', 1, 0, 0),
(52, 20, 26, 'MVS Lot.7 due for Jun 2014', 440, '2014-06-01 15:06:42', 1, 0, 0),
(53, 21, 28, 'MVS Lot.8 interest for Jun 2014', 64, '2014-06-01 15:06:42', 1, 0, 0),
(54, 21, 28, 'MVS Lot.8 due for Jun 2014', 640, '2014-06-01 15:06:42', 1, 0, 0),
(55, 22, 29, 'MVS Lot.9 interest for Jun 2014', 64, '2014-06-01 15:06:42', 1, 0, 0),
(56, 22, 29, 'MVS Lot.9 due for Jun 2014', 640, '2014-06-01 15:06:42', 1, 0, 0),
(57, 1, 1, 'P1L1B1-2345 interest for Jul 2014', 93.24, '2014-07-20 15:07:18', 1, 0, 0),
(58, 1, 1, 'P1L1B1-2345 due for Jul 2014', 444, '2014-07-20 15:07:18', 1, 0, 0),
(59, 2, 1, 'P1L2B2-3453 interest for Jul 2014', 103.32, '2014-07-20 15:07:18', 1, 0, 0),
(60, 2, 1, 'P1L2B2-3453 due for Jul 2014', 492, '2014-07-20 15:07:18', 1, 0, 0),
(61, 3, 1, 'P1L2B1-452151 interest for Jul 2014', 168, '2014-07-20 15:07:18', 1, 0, 0),
(62, 3, 1, 'P1L2B1-452151 due for Jul 2014', 800, '2014-07-20 15:07:18', 1, 0, 0),
(63, 4, 2, 'P1L3B1-54545 interest for Jul 2014', 336, '2014-07-20 15:07:18', 1, 0, 0),
(64, 4, 2, 'P1L3B1-54545 due for Jul 2014', 1600, '2014-07-20 15:07:18', 1, 0, 0),
(65, 5, 4, 'P1L2B3-12345 interest for Jul 2014', 84, '2014-07-20 15:07:18', 1, 0, 0),
(66, 5, 4, 'P1L2B3-12345 due for Jul 2014', 400, '2014-07-20 15:07:18', 1, 0, 0),
(67, 6, 6, 'P5L5B5-12345 interest for Jul 2014', 84, '2014-07-20 15:07:18', 1, 0, 0),
(68, 6, 6, 'P5L5B5-12345 due for Jul 2014', 400, '2014-07-20 15:07:18', 1, 0, 0),
(69, 10, 1, 'jaylotnum interest for Jul 2014', 45.36, '2014-07-20 15:07:18', 1, 2, 45.36),
(70, 10, 1, 'jaylotnum due for Jul 2014', 216, '2014-07-20 15:07:18', 1, 2, 216),
(71, 11, 16, '17856 interest for Jul 2014', 242.76, '2014-07-20 15:07:18', 1, 0, 0),
(72, 11, 16, '17856 due for Jul 2014', 1156, '2014-07-20 15:07:18', 1, 0, 0),
(73, 12, 17, 'P2L97K30 interest for Jul 2014', 100.8, '2014-07-20 15:07:18', 1, 0, 0),
(74, 12, 17, 'P2L97K30 due for Jul 2014', 480, '2014-07-20 15:07:18', 1, 0, 0),
(75, 13, 18, 'MVS11 due for Jul 2014', 0, '2014-07-20 15:07:18', 1, 0, 0),
(76, 14, 30, 'MVS Lot.1 interest for Jul 2014', 84, '2014-07-20 15:07:18', 1, 0, 0),
(77, 14, 30, 'MVS Lot.1 due for Jul 2014', 400, '2014-07-20 15:07:18', 1, 0, 0),
(78, 15, 19, 'MVS Lot.2 interest for Jul 2014', 67.2, '2014-07-20 15:07:18', 1, 0, 0),
(79, 15, 19, 'MVS Lot.2 due for Jul 2014', 320, '2014-07-20 15:07:18', 1, 0, 0),
(80, 16, 21, 'MVS Lot.3 interest for Jul 2014', 75.6, '2014-07-20 15:07:18', 1, 0, 0),
(81, 16, 21, 'MVS Lot.3 due for Jul 2014', 360, '2014-07-20 15:07:18', 1, 0, 0),
(82, 17, 22, 'MVS Lot.4 interest for Jul 2014', 63, '2014-07-20 15:07:18', 1, 0, 0),
(83, 17, 22, 'MVS Lot.4 due for Jul 2014', 300, '2014-07-20 15:07:18', 1, 0, 0),
(84, 18, 24, 'MVS Lot.5 interest for Jul 2014', 84, '2014-07-20 15:07:18', 1, 0, 0),
(85, 18, 24, 'MVS Lot.5 due for Jul 2014', 400, '2014-07-20 15:07:18', 1, 0, 0),
(86, 19, 25, 'MVS Lot.6 interest for Jul 2014', 126, '2014-07-20 15:07:18', 1, 0, 0),
(87, 19, 25, 'MVS Lot.6 due for Jul 2014', 600, '2014-07-20 15:07:18', 1, 0, 0),
(88, 20, 26, 'MVS Lot.7 interest for Jul 2014', 92.4, '2014-07-20 15:07:18', 1, 0, 0),
(89, 20, 26, 'MVS Lot.7 due for Jul 2014', 440, '2014-07-20 15:07:18', 1, 0, 0),
(90, 21, 28, 'MVS Lot.8 interest for Jul 2014', 134.4, '2014-07-20 15:07:18', 1, 0, 0),
(91, 21, 28, 'MVS Lot.8 due for Jul 2014', 640, '2014-07-20 15:07:18', 1, 0, 0),
(92, 22, 29, 'MVS Lot.9 interest for Jul 2014', 134.4, '2014-07-20 15:07:18', 1, 0, 0),
(93, 22, 29, 'MVS Lot.9 due for Jul 2014', 640, '2014-07-20 15:07:18', 1, 0, 0),
(94, 0, 1, 'Parking Penalty', 700, '2014-07-21 15:26:02', 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `homeowner`
--
-- Creation: Jul 21, 2014 at 11:56 AM
-- Last update: Jul 21, 2014 at 11:56 AM
--

DROP TABLE IF EXISTS `homeowner`;
CREATE TABLE `homeowner` (
`id` int(11) NOT NULL COMMENT 'Homeowner ID',
  `lastname` varchar(255) NOT NULL COMMENT 'Last Name',
  `firstname` varchar(512) NOT NULL COMMENT 'First Name',
  `middlename` varchar(255) NOT NULL COMMENT 'Middle Name',
  `contactno` varchar(255) NOT NULL COMMENT 'Contact Number',
  `email` varchar(255) NOT NULL COMMENT 'Email Address',
  `user` int(10) unsigned NOT NULL COMMENT 'User',
  `dateadded` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Active User',
  `bond` float NOT NULL COMMENT 'Cash Bond',
  `gatepass` tinyint(1) NOT NULL COMMENT 'Gate Pass Sticker'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Homeowner Information' AUTO_INCREMENT=31 ;

--
-- RELATIONS FOR TABLE `homeowner`:
--   `user`
--       `user` -> `id`
--

--
-- Truncate table before insert `homeowner`
--

TRUNCATE TABLE `homeowner`;
--
-- Dumping data for table `homeowner`
--

INSERT INTO `homeowner` (`id`, `lastname`, `firstname`, `middlename`, `contactno`, `email`, `user`, `dateadded`, `active`, `bond`, `gatepass`) VALUES
(1, 'Delas Alas', 'Jay', 'Bagasbas', '09478929659', 'jay_delasalas@ymail.com', 1, '2014-06-28 13:19:31', 1, 300000, 1),
(2, 'Garcia', 'Mark Denrich', 'Deocales', '091738363937', 'adfadjfkjdf@df', 1, '2014-06-28 14:02:25', 1, 0, 1),
(3, 'Veloro', 'Riechelle', 'Naval', '0909809809', 'sdfs@df', 1, '2014-06-29 03:19:22', 1, 0, 1),
(4, 'Maas', 'Joed', 'Marquez', '39839489384', 'df@dfd', 1, '2014-06-29 03:19:35', 1, 5000, 1),
(5, 'Dela Cruz', 'Juan', 'Bautista', '3403940', 'ffg@df', 1, '2014-06-29 03:19:53', 1, 0, 0),
(6, 'Khan', 'Shao', 'X', '09999999999', 'sh@o', 1, '2014-06-29 03:20:08', 1, 0, 0),
(7, 'Dfadf', 'adfasfd', 'adfa', 'dfadf', 'adfa@d', 1, '2014-06-29 03:20:16', 0, 0, 0),
(8, 'Dfadf', 'adfa', 'Dfdfd', 'ffdaf', 'df@d', 1, '2014-06-29 03:20:26', 0, 0, 0),
(9, 'Adfafda', 'dfadf', 'adfasdf', 'asdf', 'dfas@d', 1, '2014-06-29 03:20:38', 0, 0, 0),
(10, 'dfdf', 'k', 'kjkj', 'kjk', 'j@kj', 1, '2014-06-29 03:20:44', 0, 0, 0),
(11, 'llklkl', 'klkl', 'klk', 'lklk', 'lk@kj.com', 1, '2014-06-29 03:20:51', 0, 0, 0),
(12, 'lklkl', 'klkl', 'klk', 'lkl', 'kl@lk', 1, '2014-06-29 03:20:58', 0, 0, 0),
(13, 'delas alas', 'jay', 'bagasbas', '09478929659', 'jay_delasalas@ymail.com', 1, '2014-07-01 06:17:51', 1, 0, 0),
(14, 'delas alas', 'jay', 'bagasbas', '09078455977', '', 1, '2014-07-02 08:13:58', 0, 0, 0),
(15, 'Paalam', 'Huling', 'X', '', '', 1, '2014-07-14 02:50:59', 1, 0, 0),
(16, 'OTHER INCOME ', 'RECYCLE', 'X', '', '', 1, '2014-07-14 06:39:10', 1, 0, 0),
(17, 'Chui', 'Kim', 'X', '09478929659', 'kim_chui@yahoo.com', 1, '2014-07-19 02:24:18', 1, 50000, 1),
(18, 'veloro', 'adrian', 'd', '09081645714', '', 1, '2014-07-19 03:34:34', 1, 0, 0),
(19, 'Calipit', 'ma teresa', 'x', '09081645700', '', 1, '2014-07-19 04:17:37', 0, 0, 0),
(20, 'Calipit', 'ma teresa', 'x', '09081645700', '', 1, '2014-07-19 04:17:38', 1, 0, 0),
(21, 'reyes', 'jene', 's', '09081645800', '', 1, '2014-07-19 04:20:25', 1, 0, 0),
(22, 'hulgado', 'jayson', 'g', '09081645716', '', 1, '2014-07-19 04:26:43', 1, 0, 0),
(23, 'hulgado', 'jayson', 'g', '09081645716', '', 1, '2014-07-19 04:26:46', 1, 0, 0),
(24, 'garcia', 'dave', 'e', '09081645720', '', 1, '2014-07-19 04:27:12', 1, 0, 0),
(25, 'loropay', 'crisaline', 'x', '09081645240', '', 1, '2014-07-19 04:29:12', 1, 0, 0),
(26, 'loropay', 'crisabel', 'x', '09081645140', '', 1, '2014-07-19 04:31:10', 1, 0, 0),
(27, 'loropay', 'crisabel', 'x', '09081645140', '', 1, '2014-07-19 04:31:13', 1, 0, 0),
(28, 'navarro', 'vhong', 's', '09081645604', '', 1, '2014-07-19 04:32:10', 1, 0, 0),
(29, 'curtis', 'anne', 's', '09081645740', '', 1, '2014-07-19 04:34:11', 1, 0, 0),
(30, 'ganda', 'vice', 'x', '09081645748', '', 1, '2014-07-19 04:35:49', 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ledger`
--
-- Creation: Jun 26, 2014 at 04:25 PM
--

DROP TABLE IF EXISTS `ledger`;
CREATE TABLE `ledger` (
`id` int(10) unsigned NOT NULL COMMENT 'Ledger ID',
  `ornumber` varchar(100) NOT NULL COMMENT 'OR Number',
  `payee` varchar(255) NOT NULL COMMENT 'Name of Payee',
  `homeowner` int(11) NOT NULL COMMENT 'Homeowner (FK)',
  `user` int(10) unsigned NOT NULL COMMENT 'User who received the payment',
  `transactiondate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'System Transaction Date',
  `remarks` varchar(1000) NOT NULL COMMENT 'Remarks',
  `paymentmode` varchar(100) NOT NULL DEFAULT 'cash' COMMENT 'Mode of Payment (Cash/Check)',
  `checkno` varchar(100) DEFAULT NULL COMMENT 'Check Number'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Ledger Details' AUTO_INCREMENT=4 ;

--
-- RELATIONS FOR TABLE `ledger`:
--   `homeowner`
--       `homeowner` -> `id`
--   `user`
--       `user` -> `id`
--

--
-- Truncate table before insert `ledger`
--

TRUNCATE TABLE `ledger`;
--
-- Dumping data for table `ledger`
--

INSERT INTO `ledger` (`id`, `ornumber`, `payee`, `homeowner`, `user`, `transactiondate`, `remarks`, `paymentmode`, `checkno`) VALUES
(2, '123456789', 'Jay delas Alas', 1, 1, '2014-07-21 09:52:57', '...', 'Cash', NULL),
(3, '23456789', 'J. delas Alas', 1, 1, '2014-07-21 14:50:25', 'Check', 'Check', '456-454-2342');

-- --------------------------------------------------------

--
-- Table structure for table `ledgeritem`
--
-- Creation: Jul 01, 2014 at 02:58 PM
--

DROP TABLE IF EXISTS `ledgeritem`;
CREATE TABLE `ledgeritem` (
`ledgerid` int(10) unsigned NOT NULL COMMENT 'Ledger ID',
  `id` int(11) NOT NULL COMMENT 'Ledger (FK)',
  `amount` float NOT NULL COMMENT 'Amount',
  `lot` int(11) NOT NULL COMMENT 'Lot ID',
  `startdate` date NOT NULL COMMENT 'Start of Date Range',
  `enddate` date NOT NULL COMMENT 'End of Date Range',
  `desc` varchar(1000) NOT NULL COMMENT 'Description'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Ledger Items' AUTO_INCREMENT=42 ;

--
-- RELATIONS FOR TABLE `ledgeritem`:
--   `id`
--       `ledger` -> `id`
--   `lot`
--       `lot` -> `id`
--

--
-- Truncate table before insert `ledgeritem`
--

TRUNCATE TABLE `ledgeritem`;
--
-- Dumping data for table `ledgeritem`
--

INSERT INTO `ledgeritem` (`ledgerid`, `id`, `amount`, `lot`, `startdate`, `enddate`, `desc`) VALUES
(1, 1, 345, 1, '2014-05-01', '2014-05-31', 'P1L1B1-2345'),
(2, 1, 234, 3, '2014-05-01', '2014-05-31', 'P1L2B1-452151'),
(3, 2, 500, 1, '2014-05-01', '2014-05-31', 'P1L1B1-2345'),
(4, 2, 400, 2, '2014-05-01', '2014-05-31', 'P1L2B2-3453'),
(5, 2, 300, 3, '2014-05-01', '2014-05-31', 'P1L2B1-452151'),
(6, 3, 300, 1, '2014-05-01', '2014-05-31', 'P1L1B1-2345'),
(7, 3, 300, 2, '2014-05-01', '2014-05-31', 'P1L2B2-3453'),
(8, 3, 3000, 3, '2014-05-01', '2014-05-31', 'P1L2B1-452151'),
(9, 8, 400, 1, '2014-03-01', '2014-03-31', 'P1L1B1-2345'),
(10, 8, 600, 2, '2014-02-01', '2014-03-31', 'P1L2B2-3453'),
(11, 9, 1600, 4, '2014-05-01', '2014-05-31', 'P1L3B1-54545'),
(12, 10, 1600, 4, '2014-04-01', '2014-04-30', 'P1L3B1-54545'),
(13, 11, 50000, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(14, 12, 6000, 1, '0000-00-00', '2014-02-28', 'P1L1B1-2345'),
(15, 13, 65000, 1, '0000-00-00', '2014-02-28', 'P1L1B1-2345'),
(16, 14, 1000, 6, '2014-01-01', '2013-03-31', 'P5L5B5-12345'),
(17, 15, 500, 1, '2014-07-01', '2014-08-31', 'P1L1B1-2345'),
(18, 15, 300, 2, '2014-07-01', '2014-08-31', 'P1L2B2-3453'),
(19, 15, 250, 3, '2014-07-01', '2014-08-31', 'P1L2B1-452151'),
(20, 45, 3434, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(21, 45, 7434, 3, '2014-07-01', '2014-07-31', 'P1L2B2-3453'),
(22, 45, 34, 0, '0000-00-00', '0000-00-00', 'dfghgs'),
(23, 45, 678, 0, '0000-00-00', '0000-00-00', 'fasdfasdf'),
(24, 46, 4545, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(25, 47, 10, 0, '0000-00-00', '0000-00-00', 'asasdasd'),
(26, 48, 10, 0, '0000-00-00', '0000-00-00', 'asasdasd'),
(27, 49, 36002, 0, '0000-00-00', '0000-00-00', 'asdfasdasdasd'),
(28, 50, 200000, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(29, 50, 5000, 3, '2014-06-01', '2014-07-31', 'P1L2B2-3453'),
(30, 50, 6000, 0, '0000-00-00', '0000-00-00', 'sticker'),
(31, 51, 600, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(32, 51, 700, 2, '2014-06-01', '2014-06-30', 'P1L2B2-3453'),
(33, 51, 600, 0, '0000-00-00', '0000-00-00', 'gate pass'),
(34, 52, 8000, 12, '2014-01-01', '2014-01-31', 'P2L97K30'),
(35, 52, 200, 0, '0000-00-00', '0000-00-00', 'STICKER'),
(36, 53, 700, 12, '2014-08-01', '2014-08-31', 'P2L97K30'),
(37, 54, 300, 12, '2014-07-01', '2014-07-31', 'P2L97K30'),
(38, 55, 500, 12, '2014-06-01', '2014-06-30', 'P2L97K30'),
(39, 56, 300, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(40, 56, 900, 2, '2014-06-01', '2014-08-31', 'P1L2B2-3453'),
(41, 56, 250, 0, '0000-00-00', '0000-00-00', 'BM Discount');

-- --------------------------------------------------------

--
-- Table structure for table `lot`
--
-- Creation: Jul 21, 2014 at 11:56 AM
-- Last update: Jul 21, 2014 at 11:56 AM
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
  `numberinhouseholdc` int(11) NOT NULL COMMENT 'Number of Children in Household',
  `caretaker` varchar(512) NOT NULL COMMENT 'Name of Caretaker',
  `dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date Added to System',
  `user` int(11) NOT NULL COMMENT 'Added by User (FK)',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is the lot active?'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='House Lot Information' AUTO_INCREMENT=24 ;

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

INSERT INTO `lot` (`id`, `code`, `homeowner`, `dateacquired`, `lotsize`, `housenumber`, `street`, `lot`, `block`, `phase`, `numberinhousehold`, `numberinhouseholdc`, `caretaker`, `dateadded`, `user`, `active`) VALUES
(1, 'P1L1B1-2345', 1, '2013-02-14', '111', '1', 'First Street', '1', '1', '1', 2, 1, '', '2014-06-30 02:26:13', 1, 1),
(2, 'P1L2B2-3453', 1, '2014-06-30', '123', '21', 'Second St.', '2', '2', '1', 2, 0, '', '2014-06-30 02:48:46', 1, 1),
(3, 'P1L2B1-452151', 1, '2014-07-09', '200', '50', 'Greenhills', '2', '1', '1', 8, 0, '', '2014-07-01 03:31:45', 1, 1),
(4, 'P1L3B1-54545', 2, '2014-01-01', '400', '5', 'Ilang-ilang', '3', '1', '1', 3, 0, '', '2014-07-03 03:43:04', 1, 1),
(5, 'P1L2B3-12345', 4, '2013-05-01', '100', '13', 'Empire', '5', '5', '2', 3, 2, '', '2014-07-03 09:00:46', 1, 1),
(6, 'P5L5B5-12345', 6, '2012-06-12', '100', '13', 'Empire St.', '5', '5', '5', 100, 0, '', '2014-07-03 09:04:07', 1, 1),
(7, 'P1L1B1-8888', 0, '2009-07-16', '80', '2', 'Malunggay', '444', '4', '2', 2, 0, '', '2014-07-04 10:50:41', 1, 1),
(8, 'P1L1B1-9999', 0, '0000-00-00', '300', '34', 'Emerald', '4', '23', '1', 0, 0, '', '2014-07-05 07:29:21', 1, 1),
(9, 'jaylotnum', 0, '0000-00-00', '54', '52', 'market view', '65', '12', '4', 0, 0, '', '2014-07-07 05:35:30', 1, 1),
(10, 'jaylotnum', 1, '0000-00-00', '54', '52', 'market view', '65', '12', '4', 0, 0, '', '2014-07-07 05:35:32', 1, 1),
(11, '17856', 16, '0000-00-00', '289', '5986', 'mark', 'b', '29', '9', 17, 0, '', '2014-07-15 01:53:57', 1, 1),
(12, 'P2L97K30', 17, '0000-00-00', '120', '59', 'APPLE ', '20', '2', '4', 20, 19, '', '2014-07-19 03:00:33', 1, 1),
(13, 'MVS11', 18, '0000-00-00', '0', '', '', '', '', '', 5, 3, '', '2014-07-19 03:10:52', 1, 1),
(14, 'MVS Lot.1', 30, '0000-00-00', '100', '1', 'MVS phase 1', '1', '1', '1', 6, 2, '', '2014-07-19 03:12:36', 1, 1),
(15, 'MVS Lot.2', 19, '0000-00-00', '80', '2', 'MVS phase2', '2', '2', '2', 4, 2, '', '2014-07-19 03:14:15', 1, 1),
(16, 'MVS Lot.3', 21, '0000-00-00', '90', '3', 'MVS phase3', '3', '3', '3', 6, 3, '', '2014-07-19 03:14:47', 1, 1),
(17, 'MVS Lot.4', 22, '0000-00-00', '75', '4', 'MVS phase4', '4', '4', '4', 3, 1, '', '2014-07-19 03:15:33', 1, 1),
(18, 'MVS Lot.5', 24, '0000-00-00', '100', '5', 'MVS phase5', '5', '5', '5', 3, 1, '', '2014-07-19 03:15:57', 1, 1),
(19, 'MVS Lot.6', 25, '0000-00-00', '150', '6', 'MVS phase6', '6', '6', '6', 5, 2, '', '2014-07-19 03:16:22', 1, 1),
(20, 'MVS Lot.7', 26, '0000-00-00', '110', '7', 'MVS phase7', '7', '7', '7', 6, 2, '', '2014-07-19 03:24:24', 1, 1),
(21, 'MVS Lot.8', 28, '0000-00-00', '160', '8', 'MVS phase8', '8', '8', '8', 7, 3, '', '2014-07-19 03:25:31', 1, 1),
(22, 'MVS Lot.9', 29, '0000-00-00', '160', '9', 'MVS phase9', '9', '9', '9', 7, 3, '', '2014-07-19 03:27:09', 1, 1),
(23, 'MVS Lot.10', 0, '0000-00-00', '130', '10', 'MVS phase10', '10', '10', '10', 0, 0, '', '2014-07-19 03:30:21', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--
-- Creation: Jul 21, 2014 at 11:56 AM
-- Last update: Jul 21, 2014 at 11:56 AM
--

DROP TABLE IF EXISTS `resident`;
CREATE TABLE `resident` (
`id` int(10) unsigned NOT NULL COMMENT 'Resident ID',
  `fullname` varchar(512) NOT NULL COMMENT 'Full Name',
  `household` int(11) NOT NULL COMMENT 'Household (FK)',
  `status` int(10) unsigned NOT NULL COMMENT 'Resident Status',
  `user` int(10) unsigned NOT NULL COMMENT 'User (FK)',
  `dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- RELATIONS FOR TABLE `resident`:
--   `household`
--       `lot` -> `id`
--   `status`
--       `status` -> `id`
--   `user`
--       `user` -> `id`
--

--
-- Truncate table before insert `resident`
--

TRUNCATE TABLE `resident`;
-- --------------------------------------------------------

--
-- Table structure for table `settings`
--
-- Creation: Jul 21, 2014 at 11:56 AM
-- Last update: Jul 21, 2014 at 11:56 AM
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
`id` int(10) unsigned NOT NULL COMMENT 'Settings ID',
  `assocname` varchar(1000) NOT NULL COMMENT 'Homeowner Association Name',
  `acronym` varchar(255) NOT NULL COMMENT 'Homeowner Association Acronym',
  `subdname` varchar(255) NOT NULL COMMENT 'Subdivision Name',
  `brgy` varchar(255) NOT NULL COMMENT 'Barangay',
  `city` varchar(255) NOT NULL COMMENT 'Town/City',
  `province` varchar(255) NOT NULL COMMENT 'Province',
  `zipcode` varchar(10) NOT NULL COMMENT 'ZIP Code',
  `contactno` varchar(100) NOT NULL COMMENT 'Contact Number',
  `email` varchar(255) NOT NULL COMMENT 'Email Address',
  `price` float NOT NULL COMMENT 'Price/Sq Meter',
  `interest` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Monthly Interest Rate',
  `intgraceperiod` int(10) unsigned NOT NULL COMMENT 'Interest Grace Period (months)',
  `applieddues` date NOT NULL COMMENT 'Latest Month with Monthly Dues'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Application Settings' AUTO_INCREMENT=2 ;

--
-- Truncate table before insert `settings`
--

TRUNCATE TABLE `settings`;
--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `assocname`, `acronym`, `subdname`, `brgy`, `city`, `province`, `zipcode`, `contactno`, `email`, `price`, `interest`, `intgraceperiod`, `applieddues`) VALUES
(1, 'Santa Isabel Village Homeowners Association, Inc.', 'SIVHAI', 'Santa Isabel Village', 'Isabang', 'Lucena City', 'Quezon', '4301', '', '', 4, '0.1000', 3, '2014-07-01');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--
-- Creation: Jul 21, 2014 at 11:56 AM
-- Last update: Jul 21, 2014 at 11:56 AM
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
`id` int(10) unsigned NOT NULL COMMENT 'Status ID',
  `description` varchar(255) NOT NULL COMMENT 'Description'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Residential Status' AUTO_INCREMENT=5 ;

--
-- Truncate table before insert `status`
--

TRUNCATE TABLE `status`;
--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `description`) VALUES
(1, 'Homeowner'),
(2, 'Resident'),
(3, 'Care Taker'),
(4, 'Transient');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--
-- Creation: Jul 21, 2014 at 11:56 AM
-- Last update: Jul 21, 2014 at 11:56 AM
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
`id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `fullname` varchar(512) NOT NULL COMMENT 'Full Name',
  `username` varchar(32) NOT NULL COMMENT 'Username',
  `password` varchar(50) NOT NULL COMMENT 'Hashed Password',
  `datereg` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date registered',
  `permission` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Set of Permissions'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='User Information' AUTO_INCREMENT=4 ;

--
-- Truncate table before insert `user`
--

TRUNCATE TABLE `user`;
--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fullname`, `username`, `password`, `datereg`, `permission`) VALUES
(1, 'System Admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', '2014-06-28 13:11:40', 255),
(2, 'Vekou X Aitenshi', 'vekou', '149afd631693c895f81e508eb5aaef37', '2014-07-16 14:41:04', 5),
(3, 'Jay delas Alas', 'jay', '74df9ed7b79cfcbca84002619b670802', '2014-07-16 14:50:14', 37);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `charges`
--
ALTER TABLE `charges`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homeowner`
--
ALTER TABLE `homeowner`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ledger`
--
ALTER TABLE `ledger`
 ADD PRIMARY KEY (`id`), ADD KEY `ornumber` (`ornumber`);

--
-- Indexes for table `ledgeritem`
--
ALTER TABLE `ledgeritem`
 ADD PRIMARY KEY (`ledgerid`);

--
-- Indexes for table `lot`
--
ALTER TABLE `lot`
 ADD PRIMARY KEY (`id`), ADD KEY `code` (`code`);

--
-- Indexes for table `resident`
--
ALTER TABLE `resident`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`username`), ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `charges`
--
ALTER TABLE `charges`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Charge ID',AUTO_INCREMENT=95;
--
-- AUTO_INCREMENT for table `homeowner`
--
ALTER TABLE `homeowner`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Homeowner ID',AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ledger ID',AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `ledgeritem`
--
ALTER TABLE `ledgeritem`
MODIFY `ledgerid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ledger ID',AUTO_INCREMENT=42;
--
-- AUTO_INCREMENT for table `lot`
--
ALTER TABLE `lot`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Lot ID',AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `resident`
--
ALTER TABLE `resident`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Resident ID';
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Settings ID',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Status ID',AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User ID',AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
