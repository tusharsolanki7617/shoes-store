<?php
/**
 * Logout Script
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

logout();
setFlash('success', 'You have been logged out successfully');
redirect(BASE_URL);
