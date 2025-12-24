-- Restaurant Management System Database Schema
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS RMS;
USE restaurant_management;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
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

-- Restaurants table
CREATE TABLE IF NOT EXISTS `restaurants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `manager_id` INT(11) NOT NULL,
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
  INDEX `idx_rating` (`rating`),
  INDEX `idx_manager` (`manager_id`),
  CONSTRAINT `fk_restaurant_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservations table
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` INT(11) NOT NULL,
  `customer_id` INT(11) DEFAULT NULL,
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
  INDEX `idx_customer_email` (`customer_email`),
  INDEX `idx_customer_id` (`customer_id`),
  CONSTRAINT `fk_reservation_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reservation_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu items table
CREATE TABLE IF NOT EXISTS `menu_items` (
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

-- External services table
CREATE TABLE IF NOT EXISTS `external_services` (
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

-- Bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
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

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`email`, `password`, `name`, `role`, `phone`) VALUES
('admin@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'admin', '1234567890');

-- Insert sample manager user (password: manager123)
INSERT INTO `users` (`email`, `password`, `name`, `role`, `phone`) VALUES
('manager@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Restaurant Manager', 'manager', '0987654321');

-- Insert sample customer user (password: customer123)
INSERT INTO `users` (`email`, `password`, `name`, `role`, `phone`) VALUES
('customer@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Customer', 'customer', '5555555555');

-- Insert sample restaurants
INSERT INTO `restaurants` (`manager_id`, `name`, `description`, `cuisine`, `address`, `phone`, `price_range`, `rating`, `seating_capacity`) VALUES
(2, 'The Golden Fork', 'Fine dining experience with exquisite Italian cuisine', 'Italian', '123 Main Street, Downtown', '555-0101', '$$$', 4.5, 50),
(2, 'Spice Garden', 'Authentic Indian flavors in a cozy atmosphere', 'Indian', '456 Oak Avenue', '555-0102', '$$', 4.3, 40),
(2, 'Sakura Sushi', 'Fresh sushi and Japanese delicacies', 'Japanese', '789 Cherry Lane', '555-0103', '$$$', 4.7, 35),
(2, 'La Petite Bistro', 'Classic French cuisine with modern twist', 'French', '321 Elm Street', '555-0104', '$$$$', 4.8, 30),
(2, 'Dragon Palace', 'Traditional Chinese dishes and dim sum', 'Chinese', '654 Bamboo Road', '555-0105', '$$', 4.2, 60);

-- Insert sample menu items
INSERT INTO `menu_items` (`restaurant_id`, `name`, `description`, `price`, `category`, `available`) VALUES
(1, 'Bruschetta', 'Toasted bread with fresh tomatoes and basil', 8.99, 'appetizer', 1),
(1, 'Spaghetti Carbonara', 'Classic pasta with creamy egg sauce and pancetta', 18.99, 'main', 1),
(1, 'Tiramisu', 'Traditional Italian coffee-flavored dessert', 9.99, 'dessert', 1),
(1, 'Espresso', 'Strong Italian coffee', 3.99, 'beverage', 1),
(2, 'Samosa', 'Crispy pastry filled with spiced potatoes', 6.99, 'appetizer', 1),
(2, 'Butter Chicken', 'Tender chicken in creamy tomato sauce', 16.99, 'main', 1),
(2, 'Gulab Jamun', 'Sweet milk dumplings in rose syrup', 7.99, 'dessert', 1),
(2, 'Mango Lassi', 'Refreshing yogurt drink with mango', 4.99, 'beverage', 1);

-- Insert sample external services
INSERT INTO `external_services` (`type`, `name`, `description`, `price`, `rating`, `available`) VALUES
('tour', 'City Walking Tour', 'Explore the historic downtown area on foot', 25.00, 4.5, 1),
('tour', 'Wine Country Tour', 'Full-day tour of local wineries', 150.00, 4.8, 1),
('hotel', 'Grand Plaza Hotel', 'Luxury 5-star hotel in city center', 250.00, 4.7, 1),
('hotel', 'Budget Inn', 'Affordable accommodation with basic amenities', 75.00, 3.8, 1),
('taxi', 'Airport Transfer', 'Private car service to/from airport', 45.00, 4.4, 1),
('taxi', 'City Tour by Car', 'Comfortable car tour around the city', 80.00, 4.3, 1);
