-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2024 at 06:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `siem`
--

-- --------------------------------------------------------

--
-- Table structure for table `attack_logs`
--

CREATE TABLE `attack_logs` (
  `id` int(11) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `attack_type` enum('DoS','DDoS') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monitored_targets`
--

CREATE TABLE `monitored_targets` (
  `id` int(11) NOT NULL,
  `target_name` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `type` enum('device','website') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `log_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monitored_targets`
--

INSERT INTO `monitored_targets` (`id`, `target_name`, `ip_address`, `type`, `created_at`, `log_path`) VALUES
(2, 'New Model Site', '127.0.0.1', 'website', '2024-11-06 19:09:06', 'C:/xampp/apache/logs/newModel_access.log');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attack_logs`
--
ALTER TABLE `attack_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `target_id` (`target_id`);

--
-- Indexes for table `monitored_targets`
--
ALTER TABLE `monitored_targets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attack_logs`
--
ALTER TABLE `attack_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `monitored_targets`
--
ALTER TABLE `monitored_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attack_logs`
--
ALTER TABLE `attack_logs`
  ADD CONSTRAINT `attack_logs_ibfk_1` FOREIGN KEY (`target_id`) REFERENCES `monitored_targets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
