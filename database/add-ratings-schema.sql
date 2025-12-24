-- Add ratings table for restaurant reviews
CREATE TABLE IF NOT EXISTS `restaurant_ratings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `rating` INT(11) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `review` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_customer_restaurant` (`customer_id`, `restaurant_id`),
  INDEX `idx_restaurant_id` (`restaurant_id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_rating` (`rating`),
  CONSTRAINT `fk_rating_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rating_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
