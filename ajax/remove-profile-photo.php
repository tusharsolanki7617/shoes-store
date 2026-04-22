<?php
/**
 * AJAX: Remove Profile Photo
 */

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
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

requireLogin();

try {
    $db = new Database();
    $user = $db->fetchOne("SELECT profile_photo FROM users WHERE id = ?", [$_SESSION['user_id']]);
    
    if ($user && $user['profile_photo']) {
        // Delete physical file
        $file_path = __DIR__ . '/../uploads/profiles/' . basename($user['profile_photo']);
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        
        // Update database
        $db->query("UPDATE users SET profile_photo = NULL WHERE id = ?", [$_SESSION['user_id']]);
        
        // Update session
        $_SESSION['user_profile_photo'] = null;
        
        echo json_encode(['success' => true, 'message' => 'Profile photo removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No profile photo to remove']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
