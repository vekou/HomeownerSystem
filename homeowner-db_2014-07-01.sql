-- phpMyAdmin SQL Dump
-- version 4.2.3
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 01, 2014 at 08:31 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `homeowner`
--
-- Creation: Jul 01, 2014 at 10:10 AM
-- Last update: Jul 01, 2014 at 10:10 AM
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
  `dateadded` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Homeowner Information' AUTO_INCREMENT=14 ;

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

INSERT INTO `homeowner` (`id`, `lastname`, `firstname`, `middlename`, `contactno`, `email`, `user`, `dateadded`) VALUES
(1, 'Delas Alas', 'Jay', 'Bagasbas', '094798989898', 'sdfs@sdfs', 1, '2014-06-28 13:19:31'),
(2, 'Garcia', 'Mark Denrich', 'Deocales', '091738363937', 'adfadjfkjdf@df', 1, '2014-06-28 14:02:25'),
(3, 'Veloro', 'Riechelle', 'Naval', '0909809809', 'sdfs@df', 1, '2014-06-29 03:19:22'),
(4, 'Maas', 'Joed', 'Marquez', '39839489384', 'df@dfd', 1, '2014-06-29 03:19:35'),
(5, 'Dela Cruz', 'Juan', 'Bautista', '3403940', 'ffg@df', 1, '2014-06-29 03:19:53'),
(6, 'adfadf', 'kj', 'kjk', 'jkj', 'k@l', 1, '2014-06-29 03:20:08'),
(7, 'Dfadf', 'adfasfd', 'adfa', 'dfadf', 'adfa@d', 1, '2014-06-29 03:20:16'),
(8, 'Dfadf', 'adfa', 'Dfdfd', 'ffdaf', 'df@d', 1, '2014-06-29 03:20:26'),
(9, 'Adfafda', 'dfadf', 'adfasdf', 'asdf', 'dfas@d', 1, '2014-06-29 03:20:38'),
(10, 'dfdf', 'k', 'kjkj', 'kjk', 'j@kj', 1, '2014-06-29 03:20:44'),
(11, 'llklkl', 'klkl', 'klk', 'lklk', 'lk@kj', 1, '2014-06-29 03:20:51'),
(12, 'lklkl', 'klkl', 'klk', 'lkl', 'kl@lk', 1, '2014-06-29 03:20:58'),
(13, 'delas alas', 'jay', 'bagasbas', '09478929659', 'jay_delasalas@ymail.com', 1, '2014-07-01 06:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `ledger`
--
-- Creation: Jul 01, 2014 at 04:39 PM
-- Last update: Jul 01, 2014 at 04:40 PM
--

DROP TABLE IF EXISTS `ledger`;
CREATE TABLE `ledger` (
`id` int(10) unsigned NOT NULL COMMENT 'Ledger ID',
  `ornumber` varchar(100) NOT NULL COMMENT 'OR Number',
  `paymentdate` date NOT NULL COMMENT 'Payment Date',
  `startdate` date NOT NULL COMMENT 'Start Date',
  `enddate` date NOT NULL COMMENT 'End Date',
  `payee` varchar(255) NOT NULL COMMENT 'Name of Payee',
  `user` int(10) unsigned NOT NULL COMMENT 'User who received the payment',
  `transactiondate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'System Transaction Date'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Ledger Details' AUTO_INCREMENT=2 ;

--
-- RELATIONS FOR TABLE `ledger`:
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

INSERT INTO `ledger` (`id`, `ornumber`, `paymentdate`, `startdate`, `enddate`, `payee`, `user`, `transactiondate`) VALUES
(1, '1234355', '2014-07-02', '2014-06-01', '2014-07-31', 'Jay delas Alas', 1, '2014-07-01 16:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `ledgeritem`
--
-- Creation: Jul 01, 2014 at 04:39 PM
-- Last update: Jul 01, 2014 at 04:40 PM
--

DROP TABLE IF EXISTS `ledgeritem`;
CREATE TABLE `ledgeritem` (
  `id` int(11) NOT NULL COMMENT 'Ledger (FK)',
  `amount` float NOT NULL COMMENT 'Amount',
  `lot` int(11) NOT NULL COMMENT 'Lot ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ledger Items';

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

INSERT INTO `ledgeritem` (`id`, `amount`, `lot`) VALUES
(1, 345, 1),
(1, 234, 3);

-- --------------------------------------------------------

--
-- Table structure for table `lot`
--
-- Creation: Jul 01, 2014 at 10:10 AM
-- Last update: Jul 01, 2014 at 10:10 AM
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
  `user` int(11) NOT NULL COMMENT 'Added by User (FK)'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='House Lot Information' AUTO_INCREMENT=4 ;

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

INSERT INTO `lot` (`id`, `code`, `homeowner`, `dateacquired`, `lotsize`, `housenumber`, `street`, `lot`, `block`, `phase`, `numberinhousehold`, `caretaker`, `dateadded`, `user`) VALUES
(1, 'P1L1B1-2345', 1, '2014-06-01', '111', '1', 'First St.', '1', '1', '1', 1, '', '2014-06-30 02:26:13', 1),
(2, 'P1L2B2-3453', 1, '2014-06-30', '123', '21', 'Second St.', '2', '2', '1', 2, '', '2014-06-30 02:48:46', 1),
(3, 'pl021452151', 1, '2014-07-09', '200', '50', 'brgy market view green hills phase 3', '6', '59', '4', 8, '', '2014-07-01 03:31:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--
-- Creation: Jul 01, 2014 at 10:10 AM
-- Last update: Jul 01, 2014 at 10:10 AM
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
-- Creation: Jul 01, 2014 at 10:10 AM
-- Last update: Jul 01, 2014 at 10:10 AM
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `subdname` varchar(255) NOT NULL COMMENT 'Subdivision Name',
  `brgy` varchar(255) NOT NULL COMMENT 'Barangay',
  `city` varchar(255) NOT NULL COMMENT 'Town/City',
  `province` varchar(255) NOT NULL COMMENT 'Province',
  `zipcode` varchar(10) NOT NULL COMMENT 'ZIP Code',
  `contactno` varchar(100) NOT NULL COMMENT 'Contact Number',
  `email` varchar(255) NOT NULL COMMENT 'Email Address',
  `price` float NOT NULL COMMENT 'Price/Sq Meter',
  `interest` float NOT NULL COMMENT 'Monthly Interest Rate'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Application Settings';

--
-- Truncate table before insert `settings`
--

TRUNCATE TABLE `settings`;
-- --------------------------------------------------------

--
-- Table structure for table `status`
--
-- Creation: Jul 01, 2014 at 10:10 AM
-- Last update: Jul 01, 2014 at 10:10 AM
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
-- Creation: Jul 01, 2014 at 10:10 AM
-- Last update: Jul 01, 2014 at 10:10 AM
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
`id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `fullname` varchar(512) NOT NULL COMMENT 'Full Name',
  `username` varchar(32) NOT NULL COMMENT 'Username',
  `password` varchar(50) NOT NULL COMMENT 'Hashed Password',
  `datereg` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date registered',
  `permission` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Set of Permissions'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='User Information' AUTO_INCREMENT=2 ;

--
-- Truncate table before insert `user`
--

TRUNCATE TABLE `user`;
--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fullname`, `username`, `password`, `datereg`, `permission`) VALUES
(1, 'System Admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', '2014-06-28 13:11:40', 255);

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
-- Indexes for table `status`
--
ALTER TABLE `status`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `homeowner`
--
ALTER TABLE `homeowner`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Homeowner ID',AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ledger ID',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `lot`
--
ALTER TABLE `lot`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Lot ID',AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `resident`
--
ALTER TABLE `resident`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Resident ID';
--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Status ID',AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User ID',AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
