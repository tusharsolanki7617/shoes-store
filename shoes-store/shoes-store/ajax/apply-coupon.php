<?php
/**
 * AJAX: Apply Coupon
 */

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/cart.php';
require_once '../includes/security.php';

// Turn off error reporting for production output to avoid breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// CSRF Protection
if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$code = trim($_POST['code'] ?? '');

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
    exit;
}

try {
    $cart = new Cart();
    $result = $cart->applyCoupon($code);
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
