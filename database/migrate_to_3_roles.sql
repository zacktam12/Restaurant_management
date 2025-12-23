-- Migration Script: Convert from 5 roles to 3 roles
-- This script migrates the database from the old 5-role system (admin, manager, customer, tourist, tour_guide)
-- to the new 3-role system (admin, manager, customer)
-- 
-- Date: 2025-12-23
-- Version: 1.0

USE `restaurant_management`;

-- Step 1: Backup existing users table structure and data
CREATE TABLE IF NOT EXISTS `users_backup_before_migration` LIKE `users`;
INSERT INTO `users_backup_before_migration` SELECT * FROM `users`;

-- Step 2: Update all 'tourist' users to 'customer'
UPDATE `users` SET `role` = 'customer' WHERE `role` = 'tourist';

-- Step 3: Update all 'tour_guide' users to 'customer'
UPDATE `users` SET `role` = 'customer' WHERE `role` = 'tour_guide';

-- Step 4: Alter the users table to update the ENUM to only include 3 roles
ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'manager', 'customer') NOT NULL DEFAULT 'customer';

-- Step 5: Verify migration
-- Display count of users by role after migration
SELECT 
    'Migration Complete - User Count by Role:' AS Message,
    role,
    COUNT(*) AS user_count
FROM `users`
GROUP BY role
ORDER BY FIELD(role, 'admin', 'manager', 'customer');

-- Step 6: Display any potential issues
SELECT 
    'Users that were migrated from tourist/tour_guide:' AS Message,
    COUNT(*) AS migrated_count
FROM `users_backup_before_migration`
WHERE `role` IN ('tourist', 'tour_guide');

-- To rollback this migration, run:
-- DROP TABLE IF EXISTS `users`;
-- CREATE TABLE `users` LIKE `users_backup_before_migration`;
-- INSERT INTO `users` SELECT * FROM `users_backup_before_migration`;
-- Then manually restore the ENUM values

COMMIT;
