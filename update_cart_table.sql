-- SQL Script to add customization columns to cart table
-- Run each statement one at a time in phpMyAdmin

-- First, check the current structure of cart table
DESCRIBE cart;

-- If the columns don't exist, add them one by one:
-- (If you get "Duplicate column" error, that column already exists - skip it)

ALTER TABLE `cart` ADD COLUMN `portion` VARCHAR(50) DEFAULT 'Regular';

ALTER TABLE `cart` ADD COLUMN `spice_level` VARCHAR(20) DEFAULT 'Medium';

ALTER TABLE `cart` ADD COLUMN `oil_level` VARCHAR(20) DEFAULT 'Medium';

ALTER TABLE `cart` ADD COLUMN `salt_level` VARCHAR(20) DEFAULT 'Normal';

-- Alternative: If the cart table doesn't exist, create it:
-- CREATE TABLE IF NOT EXISTS `cart` (
--     `id` INT AUTO_INCREMENT PRIMARY KEY,
--     `customer_id` INT NOT NULL,
--     `dish_id` INT NOT NULL,
--     `quantity` INT DEFAULT 1,
--     `portion` VARCHAR(50) DEFAULT 'Regular',
--     `spice_level` VARCHAR(20) DEFAULT 'Medium',
--     `oil_level` VARCHAR(20) DEFAULT 'Medium',
--     `salt_level` VARCHAR(20) DEFAULT 'Normal',
--     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );
