USE restaurant_management;

ALTER TABLE `users`
ADD COLUMN `profile_image` VARCHAR(255) DEFAULT NULL AFTER `professional_details`;
