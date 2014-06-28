-- phpMyAdmin SQL Dump
-- version 4.2.3
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 26, 2014 at 07:22 AM
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
-- Creation: Jun 26, 2014 at 05:17 AM
-- Last update: Jun 26, 2014 at 05:17 AM
--

DROP TABLE IF EXISTS `homeowner`;
CREATE TABLE IF NOT EXISTS `homeowner` (
`id` int(11) NOT NULL COMMENT 'Homeowner ID',
  `lastname` varchar(255) NOT NULL COMMENT 'Last Name',
  `firstname` varchar(512) NOT NULL COMMENT 'First Name',
  `middlename` varchar(255) NOT NULL COMMENT 'Middle Name',
  `contactno` varchar(255) NOT NULL COMMENT 'Contact Number',
  `email` varchar(255) NOT NULL COMMENT 'Email Address'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Homeowner Information' AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `homeowner`
--

TRUNCATE TABLE `homeowner`;
-- --------------------------------------------------------

--
-- Table structure for table `ledger`
--
-- Creation: Jun 26, 2014 at 05:54 AM
-- Last update: Jun 26, 2014 at 05:54 AM
--

DROP TABLE IF EXISTS `ledger`;
CREATE TABLE IF NOT EXISTS `ledger` (
`id` int(10) unsigned NOT NULL COMMENT 'Ledger ID',
  `ornumber` varchar(100) NOT NULL COMMENT 'OR Number',
  `paymentdate` date NOT NULL COMMENT 'Payment Date',
  `startdate` date NOT NULL COMMENT 'Start Date',
  `enddate` date NOT NULL COMMENT 'End Date',
  `amount` float NOT NULL COMMENT 'Amount Paid',
  `user` int(10) unsigned NOT NULL COMMENT 'User who received the payment',
  `transactiondate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'System Transaction Date'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ledger Details' AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `ledger`
--

TRUNCATE TABLE `ledger`;
-- --------------------------------------------------------

--
-- Table structure for table `lot`
--
-- Creation: Jun 26, 2014 at 05:34 AM
-- Last update: Jun 26, 2014 at 05:34 AM
-- Last check: Jun 26, 2014 at 05:34 AM
--

DROP TABLE IF EXISTS `lot`;
CREATE TABLE IF NOT EXISTS `lot` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='House Lot Information' AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `lot`
--

TRUNCATE TABLE `lot`;
-- --------------------------------------------------------

--
-- Table structure for table `resident`
--
-- Creation: Jun 26, 2014 at 05:39 AM
-- Last update: Jun 26, 2014 at 05:39 AM
--

DROP TABLE IF EXISTS `resident`;
CREATE TABLE IF NOT EXISTS `resident` (
`id` int(10) unsigned NOT NULL COMMENT 'Resident ID',
  `fullname` varchar(512) NOT NULL COMMENT 'Full Name',
  `household` int(11) NOT NULL COMMENT 'Household (FK)',
  `status` int(10) unsigned NOT NULL COMMENT 'Resident Status',
  `user` int(10) unsigned NOT NULL COMMENT 'User (FK)',
  `dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `resident`
--

TRUNCATE TABLE `resident`;
-- --------------------------------------------------------

--
-- Table structure for table `settings`
--
-- Creation: Jun 26, 2014 at 06:04 AM
-- Last update: Jun 26, 2014 at 06:04 AM
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
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
-- Creation: Jun 26, 2014 at 05:44 AM
-- Last update: Jun 26, 2014 at 05:44 AM
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `id` int(10) unsigned NOT NULL COMMENT 'Status ID',
  `description` varchar(255) NOT NULL COMMENT 'Description'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Residential Status';

--
-- Truncate table before insert `status`
--

TRUNCATE TABLE `status`;
-- --------------------------------------------------------

--
-- Table structure for table `user`
--
-- Creation: Jun 26, 2014 at 05:12 AM
-- Last update: Jun 26, 2014 at 05:12 AM
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
`id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `fullname` varchar(512) NOT NULL COMMENT 'Full Name',
  `username` varchar(32) NOT NULL COMMENT 'Username',
  `password` varchar(50) NOT NULL COMMENT 'Hashed Password',
  `datereg` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date registered',
  `permission` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Set of Permissions'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='User Information' AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `user`
--

TRUNCATE TABLE `user`;
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
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Homeowner ID';
--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ledger ID';
--
-- AUTO_INCREMENT for table `lot`
--
ALTER TABLE `lot`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Lot ID';
--
-- AUTO_INCREMENT for table `resident`
--
ALTER TABLE `resident`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Resident ID';
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User ID';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
