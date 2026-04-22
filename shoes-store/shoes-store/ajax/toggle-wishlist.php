<?php
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add to wishlist']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    $db = new Database();
    
    // Check if already in wishlist
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $db->query($sql, [$user_id, $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Remove from wishlist
        $deleteSql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $db->query($deleteSql, [$user_id, $product_id]);
        $action = 'removed';
        $message = 'Removed from wishlist';
    } else {
        // Add to wishlist
        $insertSql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $db->query($insertSql, [$user_id, $product_id]);
        $action = 'added';
        $message = 'Added to wishlist';
    }
    
    // Get new count
    $countSql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = $db->query($countSql, [$user_id]);
    $countResult = $stmt->fetch();
    
    echo json_encode([
        'success' => true, 
        'action' => $action, 
        'message' => $message,
        'count' => $countResult['count']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
