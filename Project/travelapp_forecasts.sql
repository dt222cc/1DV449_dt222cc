-- phpMyAdmin SQL Dump
-- version 4.2.7
-- http://www.phpmyadmin.net
--
-- Generation Time: Jan 12, 2016 at 02:35 PM
-- Server version: 5.5.41-log
-- PHP Version: 5.6.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `travelapp_forecasts`
--

CREATE TABLE IF NOT EXISTS `travelapp_forecasts` (
`id` int(11) NOT NULL,
  `location` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `lat` varchar(20) COLLATE utf8_swedish_ci NOT NULL,
  `lng` varchar(20) COLLATE utf8_swedish_ci NOT NULL,
  `forecast_time` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `temperature` float NOT NULL,
  `icon` varchar(3) COLLATE utf8_swedish_ci NOT NULL,
  `description` varchar(100) COLLATE utf8_swedish_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=0 ;

--
-- Indexes for table `travelapp_forecasts`
--
ALTER TABLE `travelapp_forecasts`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `travelapp_forecasts`
--
ALTER TABLE `travelapp_forecasts`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=0;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
