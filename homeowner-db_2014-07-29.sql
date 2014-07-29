-- phpMyAdmin SQL Dump
-- version 4.2.3
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 29, 2014 at 03:39 AM
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
DROP FUNCTION IF EXISTS `formatAddress`$$
CREATE DEFINER=`root`@`127.0.0.1` FUNCTION `formatAddress`(`houseno` VARCHAR(255), `lot` VARCHAR(255), `block` VARCHAR(255), `street` VARCHAR(255), `phase` VARCHAR(255)) RETURNS varchar(1024) CHARSET utf8
    NO SQL
    DETERMINISTIC
    COMMENT 'Formats to an address'
BEGIN
	RETURN CONCAT(houseno," Lot ",lot," Block ",block,", ",street," Phase ",phase);
END$$

DROP FUNCTION IF EXISTS `formatName`$$
CREATE DEFINER=`root`@`127.0.0.1` FUNCTION `formatName`(`lastname` VARCHAR(512), `firstname` VARCHAR(512), `middlename` VARCHAR(512)) RETURNS varchar(2048) CHARSET utf8
    NO SQL
    DETERMINISTIC
    COMMENT 'Formats a name'
BEGIN
	RETURN CONCAT(lastname,", ",firstname," ",SUBSTR(middlename,1,1),".");
END$$

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
-- Table structure for table `cashflows`
--
-- Creation: Jul 26, 2014 at 02:06 AM
--

