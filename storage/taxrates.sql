-- phpMyAdmin SQL Dump
-- version 4.0.10.14
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Jun 10, 2017 at 08:00 AM
-- Server version: 5.5.54-38.6-log
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `faveohel_agora`
--

-- --------------------------------------------------------

--
-- Table structure for table `tax_by_states`
--

CREATE TABLE IF NOT EXISTS `tax_by_states` (
  `id` smallint(5) unsigned NOT NULL,
  `country` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `state_code` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `state` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `c_gst` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `s_gst` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `i_gst` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `ut_gst` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `tax_by_states` (`id`,`country`,`state_code`, `state`, `c_gst`, `s_gst` ,`i_gst` , `ut_gst`,`created_at`, `updated_at`) VALUES
(1, 'IN','IN-AN','Andaman Nicobar Islands', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, 'IN','IN-AP','Andhra Pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(3, 'IN','IN-AR','Arunachal Pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, 'IN','IN-AS','Assam', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(5, 'IN','IN-BR','Bihar', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(6, 'IN','IN-CH','Chandigarh', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(7, 'IN','IN-CT','Chhattisgarh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(8, 'IN','IN-DN','Dadra and Nagar Haveli', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(9, 'IN','IN-DD','Daman and Diu', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(10,'IN','IN-DL', 'Delhi', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(11, 'IN','IN-GA','Goa', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(12, 'IN','IN-GJ','Gujarat', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(13, 'IN','IN-HR','Haryana', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(14, 'IN','IN-HP','Himachal pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(15, 'IN','IN-JK','Jammu and Kashmir', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(16, 'IN','IN-JH','Jharkand', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(17, 'IN','IN-KA','Karnataka', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(18, 'IN','IN-KL','Kerala', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(19, 'IN','IN-LD','Lakshadweep', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(20, 'IN','IN-MP','Madhya Pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(21, 'IN','IN-MH','Maharashtra', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(22, 'IN','IN-MN','Manipur', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(23, 'IN','IN-ML','Megalaya', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(24, 'IN','IN-MZ','Mizoram', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(25, 'IN','IN-NL','Nagaland', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(26, 'IN','IN-OR','Orissa', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(27, 'IN','IN-OR','Orissa', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(28, 'IN','IN-PY','Pondichery', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(29, 'IN','IN-PB','Punjab', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(30, 'IN','IN-RJ','Rajastan', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(31, 'IN','IN-SK','Sikkim', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(32, 'IN','IN-TN','Tamilnadu', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(33, 'IN','IN-TS','Telangana', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(34, 'IN','IN-TR','Tripura', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(35, 'IN','IN-UP','Uttar Pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(36, 'IN','IN-UL','Uttaranchal', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(37, 'IN','IN-WB','West Bengal', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58');









