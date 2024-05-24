-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for tbdots
CREATE DATABASE IF NOT EXISTS `tbdots` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `tbdots`;

-- Dumping structure for table tbdots.lab_results
CREATE TABLE IF NOT EXISTS `lab_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reason_for_examination` varchar(50) DEFAULT NULL,
  `history_of_treatment` varchar(50) DEFAULT NULL,
  `month_of_treatment` varchar(50) DEFAULT NULL,
  `test_requested` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.lab_results: ~0 rows (approximately)

-- Dumping structure for table tbdots.locations
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location` text NOT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.locations: ~6 rows (approximately)
INSERT INTO `locations` (`id`, `location`, `contact`, `updated_at`, `created_at`) VALUES
	(1, 'Hinigaran', NULL, '2024-05-24 09:02:27', '2024-05-24 09:02:27'),
	(2, 'Isabela', NULL, '2024-05-24 09:02:33', '2024-05-24 09:02:33'),
	(3, 'Binalbagan', NULL, '2024-05-24 09:02:41', '2024-05-24 09:02:41'),
	(4, 'Himamaylan', NULL, '2024-05-24 09:02:49', '2024-05-24 09:02:49'),
	(5, 'La Castellana', NULL, '2024-05-24 09:02:57', '2024-05-24 09:02:57'),
	(6, 'Moises Padilla', NULL, '2024-05-24 09:03:06', '2024-05-24 09:03:06');

-- Dumping structure for table tbdots.patients
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` text NOT NULL,
  `age` int NOT NULL DEFAULT '0',
  `gender` tinyint NOT NULL DEFAULT '0',
  `contact` varchar(50) DEFAULT NULL,
  `address` text,
  `physician_id` int DEFAULT NULL,
  `location_id` int DEFAULT NULL,
  `lab_results_id` int DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_patients_physician` (`physician_id`),
  KEY `FK_patients_location` (`location_id`),
  KEY `FK_patients_lab_results` (`lab_results_id`),
  CONSTRAINT `FK_patients_lab_results` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`),
  CONSTRAINT `FK_patients_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `FK_patients_physician` FOREIGN KEY (`physician_id`) REFERENCES `physicians` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.patients: ~0 rows (approximately)

-- Dumping structure for table tbdots.physicians
CREATE TABLE IF NOT EXISTS `physicians` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.physicians: ~0 rows (approximately)

-- Dumping structure for table tbdots.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL DEFAULT '0',
  `permission` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.roles: ~0 rows (approximately)
INSERT INTO `roles` (`id`, `description`, `permission`, `updated_at`, `created_at`) VALUES
	(3, 'test', 'test', '2024-05-03 05:28:55', '2024-05-03 05:28:56');

-- Dumping structure for table tbdots.test_lists
CREATE TABLE IF NOT EXISTS `test_lists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.test_lists: ~6 rows (approximately)
INSERT INTO `test_lists` (`id`, `name`, `updated_at`, `created_at`) VALUES
	(1, 'Xpert MTB / RIF', '2024-05-24 09:14:07', '2024-05-24 09:14:07'),
	(2, 'Smear Microscopy', '2024-05-24 09:14:22', '2024-05-24 09:14:22'),
	(3, 'TB LAMP', '2024-05-24 09:14:30', '2024-05-24 09:14:30'),
	(4, 'LPA 1st Line', '2024-05-24 09:14:40', '2024-05-24 09:14:40'),
	(5, 'LPA 2nd Line', '2024-05-24 09:14:45', '2024-05-24 09:14:45'),
	(6, 'Culture', '2024-05-24 09:14:52', '2024-05-24 09:14:52'),
	(7, 'DST', '2024-05-24 09:14:56', '2024-05-24 09:14:56');

-- Dumping structure for table tbdots.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `role` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_users_roles` (`role`),
  CONSTRAINT `FK_users_roles` FOREIGN KEY (`role`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`, `role`, `updated_at`, `created_at`) VALUES
	(9, 'test', '098f6bcd4621d373cade4e832627b4f6', 'test', 'test', 3, '2024-05-03 05:29:07', '2024-05-03 05:29:08');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
