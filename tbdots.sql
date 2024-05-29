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

-- Dumping structure for table tbdots.modules
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.modules: ~14 rows (approximately)
INSERT INTO `modules` (`id`, `module`) VALUES
	(1, 'user_index'),
	(2, 'user_add'),
	(3, 'user_edit'),
	(4, 'user_delete'),
	(5, 'physician_index'),
	(6, 'physician_add'),
	(7, 'physician_edit'),
	(8, 'physician_delete'),
	(9, 'patient_index'),
	(10, 'patient_add'),
	(11, 'patient_edit'),
	(12, 'patient_delete'),
	(13, 'patient_add_lab_results'),
	(14, 'patient_show_lab_results');

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
  KEY `FK_patients_location` (`location_id`),
  KEY `FK_patients_lab_results` (`lab_results_id`),
  KEY `FK_patients_users` (`physician_id`),
  CONSTRAINT `FK_patients_lab_results` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`),
  CONSTRAINT `FK_patients_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `FK_patients_users` FOREIGN KEY (`physician_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.patients: ~0 rows (approximately)

-- Dumping structure for table tbdots.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL DEFAULT '0',
  `module` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.roles: ~4 rows (approximately)
INSERT INTO `roles` (`id`, `description`, `module`, `updated_at`, `created_at`) VALUES
	(1, 'Super Admin', '[1,2,3,4,5,6,7,8,9,10,11,12]', '2024-05-26 07:48:21', '2024-05-26 07:20:56'),
	(2, 'Admin', '[1,2,3,4,5,6,7,8,9,10,11,12]', '2024-05-26 07:48:28', '2024-05-26 07:21:42'),
	(3, 'Physician', '[5,9,10,11,12]', '2024-05-26 08:01:46', '2024-05-26 07:22:14'),
	(4, 'Regular', '[5,9,10,11,12]', '2024-05-26 08:01:50', '2024-05-26 07:23:23');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.users: ~3 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`, `role`, `updated_at`, `created_at`) VALUES
	(9, 'test', '098f6bcd4621d373cade4e832627b4f6', 'test', 'test', 2, '2024-05-03 05:29:07', '2024-05-26 08:02:01'),
	(14, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'admin', 2, '2024-05-27 06:30:41', '2024-05-27 06:30:41'),
	(15, 'Docone', '827ccb0eea8a706c4c34a16891f84e7b', 'Doc', 'One', 3, '2024-05-27 06:39:57', '2024-05-27 06:40:07');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
