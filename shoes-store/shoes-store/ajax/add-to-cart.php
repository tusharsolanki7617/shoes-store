<?php
/**
 * AJAX: Add to Cart
 */

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/cart.php';
require_once '../includes/security.php';

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

$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
$size = $_POST['size'] ?? null;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

try {
    $cart = new Cart();
    $success = $cart->addItem($product_id, $quantity, $size);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Product added to cart!',
            'cart_count' => $cart->getCount()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Could not add product. Please check stock availability.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
