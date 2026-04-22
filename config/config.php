<?php
// Start output buffering FIRST - must be before ANY output
ob_start();

// Force UTF-8 encoding throughout — critical for correct ₹ symbol on all servers
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

/**
 * Global Configuration File
 * Zero-code-change deployment: Auto-detects localhost vs production
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Stub $_SERVER for CLI
if (php_sapi_name() === 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

// Environment detection
$is_localhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']);

if ($is_localhost) {
    // ============================================
    // LOCALHOST CONFIGURATION (XAMPP)
    // ============================================
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'shoes_store');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/shoes-store/');
    define('ENVIRONMENT', 'development');
    
    // Display errors in development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // ============================================
    // PRODUCTION CONFIGURATION (PANEL)
    // Edit these values before deployment
    // ============================================
    define('DB_HOST', 'sql307.infinityfree.com');
    define('DB_NAME', 'if0_41549730_shoes_store');
    define('DB_USER', 'if0_41549730');
    define('DB_PASS', 'O2ANcQoXpAA');
    define('BASE_URL', 'https://kicks-comfort.wuaze.com/shoes-store/');
    define('ENVIRONMENT', 'production');
    
    // Hide errors in production
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// GLOBAL CONSTANTS
// ============================================
define('SITE_NAME', 'Kicks & Comfort');
define('SITE_EMAIL', 'info@kickscomfort.com');
define('ADMIN_EMAIL', 'admin@kickscomfort.com');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// File upload settings
define('MAX_FILE_SIZE', 2097152); // 2MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);

// Email settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'solankitushar010@gmail.com');
define('SMTP_PASS', 'occz rvtc dmpw qynt');
define('SMTP_ENCRYPTION', 'tls');

// Security
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);

// Currency
// Raw UTF-8 hex bytes for ₹ (U+20B9) — works on ALL PHP versions and server encodings.
// \u{20B9} requires PHP 7.0+; \xE2\x82\xB9 works from PHP 5.3+ with no encoding dependency.
define('CURRENCY_SYMBOL', "\xE2\x82\xB9");
define('CURRENCY_CODE', 'INR');

// Paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('PRODUCT_IMAGE_PATH', UPLOAD_PATH . 'products/');
define('PROFILE_IMAGE_PATH', UPLOAD_PATH . 'profiles/');

// URLs
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('PRODUCT_IMAGES_URL', UPLOADS_URL . 'products/');
define('PROFILE_IMAGES_URL', UPLOADS_URL . 'profiles/');
