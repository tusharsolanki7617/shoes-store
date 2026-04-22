<?php
/**
 * AJAX: Get Cart Count
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

echo json_encode(['count' => getCartCount()]);
