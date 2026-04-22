-- Performance Optimization: Add Database Indexes
-- Run this in phpMyAdmin SQL tab

-- Products table indexes
ALTER TABLE `products` ADD INDEX `idx_is_active` (`is_active`);
ALTER TABLE `products` ADD INDEX `idx_category_active` (`category_id`, `is_active`);
ALTER TABLE `products` ADD INDEX `idx_stock` (`stock`);

-- Orders table indexes
ALTER TABLE `orders` ADD INDEX `idx_user_created` (`user_id`, `created_at`);
ALTER TABLE `orders` ADD INDEX `idx_status` (`status`);
ALTER TABLE `orders` ADD INDEX `idx_payment_status` (`payment_status`);

-- Users table indexes
ALTER TABLE `users` ADD INDEX `idx_email` (`email`);
ALTER TABLE `users` ADD INDEX `idx_is_active` (`is_active`);

-- Categories table indexes
ALTER TABLE `categories` ADD INDEX `idx_is_active` (`is_active`);

-- Reviews table indexes
ALTER TABLE `reviews` ADD INDEX `idx_product_id` (`product_id`);

-- Coupons table indexes
ALTER TABLE `coupons` ADD INDEX `idx_code` (`code`);
ALTER TABLE `coupons` ADD INDEX `idx_active_dates` (`is_active`, `valid_from`, `valid_until`);
