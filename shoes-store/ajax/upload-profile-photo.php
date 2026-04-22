<?php
/**
 * AJAX: Upload Profile Photo
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
    // Check if file was uploaded
    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] === UPLOAD_ERR_NO_FILE) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        exit;
    }
    
    $file = $_FILES['profile_photo'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
        exit;
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and GIF images are allowed']);
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/profiles';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . '/' . $filename;
    
    // Delete old profile photo if exists
    $db = new Database();
    $user = $db->fetchOne("SELECT profile_photo FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if ($user && $user['profile_photo']) {
        $old_file = $upload_dir . '/' . basename($user['profile_photo']);
        if (file_exists($old_file)) {
            @unlink($old_file);
        }
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $error = error_get_last();
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to save file: ' . ($error['message'] ?? 'Unknown error')
        ]);
        exit;
    }
    
    // Update database - store only filename
    $db->query(
        "UPDATE users SET profile_photo = ? WHERE id = ?",
        [$filename, $_SESSION['user_id']]
    );
    
    // Update session
    $_SESSION['user_profile_photo'] = $filename;
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile photo updated successfully',
        'photo_url' => PROFILE_IMAGES_URL . $filename
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
