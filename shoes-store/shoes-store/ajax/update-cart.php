<?php
/**
 * AJAX: Update Cart Quantity
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

$item_key = $_POST['item_key'] ?? $_POST['product_id'] ?? null;
$quantity = intval($_POST['quantity'] ?? 1);

if (!$item_key) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

try {
    $cart = new Cart();
    $success = $cart->updateQuantity($item_key, $quantity);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated',
            'cart_count' => $cart->getCount()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Could not update quantity'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
