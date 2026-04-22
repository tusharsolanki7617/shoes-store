<?php
/**
 * Security Functions
 * CSRF protection and XSS prevention
 */

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool Valid or not
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field
 * @return string HTML input field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

/**
 * Check CSRF token from POST request
 * Dies if invalid
 */
function checkCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }
    }
}


/**
 * Sanitize filename
 * @param string $filename Filename
 * @return string Sanitized filename
 */
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return $filename;
}

/**
 * Rate limiting check
 * @param string $key Unique key for the action
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if allowed, false if rate limited
 */
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
    $sessionKey = 'rate_limit_' . $key;
    
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = [
            'attempts' => 1,
            'time' => time()
        ];
        return true;
    }
    
    $data = $_SESSION[$sessionKey];
    $currentTime = time();
    
    // Reset if time window has passed
    if ($currentTime - $data['time'] > $timeWindow) {
        $_SESSION[$sessionKey] = [
            'attempts' => 1,
            'time' => $currentTime
        ];
        return true;
    }
    
    // Increment attempts
    if ($data['attempts'] < $maxAttempts) {
        $_SESSION[$sessionKey]['attempts']++;
        return true;
    }
    
    return false;
}

/**
 * Get remaining rate limit time
 * @param string $key Unique key for the action
 * @param int $timeWindow Time window in seconds
 * @return int Seconds remaining
 */
function getRateLimitRemaining($key, $timeWindow = 300) {
    $sessionKey = 'rate_limit_' . $key;
    
    if (!isset($_SESSION[$sessionKey])) {
        return 0;
    }
    
    $data = $_SESSION[$sessionKey];
    $elapsed = time() - $data['time'];
    $remaining = $timeWindow - $elapsed;
    
    return max(0, $remaining);
}
