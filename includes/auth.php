<?php
/**
 * Authentication Functions
 * Session management and access control
 */

// Ensure functions are available
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/functions.php';
}

/**
 * Require user to be logged in
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        setFlash('warning', 'Please login to continue');
        redirect(BASE_URL . 'user/login.php');
    }
}

/**
 * Require admin access
 * Redirect to admin login if not admin
 */
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        setFlash('error', 'Admin access required');
        redirect(BASE_URL . 'user/login.php');
    }
}

/**
 * Require guest (not logged in)
 * Redirect if already logged in
 */
function requireGuest() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            redirect(BASE_URL . 'admin/index.php');
        } else {
            redirect(BASE_URL . 'index.php');
        }
    }
}

/**
 * Login user
 * @param int $userId User ID
 * @param string $role User role
 * @param string $email User email
 * @param string|null $profilePhoto User profile photo filename
 */
function login($userId, $role, $email, $profilePhoto = null) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['email'] = $email;
    $_SESSION['user_profile_photo'] = $profilePhoto;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user
 */
function logout() {
    // Clear all session variables
    $_SESSION = [];
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check session timeout
 * @return bool True if session is valid
 */
function checkSessionTimeout() {
    if (isset($_SESSION['login_time'])) {
        $elapsed = time() - $_SESSION['login_time'];
        
        if ($elapsed > SESSION_LIFETIME) {
            logout();
            setFlash('warning', 'Session expired. Please login again.');
            return false;
        }
        
        // Update login time
        $_SESSION['login_time'] = time();
    }
    
    return true;
}

/**
 * Verify password
 * @param string $password Plain password
 * @param string $hash Password hash
 * @return bool Match status
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Hash password
 * @param string $password Plain password
 * @return string Password hash
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array Array with 'valid' boolean and 'message' string
 */
function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return [
            'valid' => false,
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
        ];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one uppercase letter'
        ];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one lowercase letter'
        ];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one number'
        ];
    }
    
    return ['valid' => true, 'message' => ''];
}