DROP TABLE IF EXISTS `cashflows`;
CREATE TABLE `cashflows` (
`id` int(10) unsigned NOT NULL COMMENT 'Cash Flow ID',
  `type` tinyint(4) NOT NULL COMMENT 'Credit(1) or Debit(-1)',
  `transactiondate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of Transaction',
  `ornumber` varchar(255) NOT NULL COMMENT 'OR Number',
  `paymentmode` enum('Cash','Check') NOT NULL COMMENT 'Mode of Payment',
  `checkno` int(11) DEFAULT NULL COMMENT 'Check Number',
  `description` varchar(1000) NOT NULL COMMENT 'Description',
  `amount` decimal(10,2) NOT NULL COMMENT 'Amount',
  `remarks` varchar(1000) NOT NULL COMMENT 'Remarks',
  `user` int(11) NOT NULL COMMENT 'User who processed transaction',
  `cancelremarks` varchar(255) NOT NULL COMMENT 'Reason for Cancellation',
  `canceluser` int(11) NOT NULL COMMENT 'User who cancelled transaction',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is active?'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Cash Flow for the Association' AUTO_INCREMENT=4 ;

--
-- Truncate table before insert `cashflows`
--

TRUNCATE TABLE `cashflows`;
--
-- Dumping data for table `cashflows`
--

INSERT INTO `cashflows` (`id`, `type`, `transactiondate`, `ornumber`, `paymentmode`, `checkno`, `description`, `amount`, `remarks`, `user`, `cancelremarks`, `canceluser`, `active`) VALUES
(1, 1, '2014-07-26 04:47:39', '454345345', 'Check', 3445432, 'Recycled Items', '5000.00', 'From Junk Shop', 1, '', 0, 1),
(2, -1, '2014-07-26 05:05:12', '', 'Cash', NULL, 'Staff Salary', '10000.00', '', 1, '', 0, 1),
(3, -1, '2014-07-26 06:33:10', '34345454', 'Cash', NULL, 'Office Supplies', '5000.00', '...', 1, 'Wrong amount', 1, 0);

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
  `amount` decimal(10,2) NOT NULL COMMENT 'Amount to be Paid',
  `dateposted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date Posted',
  `uid` int(10) unsigned NOT NULL COMMENT 'User who added the charges',
  `ledgerid` int(10) unsigned NOT NULL COMMENT 'Ledger ID if paid',
  `amountpaid` decimal(10,2) NOT NULL COMMENT 'Amount paid, to check partial payment',
  `winterest` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'With Interest?',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is active?',
  `cancelremarks` varchar(512) NOT NULL COMMENT 'Cancel remarks',
  `canceluser` int(10) unsigned NOT NULL COMMENT 'User who deleted charges'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Table of Charges' AUTO_INCREMENT=137 ;

--
-- Truncate table before insert `charges`
--

TRUNCATE TABLE `charges`;
--
-- Dumping data for table `charges`
--

INSERT INTO `charges` (`id`, `lot`, `homeowner`, `description`, `amount`, `dateposted`, `uid`, `ledgerid`, `amountpaid`, `winterest`, `active`, `cancelremarks`, `canceluser`) VALUES
(1, 1, 1, 'P1L1B1-2345 due for May 2014', '444.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 2),
(2, 2, 1, 'P1L2B2-3453 due for May 2014', '492.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(3, 3, 1, 'P1L2B1-452151 due for May 2014', '800.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(4, 4, 2, 'P1L3B1-54545 due for May 2014', '1600.00', '2014-05-01 04:39:34', 0, 2, '1600.00', 1, 1, '', 0),
(5, 5, 4, 'P1L2B3-12345 due for May 2014', '400.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(6, 6, 6, 'P5L5B5-12345 due for May 2014', '400.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(7, 10, 1, 'jaylotnum due for May 2014', '216.00', '2014-05-01 04:39:34', 0, 1, '216.00', 1, 1, '', 0),
(8, 11, 16, '17856 due for May 2014', '1156.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(9, 12, 17, 'P2L97K30 due for May 2014', '480.00', '2014-05-01 04:39:34', 0, 9, '480.00', 1, 1, '', 0),
(10, 13, 18, 'MVS11 due for May 2014', '0.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(11, 14, 30, 'MVS Lot.1 due for May 2014', '400.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(12, 15, 19, 'MVS Lot.2 due for May 2014', '320.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(13, 16, 21, 'MVS Lot.3 due for May 2014', '360.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(14, 17, 22, 'MVS Lot.4 due for May 2014', '300.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(15, 18, 24, 'MVS Lot.5 due for May 2014', '400.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(16, 19, 25, 'MVS Lot.6 due for May 2014', '600.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(17, 20, 26, 'MVS Lot.7 due for May 2014', '440.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(18, 21, 28, 'MVS Lot.8 due for May 2014', '640.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(19, 22, 29, 'MVS Lot.9 due for May 2014', '640.00', '2014-05-01 04:39:34', 0, 0, '0.00', 1, 1, '', 0),
(20, 1, 1, 'P1L1B1-2345 interest for Jun 2014', '44.40', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(21, 1, 1, 'P1L1B1-2345 due for Jun 2014', '444.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(22, 2, 1, 'P1L2B2-3453 interest for Jun 2014', '49.20', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(23, 2, 1, 'P1L2B2-3453 due for Jun 2014', '492.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(24, 3, 1, 'P1L2B1-452151 interest for Jun 2014', '80.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(25, 3, 1, 'P1L2B1-452151 due for Jun 2014', '800.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(26, 4, 2, 'P1L3B1-54545 interest for Jun 2014', '160.00', '2014-06-01 04:40:04', 0, 2, '160.00', 1, 1, '', 0),
(27, 4, 2, 'P1L3B1-54545 due for Jun 2014', '1600.00', '2014-06-01 04:40:04', 0, 8, '1390.00', 1, 1, '', 0),
(28, 5, 4, 'P1L2B3-12345 interest for Jun 2014', '40.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(29, 5, 4, 'P1L2B3-12345 due for Jun 2014', '400.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(30, 6, 6, 'P5L5B5-12345 interest for Jun 2014', '40.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(31, 6, 6, 'P5L5B5-12345 due for Jun 2014', '400.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(32, 10, 1, 'jaylotnum interest for Jun 2014', '21.60', '2014-06-01 04:40:04', 0, 1, '21.60', 1, 1, '', 0),
(33, 10, 1, 'jaylotnum due for Jun 2014', '216.00', '2014-06-01 04:40:04', 0, 1, '216.00', 1, 1, '', 0),
(34, 11, 16, '17856 interest for Jun 2014', '115.60', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(35, 11, 16, '17856 due for Jun 2014', '1156.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(36, 12, 17, 'P2L97K30 interest for Jun 2014', '48.00', '2014-06-01 04:40:04', 0, 9, '48.00', 1, 1, '', 0),
(37, 12, 17, 'P2L97K30 due for Jun 2014', '480.00', '2014-06-01 04:40:04', 0, 9, '480.00', 1, 1, '', 0),
(38, 13, 18, 'MVS11 due for Jun 2014', '0.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(39, 14, 30, 'MVS Lot.1 interest for Jun 2014', '40.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(40, 14, 30, 'MVS Lot.1 due for Jun 2014', '400.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(41, 15, 19, 'MVS Lot.2 interest for Jun 2014', '32.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(42, 15, 19, 'MVS Lot.2 due for Jun 2014', '320.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(43, 16, 21, 'MVS Lot.3 interest for Jun 2014', '36.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(44, 16, 21, 'MVS Lot.3 due for Jun 2014', '360.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(45, 17, 22, 'MVS Lot.4 interest for Jun 2014', '30.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(46, 17, 22, 'MVS Lot.4 due for Jun 2014', '300.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(47, 18, 24, 'MVS Lot.5 interest for Jun 2014', '40.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(48, 18, 24, 'MVS Lot.5 due for Jun 2014', '400.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(49, 19, 25, 'MVS Lot.6 interest for Jun 2014', '60.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(50, 19, 25, 'MVS Lot.6 due for Jun 2014', '600.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(51, 20, 26, 'MVS Lot.7 interest for Jun 2014', '44.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(52, 20, 26, 'MVS Lot.7 due for Jun 2014', '440.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(53, 21, 28, 'MVS Lot.8 interest for Jun 2014', '64.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(54, 21, 28, 'MVS Lot.8 due for Jun 2014', '640.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(55, 22, 29, 'MVS Lot.9 interest for Jun 2014', '64.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(56, 22, 29, 'MVS Lot.9 due for Jun 2014', '640.00', '2014-06-01 04:40:04', 0, 0, '0.00', 1, 1, '', 0),
(57, 1, 1, 'P1L1B1-2345 interest for Jul 2014', '93.24', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(58, 1, 1, 'P1L1B1-2345 due for Jul 2014', '444.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(59, 2, 1, 'P1L2B2-3453 interest for Jul 2014', '103.32', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(60, 2, 1, 'P1L2B2-3453 due for Jul 2014', '492.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(61, 3, 1, 'P1L2B1-452151 interest for Jul 2014', '168.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(62, 3, 1, 'P1L2B1-452151 due for Jul 2014', '800.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(63, 4, 2, 'P1L3B1-54545 interest for Jul 2014', '336.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(64, 4, 2, 'P1L3B1-54545 due for Jul 2014', '1600.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(65, 5, 4, 'P1L2B3-12345 interest for Jul 2014', '84.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(66, 5, 4, 'P1L2B3-12345 due for Jul 2014', '400.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(67, 6, 6, 'P5L5B5-12345 interest for Jul 2014', '84.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(68, 6, 6, 'P5L5B5-12345 due for Jul 2014', '400.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(69, 10, 1, 'jaylotnum interest for Jul 2014', '45.36', '2014-07-23 04:41:42', 0, 1, '45.36', 1, 1, '', 0),
(70, 10, 1, 'jaylotnum due for Jul 2014', '216.00', '2014-07-23 04:41:42', 0, 1, '216.00', 1, 1, '', 0),
(71, 11, 16, '17856 interest for Jul 2014', '242.76', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(72, 11, 16, '17856 due for Jul 2014', '1156.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(73, 12, 17, 'P2L97K30 interest for Jul 2014', '100.80', '2014-07-23 04:41:42', 0, 9, '100.80', 1, 1, '', 0),
(74, 12, 17, 'P2L97K30 due for Jul 2014', '480.00', '2014-07-23 04:41:42', 0, 9, '480.00', 1, 1, '', 0),
(75, 13, 18, 'MVS11 due for Jul 2014', '0.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(76, 14, 30, 'MVS Lot.1 interest for Jul 2014', '84.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(77, 14, 30, 'MVS Lot.1 due for Jul 2014', '400.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(78, 15, 19, 'MVS Lot.2 interest for Jul 2014', '67.20', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(79, 15, 19, 'MVS Lot.2 due for Jul 2014', '320.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(80, 16, 21, 'MVS Lot.3 interest for Jul 2014', '75.60', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(81, 16, 21, 'MVS Lot.3 due for Jul 2014', '360.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(82, 17, 22, 'MVS Lot.4 interest for Jul 2014', '63.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(83, 17, 22, 'MVS Lot.4 due for Jul 2014', '300.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(84, 18, 24, 'MVS Lot.5 interest for Jul 2014', '84.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(85, 18, 24, 'MVS Lot.5 due for Jul 2014', '400.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(86, 19, 25, 'MVS Lot.6 interest for Jul 2014', '126.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(87, 19, 25, 'MVS Lot.6 due for Jul 2014', '600.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(88, 20, 26, 'MVS Lot.7 interest for Jul 2014', '92.40', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(89, 20, 26, 'MVS Lot.7 due for Jul 2014', '440.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(90, 21, 28, 'MVS Lot.8 interest for Jul 2014', '134.40', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(91, 21, 28, 'MVS Lot.8 due for Jul 2014', '640.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(92, 22, 29, 'MVS Lot.9 interest for Jul 2014', '134.40', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(93, 22, 29, 'MVS Lot.9 due for Jul 2014', '640.00', '2014-07-23 04:41:42', 0, 0, '0.00', 1, 1, '', 0),
(94, 1, 1, 'Parking Penalty', '700.00', '2014-07-23 05:06:17', 1, 1, '700.00', 0, 1, '', 0),
(95, 1, 1, 'Waste Segregation Penalty', '300.00', '2014-07-23 05:06:57', 1, 1, '300.00', 0, 1, '', 0),
(96, 3, 1, 'Parking Penalty', '700.00', '2014-07-24 01:20:20', 1, 0, '0.00', 0, 1, '', 0),
(97, 10, 1, 'Advanced Payment', '0.00', '2014-07-24 03:08:08', 1, 4, '888.00', 1, 1, '', 0),
(98, 1, 1, 'Advanced Payment', '0.00', '2014-07-24 03:12:49', 1, 5, '888.00', 1, 1, 'Wrong Charges', 1),
(99, 1, 1, 'Littering', '100.00', '2014-07-24 03:27:32', 1, 0, '0.00', 0, 1, 'Wrong accusation', 1),
(100, 1, 1, 'P1L1B1-2345 interest for Aug 2014', '58.16', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(101, 1, 1, 'P1L1B1-2345 due for Aug 2014', '444.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(102, 2, 1, 'P1L2B2-3453 interest for Aug 2014', '162.85', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(103, 2, 1, 'P1L2B2-3453 due for Aug 2014', '492.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(104, 3, 1, 'P1L2B1-452151 interest for Aug 2014', '264.80', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(105, 3, 1, 'P1L2B1-452151 due for Aug 2014', '800.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(106, 4, 2, 'P1L3B1-54545 interest for Aug 2014', '253.60', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(107, 4, 2, 'P1L3B1-54545 due for Aug 2014', '1600.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(108, 5, 4, 'P1L2B3-12345 interest for Aug 2014', '132.40', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(109, 5, 4, 'P1L2B3-12345 due for Aug 2014', '400.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(110, 6, 6, 'P5L5B5-12345 interest for Aug 2014', '132.40', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(111, 6, 6, 'P5L5B5-12345 due for Aug 2014', '400.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(112, 10, 1, 'jaylotnum due for Aug 2014', '216.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(113, 11, 16, '17856 interest for Aug 2014', '382.64', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(114, 11, 16, '17856 due for Aug 2014', '1156.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(115, 12, 17, 'P2L97K30 interest for Aug 2014', '158.88', '2014-08-01 03:27:56', 0, 9, '158.88', 1, 1, '', 0),
(116, 12, 17, 'P2L97K30 due for Aug 2014', '480.00', '2014-08-01 03:27:56', 0, 9, '480.00', 1, 1, '', 0),
(117, 13, 18, 'MVS11 due for Aug 2014', '0.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(118, 14, 30, 'MVS Lot.1 interest for Aug 2014', '132.40', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(119, 14, 30, 'MVS Lot.1 due for Aug 2014', '400.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(120, 15, 19, 'MVS Lot.2 interest for Aug 2014', '105.92', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(121, 15, 19, 'MVS Lot.2 due for Aug 2014', '320.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(122, 16, 21, 'MVS Lot.3 interest for Aug 2014', '119.16', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(123, 16, 21, 'MVS Lot.3 due for Aug 2014', '360.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(124, 17, 22, 'MVS Lot.4 interest for Aug 2014', '99.30', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(125, 17, 22, 'MVS Lot.4 due for Aug 2014', '300.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(126, 18, 24, 'MVS Lot.5 interest for Aug 2014', '132.40', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(127, 18, 24, 'MVS Lot.5 due for Aug 2014', '400.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(128, 19, 25, 'MVS Lot.6 interest for Aug 2014', '198.60', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(129, 19, 25, 'MVS Lot.6 due for Aug 2014', '600.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(130, 20, 26, 'MVS Lot.7 interest for Aug 2014', '145.64', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(131, 20, 26, 'MVS Lot.7 due for Aug 2014', '440.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(132, 21, 28, 'MVS Lot.8 interest for Aug 2014', '211.84', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(133, 21, 28, 'MVS Lot.8 due for Aug 2014', '640.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(134, 22, 29, 'MVS Lot.9 interest for Aug 2014', '211.84', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(135, 22, 29, 'MVS Lot.9 due for Aug 2014', '640.00', '2014-08-01 03:27:56', 0, 0, '0.00', 1, 1, '', 0),
(136, 25, 32, 'PDAF', '40000.00', '2014-07-28 04:35:50', 8, 11, '8000.00', 0, 1, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gatepass`
--
-- Creation: Jul 24, 2014 at 05:08 AM
--

DROP TABLE IF EXISTS `gatepass`;
CREATE TABLE `gatepass` (
`id` int(10) unsigned NOT NULL COMMENT 'Gatepass ID',
  `serial` varchar(255) NOT NULL COMMENT 'Control Number',
  `homeowner` int(10) unsigned NOT NULL COMMENT 'Homeowner ID (FK)',
  `plateno` varchar(100) NOT NULL COMMENT 'Vehicle Plate Number',
  `model` varchar(1000) NOT NULL COMMENT 'Vehicle Model',
  `remarks` varchar(1000) NOT NULL COMMENT 'Remarks',
  `userid` int(10) unsigned NOT NULL COMMENT 'User Added',
  `transactiondate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date Added'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Gate Pass Sticker Table' AUTO_INCREMENT=9 ;

--
-- RELATIONS FOR TABLE `gatepass`:
--   `id`
--       `homeowner` -> `id`
--

--
-- Truncate table before insert `gatepass`
--

TRUNCATE TABLE `gatepass`;
--
-- Dumping data for table `gatepass`
--

INSERT INTO `gatepass` (`id`, `serial`, `homeowner`, `plateno`, `model`, `remarks`, `userid`, `transactiondate`) VALUES
(5, '000001', 1, 'ABC123', 'Ferrari Enzo', 'Made in China, Class A', 1, '2014-07-24 08:40:16'),
(6, '000002', 1, 'XYZ890', 'Sarao Jeepney', 'Gold-plated', 1, '2014-07-24 08:40:39'),
(7, '158964', 32, '45875', '454165456', 'ASDFGFAG', 8, '2014-07-28 04:20:12'),
(8, '11232', 8, 'JA21', 'AFGAF', 'AFGG', 8, '2014-07-28 04:20:38');

-- --------------------------------------------------------

--
-- Table structure for table `homeowner`
--
-- Creation: Jul 24, 2014 at 02:10 AM
-- Last update: Jul 28, 2014 at 04:15 AM
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
  `bonddesc` varchar(1000) NOT NULL COMMENT 'Bond Description',
  `gatepass` tinyint(1) NOT NULL COMMENT 'Gate Pass Sticker'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Homeowner Information' AUTO_INCREMENT=33 ;

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

INSERT INTO `homeowner` (`id`, `lastname`, `firstname`, `middlename`, `contactno`, `email`, `user`, `dateadded`, `active`, `bond`, `bonddesc`, `gatepass`) VALUES
(1, 'Delas Alas', 'Jay', 'Bagasbas', '09478929659', 'jay_delasalas@ymail.com', 1, '2014-06-28 13:19:31', 1, 300000, 'Home renovations', 1),
(2, 'Garcia', 'Mark Denrich', 'Deocales', '091738363937', 'adfadjfkjdf@df', 1, '2014-06-28 14:02:25', 1, 0, '', 1),
(3, 'Veloro', 'Riechelle', 'Naval', '0909809809', 'sdfs@df', 1, '2014-06-29 03:19:22', 1, 0, '', 1),
(4, 'Maas', 'Joed', 'Marquez', '39839489384', 'df@dfd', 1, '2014-06-29 03:19:35', 1, 5000, '', 1),
(5, 'Dela Cruz', 'Juan', 'Bautista', '3403940', 'ffg@df', 1, '2014-06-29 03:19:53', 1, 0, '', 0),
(6, 'Khan', 'Shao', 'X', '09999999999', 'sh@o', 1, '2014-06-29 03:20:08', 1, 0, '', 0),
(7, 'Dfadf', 'adfasfd', 'adfa', 'dfadf', 'adfa@d', 1, '2014-06-29 03:20:16', 0, 0, '', 0),
(8, 'Dfadf', 'adfa', 'Dfdfd', 'ffdaf', 'df@d', 1, '2014-06-29 03:20:26', 0, 0, '', 0),
(9, 'Adfafda', 'dfadf', 'adfasdf', 'asdf', 'dfas@d', 1, '2014-06-29 03:20:38', 0, 0, '', 0),
(10, 'dfdf', 'k', 'kjkj', 'kjk', 'j@kj', 1, '2014-06-29 03:20:44', 0, 0, '', 0),
(11, 'llklkl', 'klkl', 'klk', 'lklk', 'lk@kj.com', 1, '2014-06-29 03:20:51', 0, 0, '', 0),
(12, 'lklkl', 'klkl', 'klk', 'lkl', 'kl@lk', 1, '2014-06-29 03:20:58', 0, 0, '', 0),
(13, 'delas alas', 'jay', 'bagasbas', '09478929659', 'jay_delasalas@ymail.com', 1, '2014-07-01 06:17:51', 1, 0, '', 0),
(14, 'delas alas', 'jay', 'bagasbas', '09078455977', '', 1, '2014-07-02 08:13:58', 0, 0, '', 0),
(15, 'Paalam', 'Huling', 'X', '', '', 1, '2014-07-14 02:50:59', 1, 0, '', 0),
(16, 'OTHER INCOME ', 'RECYCLE', 'X', '', '', 1, '2014-07-14 06:39:10', 1, 0, '', 0),
(17, 'Chui', 'Kim', 'X', '09478929659', 'kim_chui@yahoo.com', 1, '2014-07-19 02:24:18', 1, 50000, '', 1),
(18, 'veloro', 'adrian', 'd', '09081645714', '', 1, '2014-07-19 03:34:34', 1, 0, '', 0),
(19, 'Calipit', 'ma teresa', 'x', '09081645700', '', 1, '2014-07-19 04:17:37', 0, 0, '', 0),
(20, 'Calipit', 'ma teresa', 'x', '09081645700', '', 1, '2014-07-19 04:17:38', 1, 0, '', 0),
(21, 'reyes', 'jene', 's', '09081645800', '', 1, '2014-07-19 04:20:25', 1, 0, '', 0),
(22, 'hulgado', 'jayson', 'g', '09081645716', '', 1, '2014-07-19 04:26:43', 1, 0, '', 0),
(23, 'hulgado', 'jayson', 'g', '09081645716', '', 1, '2014-07-19 04:26:46', 1, 0, '', 0),
(24, 'garcia', 'dave', 'e', '09081645720', '', 1, '2014-07-19 04:27:12', 1, 0, '', 0),
(25, 'loropay', 'crisaline', 'x', '09081645240', '', 1, '2014-07-19 04:29:12', 1, 0, '', 0),
(26, 'loropay', 'crisabel', 'x', '09081645140', '', 1, '2014-07-19 04:31:10', 1, 0, '', 0),
(27, 'loropay', 'crisabel', 'x', '09081645140', '', 1, '2014-07-19 04:31:13', 1, 0, '', 0),
(28, 'navarro', 'vhong', 's', '09081645604', '', 1, '2014-07-19 04:32:10', 1, 0, '', 0),
(29, 'curtis', 'anne', 's', '09081645740', '', 1, '2014-07-19 04:34:11', 1, 0, '', 0),
(30, 'ganda', 'vice', 'x', '09081645748', '', 1, '2014-07-19 04:35:49', 1, 0, '', 0),
(31, 'SIVHAI', '.', '.', '(042) 797–0544', 'st7isabel_village@yahoo.com.ph', 1, '2014-07-24 05:18:16', 0, 0, '', 0),
(32, 'NAPOLES', 'JANET', 'LIM', '09078455977', '', 8, '2014-07-28 04:15:24', 1, 10000000, '', 0);

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
  `paymentmode` varchar(100) NOT NULL DEFAULT 'Cash' COMMENT 'Mode of Payment (Cash/Check)',
  `checkno` varchar(100) DEFAULT NULL COMMENT 'Check Number',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If ledger is active',
  `cancelremarks` varchar(512) NOT NULL COMMENT 'Cancellation Remarks',
  `canceluser` int(10) unsigned NOT NULL COMMENT 'User who cancelled the payment'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Ledger Details' AUTO_INCREMENT=12 ;

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

INSERT INTO `ledger` (`id`, `ornumber`, `payee`, `homeowner`, `user`, `transactiondate`, `remarks`, `paymentmode`, `checkno`, `active`, `cancelremarks`, `canceluser`) VALUES
(1, '00000001', 'Jay delas Alas', 1, 1, '2014-07-23 06:53:33', 'Post-dated Check', 'Check', '34343-343443-23434', 1, '', 0),
(2, '1343434', 'Mark Garcia', 2, 1, '2014-07-24 01:07:36', '...', 'Cash', NULL, 1, '', 0),
(3, '343434', 'Mark Garcia', 2, 1, '2014-07-24 01:08:42', '', 'Cash', NULL, 1, '', 0),
(4, '45453454', 'Jay delas Alas', 1, 1, '2014-07-24 03:10:50', 'Adv. Payment up to Aug', 'Check', '4543-454-3333', 1, '', 0),
(5, '234545', 'Jay delas Alas', 1, 1, '2014-07-24 03:13:26', '', 'Cash', NULL, 1, 'Wrong amount', 1),
(7, '00000002', 'Mark Garcia', 2, 1, '2014-07-24 09:24:03', 'Partial', 'Cash', NULL, 1, '', 0),
(8, '34342343', 'Mark Garcia', 2, 1, '2014-07-24 09:45:41', '', 'Cash', NULL, 1, '', 0),
(9, '34345567', 'Gerald Anderson', 17, 1, '2014-07-26 07:31:03', '', 'Cash', NULL, 1, '', 0),
(10, '123546', 'JAY', 32, 8, '2014-07-28 04:36:46', '', 'Cash', NULL, 0, 'WRONG INPUT', 8),
(11, '78988', '56454465654', 32, 8, '2014-07-28 04:40:13', '13212332', 'Cash', NULL, 1, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `ledgeritem`
--
-- Creation: Jul 22, 2014 at 05:08 AM
--

DROP TABLE IF EXISTS `ledgeritem`;
CREATE TABLE `ledgeritem` (
`id` int(10) unsigned NOT NULL COMMENT 'Item ID',
  `ledgerid` int(10) unsigned NOT NULL COMMENT 'Ledger ID (FK)',
  `chargeid` int(10) unsigned NOT NULL COMMENT 'Charge ID (FK)',
  `description` varchar(1000) NOT NULL COMMENT 'Description',
  `amount` decimal(10,2) NOT NULL COMMENT 'Amount',
  `uid` int(10) unsigned NOT NULL COMMENT 'User who added',
  `transactiondate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Transaction Date',
  `amountpaid` decimal(10,2) NOT NULL COMMENT 'Amount Paid'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Truncate table before insert `ledgeritem`
--

TRUNCATE TABLE `ledgeritem`;
--
-- Dumping data for table `ledgeritem`
--

INSERT INTO `ledgeritem` (`id`, `ledgerid`, `chargeid`, `description`, `amount`, `uid`, `transactiondate`, `amountpaid`) VALUES
(1, 1, 7, 'jaylotnum due for May 2014', '216.00', 1, '2014-07-23 06:53:33', '216.00'),
(2, 1, 32, 'jaylotnum interest for Jun 2014', '21.60', 1, '2014-07-23 06:53:33', '21.60'),
(3, 1, 33, 'jaylotnum due for Jun 2014', '216.00', 1, '2014-07-23 06:53:33', '216.00'),
(4, 1, 69, 'jaylotnum interest for Jul 2014', '45.36', 1, '2014-07-23 06:53:33', '45.36'),
(5, 1, 70, 'jaylotnum due for Jul 2014', '216.00', 1, '2014-07-23 06:53:33', '216.00'),
(6, 1, 94, 'Parking Penalty', '700.00', 1, '2014-07-23 06:53:33', '700.00'),
(7, 1, 95, 'Waste Segregation Penalty', '300.00', 1, '2014-07-23 06:53:33', '300.00'),
(8, 2, 4, 'P1L3B1-54545 due for May 2014', '1600.00', 1, '2014-07-24 01:07:36', '1600.00'),
(9, 2, 26, 'P1L3B1-54545 interest for Jun 2014', '160.00', 1, '2014-07-24 01:07:36', '160.00'),
(10, 3, 27, 'P1L3B1-54545 due for Jun 2014', '1600.00', 1, '2014-07-24 01:08:42', '1000.00'),
(11, 4, 97, 'Advanced Payment', '0.00', 1, '2014-07-24 03:10:51', '888.00'),
(12, 5, 98, 'Advanced Payment', '0.00', 1, '2014-07-24 03:13:26', '888.00'),
(13, 7, 27, 'P1L3B1-54545 due for Jun 2014', '1600.00', 1, '2014-07-24 09:24:03', '389.60'),
(14, 8, 27, 'P1L3B1-54545 due for Jun 2014', '1600.00', 1, '2014-07-24 09:45:41', '0.40'),
(15, 9, 9, 'P2L97K30 due for May 2014', '480.00', 1, '2014-07-26 07:31:03', '480.00'),
(16, 9, 36, 'P2L97K30 interest for Jun 2014', '48.00', 1, '2014-07-26 07:31:03', '48.00'),
(17, 9, 37, 'P2L97K30 due for Jun 2014', '480.00', 1, '2014-07-26 07:31:03', '480.00'),
(18, 9, 74, 'P2L97K30 due for Jul 2014', '480.00', 1, '2014-07-26 07:31:03', '480.00'),
(19, 9, 73, 'P2L97K30 interest for Jul 2014', '100.80', 1, '2014-07-26 07:31:03', '100.80'),
(20, 9, 115, 'P2L97K30 interest for Aug 2014', '158.88', 1, '2014-07-26 07:31:03', '158.88'),
(21, 9, 116, 'P2L97K30 due for Aug 2014', '480.00', 1, '2014-07-26 07:31:03', '480.00'),
(22, 10, 136, 'PDAF', '40000.00', 8, '2014-07-28 04:36:46', '2000.00'),
(23, 11, 136, 'PDAF', '40000.00', 8, '2014-07-28 04:40:13', '6000.00');

-- --------------------------------------------------------

--
-- Table structure for table `lot`
--
-- Creation: Jun 26, 2014 at 04:25 PM
--

DROP TABLE IF EXISTS `lot`;
CREATE TABLE `lot` (
`id` int(10) NOT NULL COMMENT 'Lot ID',
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='House Lot Information' AUTO_INCREMENT=26 ;

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
(5, 'P1L2B3-12345', 4, '2012-06-01', '100', '13', 'Empire', '5', '5', '2', 2, 1, '', '2014-07-03 09:00:46', 1, 1),
(6, 'P5L5B5-12345', 6, '2012-06-12', '100', '13', 'Empire St.', '5', '5', '5', 100, 0, '', '2014-07-03 09:04:07', 1, 1),
(7, 'P1L1B1-8888', 0, '2009-07-16', '80', '2', 'Malunggay', '444', '4', '2', 2, 0, '', '2014-07-04 10:50:41', 1, 1),
(8, 'P1L1B1-9999', 0, '0000-00-00', '300', '34', 'Emerald', '4', '23', '1', 0, 0, '', '2014-07-05 07:29:21', 1, 1),
(9, 'jaylotnum', 0, '0000-00-00', '54', '52', 'market view', '65', '12', '4', 0, 0, '', '2014-07-07 05:35:30', 1, 0),
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
(23, 'MVS Lot.10', 0, '0000-00-00', '130', '10', 'MVS phase10', '10', '10', '10', 0, 0, '', '2014-07-19 03:30:21', 1, 1),
(24, 'P1L3B1-6735', 3, '2013-01-01', '150', '23', 'Lakambini', '3', '1', '1', 0, 0, '', '2014-07-27 17:56:20', 1, 1),
(25, 'testjay', 32, '2014-01-01', '2000', '10021', 'Test test', 'Test test', 'Test test', 'Test test', 0, 0, '', '2014-07-28 03:49:05', 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--
-- Creation: Jul 26, 2014 at 12:55 AM
-- Last update: Jul 29, 2014 at 02:35 AM
--

DROP TABLE IF EXISTS `resident`;
CREATE TABLE `resident` (
`id` int(10) unsigned NOT NULL COMMENT 'Resident ID',
  `fullname` varchar(512) NOT NULL COMMENT 'Full Name',
  `gender` enum('Unspecified','Male','Female','') NOT NULL DEFAULT 'Unspecified' COMMENT 'Gender',
  `household` int(11) NOT NULL COMMENT 'Household (FK)',
  `status` int(10) unsigned NOT NULL COMMENT 'Resident Status',
  `user` int(10) unsigned NOT NULL COMMENT 'User (FK)',
  `dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

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
--
-- Dumping data for table `resident`
--

INSERT INTO `resident` (`id`, `fullname`, `gender`, `household`, `status`, `user`, `dateadded`) VALUES
(1, 'Monica Garcia', 'Female', 4, 2, 1, '2014-07-26 01:06:29'),
(4, '', 'Unspecified', 1, 3, 1, '2014-07-26 07:40:38'),
(5, 'Adrian Veloro', 'Male', 24, 2, 1, '2014-07-27 17:59:00'),
(7, 'Pepe', 'Female', 1, 5, 1, '2014-07-29 02:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--
-- Creation: Jul 23, 2014 at 03:49 AM
-- Last update: Jul 28, 2014 at 03:13 AM
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
  `price` decimal(10,2) NOT NULL COMMENT 'Price/Sq Meter',
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
(1, 'Santa Isabel Village Homeowners Association, Inc.', 'SIVHAI', 'Santa Isabel Village', 'Isabang', 'Lucena City', 'Quezon', '4301', '(042) 797–0544', 'st7isabel_village@yahoo.com.ph', '4.00', '0.1000', 3, '2014-08-01');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--
-- Creation: Jul 26, 2014 at 01:13 AM
-- Last update: Jul 29, 2014 at 02:26 AM
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
`id` int(10) unsigned NOT NULL COMMENT 'Status ID',
  `description` varchar(255) NOT NULL COMMENT 'Description',
  `ischild` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is child?'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Residential Status' AUTO_INCREMENT=8 ;

--
-- Truncate table before insert `status`
--

TRUNCATE TABLE `status`;
--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `description`, `ischild`) VALUES
(1, 'Homeowner', 0),
(2, 'Resident', 0),
(3, 'Care Taker', 0),
(4, 'Transient', 0),
(5, 'Resident (Minor)', 1),
(6, 'Transient (Minor)', 1),
(7, 'Staff', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--
-- Creation: Jul 26, 2014 at 11:07 AM
-- Last update: Jul 28, 2014 at 04:13 AM
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
`id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `fullname` varchar(512) NOT NULL COMMENT 'Full Name',
  `username` varchar(32) NOT NULL COMMENT 'Username',
  `password` varchar(50) NOT NULL COMMENT 'Hashed Password',
  `datereg` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date registered',
  `permission` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT 'Set of Permissions',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is active?',
  `question` varchar(500) NOT NULL COMMENT 'Security Question',
  `answer` varchar(100) NOT NULL COMMENT 'Hashed Security Answer'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='User Information' AUTO_INCREMENT=9 ;

--
-- Truncate table before insert `user`
--

TRUNCATE TABLE `user`;
--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fullname`, `username`, `password`, `datereg`, `permission`, `active`, `question`, `answer`) VALUES
(1, 'System Admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', '2014-06-28 13:11:40', 33554431, 1, 'What is my password?', '2dfe32c2831b642e24f854a3785c5782'),
(2, 'Vekou X Aitenshi', 'vekou', '149afd631693c895f81e508eb5aaef37', '2014-07-16 14:41:04', 116249, 1, 'What is my password?', '5f4dcc3b5aa765d61d8327deb882cf99'),
(3, 'Jay delas Alas', 'jay', '74df9ed7b79cfcbca84002619b670802', '2014-07-16 14:50:14', 485, 1, 'What is the capital of Philippines?', ''),
(4, 'Vekoux Aitenshi', 'vekoux', '74df9ed7b79cfcbca84002619b670802', '2014-07-26 13:05:34', 8390685, 0, 'What is my password', '5f4dcc3b5aa765d61d8327deb882cf99'),
(7, 'Riechelle Veloro', 'cheng', '74df9ed7b79cfcbca84002619b670802', '2014-07-27 17:36:35', 8391133, 0, 'What is my cellphone?', 'a4b69a497f5d48d0eab4612f6c10ae69'),
(5, 'Mark Garcia', 'mark', 'd41d8cd98f00b204e9800998ecf8427e', '2014-07-27 06:34:17', 8390685, 1, 'What is my password?', '2dfe32c2831b642e24f854a3785c5782'),
(6, 'Joed Maas', 'joed', 'd41d8cd98f00b204e9800998ecf8427e', '2014-07-27 07:14:46', 8390685, 1, 'What is the brand of my Badminton Racket?', '5e236d9eefeb5e77133a6718bbbc44f0'),
(8, 'jay b. delas alas ', 'jayjade', '7f9059211cfcf3303b45585e5b97897e', '2014-07-28 03:35:31', 33488895, 1, 'ading', '31edb762814dac4b2d00bea6986ef2c4');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cashflows`
--
ALTER TABLE `cashflows`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `charges`
--
ALTER TABLE `charges`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gatepass`
--
ALTER TABLE `gatepass`
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
 ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `cashflows`
--
ALTER TABLE `cashflows`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Cash Flow ID',AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `charges`
--
ALTER TABLE `charges`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Charge ID',AUTO_INCREMENT=137;
--
-- AUTO_INCREMENT for table `gatepass`
--
ALTER TABLE `gatepass`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Gatepass ID',AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `homeowner`
--
ALTER TABLE `homeowner`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Homeowner ID',AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ledger ID',AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `ledgeritem`
--
ALTER TABLE `ledgeritem`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Item ID',AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `lot`
--
ALTER TABLE `lot`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Lot ID',AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT for table `resident`
--
ALTER TABLE `resident`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Resident ID',AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Settings ID',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Status ID',AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User ID',AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
