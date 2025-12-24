-- Migration Script: Add customer_id to existing reservations table
-- Run this if you already have the database created
-- This script is safe to run multiple times

USE restaurant_management;

-- Add customer_id column if it doesn't exist
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'restaurant_management' 
    AND TABLE_NAME = 'reservations' 
    AND COLUMN_NAME = 'customer_id'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `reservations` ADD COLUMN `customer_id` INT(11) DEFAULT NULL AFTER `restaurant_id`',
    'SELECT "Column customer_id already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for customer_id if it doesn't exist
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'restaurant_management' 
    AND TABLE_NAME = 'reservations' 
    AND INDEX_NAME = 'idx_customer_id'
);

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE `reservations` ADD INDEX `idx_customer_id` (`customer_id`)',
    'SELECT "Index idx_customer_id already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint if it doesn't exist
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'restaurant_management' 
    AND TABLE_NAME = 'reservations' 
    AND CONSTRAINT_NAME = 'fk_reservation_customer'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `reservations` ADD CONSTRAINT `fk_reservation_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
    'SELECT "Foreign key fk_reservation_customer already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration completed successfully!' as status;
