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

-- Dumping structure for table tbdots.activity_logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(50) DEFAULT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int NOT NULL,
  `details` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_logs_user` (`user_id`),
  CONSTRAINT `FK_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.activity_logs: ~44 rows (approximately)
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `details`, `created_at`) VALUES
	(1, 14, 'UPDATE', 'patients', 4, 'Updated patient: Lester bon Biono', '2024-11-12 08:05:46'),
	(2, 14, 'UPDATE', 'patients', 4, 'Updated patient: Lester bon Biono', '2024-11-12 08:06:19'),
	(3, 14, 'UPDATE', 'patients', 4, 'Updated patient: Lester bon Biono', '2024-11-12 08:16:43'),
	(4, 14, 'UPDATE', 'patients', 4, 'Updated patient: Lester bon Biono', '2024-11-12 08:16:51'),
	(5, 14, 'DELETE', 'products', 2, 'Deleted product', '2024-11-12 20:43:23'),
	(6, 14, 'UPDATE', 'lab_results', 2, 'Updated laboratory record #2', '2024-11-12 21:40:42'),
	(7, 14, 'UPDATE', 'lab_results', 2, 'Updated laboratory record #2', '2024-11-12 21:40:50'),
	(8, 14, 'UPDATE', 'lab_results', 2, 'Updated laboratory record #2', '2024-11-12 21:46:05'),
	(9, 14, 'UPDATE', 'lab_results', 2, 'Updated laboratory record #2', '2024-11-12 21:47:03'),
	(10, 14, 'UPDATE', 'lab_results', 2, 'Updated laboratory record #2', '2024-11-12 21:47:11'),
	(11, 14, 'UPDATE', 'lab_results', 2, 'Updated laboratory record #2', '2024-11-12 21:47:28'),
	(12, 14, 'UPDATE', 'lab_results', 2, 'Updated laboratory record #2', '2024-11-12 21:47:44'),
	(13, 14, 'UPDATE', 'roles', 2, 'Updated role permissions', '2024-11-12 23:08:34'),
	(14, 14, 'CREATE', 'patients', 10, 'Added patient: Bea Sasi', '2024-11-13 02:31:37'),
	(15, 14, 'DELETE', 'patients', 9, 'Deleted patient: ', '2024-11-13 02:31:41'),
	(16, 14, 'DELETE', 'patients', 9, 'Deleted patient: ', '2024-11-13 02:31:56'),
	(17, 14, 'DELETE', 'patients', 9, 'Deleted patient: ', '2024-11-13 02:32:28'),
	(18, 14, 'CREATE', 'physicians', 16, 'Created new physician: asdas dasda', '2024-11-13 02:35:40'),
	(19, 14, 'UPDATE', 'users', 14, 'Updated user: admin', '2024-11-21 10:50:28'),
	(20, 17, 'UPDATE', 'lab_results', 3, 'Updated laboratory record #3', '2024-11-25 08:53:58'),
	(21, 17, 'UPDATE', 'lab_results', 3, 'Updated laboratory record #3', '2024-11-25 08:54:08'),
	(22, 17, 'UPDATE', 'lab_results', 3, 'Updated laboratory record #3', '2024-11-25 08:54:13'),
	(23, 17, 'UPDATE', 'lab_results', 3, 'Updated laboratory record #3', '2024-11-25 08:55:50'),
	(24, 17, 'UPDATE', 'lab_results', 3, 'Updated laboratory record #3', '2024-11-25 08:56:03'),
	(25, 17, 'CREATE', 'lab_results', 2, 'Added new laboratory record', '2024-11-25 19:24:24'),
	(26, 17, 'CREATE', 'lab_results', 1, 'Added new laboratory record', '2024-11-25 19:53:04'),
	(27, 17, 'CREATE', 'lab_results', 2, 'Added new laboratory record', '2024-11-25 19:54:15'),
	(28, 17, 'CREATE', 'patients', 11, 'Added patient: Kuyas kuys', '2024-11-27 07:28:39'),
	(29, 17, 'INSERT', 'inventory', 15, 'Added inventory for product ID: 1', '2024-12-02 11:49:36'),
	(30, 17, 'CREATE', 'patients', 12, 'Added patient: 12312', '2024-12-02 12:39:51'),
	(31, 17, 'CREATE', 'patients', 13, 'Added patient: 3245435345sadas', '2024-12-02 12:40:58'),
	(32, 14, 'CREATE', 'patients', 14, 'Added patient: Juan Ponce', '2024-12-03 02:17:16'),
	(33, 17, 'CREATE', 'lab_results', 14, 'Added new laboratory record', '2024-12-03 03:10:02'),
	(34, 17, 'CREATE', 'lab_results', 1, 'Added new laboratory record', '2024-12-03 03:15:17'),
	(35, 17, 'CREATE', 'lab_results', 2, 'Added new laboratory record', '2024-12-03 03:20:16'),
	(36, 17, 'CREATE', 'lab_results', 3, 'Added new laboratory record', '2024-12-03 03:20:38'),
	(37, 17, 'CREATE', 'lab_results', 4, 'Added new laboratory record', '2024-12-03 03:30:27'),
	(38, 17, 'CREATE', 'lab_results', 5, 'Added new laboratory record', '2024-12-03 03:30:35'),
	(39, 17, 'CREATE', 'lab_results', 6, 'Added new laboratory record', '2024-12-03 03:36:32'),
	(40, 17, 'CREATE', 'lab_results', 7, 'Added new laboratory record', '2024-12-03 03:36:42'),
	(41, 17, 'CREATE', 'lab_results', 1, 'Added new laboratory record', '2024-12-03 03:45:48'),
	(42, 17, 'CREATE', 'lab_results', 2, 'Added new laboratory record', '2024-12-03 03:46:04'),
	(43, 17, 'CREATE', 'lab_results', 3, 'Added new laboratory record', '2024-12-03 03:46:16'),
	(44, 17, 'CREATE', 'lab_results', 4, 'Added new laboratory record', '2024-12-03 03:46:27'),
	(45, 17, 'CREATE', 'lab_results', 5, 'Added new laboratory record', '2024-12-03 03:46:40'),
	(46, 17, 'CREATE', 'lab_results', 6, 'Added new laboratory record', '2024-12-03 03:46:53'),
	(47, 14, 'INSERT', 'inventory', 1, 'Added inventory transaction for product ID: 1', '2024-12-11 18:14:52'),
	(48, 14, 'UPDATE', 'physicians', 15, 'Updated physician: Doc One', '2024-12-12 06:50:18'),
	(49, 14, 'CREATE', 'users', 18, 'Created new user: testing', '2025-01-17 07:07:28'),
	(50, 14, 'UPDATE', 'users', 18, 'Updated user: phys', '2025-01-17 07:14:46'),
	(51, 17, 'CREATE', 'lab_results', 7, 'Added new laboratory record', '2025-01-17 07:15:31'),
	(52, 17, 'UPDATE', 'products', 0, 'Added/Updated product: Biogesic', '2025-01-17 07:19:45'),
	(53, 17, 'UPDATE', 'users', 9, 'Updated user: test', '2025-01-21 09:58:26'),
	(54, 17, 'UPDATE', 'users', 9, 'Updated user: test', '2025-01-21 09:58:31'),
	(55, 17, 'UPDATE', 'roles', 2, 'Updated role permissions', '2025-01-21 10:03:22'),
	(56, 17, 'CREATE', 'patient_logbook', 1, 'Added new logbook entry for patient #12', '2025-01-21 10:16:58'),
	(57, 17, 'CREATE', 'patient_logbook', 2, 'Added new logbook entry for patient #14', '2025-01-21 10:17:13'),
	(58, 17, 'UPDATE', 'roles', 3, 'Updated role permissions', '2025-01-21 10:23:44'),
	(59, 18, 'CREATE', 'patient_logbook', 4, 'Added logbook entry for patient #14', '2025-01-21 10:24:26'),
	(60, 18, 'CREATE', 'patient_logbook', 5, 'Added logbook entry for patient #10', '2025-01-21 10:25:42'),
	(61, 18, 'CREATE', 'patient_logbook', 6, 'Added logbook entry for patient #14', '2025-01-21 10:31:02'),
	(62, 18, 'CREATE', 'patient_logbook', 7, 'Added logbook entry for patient #11', '2025-01-21 10:31:16'),
	(63, 17, 'CREATE', 'lab_results', 8, 'Added new laboratory record', '2025-01-21 10:55:21'),
	(64, 17, 'CREATE', 'patients', 15, 'Added patient: Testing', '2025-01-21 10:58:35'),
	(65, 17, 'CREATE', 'users', 19, 'Created new user: testingssss', '2025-01-21 13:04:22'),
	(66, 17, 'CREATE', 'users', 20, 'Created new user: phyx', '2025-01-21 13:04:57'),
	(67, 17, 'UPDATE', 'users', 9, 'Updated user: test', '2025-01-21 13:12:31'),
	(68, 17, 'UPDATE', 'users', 9, 'Updated user: test', '2025-01-21 13:12:54'),
	(69, 17, 'CREATE', 'lab_results', 9, 'Added new laboratory record', '2025-01-21 13:15:21'),
	(70, 17, 'CREATE', 'lab_results', 10, 'Added new laboratory record', '2025-01-21 13:40:25'),
	(71, 17, 'CREATE', 'lab_results', 11, 'Added new laboratory record', '2025-01-21 13:40:51'),
	(72, 17, 'CREATE', 'lab_results', 12, 'Added new laboratory record', '2025-01-21 13:42:03'),
	(73, 17, 'CREATE', 'lab_results', 13, 'Added new laboratory record', '2025-01-21 13:43:59'),
	(74, 17, 'CREATE', 'lab_results', 14, 'Added new laboratory record', '2025-01-21 13:44:28'),
	(75, 17, 'CREATE', 'lab_results', 15, 'Added new laboratory record', '2025-01-21 13:45:05');

