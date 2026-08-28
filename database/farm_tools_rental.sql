-- ============================================================
-- Farm Tools Rental Platform Database Schema
-- Database: farm_tools_rental
-- Compatible with MySQL / MariaDB / XAMPP phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS `farm_tools_rental` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `farm_tools_rental`;

-- --------------------------------------------------------
-- Table structure for `admin`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `equipment`;
DROP TABLE IF EXISTS `farmers`;
DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `farmers`
-- --------------------------------------------------------
CREATE TABLE `farmers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `equipment`
-- --------------------------------------------------------
CREATE TABLE `equipment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `price_per_day` DECIMAL(10,2) NOT NULL,
  `availability` ENUM('Available','Unavailable','Maintenance') NOT NULL DEFAULT 'Available',
  `image` VARCHAR(255) DEFAULT 'default_equipment.jpg',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `bookings`
-- --------------------------------------------------------
CREATE TABLE `bookings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `farmer_id` INT(11) NOT NULL,
  `equipment_id` INT(11) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `days` INT(11) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pending','Approved','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  KEY `equipment_id` (`equipment_id`),
  CONSTRAINT `fk_bookings_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA FOR TESTING
-- Default Admin credentials: admin / admin123
-- Default Farmer credentials: farmer@example.com / farmer123
-- ============================================================

INSERT INTO `admin` (`id`, `username`, `password`, `email`) VALUES
(1, 'admin', '$2y$10$mqrJiw/TEOCReDMvZXuGdujeTaZ6qxL8eScMdqPXYoQNcY/c3F8ky', 'admin@farmtools.com');

INSERT INTO `farmers` (`id`, `name`, `email`, `phone`, `address`, `password`) VALUES
(1, 'Ramesh Kumar', 'farmer@example.com', '9876543210', 'Green Valley Farm, Village Rampur, District Karnal, Haryana', '$2y$10$kIdlh/RNCYDRolvlIeDSwud.tM0m1o6pzHCSie23W16/16Sl25mxm'),
(2, 'Sunita Patel', 'sunita@example.com', '9812345678', 'Plot 45, Sunrise Organic Farm, Anand, Gujarat', '$2y$10$kIdlh/RNCYDRolvlIeDSwud.tM0m1o6pzHCSie23W16/16Sl25mxm'),
(3, 'Rajesh Sharma', 'rajesh@example.com', '9765432109', 'Kisan Nagar, Bathinda, Punjab', '$2y$10$kIdlh/RNCYDRolvlIeDSwud.tM0m1o6pzHCSie23W16/16Sl25mxm');

INSERT INTO `equipment` (`id`, `name`, `category`, `description`, `price_per_day`, `availability`, `image`) VALUES
(1, 'Tractor (50 HP Heavy Duty)', 'Tractors', 'High-performance 50 HP 4WD diesel tractor suitable for heavy plowing, tilling, hauling, and field preparation across large farmland.', 1500.00, 'Available', 'tractor.jpg'),
(2, 'Combine Harvester', 'Harvesting', 'Multi-crop combine harvester equipped with advanced threshing mechanism for wheat, paddy, and corn harvesting with high efficiency.', 3500.00, 'Available', 'harvester.jpg'),
(3, 'High-Flow Diesel Water Pump', 'Irrigation', 'Portable 10 HP diesel engine water pump designed for heavy irrigation, flood drainage, and tube well water extraction.', 450.00, 'Available', 'water_pump.jpg'),
(4, 'Rotary Cultivator (Rotavator)', 'Tillage', 'Heavy-duty 6-foot rotary cultivator for rapid soil conditioning, weed removal, and seedbed preparation.', 750.00, 'Available', 'cultivator.jpg'),
(5, 'Automatic Seed Drill & Fertilizer Unit', 'Planting', 'Precision automatic seed drill machine with double box for simultaneous seed sowing and fertilizer placement.', 600.00, 'Available', 'seed_drill.jpg'),
(6, 'Power Boom Sprayer', 'Pest Control', '500-liter tractor-mounted power boom sprayer for uniform pesticide, herbicide, and liquid fertilizer application.', 500.00, 'Available', 'sprayer.jpg');

INSERT INTO `bookings` (`id`, `farmer_id`, `equipment_id`, `start_date`, `end_date`, `days`, `total_amount`, `status`, `created_at`) VALUES
(1, 1, 1, '2026-09-01', '2026-09-03', 3, 4500.00, 'Approved', '2026-08-20 10:15:00'),
(2, 1, 4, '2026-09-05', '2026-09-06', 2, 1500.00, 'Pending', '2026-08-22 14:30:00'),
(3, 2, 2, '2026-09-10', '2026-09-12', 3, 10500.00, 'Pending', '2026-08-24 09:45:00'),
(4, 3, 3, '2026-08-15', '2026-08-17', 3, 1350.00, 'Completed', '2026-08-14 11:20:00'),
(5, 2, 5, '2026-08-18', '2026-08-19', 2, 1200.00, 'Completed', '2026-08-16 16:00:00');
