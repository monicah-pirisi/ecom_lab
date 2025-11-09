-- Update brands table to support brand management with categories and user tracking
-- Execute this script after the main database setup

USE `ecommerce_2025A_monicah_lekupe`;

-- Add category and user columns to brands table
ALTER TABLE `brands`
ADD COLUMN `brand_cat` int(11) NOT NULL AFTER `brand_name`,
ADD COLUMN `user_id` int(11) NOT NULL AFTER `brand_cat`;

-- Add unique constraint for brand_name + brand_cat combination
ALTER TABLE `brands`
ADD UNIQUE KEY `unique_brand_category` (`brand_name`, `brand_cat`);

-- Add foreign key constraints
ALTER TABLE `brands`
ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`brand_cat`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `brands_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;
