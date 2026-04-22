<?php
/**
 * Helper Functions
 * Reusable utilities across the application
 */

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Set flash message in session
 * @param string $type Type of message (success, error, warning, info)
 * @param string $message Message content
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Check if product is in user's wishlist
 */
function isInWishlist($product_id) {
    if (!isLoggedIn()) return false;
    
    global $db;
    if (!isset($db)) $db = new Database();
    
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $db->query($sql, [$_SESSION['user_id'], $product_id]);
    $result = $stmt->fetch();
    
    return $result ? true : false;
}

/**
 * Get user's wishlist count
 */
function getWishlistCount() {
    if (!isLoggedIn()) return 0;
    
    global $db;
    if (!isset($db)) $db = new Database();
    
    $sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = $db->query($sql, [$_SESSION['user_id']]);
    $result = $stmt->fetch();
    
    return $result['count'] ?? 0;
}

/**
 * Get and clear flash message
 * @param string|null $key Specific key to get (type or message)
 * @return mixed Flash message array, specific value, or null
 */
function getFlash($key = null) {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        if ($key === null) {
            unset($_SESSION['flash']);
            return $flash;
        }
        return $flash[$key] ?? null;
    }
    return null;
}

/**
 * Check if flash message exists
 * @return bool True if flash message exists
 */
function hasFlash() {
    return isset($_SESSION['flash']);
}

/**
 * Display flash message HTML
 * @return string HTML for flash message
 */
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        $class = $alertClass[$flash['type']] ?? 'alert-info';
        return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">' . 
               htmlspecialchars($flash['message']) . 
               '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    return '';
}

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape HTML entities (shorthand)
 * @param string $string String to escape
 * @return string Escaped string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}


/**
 * Format price with currency symbol
 * @param float $price Price amount
 * @return string Formatted price
 */
function formatPrice($price) {
    // Cast to float first — prevents any accidental string concatenation
    // Explicitly set decimal point (.) and thousands separator (,)
    // so this works correctly on ALL server locales
    return CURRENCY_SYMBOL . number_format((float)$price, 2, '.', ',');
}

/**
 * Generate a URL slug from text
 * @param string $text Text to slugify
 * @return string URL slug
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

/**
 * Check if user is logged in
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 * @return bool True if admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user ID
 * @return int|null User ID or null
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * @return array|null User data or null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = new Database();
        return $db->fetchOne("SELECT * FROM users WHERE id = ?", [getUserId()]);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Time ago format
 * @param string $datetime Datetime string
 * @return string Time ago text
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return formatDate($datetime);
}

/**
 * Generate random token
 * @param int $length Token length
 * @return string Random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate OTP
 * @param int $length OTP length
 * @return string OTP
 */
function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Upload image file
 * @param array $file $_FILES array
 * @param string $destination Destination folder (e.g., 'products', 'profiles')
 * @param string $prefix File prefix
 * @return array Array with 'success', 'filename' (on success), and 'message' (on error)
 */
function uploadImage($file, $destination, $prefix = 'img_') {
    // Check if file was uploaded
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        ];
        $message = $errorMessages[$file['error'] ?? 0] ?? 'Unknown upload error';
        return ['success' => false, 'message' => $message];
    }
    
    // Validate file type
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and WebP images are allowed'];
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $maxMB = MAX_FILE_SIZE / 1024 / 1024;
        return ['success' => false, 'message' => "File size exceeds {$maxMB}MB limit"];
    }
    
    // Prepare destination path - map destination to constant name
    $constantMap = [
        'products' => 'PRODUCT_IMAGE_PATH',
        'product' => 'PRODUCT_IMAGE_PATH',
        'profiles' => 'PROFILE_IMAGE_PATH',
        'profile' => 'PROFILE_IMAGE_PATH',
    ];
    
    $constantName = $constantMap[$destination] ?? strtoupper($destination) . '_IMAGE_PATH';
    
    if (defined($constantName)) {
        $uploadPath = constant($constantName);
    } else {
        // Fallback to UPLOAD_PATH if specific constant not found
        $uploadPath = UPLOAD_PATH . $destination . '/';
    }
    
    // Ensure directory exists
    if (!file_exists($uploadPath)) {
        if (!mkdir($uploadPath, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory'];
        }
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . uniqid() . '.' . $ext;
    $filepath = $uploadPath . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 * @param string $filepath File path
 * @return bool Success status
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Validate email
 * @param string $email Email address
 * @return bool Valid or not
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get cart item count
 * @return int Item count
 */
function getCartCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

/**
 * Get cart total
 * @return float Cart total
 */
function getCartTotal() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
