-- Restaurant Management System Database
-- Version: 1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_management`
--
CREATE DATABASE IF NOT EXISTS `restaurant_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `restaurant_management`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'manager', 'customer') NOT NULL DEFAULT 'customer',
  `phone` VARCHAR(20) DEFAULT NULL,
  `professional_details` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `cuisine` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `price_range` ENUM('$', '$$', '$$$', '$$$$') NOT NULL,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT '0.0',
  `image` VARCHAR(255) DEFAULT NULL,
  `seating_capacity` INT(11) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_cuisine` (`cuisine`),
  INDEX `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_managers`
--

CREATE TABLE `restaurant_managers` (
  `restaurant_id` INT(11) NOT NULL,
  `manager_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`restaurant_id`, `manager_id`),
  INDEX `idx_manager_id` (`manager_id`),
  CONSTRAINT `fk_rm_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rm_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `category` ENUM('appetizer', 'main', 'dessert', 'beverage') NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `available` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_restaurant_category` (`restaurant_id`, `category`),
  CONSTRAINT `fk_menu_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` INT(11) NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `guests` INT(11) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  `special_requests` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_restaurant_date` (`restaurant_id`, `date`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_reservation_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `external_services`
--

CREATE TABLE `external_services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` ENUM('tour', 'hotel', 'taxi') NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT '0.0',
  `available` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `service_type` ENUM('tour', 'hotel', 'taxi', 'restaurant') NOT NULL,
  `service_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `date` DATE DEFAULT NULL,
  `time` TIME DEFAULT NULL,
  `guests` INT(11) DEFAULT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  `special_requests` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_customer_status` (`customer_id`, `status`),
  INDEX `idx_service` (`service_type`, `service_id`),
  CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` INT(11) NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `rating` INT(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` TEXT NOT NULL,
  `date` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_restaurant_rating` (`restaurant_id`, `rating`),
  CONSTRAINT `fk_review_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `api_key` VARCHAR(64) NOT NULL UNIQUE,
  `service_name` VARCHAR(255) NOT NULL,
  `consumer_group` VARCHAR(50) NOT NULL,
  `permissions` ENUM('read', 'write', 'read_write') NOT NULL DEFAULT 'read',
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `usage_count` INT(11) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_used` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_api_key` (`api_key`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `country` VARCHAR(100) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT '0.0',
  `category` ENUM('historical', 'nature', 'cultural', 'adventure') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Insert sample data
--

-- Sample restaurants
INSERT INTO `restaurants` (`id`, `name`, `description`, `cuisine`, `address`, `phone`, `price_range`, `rating`, `image`, `seating_capacity`) VALUES
(1, 'La Bella Vista', 'Authentic Italian cuisine with a modern twist', 'Italian', '123 Harbor View, Downtown', '+1 234 567 8900', '$$$', 4.8, '/elegant-italian-restaurant.png', 80),
(2, 'Sakura Garden', 'Traditional Japanese dining experience', 'Japanese', '456 Cherry Blossom Lane', '+1 234 567 8901', '$$$$', 4.9, '/japanese-restaurant-sushi-bar.jpg', 60),
(3, 'Spice Route', 'Vibrant Indian flavors and spices', 'Indian', '789 Curry Street, Midtown', '+1 234 567 8902', '$$', 4.6, '/indian-restaurant-colorful-interior.jpg', 100);

-- Sample menu items
INSERT INTO `menu_items` (`id`, `restaurant_id`, `name`, `description`, `price`, `category`, `image`, `available`) VALUES
(1, 1, 'Margherita Pizza', 'Fresh mozzarella, tomatoes, basil', 18.99, 'main', '/margherita-pizza.png', 1),
(2, 1, 'Tiramisu', 'Classic Italian dessert', 9.99, 'dessert', '/classic-tiramisu.png', 1),
(3, 1, 'Bruschetta', 'Toasted bread with tomatoes and garlic', 12.99, 'appetizer', '/classic-bruschetta.png', 1);

-- Sample users
INSERT INTO `users` (`id`, `email`, `password`, `name`, `role`, `phone`, `professional_details`) VALUES
(1, 'admin@restaurant.com', '$2y$10$example_hashed_password', 'Admin User', 'admin', '+1-555-0123', 'Restaurant Manager with 10+ years experience'),
(2, 'manager@restaurant.com', '$2y$10$example_hashed_password', 'Manager User', 'manager', '+1-555-0456', 'Professional Chef with 5+ years experience'),
(3, 'customer@example.com', '$2y$10$example_hashed_password', 'Customer User', 'customer', '+1-555-0789', NULL);

-- Sample reservations
INSERT INTO `reservations` (`id`, `restaurant_id`, `customer_name`, `customer_email`, `customer_phone`, `date`, `time`, `guests`, `status`, `special_requests`) VALUES
(1, 1, 'John Doe', 'john@example.com', '+1 234 567 1111', '2025-01-15', '19:00:00', 4, 'confirmed', 'Window seat preferred'),
(2, 1, 'Jane Smith', 'jane@example.com', '+1 234 567 2222', '2025-01-16', '20:00:00', 2, 'pending', NULL);

-- Sample restaurant manager assignments
INSERT INTO `restaurant_managers` (`restaurant_id`, `manager_id`) VALUES
(1, 2);

-- Sample external services
INSERT INTO `external_services` (`id`, `type`, `name`, `description`, `price`, `image`, `rating`, `available`) VALUES
(1, 'tour', 'City Historical Tour', '3-hour guided tour of historical landmarks', 45.00, '/city-tour-bus.jpg', 4.7, 1),
(2, 'hotel', 'Grand Palace Hotel', '5-star luxury accommodation', 250.00, '/luxury-hotel-exterior.png', 4.9, 1),
(3, 'taxi', 'Premium Taxi Service', '24/7 reliable transportation', 25.00, '/taxi-cab-service.jpg', 4.5, 1);

-- Sample places
INSERT INTO `places` (`id`, `name`, `description`, `country`, `city`, `image`, `rating`, `category`) VALUES
(1, 'Grand Canyon', 'Breathtaking natural wonder with stunning views', 'USA', 'Arizona', '/grand-canyon.png', 4.9, 'nature'),
(2, 'Eiffel Tower', 'Iconic landmark and symbol of Paris', 'France', 'Paris', '/eiffel-tower.png', 4.8, 'historical'),
(3, 'Machu Picchu', 'Ancient Incan citadel in the Andes Mountains', 'Peru', 'Cusco', '/machu-picchu-ancient-city.png', 4.9, 'historical'),
(4, 'Kyoto Temples', 'Traditional Japanese temples and gardens', 'Japan', 'Kyoto', '/kyoto-temples.jpg', 4.7, 'cultural');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;