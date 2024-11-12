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
  `action` enum('CREATE','UPDATE','DELETE') NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int NOT NULL,
  `details` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_logs_user` (`user_id`),
  CONSTRAINT `FK_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.activity_logs: ~13 rows (approximately)
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
	(13, 14, 'UPDATE', 'roles', 2, 'Updated role permissions', '2024-11-12 23:08:34');

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
  `treatment_card_id` int NOT NULL,
  `examination_date` date NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `unexplained_fever` tinyint(1) DEFAULT NULL,
  `unexplained_cough` tinyint(1) DEFAULT NULL,
  `unimproved_wellbeing` tinyint(1) DEFAULT NULL,
  `poor_appetite` tinyint(1) DEFAULT NULL,
  `positive_pe_findings` tinyint(1) DEFAULT NULL,
  `side_effects` text,
  PRIMARY KEY (`id`),
  KEY `FK_examination_treatment` (`treatment_card_id`),
  CONSTRAINT `FK_examination_treatment` FOREIGN KEY (`treatment_card_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.clinical_examinations: ~0 rows (approximately)
-- Dumping structure for table tbdots.drug_histories
CREATE TABLE IF NOT EXISTS `drug_histories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `treatment_card_id` int NOT NULL,
  `has_history` tinyint(1) DEFAULT '0',
  `duration` enum('less than 1 mo','1 mo or more') DEFAULT NULL,
  `drugs_taken` set('H','R','Z','E','S') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_history_treatment` (`treatment_card_id`),
  CONSTRAINT `FK_history_treatment` FOREIGN KEY (`treatment_card_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.drug_histories: ~0 rows (approximately)
-- Dumping structure for table tbdots.drug_prescriptions
CREATE TABLE IF NOT EXISTS `drug_prescriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `treatment_card_id` int NOT NULL,
  `drug_type` enum('H','R','Z','E','S') NOT NULL,
  `month_number` int NOT NULL,
  `dosage` decimal(5,2) NOT NULL,
  `unit` enum('ml','tab') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_prescription_treatment` (`treatment_card_id`),
  CONSTRAINT `FK_prescription_treatment` FOREIGN KEY (`treatment_card_id`) REFERENCES `lab_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.drug_prescriptions: ~0 rows (approximately)
-- Dumping structure for table tbdots.household_members
CREATE TABLE IF NOT EXISTS `household_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `treatment_card_id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `age` int DEFAULT NULL,
  `screened` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_household_treatment` (`treatment_card_id`),
  CONSTRAINT `FK_household_treatment` FOREIGN KEY (`treatment_card_id`) REFERENCES `lab_results` (`id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.inventory: ~1 rows (approximately)
INSERT INTO `inventory` (`id`, `product_id`, `quantity`, `expiration_date`, `batch_number`, `created_at`) VALUES
	(1, 1, 23, '2024-11-14', '20241112-001', '2024-11-12 20:43:33');

-- Dumping structure for table tbdots.lab_results
CREATE TABLE IF NOT EXISTS `lab_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `case_number` varchar(50) NOT NULL,
  `date_opened` date DEFAULT NULL,
  `region_province` varchar(100) DEFAULT NULL,
  `facility_name` varchar(100) DEFAULT NULL,
  `patient_id` int NOT NULL,
  `physician_id` int NOT NULL,
  `source_of_patient` enum('Public Health Center','Other Health Facility','Private Hospital/Clinics/Physicians/NGOs','Community') DEFAULT NULL,
  `bacteriological_status` enum('Bacteriologically Confirmed','Clinically Diagnosed') DEFAULT NULL,
  `tb_classification` enum('Pulmonary','Extra Pulmonary') DEFAULT NULL,
  `diagnosis` enum('TB DISEASE','TB INFECTION','TB EXPOSURE') DEFAULT NULL,
  `registration_group` enum('New','Relapse','Treatment after Failure','TALF','PTOU','Other') DEFAULT NULL,
  `treatment_regimen` varchar(50) DEFAULT NULL,
  `treatment_started_date` date DEFAULT NULL,
  `treatment_outcome` enum('CURED','TREATMENT COMPLETED','TREATMENT FAILED','DIED','LOST TO FOLLOW UP','NOT EVALUATED') DEFAULT NULL,
  `treatment_outcome_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_treatment_patient` (`patient_id`),
  KEY `FK_treatment_physician` (`physician_id`),
  CONSTRAINT `FK_treatment_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `FK_treatment_physician` FOREIGN KEY (`physician_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.lab_results: ~2 rows (approximately)
INSERT INTO `lab_results` (`id`, `case_number`, `date_opened`, `region_province`, `facility_name`, `patient_id`, `physician_id`, `source_of_patient`, `bacteriological_status`, `tb_classification`, `diagnosis`, `registration_group`, `treatment_regimen`, `treatment_started_date`, `treatment_outcome`, `treatment_outcome_date`, `created_at`, `updated_at`) VALUES
	(1, '21312', '2024-11-05', '', NULL, 4, 14, 'Public Health Center', NULL, NULL, NULL, 'New', 'Select Treatment Regimen', NULL, 'CURED', NULL, '2024-11-12 06:17:33', '2024-11-12 06:17:33'),
	(2, '2024-0001', '2024-11-14', '', NULL, 4, 14, 'Public Health Center', NULL, NULL, NULL, 'New', 'Select Treatment Regimen', NULL, 'CURED', NULL, '2024-11-12 21:21:52', '2024-11-12 21:47:44');

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
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.modules: ~21 rows (approximately)
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
	(21, 'roles_permissions');

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
  KEY `FK_patients_location` (`location_id`),
  KEY `FK_patients_lab_results` (`lab_results_id`),
  KEY `FK_patients_users` (`physician_id`),
  KEY `idx_fullname` (`fullname`(50)),
  KEY `idx_contact` (`contact`),
  CONSTRAINT `FK_patients_lab_results` FOREIGN KEY (`lab_results_id`) REFERENCES `lab_results` (`id`),
  CONSTRAINT `FK_patients_location` FOREIGN KEY (`location_id`) REFERENCES `municipalities` (`id`),
  CONSTRAINT `FK_patients_users` FOREIGN KEY (`physician_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.patients: ~1 rows (approximately)
INSERT INTO `patients` (`id`, `fullname`, `age`, `gender`, `contact`, `address`, `physician_id`, `location_id`, `lab_results_id`, `updated_at`, `created_at`, `height`, `dob`, `bcg_scar`, `occupation`, `phil_health_no`, `contact_person`, `contact_person_no`) VALUES
	(4, 'Lester bon Biono', 25, 1, '636565113', 'Brgy. San Teodoro, Binalbagan, Negros Occidental', 15, 1, NULL, '2024-11-12 08:16:51', '2024-11-10 17:34:52', 21312, '2024-11-22', NULL, 'Farmer', '12312', '12sdas', '164654');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table tbdots.products: ~1 rows (approximately)
INSERT INTO `products` (`id`, `brand_name`, `generic_name`, `uses`, `dosage`, `unit_of_measure`, `created_at`) VALUES
	(1, 'Biogesic', 'Paracetamol', 'Fever', '500', 'Tablet', '2024-11-12 20:41:49');

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
	(1, 'Super Admin', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21]', '2024-11-12 20:19:13', '2024-05-26 07:20:56'),
	(2, 'Admin', '["1","2","3","4","5","6","7","8","9","10","11","12","17","18","19","20"]', '2024-11-12 23:08:34', '2024-05-26 07:21:42'),
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

-- Dumping data for table tbdots.test_lists: ~7 rows (approximately)
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
	(15, 'Docone', '21232f297a57a5a743894a0e4a801fc3', 'Doc', 'One', 3, '2024-05-27 06:39:57', '2024-11-12 23:11:19');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
