<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

echo "<h1>Debug Image Paths</h1>";
echo "<pre>";
echo "BASE_URL: " . BASE_URL . "\n";
echo "UPLOADS_URL: " . UPLOADS_URL . "\n";
echo "PROFILE_IMAGES_URL: " . PROFILE_IMAGES_URL . "\n";
echo "PROFILE_IMAGE_PATH (Dir): " . PROFILE_IMAGE_PATH . "\n";

$db = new Database();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

echo "User ID: " . $_SESSION['user_id'] . "\n";
echo "Profile Photo in DB: " . ($user['profile_photo'] ?? 'NULL') . "\n";

if ($user['profile_photo']) {
    $file_path = PROFILE_IMAGE_PATH . $user['profile_photo'];
    echo "Full File Path: " . $file_path . "\n";
    echo "File Exists: " . (file_exists($file_path) ? 'YES' : 'NO') . "\n";
    echo "File Size: " . (file_exists($file_path) ? filesize($file_path) : 'N/A') . "\n";
    echo "Image URL: " . PROFILE_IMAGES_URL . $user['profile_photo'] . "\n";
}

echo "</pre>";
echo "<hr>";
if ($user['profile_photo']) {
    echo "<img src='" . PROFILE_IMAGES_URL . $user['profile_photo'] . "' alt='Debug Image' style='border: 2px solid red; max-width: 300px;'>";
} else {
    echo "No photo set in DB.";
}
?>