-- Dumping structure for table tbdots.barangays
CREATE TABLE IF NOT EXISTS `barangays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `municipality_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `municipality_id` (`municipality_id`),
  CONSTRAINT `barangays_ibfk_1` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.barangays: ~117 rows (approximately)
INSERT INTO `barangays` (`id`, `name`, `municipality_id`, `created_at`, `updated_at`) VALUES
	(1, 'Anahaw', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(2, 'Aranda', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(3, 'Baga-as', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(4, 'Brgy. 1', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(5, 'Brgy. 2', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(6, 'Brgy. 3', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(7, 'Cambugsa', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(8, 'Cambaog', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(9, 'Camalobalo', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(10, 'Calapi', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(11, 'Bato', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(12, 'Brgy. 4', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(13, 'Narauis', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(14, 'Nanunga', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(15, 'Miranda', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(16, 'Himaya', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(17, 'Gargato', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(18, 'Candumarao', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(19, 'Tuguis', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(20, 'Tagda', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(21, 'Quiwi', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(22, 'Pilar', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(23, 'Paticui', 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(24, 'Amin', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(25, 'Banogbanog', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(26, 'Bulad', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(27, 'Bungahin', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(28, 'Cabcab', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(29, 'Camangcamang', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(30, 'Camp Clark', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(31, 'Cansalongon', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(32, 'Guintubhan', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(33, 'Libas', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(34, 'Limalima', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(35, 'Makilignit', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(36, 'Mansablay', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(37, 'Maytubig', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(38, 'Panaquiao', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(39, 'Barangay 1 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(40, 'Barangay 2 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(41, 'Barangay 3 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(42, 'Barangay 4 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(43, 'Barangay 5 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(44, 'Barangay 6 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(45, 'Barangay 7 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(46, 'Barangay 8 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(47, 'Barangay 9 (Poblacion)', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(48, 'Riverside', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(49, 'Rumirang', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(50, 'San Agustin', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(51, 'Sebucawan', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(52, 'Sikatuna', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(53, 'Tinongan', 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(54, 'Amontay', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(55, 'Bagroy', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(56, 'Bi-ao', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(57, 'Canmoros', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(58, 'Enclaro', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(59, 'Marina', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(60, 'Pagla-um (Poblacion)', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(61, 'Payao', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(62, 'Progreso', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(63, 'Remedios', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(64, 'San Jose', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(65, 'San Juan', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(66, 'San Pedro (Poblacion)', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(67, 'San Teodoro', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(68, 'San Vicente', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(69, 'Santo Rosario (Poblacion)', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(70, 'Santol', 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(71, 'Aguisan', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(72, 'Buenavista', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(73, 'Cabadiangan', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(74, 'Cabanbanan', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(75, 'Carabalan', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(76, 'Caradio-an', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(77, 'Libacao', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(78, 'Mambagaton', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(79, 'Nabali-an', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(80, 'Mahalang', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(81, 'San Antonio', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(82, 'Sara-et', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(83, 'Su-ay', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(84, 'Talaban', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(85, 'To-oy', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(86, 'Barangay I (Poblacion)', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(87, 'Barangay II (Poblacion)', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(88, 'Barangay III (Poblacion)', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(89, 'Barangay IV (Poblacion)', 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(90, 'Brgy. 1', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(91, 'Brgy. 2', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(92, 'Brgy. 3', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(93, 'Brgy. 4', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(94, 'Brgy. 5', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(95, 'Brgy. 6', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(96, 'Brgy. 7', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(97, 'Crossing magallon', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(98, 'Guinpana-an', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(99, 'Inolingan', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(100, 'Macagahay', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(101, 'Magallon Cadre', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(102, 'Montilla', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(103, 'Odiong', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(104, 'Quintin Remo', 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(105, 'Biaknabato', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(106, 'Cabacungan', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(107, 'Cabagnaan', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(108, 'Camandag', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(109, 'Lalagsan', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(110, 'Manghanoy', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(111, 'Mansalanao', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(112, 'Masulog', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(113, 'Nato', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(114, 'Puso', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(115, 'Robles (Poblacion)', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(116, 'Sag-Ang', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(117, 'Talaptap', 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49');

-- Dumping structure for table tbdots.clinical_examinations
CREATE TABLE IF NOT EXISTS `clinical_examinations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_results_id` int NOT NULL,
  `examination_date` date NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `unexplained_fever` tinyint(1) DEFAULT '0',
  `unexplained_cough` tinyint(1) DEFAULT '0',
  `unimproved_wellbeing` tinyint(1) DEFAULT '0',
  `poor_appetite` tinyint(1) DEFAULT '0',
  `positive_pe_findings` tinyint(1) DEFAULT '0',
  `side_effects` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_clinical_lab_results` (`lab_results_id`),
  CONSTRAINT `FK_clinical_lab_results` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.clinical_examinations: ~0 rows (approximately)

-- Dumping structure for table tbdots.drug_administrations
CREATE TABLE IF NOT EXISTS `drug_administrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_results_id` int NOT NULL,
  `drug_name` varchar(100) NOT NULL,
  `dosage` decimal(10,2) NOT NULL,
  `initial` varchar(50) DEFAULT NULL,
  `month_2` varchar(50) DEFAULT NULL,
  `month_3` varchar(50) DEFAULT NULL,
  `month_4` varchar(50) DEFAULT NULL,
  `month_5` varchar(50) DEFAULT NULL,
  `month_6` varchar(50) DEFAULT NULL,
  `month_7` varchar(50) DEFAULT NULL,
  `month_8` varchar(50) DEFAULT NULL,
  `month_9` varchar(50) DEFAULT NULL,
  `month_10` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_drug_lab_results` (`lab_results_id`),
  CONSTRAINT `FK_drug_lab_results` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.drug_administrations: ~0 rows (approximately)

-- Dumping structure for table tbdots.drug_dosages
CREATE TABLE IF NOT EXISTS `drug_dosages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_results_id` int DEFAULT NULL,
  `drug_name` varchar(255) DEFAULT NULL,
  `month_1` decimal(10,2) DEFAULT NULL,
  `month_2` decimal(10,2) DEFAULT NULL,
  `month_3` decimal(10,2) DEFAULT NULL,
  `month_4` decimal(10,2) DEFAULT NULL,
  `month_5` decimal(10,2) DEFAULT NULL,
  `month_6` decimal(10,2) DEFAULT NULL,
  `month_7` decimal(10,2) DEFAULT NULL,
  `month_8` decimal(10,2) DEFAULT NULL,
  `month_9` decimal(10,2) DEFAULT NULL,
  `month_10` decimal(10,2) DEFAULT NULL,
  `month_11` decimal(10,2) DEFAULT NULL,
  `month_12` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lab_results_id` (`lab_results_id`),
  CONSTRAINT `drug_dosages_ibfk_1` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.drug_dosages: ~28 rows (approximately)
INSERT INTO `drug_dosages` (`id`, `lab_results_id`, `drug_name`, `month_1`, `month_2`, `month_3`, `month_4`, `month_5`, `month_6`, `month_7`, `month_8`, `month_9`, `month_10`, `month_11`, `month_12`) VALUES
	(1, 1, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(2, 1, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(3, 1, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(4, 1, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(5, 2, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(6, 2, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(7, 2, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(8, 2, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(9, 3, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(10, 3, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(11, 3, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(12, 3, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(13, 4, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(14, 4, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(15, 4, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(16, 4, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(17, 5, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(18, 5, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(19, 5, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(20, 5, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(21, 6, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(22, 6, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(23, 6, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(24, 6, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(25, 7, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(26, 7, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(27, 7, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(28, 7, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(29, 8, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(30, 8, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(31, 8, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(32, 8, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(33, 9, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(34, 9, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(35, 9, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(36, 9, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(37, 10, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(38, 10, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(39, 10, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(40, 10, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(41, 11, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(42, 11, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(43, 11, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(44, 11, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(45, 12, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(46, 12, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(47, 12, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(48, 12, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(49, 13, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(50, 13, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(51, 13, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(52, 13, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(53, 14, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(54, 14, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(55, 14, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(56, 14, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(57, 15, 'Isoniazid [H] 10mg/kg (200mg/5ml)', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(58, 15, 'Rifampicin [R]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(59, 15, 'Pyrazinamide [Z]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(60, 15, 'Ethambutol [E]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

-- Dumping structure for table tbdots.drug_histories
CREATE TABLE IF NOT EXISTS `drug_histories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_results_id` int NOT NULL,
  `has_history` tinyint(1) DEFAULT '0',
  `duration` enum('less than 1 mo','1 mo or more') DEFAULT NULL,
  `drugs_taken` set('H','R','Z','E','S') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_history_treatment` (`lab_results_id`),
  CONSTRAINT `FK_history_treatment` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.drug_histories: ~6 rows (approximately)
INSERT INTO `drug_histories` (`id`, `lab_results_id`, `has_history`, `duration`, `drugs_taken`) VALUES
	(1, 1, 0, 'less than 1 mo', NULL),
	(2, 2, 0, 'less than 1 mo', NULL),
	(3, 3, 0, 'less than 1 mo', NULL),
	(4, 4, 0, 'less than 1 mo', NULL),
	(5, 5, 0, 'less than 1 mo', NULL),
	(6, 6, 0, 'less than 1 mo', NULL),
	(7, 7, 0, 'less than 1 mo', NULL),
	(8, 8, 0, 'less than 1 mo', NULL),
	(9, 9, 0, 'less than 1 mo', NULL),
	(10, 10, 0, 'less than 1 mo', NULL),
	(11, 11, 0, 'less than 1 mo', NULL),
	(12, 12, 0, 'less than 1 mo', NULL),
	(13, 13, 0, 'less than 1 mo', NULL),
	(14, 14, 0, 'less than 1 mo', NULL),
	(15, 15, 0, 'less than 1 mo', NULL);

-- Dumping structure for table tbdots.drug_prescriptions
CREATE TABLE IF NOT EXISTS `drug_prescriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_results_id` int NOT NULL,
  `drug_type` enum('H','R','Z','E','S') NOT NULL,
  `month_number` int NOT NULL,
  `dosage` decimal(5,2) NOT NULL,
  `unit` enum('ml','tab') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_prescription_treatment` (`lab_results_id`),
  CONSTRAINT `FK_prescription_treatment` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.drug_prescriptions: ~0 rows (approximately)

-- Dumping structure for table tbdots.dssm_results
CREATE TABLE IF NOT EXISTS `dssm_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_results_id` int DEFAULT NULL,
  `month` int DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `result` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lab_results_id` (`lab_results_id`),
  CONSTRAINT `dssm_results_ibfk_1` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.dssm_results: ~0 rows (approximately)

-- Dumping structure for table tbdots.household_members
CREATE TABLE IF NOT EXISTS `household_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_results_id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `age` int DEFAULT NULL,
  `screened` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_household_treatment` (`lab_results_id`) USING BTREE,
  CONSTRAINT `FK_household_treatment` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.household_members: ~0 rows (approximately)

-- Dumping structure for table tbdots.inventory
CREATE TABLE IF NOT EXISTS `inventory` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.inventory: ~15 rows (approximately)
INSERT INTO `inventory` (`id`, `product_id`, `quantity`, `expiration_date`, `batch_number`, `created_at`) VALUES
	(1, 1, 13, '2024-12-30', '20241112-001', '2024-11-12 20:43:33'),
	(2, 1, 2, '2024-12-02', '20241202-001', '2024-12-02 11:38:12'),
	(3, 1, 2, '2024-12-02', '20241202-002', '2024-12-02 11:39:22'),
	(4, 1, 3, '2024-12-02', '20241202-003', '2024-12-02 11:39:34'),
	(5, 1, 3, '2024-12-02', '20241202-004', '2024-12-02 11:40:23'),
	(6, 1, 2, '2024-12-02', '20241202-005', '2024-12-02 11:40:35'),
	(7, 1, 5, '2024-12-09', '20241202-006', '2024-12-02 11:40:59'),
	(8, 1, 3, '2024-12-02', '20241202-007', '2024-12-02 11:43:48'),
	(9, 1, 2, '2024-12-02', '20241202-008', '2024-12-02 11:44:42'),
	(10, 1, 3, '2024-12-02', '20241202-009', '2024-12-02 11:45:38'),
	(11, 1, 1, '2024-12-02', '20241202-010', '2024-12-02 11:46:33'),
	(12, 1, 3, '2024-12-02', '20241202-011', '2024-12-02 11:46:49'),
	(13, 1, 3, '2024-12-04', '20241202-012', '2024-12-02 11:48:10'),
	(14, 1, 5, '2024-12-02', '20241202-013', '2024-12-02 11:48:28'),
	(15, 1, 6, '2024-12-06', '20241202-014', '2024-12-02 11:49:36'),
	(16, 1, 4, '2024-12-12', '20241211-001', '2024-12-11 18:14:52'),
	(17, 1, 0, '2025-01-25', '20250117-001', '2025-01-17 07:19:20'),
	(18, 1, 15, '2025-07-21', '20250121-001', '2025-01-21 10:54:16');

-- Dumping structure for table tbdots.inventory_transactions
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('IN','OUT') NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `patient_id` int DEFAULT NULL,
  `notes` text,
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `patient_id` (`patient_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `inventory_transactions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.inventory_transactions: ~0 rows (approximately)
INSERT INTO `inventory_transactions` (`id`, `type`, `product_id`, `quantity`, `batch_number`, `patient_id`, `notes`, `transaction_date`, `user_id`) VALUES
	(1, 'IN', 1, 4, '20241211-001', NULL, NULL, '2024-12-11 18:14:52', 14),
	(2, 'OUT', 1, 10, NULL, NULL, '0', '2024-12-11 18:26:58', 14),
	(3, 'IN', 1, 2, '20250117-001', NULL, NULL, '2025-01-17 07:19:20', 17),
	(4, 'OUT', 1, 2, NULL, 13, '2323', '2025-01-21 09:55:53', 17),
	(5, 'IN', 1, 20, '20250121-001', NULL, NULL, '2025-01-21 10:54:16', 17),
	(6, 'OUT', 1, 5, NULL, 14, '0', '2025-01-21 10:54:29', 17);

-- Dumping structure for table tbdots.lab_results
CREATE TABLE IF NOT EXISTS `lab_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `case_number` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_opened` date DEFAULT NULL,
  `region_province` varchar(100) DEFAULT NULL,
  `facility_name` varchar(100) DEFAULT NULL,
  `patient_id` int NOT NULL,
  `physician_id` int NOT NULL,
  `source_of_patient` enum('Public Health Center','Other Health Facility','Private Hospital/Clinics/Physicians/NGOs','Community') DEFAULT NULL,
  `bacteriological_status` enum('confirmed','clinically') DEFAULT NULL,
  `tb_classification` enum('pulmonary','extra_pulmonary') DEFAULT NULL,
  `diagnosis` enum('TB DISEASE','TB INFECTION','TB EXPOSURE') DEFAULT NULL,
  `registration_group` enum('New','Relapse','Treatment after Failure','TALF','PTOU','Other') DEFAULT NULL,
  `treatment_regimen` varchar(50) DEFAULT NULL,
  `treatment_started_date` date DEFAULT NULL,
  `treatment_outcome` enum('CURED','TREATMENT COMPLETED','TREATMENT FAILED','DIED','LOST TO FOLLOW UP','NOT EVALUATED','ON-GOING') DEFAULT NULL,
  `treatment_outcome_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tst_result` varchar(255) DEFAULT NULL,
  `cxr_findings` text,
  `other_exam` varchar(255) DEFAULT NULL,
  `other_exam_date` date DEFAULT NULL,
  `tbdc` varchar(255) DEFAULT NULL,
  `dssm_due_date_0` date DEFAULT NULL,
  `dssm_due_date_1` date DEFAULT NULL,
  `dssm_due_date_2` date DEFAULT NULL,
  `dssm_due_date_3` date DEFAULT NULL,
  `dssm_due_date_4` date DEFAULT NULL,
  `dssm_due_date_5` date DEFAULT NULL,
  `dssm_due_date_6` date DEFAULT NULL,
  `dssm_due_date_7` date DEFAULT NULL,
  `dssm_exam_date_0` date DEFAULT NULL,
  `dssm_exam_date_1` date DEFAULT NULL,
  `dssm_exam_date_2` date DEFAULT NULL,
  `dssm_exam_date_3` date DEFAULT NULL,
  `dssm_exam_date_4` date DEFAULT NULL,
  `dssm_exam_date_5` date DEFAULT NULL,
  `dssm_exam_date_6` date DEFAULT NULL,
  `dssm_exam_date_7` date DEFAULT NULL,
  `dssm_result_0` varchar(255) DEFAULT NULL,
  `dssm_result_1` varchar(255) DEFAULT NULL,
  `dssm_result_2` varchar(255) DEFAULT NULL,
  `dssm_result_3` varchar(255) DEFAULT NULL,
  `dssm_result_4` varchar(255) DEFAULT NULL,
  `dssm_result_5` varchar(255) DEFAULT NULL,
  `dssm_result_6` varchar(255) DEFAULT NULL,
  `dssm_result_7` varchar(255) DEFAULT NULL,
  `bcg_scar` varchar(10) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `phil_health_no` varchar(50) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_treatment_patient` (`patient_id`),
  KEY `FK_treatment_physician` (`physician_id`),
  CONSTRAINT `FK_treatment_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `FK_treatment_physician` FOREIGN KEY (`physician_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.lab_results: ~15 rows (approximately)
INSERT INTO `lab_results` (`id`, `case_number`, `date_opened`, `region_province`, `facility_name`, `patient_id`, `physician_id`, `source_of_patient`, `bacteriological_status`, `tb_classification`, `diagnosis`, `registration_group`, `treatment_regimen`, `treatment_started_date`, `treatment_outcome`, `treatment_outcome_date`, `created_at`, `updated_at`, `tst_result`, `cxr_findings`, `other_exam`, `other_exam_date`, `tbdc`, `dssm_due_date_0`, `dssm_due_date_1`, `dssm_due_date_2`, `dssm_due_date_3`, `dssm_due_date_4`, `dssm_due_date_5`, `dssm_due_date_6`, `dssm_due_date_7`, `dssm_exam_date_0`, `dssm_exam_date_1`, `dssm_exam_date_2`, `dssm_exam_date_3`, `dssm_exam_date_4`, `dssm_exam_date_5`, `dssm_exam_date_6`, `dssm_exam_date_7`, `dssm_result_0`, `dssm_result_1`, `dssm_result_2`, `dssm_result_3`, `dssm_result_4`, `dssm_result_5`, `dssm_result_6`, `dssm_result_7`, `bcg_scar`, `height`, `occupation`, `phil_health_no`, `contact_person`, `contact_number`) VALUES
	(1, '2024-1', '2024-12-03', '', NULL, 4, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'ON-GOING', '2024-12-03', '2024-12-03 03:45:48', '2024-12-03 03:45:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(2, '2024-2', '2024-12-03', '', NULL, 10, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'CURED', '2024-12-03', '2024-12-03 03:46:04', '2024-12-03 03:46:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(3, '2024-3', '2024-12-03', '', NULL, 11, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'TREATMENT COMPLETED', '2024-12-03', '2024-12-03 03:46:16', '2024-12-03 03:46:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(4, '2024-4', '2024-12-03', '', NULL, 12, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'TREATMENT FAILED', '2024-12-03', '2024-12-03 03:46:27', '2024-12-03 03:46:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(5, '2024-5', '2024-12-03', '', NULL, 13, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'DIED', '2024-12-03', '2024-12-03 03:46:40', '2024-12-03 03:46:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(6, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2024-12-03 03:46:53', '2024-12-03 03:46:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(7, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-17 07:15:31', '2025-01-17 07:15:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(8, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-21 10:55:21', '2025-01-21 10:55:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(9, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-21 13:15:21', '2025-01-21 13:15:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(10, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-21 13:40:25', '2025-01-21 13:40:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'dafa', '1231as', '123sd', '21312123'),
	(11, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-21 13:40:51', '2025-01-21 13:40:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'testa', 'stas', 'treasd', 'asda'),
	(12, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-21 13:42:03', '2025-01-21 13:42:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'tes', 'sada', '21312', '12312'),
	(13, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-21 13:43:59', '2025-01-21 13:43:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'tsadas', 'asdada', 'adasdas', 'asdas'),
	(14, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-21 13:44:28', '2025-01-21 13:44:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '231', '1231', '12312sa', 'da21312'),
	(15, '2024-6', '2024-12-03', '', NULL, 14, 17, 'Public Health Center', NULL, NULL, 'TB DISEASE', 'New', 'Select Treatment Regimen', '2024-12-03', 'LOST TO FOLLOW UP', '2024-12-03', '2025-01-21 13:45:05', '2025-01-21 13:45:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '21312a', 'dasd123', '23a', 'dasdas');

-- Dumping structure for table tbdots.locations
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `municipality_id` int NOT NULL,
  `barangay_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`municipality_id`,`barangay_id`),
  KEY `barangay_id` (`barangay_id`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`),
  CONSTRAINT `locations_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.locations: ~117 rows (approximately)
INSERT INTO `locations` (`id`, `municipality_id`, `barangay_id`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(2, 1, 2, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(3, 1, 3, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(4, 1, 4, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(5, 1, 5, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(6, 1, 6, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(7, 1, 7, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(8, 1, 8, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(9, 1, 9, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(10, 1, 10, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(11, 1, 11, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(12, 1, 12, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(13, 1, 13, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(14, 1, 14, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(15, 1, 15, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(16, 1, 16, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(17, 1, 17, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(18, 1, 18, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(19, 1, 19, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(20, 1, 20, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(21, 1, 21, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(22, 1, 22, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(23, 1, 23, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(24, 2, 24, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(25, 2, 25, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(26, 2, 26, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(27, 2, 27, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(28, 2, 28, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(29, 2, 29, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(30, 2, 30, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(31, 2, 31, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(32, 2, 32, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(33, 2, 33, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(34, 2, 34, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(35, 2, 35, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(36, 2, 36, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(37, 2, 37, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(38, 2, 38, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(39, 2, 39, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(40, 2, 40, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(41, 2, 41, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(42, 2, 42, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(43, 2, 43, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(44, 2, 44, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(45, 2, 45, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(46, 2, 46, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(47, 2, 47, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(48, 2, 48, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(49, 2, 49, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(50, 2, 50, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(51, 2, 51, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(52, 2, 52, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(53, 2, 53, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(54, 3, 54, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(55, 3, 55, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(56, 3, 56, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(57, 3, 57, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(58, 3, 58, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(59, 3, 59, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(60, 3, 60, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(61, 3, 61, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(62, 3, 62, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(63, 3, 63, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(64, 3, 64, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(65, 3, 65, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(66, 3, 66, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(67, 3, 67, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(68, 3, 68, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(69, 3, 69, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(70, 3, 70, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(71, 4, 71, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(72, 4, 72, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(73, 4, 73, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(74, 4, 74, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(75, 4, 75, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(76, 4, 76, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(77, 4, 77, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(78, 4, 78, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(79, 4, 79, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(80, 4, 80, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(81, 4, 81, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(82, 4, 82, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(83, 4, 83, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(84, 4, 84, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(85, 4, 85, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(86, 4, 86, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(87, 4, 87, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(88, 4, 88, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(89, 4, 89, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(90, 5, 90, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(91, 5, 91, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(92, 5, 92, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(93, 5, 93, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(94, 5, 94, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(95, 5, 95, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(96, 5, 96, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(97, 5, 97, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(98, 5, 98, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(99, 5, 99, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(100, 5, 100, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(101, 5, 101, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(102, 5, 102, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(103, 5, 103, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(104, 5, 104, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(105, 6, 105, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(106, 6, 106, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(107, 6, 107, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(108, 6, 108, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(109, 6, 109, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(110, 6, 110, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(111, 6, 111, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(112, 6, 112, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(113, 6, 113, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(114, 6, 114, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(115, 6, 115, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(116, 6, 116, '2024-11-12 20:56:49', '2024-11-12 20:56:49'),
	(117, 6, 117, '2024-11-12 20:56:49', '2024-11-12 20:56:49');

-- Dumping structure for table tbdots.modules
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.modules: ~25 rows (approximately)
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
	(14, 'patient_show_lab_results'),
	(15, 'patient_edit_lab_results'),
	(16, 'patient_index_lab_results'),
	(17, 'activity_logs_index'),
	(18, 'inventory_index'),
	(19, 'inventory_add'),
	(20, 'inventory_edit'),
	(21, 'roles_permissions'),
	(22, 'logbook_index'),
	(23, 'logbook_add'),
	(24, 'logbook_edit'),
	(25, 'logbook_delete');

-- Dumping structure for table tbdots.municipalities
CREATE TABLE IF NOT EXISTS `municipalities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location` text NOT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.municipalities: ~6 rows (approximately)
INSERT INTO `municipalities` (`id`, `location`, `contact`, `updated_at`, `created_at`) VALUES
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
  `height` int DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `bcg_scar` enum('Yes','No','Doubtful') DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `phil_health_no` varchar(50) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_person_no` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_patients_lab_results` (`lab_results_id`),
  KEY `FK_patients_users` (`physician_id`),
  KEY `idx_fullname` (`fullname`(50)),
  KEY `idx_contact` (`contact`),
  KEY `FK_patients_locations` (`location_id`),
  CONSTRAINT `FK_patients_lab_results` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`),
  CONSTRAINT `FK_patients_locations` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `FK_patients_users` FOREIGN KEY (`physician_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.patients: ~7 rows (approximately)
INSERT INTO `patients` (`id`, `fullname`, `age`, `gender`, `contact`, `address`, `physician_id`, `location_id`, `lab_results_id`, `updated_at`, `created_at`, `height`, `dob`, `bcg_scar`, `occupation`, `phil_health_no`, `contact_person`, `contact_person_no`) VALUES
	(4, 'Lester bon Biono', 25, 1, '636565113', 'Brgy. San Teodoro, Binalbagan, Negros Occidental', 15, 1, 1, '2024-12-02 12:56:30', '2024-11-10 17:34:52', 21312, '2024-11-22', NULL, 'Farmer', '12312', '12sdas', '164654'),
	(10, 'Bea Sasi', 50, 2, NULL, NULL, 15, 66, 6, '2024-12-02 12:56:10', '2024-11-13 02:31:37', 0, '2024-11-11', NULL, '', '', '', ''),
	(11, 'Kuyas kuys', 21, 1, '', '', 16, 60, 2, '2024-12-02 12:56:40', '2024-11-27 07:28:39', 0, '2024-11-27', NULL, '', '', '', ''),
	(12, '12312', 2312, 1, '', '', 15, 54, NULL, '2024-12-02 12:39:51', '2024-12-02 12:39:51', 23, '2024-12-02', NULL, '', '', '', ''),
	(13, '3245435345sadas', 32, 1, '', '', 15, 54, NULL, '2024-12-02 12:40:58', '2024-12-02 12:40:58', 32, '2024-12-02', NULL, '', '', '', ''),
	(14, 'Juan Ponce', 26, 1, '', '', 15, 56, NULL, '2024-12-03 02:17:16', '2024-12-03 02:17:16', 0, '1985-01-15', NULL, '', '', '', ''),
	(15, 'Testing', 0, 1, '', '', 15, 66, NULL, '2025-01-21 10:58:35', '2025-01-21 10:58:35', 0, '2025-01-21', NULL, '', '', '', '');

-- Dumping structure for table tbdots.patient_logbook
CREATE TABLE IF NOT EXISTS `patient_logbook` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `physician_id` int NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `log_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_logbook_patient` (`patient_id`),
  KEY `FK_logbook_physician` (`physician_id`),
  CONSTRAINT `FK_logbook_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_logbook_physician` FOREIGN KEY (`physician_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.patient_logbook: ~0 rows (approximately)
INSERT INTO `patient_logbook` (`id`, `patient_id`, `physician_id`, `notes`, `log_date`, `created_at`) VALUES
	(1, 12, 15, 'tstt', '2025-01-21', '2025-01-21 10:16:58'),
	(2, 14, 15, 'Proficient in Amazon Web Services (AWS), with expertise in deploying, managing, and optimizing cloud-based applications and infrastructure. Skilled in utilizing AWS services such as EC2, S3, RDS, and Lambda to build scalable and cost-efficient solutions. I have hands-on experience with AWS DevOps practices, including CI/CD pipelines, infrastructure as code (IaC) using tools like CloudFormation and Terraform, and monitoring using CloudWatch. My work includes configuring secure environments, managing databases on AWS, and leveraging services like API Gateway for seamless integrations. I am adept at troubleshooting and optimizing AWS deployments to ensure high performance and reliability.', '2025-01-21', '2025-01-21 10:17:13'),
	(4, 14, 18, 'tysdsfgslk trjlksadas', '2025-01-21', '2025-01-21 10:24:26'),
	(5, 10, 18, 'testging sad;lsdgpo;adasda', '2025-01-21', '2025-01-21 10:25:42'),
	(6, 14, 18, 'tesats', '2025-01-21', '2025-01-21 10:31:02'),
	(7, 11, 18, 'tstsat', '2025-01-21', '2025-01-21 10:31:16');

-- Dumping structure for table tbdots.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) NOT NULL,
  `generic_name` varchar(255) NOT NULL,
  `uses` text,
  `dosage` varchar(255) DEFAULT NULL,
  `unit_of_measure` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.products: ~2 rows (approximately)
INSERT INTO `products` (`id`, `brand_name`, `generic_name`, `uses`, `dosage`, `unit_of_measure`, `created_at`) VALUES
	(1, 'Biogesic', 'Paracetamol', 'Fever', '500', 'Tablet', '2024-11-12 20:41:49'),
	(3, 'Biogesic', 'Paracetamol', '2321', '500', 'Tablet', '2025-01-17 07:19:45');

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
	(1, 'Super Admin', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25]', '2025-01-21 10:08:19', '2024-05-26 07:20:56'),
	(2, 'Admin', '[1,2,3,4,5,6,7,8,9,10,11,12,17,18,19,20,21,22]', '2025-01-21 10:03:22', '2024-05-26 07:21:42'),
	(3, 'Physician', '[5,9,10,11,12,22,23]', '2025-01-21 10:23:44', '2024-05-26 07:22:14'),
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
  `location_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_users_roles` (`role`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `FK_users_roles` FOREIGN KEY (`role`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.users: ~6 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`, `role`, `updated_at`, `created_at`, `location_id`) VALUES
	(9, 'test', '098f6bcd4621d373cade4e832627b4f6', 'test', 'test', 2, '2024-05-03 05:29:07', '2025-01-21 09:58:31', 5),
	(14, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'admin', 2, '2024-05-27 06:30:41', '2024-05-27 06:30:41', NULL),
	(15, 'Docone', '21232f297a57a5a743894a0e4a801fc3', 'Doc', 'One', 3, '2024-05-27 06:39:57', '2024-11-12 23:11:19', NULL),
	(16, 'asdas', '0aa1ea9a5a04b78d4581dd6d17742627', 'asdas', 'dasda', 3, '2024-11-13 02:35:40', '2024-11-13 02:35:40', NULL),
	(17, 'superad', '21232f297a57a5a743894a0e4a801fc3', 'Super', 'Admin', 1, '2024-11-25 07:30:34', '2024-11-25 07:30:34', NULL),
	(18, 'phys', '21232f297a57a5a743894a0e4a801fc3', 'test', 'user', 3, '2025-01-17 07:07:28', '2025-01-17 07:14:46', 54),
	(19, 'testingssss', '21232f297a57a5a743894a0e4a801fc3', 'testsaasd', 'teasda', 4, '2025-01-21 13:04:22', '2025-01-21 13:04:22', 4),
	(20, 'phyx', '21232f297a57a5a743894a0e4a801fc3', 'daa', 'asdas', 3, '2025-01-21 13:04:57', '2025-01-21 13:04:57', 5);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
