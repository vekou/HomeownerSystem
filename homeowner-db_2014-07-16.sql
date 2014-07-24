-- phpMyAdmin SQL Dump
-- version 4.2.3
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 16, 2014 at 06:35 PM
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
-- Table structure for table `homeowner`
--
-- Creation: Jul 15, 2014 at 12:17 PM
-- Last update: Jul 16, 2014 at 04:55 PM
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Homeowner Information' AUTO_INCREMENT=17 ;

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
(1, 'Delas Alas', 'Jay', 'Bagasbas', '09478929659', 'jay_delasalas@ymail.com', 1, '2014-06-28 13:19:31', 1, 10000, 1),
(2, 'Garcia', 'Mark Denrich', 'Deocales', '091738363937', 'adfadjfkjdf@df', 1, '2014-06-28 14:02:25', 1, 0, 1),
(3, 'Veloro', 'Riechelle', 'Naval', '0909809809', 'sdfs@df', 1, '2014-06-29 03:19:22', 1, 0, 1),
(4, 'Maas', 'Joed', 'Marquez', '39839489384', 'df@dfd', 1, '2014-06-29 03:19:35', 1, 5000, 1),
(5, 'Dela Cruz', 'Juan', 'Bautista', '3403940', 'ffg@df', 1, '2014-06-29 03:19:53', 1, 0, 0),
(6, 'Khan', 'Shao', 'X', '09999999999', 'sh@o', 1, '2014-06-29 03:20:08', 1, 0, 0),
(7, 'Dfadf', 'adfasfd', 'adfa', 'dfadf', 'adfa@d', 1, '2014-06-29 03:20:16', 1, 0, 0),
(8, 'Dfadf', 'adfa', 'Dfdfd', 'ffdaf', 'df@d', 1, '2014-06-29 03:20:26', 1, 0, 0),
(9, 'Adfafda', 'dfadf', 'adfasdf', 'asdf', 'dfas@d', 1, '2014-06-29 03:20:38', 0, 0, 0),
(10, 'dfdf', 'k', 'kjkj', 'kjk', 'j@kj', 1, '2014-06-29 03:20:44', 1, 0, 0),
(11, 'llklkl', 'klkl', 'klk', 'lklk', 'lk@kj.com', 1, '2014-06-29 03:20:51', 1, 0, 0),
(12, 'lklkl', 'klkl', 'klk', 'lkl', 'kl@lk', 1, '2014-06-29 03:20:58', 0, 0, 0),
(13, 'delas alas', 'jay', 'bagasbas', '09478929659', 'jay_delasalas@ymail.com', 1, '2014-07-01 06:17:51', 1, 0, 0),
(14, 'delas alas', 'jay', 'bagasbas', '09078455977', '', 1, '2014-07-02 08:13:58', 1, 0, 0),
(15, 'Paalam', 'Huling', 'X', '', '', 1, '2014-07-14 02:50:59', 1, 0, 0),
(16, 'OTHER INCOME ', 'RECYCLE', 'X', '', '', 1, '2014-07-14 06:39:10', 1, 0, 0);

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
  `paymentdate` date NOT NULL COMMENT 'Payment Date',
  `payee` varchar(255) NOT NULL COMMENT 'Name of Payee',
  `homeowner` int(11) NOT NULL COMMENT 'Homeowner (FK)',
  `user` int(10) unsigned NOT NULL COMMENT 'User who received the payment',
  `transactiondate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'System Transaction Date',
  `remarks` varchar(1000) NOT NULL COMMENT 'Remarks'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Ledger Details' AUTO_INCREMENT=50 ;

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

INSERT INTO `ledger` (`id`, `ornumber`, `paymentdate`, `payee`, `homeowner`, `user`, `transactiondate`, `remarks`) VALUES
(1, '1234355', '2014-07-02', 'Jay delas Alas', 1, 1, '2014-07-01 16:40:32', ''),
(2, '321654', '2014-07-02', 'Mark Garcia', 1, 1, '2014-07-02 08:04:37', ''),
(3, '123035', '2014-07-15', 'jay', 1, 1, '2014-07-02 08:12:54', ''),
(8, '234234', '2014-07-02', 'Jay delas Alas', 1, 1, '2014-07-02 14:02:15', ''),
(9, '865656', '2014-07-03', 'Mark Garcia', 2, 1, '2014-07-03 03:43:46', ''),
(10, '321654', '2014-07-04', 'Jay', 2, 1, '2014-07-03 03:47:45', ''),
(11, '528lhknmllk', '2003-05-06', 'mark', 1, 1, '2014-07-03 03:51:28', ''),
(12, '8956893', '2014-07-01', 'jay delas alas ', 1, 1, '2014-07-04 05:15:39', ''),
(13, '8956894', '2014-07-20', 'jay delas alas ', 1, 1, '2014-07-04 05:17:27', ''),
(14, '234234', '2014-07-06', 'Shao Khan', 6, 1, '2014-07-05 18:21:26', ''),
(15, '098765', '2014-07-21', 'Totoy Bibo', 1, 1, '2014-07-06 03:27:22', ''),
(45, '43534545', '2014-07-14', 'dfd dfdf', 1, 1, '2014-07-14 09:14:00', ''),
(46, '56789', '2014-07-14', 'dfguio', 1, 1, '2014-07-14 12:56:29', 'asdf?'),
(47, '12231', '2014-07-15', 'jay b delas alas', 16, 1, '2014-07-15 01:50:00', ''),
(48, '12231', '2014-07-15', 'jay b delas alas', 16, 1, '2014-07-15 01:50:02', 'assad'),
(49, '58956', '2014-07-16', 'jay b delas alas', 16, 1, '2014-07-15 01:50:56', 'zxcafdacaccac');

-- --------------------------------------------------------

--
-- Table structure for table `ledgeritem`
--
-- Creation: Jul 01, 2014 at 02:58 PM
--

DROP TABLE IF EXISTS `ledgeritem`;
CREATE TABLE `ledgeritem` (
  `id` int(11) NOT NULL COMMENT 'Ledger (FK)',
  `amount` float NOT NULL COMMENT 'Amount',
  `lot` int(11) NOT NULL COMMENT 'Lot ID',
  `startdate` date NOT NULL COMMENT 'Start of Date Range',
  `enddate` date NOT NULL COMMENT 'End of Date Range',
  `desc` varchar(1000) NOT NULL COMMENT 'Description'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ledger Items';

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

INSERT INTO `ledgeritem` (`id`, `amount`, `lot`, `startdate`, `enddate`, `desc`) VALUES
(1, 345, 1, '2014-05-01', '2014-05-31', 'P1L1B1-2345'),
(1, 234, 3, '2014-05-01', '2014-05-31', 'P1L2B1-452151'),
(2, 500, 1, '2014-05-01', '2014-05-31', 'P1L1B1-2345'),
(2, 400, 2, '2014-05-01', '2014-05-31', 'P1L2B2-3453'),
(2, 300, 3, '2014-05-01', '2014-05-31', 'P1L2B1-452151'),
(3, 300, 1, '2014-05-01', '2014-05-31', 'P1L1B1-2345'),
(3, 300, 2, '2014-05-01', '2014-05-31', 'P1L2B2-3453'),
(3, 3000, 3, '2014-05-01', '2014-05-31', 'P1L2B1-452151'),
(8, 400, 1, '2014-03-01', '2014-03-31', 'P1L1B1-2345'),
(8, 600, 2, '2014-02-01', '2014-03-31', 'P1L2B2-3453'),
(9, 1600, 4, '2014-05-01', '2014-05-31', 'P1L3B1-54545'),
(10, 1600, 4, '2014-04-01', '2014-04-30', 'P1L3B1-54545'),
(11, 50000, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(12, 6000, 1, '0000-00-00', '2014-02-28', 'P1L1B1-2345'),
(13, 65000, 1, '0000-00-00', '2014-02-28', 'P1L1B1-2345'),
(14, 1000, 6, '2014-01-01', '2013-03-31', 'P5L5B5-12345'),
(15, 500, 1, '2014-07-01', '2014-08-31', 'P1L1B1-2345'),
(15, 300, 2, '2014-07-01', '2014-08-31', 'P1L2B2-3453'),
(15, 250, 3, '2014-07-01', '2014-08-31', 'P1L2B1-452151'),
(45, 3434, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(45, 7434, 3, '2014-07-01', '2014-07-31', 'P1L2B2-3453'),
(45, 34, 0, '0000-00-00', '0000-00-00', 'dfghgs'),
(45, 678, 0, '0000-00-00', '0000-00-00', 'fasdfasdf'),
(46, 4545, 1, '2014-07-01', '2014-07-31', 'P1L1B1-2345'),
(47, 10, 0, '0000-00-00', '0000-00-00', 'asasdasd'),
(48, 10, 0, '0000-00-00', '0000-00-00', 'asasdasd'),
(49, 36002, 0, '0000-00-00', '0000-00-00', 'asdfasdasdasd');

-- --------------------------------------------------------

--
-- Table structure for table `lot`
--
-- Creation: Jul 15, 2014 at 12:17 PM
-- Last update: Jul 16, 2014 at 03:52 PM
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='House Lot Information' AUTO_INCREMENT=12 ;

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
(1, 'P1L1B1-2345', 1, '2014-06-01', '111', '1', 'First Street', '1', '1', '1', 1, '', '2014-06-30 02:26:13', 1, 1),
(2, 'P1L2B2-3453', 1, '2014-06-30', '123', '21', 'Second St.', '2', '2', '1', 2, '', '2014-06-30 02:48:46', 1, 1),
(3, 'P1L2B1-452151', 1, '2014-07-09', '200', '50', 'Greenhills', '2', '1', '1', 8, '', '2014-07-01 03:31:45', 1, 1),
(4, 'P1L3B1-54545', 2, '2014-01-01', '400', '5', 'Ilang-ilang', '3', '1', '1', 3, '', '2014-07-03 03:43:04', 1, 1),
(5, 'P1L2B3-12345', 0, '0000-00-00', '100', '13', 'Empire', '5', '5', '2', 0, '', '2014-07-03 09:00:46', 1, 1),
(6, 'P5L5B5-12345', 6, '2012-06-12', '100', '13', 'Empire St.', '5', '5', '5', 100, '', '2014-07-03 09:04:07', 1, 1),
(7, 'P1L1B1-8888', 0, '2009-07-16', '80', '2', 'Malunggay', '444', '4', '2', 2, '', '2014-07-04 10:50:41', 1, 1),
(8, 'P1L1B1-9999', 0, '0000-00-00', '300', '34', 'Emerald', '4', '23', '1', 0, '', '2014-07-05 07:29:21', 1, 1),
(9, 'jaylotnum', 0, '0000-00-00', '54', '52', 'market view', '65', '12', '4', 0, '', '2014-07-07 05:35:30', 1, 1),
(10, 'jaylotnum', 1, '0000-00-00', '54', '52', 'market view', '65', '12', '4', 0, '', '2014-07-07 05:35:32', 1, 1),
(11, '17856', 16, '0000-00-00', '289', '5986', 'mark', 'b', '29', '9', 17, '', '2014-07-15 01:53:57', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--
-- Creation: Jul 15, 2014 at 12:17 PM
-- Last update: Jul 15, 2014 at 12:17 PM
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
-- Creation: Jul 16, 2014 at 03:19 PM
-- Last update: Jul 16, 2014 at 03:55 PM
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
  `intgraceperiod` int(10) unsigned NOT NULL COMMENT 'Interest Grace Period (months)'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Application Settings' AUTO_INCREMENT=2 ;

--
-- Truncate table before insert `settings`
--

TRUNCATE TABLE `settings`;
--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `assocname`, `acronym`, `subdname`, `brgy`, `city`, `province`, `zipcode`, `contactno`, `email`, `price`, `interest`, `intgraceperiod`) VALUES
(1, 'Santa Isabel Village Homeowners Association, Inc.', 'SIVHAI', 'Santa Isabel Village', 'Isabang', 'Lucena City', 'Quezon', '4301', '', '', 4, '0.1000', 3);

-- --------------------------------------------------------

--
-- Table structure for table `status`
--
-- Creation: Jul 15, 2014 at 12:17 PM
-- Last update: Jul 15, 2014 at 12:17 PM
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
-- Creation: Jul 16, 2014 at 02:16 PM
-- Last update: Jul 16, 2014 at 02:50 PM
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
-- AUTO_INCREMENT for table `homeowner`
--
ALTER TABLE `homeowner`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Homeowner ID',AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ledger ID',AUTO_INCREMENT=50;
--
-- AUTO_INCREMENT for table `lot`
--
ALTER TABLE `lot`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Lot ID',AUTO_INCREMENT=12;
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
