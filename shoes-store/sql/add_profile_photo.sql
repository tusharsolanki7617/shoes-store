-- Add profile_photo column to users table
-- Run this SQL in phpMyAdmin or MySQL command line

ALTER TABLE `users` 
ADD COLUMN `profile_photo` VARCHAR(255) NULL DEFAULT NULL AFTER `phone`;
