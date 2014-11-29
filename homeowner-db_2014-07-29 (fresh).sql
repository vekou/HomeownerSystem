-- phpMyAdmin SQL Dump
-- version 4.2.3
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 29, 2014 at 04:48 AM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cash Flow for the Association' AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `cashflows`
--

TRUNCATE TABLE `cashflows`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table of Charges' AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `charges`
--

TRUNCATE TABLE `charges`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Gate Pass Sticker Table' AUTO_INCREMENT=1 ;

--
-- RELATIONS FOR TABLE `gatepass`:
--   `id`
--       `homeowner` -> `id`
--

--
-- Truncate table before insert `gatepass`
--

TRUNCATE TABLE `gatepass`;
-- --------------------------------------------------------

--
-- Table structure for table `homeowner`
--
-- Creation: Jul 29, 2014 at 03:41 AM
-- Last update: Jul 29, 2014 at 03:41 AM
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Homeowner Information' AUTO_INCREMENT=1 ;

--
-- RELATIONS FOR TABLE `homeowner`:
--   `user`
--       `user` -> `id`
--

--
-- Truncate table before insert `homeowner`
--

TRUNCATE TABLE `homeowner`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ledger Details' AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `ledgeritem`
--

TRUNCATE TABLE `ledgeritem`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='House Lot Information' AUTO_INCREMENT=1 ;

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
-- --------------------------------------------------------

--
-- Table structure for table `resident`
--
-- Creation: Jul 29, 2014 at 03:41 AM
-- Last update: Jul 29, 2014 at 03:41 AM
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
-- Creation: Jul 29, 2014 at 03:41 AM
-- Last update: Jul 29, 2014 at 03:43 AM
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
(1, 'Santa Isabel Village Homeowners Association, Inc.', 'SIVHAI', 'Santa Isabel Village', 'Isabang', 'Lucena City', 'Quezon', '4301', '(042) 797 – 0544', 'st7isabel_village@yahoo.com.ph', '4.00', '0.1000', 0, '2014-07-29');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--
-- Creation: Jul 29, 2014 at 03:41 AM
-- Last update: Jul 29, 2014 at 03:41 AM
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
`id` int(10) unsigned NOT NULL COMMENT 'Status ID',
  `description` varchar(255) NOT NULL COMMENT 'Description',
  `ischild` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is child?'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Residential Status' AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `status`
--

TRUNCATE TABLE `status`;
-- --------------------------------------------------------

--
-- Table structure for table `user`
--
-- Creation: Jul 29, 2014 at 03:46 AM
-- Last update: Jul 29, 2014 at 03:46 AM
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
(1, 'System Admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', '2014-06-28 13:11:40', 33554431, 1, 'What is my password?', '2dfe32c2831b642e24f854a3785c5782');

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
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Cash Flow ID';
--
-- AUTO_INCREMENT for table `charges`
--
ALTER TABLE `charges`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Charge ID';
--
-- AUTO_INCREMENT for table `gatepass`
--
ALTER TABLE `gatepass`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Gatepass ID';
--
-- AUTO_INCREMENT for table `homeowner`
--
ALTER TABLE `homeowner`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Homeowner ID';
--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ledger ID';
--
-- AUTO_INCREMENT for table `ledgeritem`
--
ALTER TABLE `ledgeritem`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Item ID';
--
-- AUTO_INCREMENT for table `lot`
--
ALTER TABLE `lot`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Lot ID';
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
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Status ID';
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User ID',AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
