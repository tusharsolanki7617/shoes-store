-- ============================================================
-- Migration: Add razorpay_payment_id column to orders table
-- Run this ONCE in phpMyAdmin on the shoes_store database
-- ============================================================

ALTER TABLE orders
    ADD COLUMN razorpay_payment_id VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'Razorpay payment ID (e.g. pay_XXXXXXXXXXXXXXXX)'
    AFTER payment_method;
