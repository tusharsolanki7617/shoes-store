<?php
/**
 * Registration & Activation Diagnostic Tool
 * Check if activation system is working properly
 */

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

echo "<!DOCTYPE html><html><head><title>Activation Diagnostic</title></head><body>";
echo "<h1>Registration & Activation System Diagnostic</h1>";

try {
    $db = new Database();
    
    // Check recent users
    echo "<h2>Recent Users (Last 5)</h2>";
    $users = $db->fetchAll("SELECT id, email, first_name, last_name, is_active, activation_token, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    
    if ($users) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Active</th><th>Token (first 20 chars)</th><th>Created</th><th>Activation Link</th></tr>";
        
        foreach ($users as $user) {
            $token_preview = $user['activation_token'] ? substr($user['activation_token'], 0, 20) . '...' : 'NULL';
            $activation_link = $user['activation_token'] ? 
                BASE_URL . 'user/activate.php?token=' . $user['activation_token'] : 
                'N/A';
            
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['first_name']} {$user['last_name']}</td>";
            echo "<td>" . ($user['is_active'] ? 'YES' : 'NO') . "</td>";
            echo "<td>{$token_preview}</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "<td>";
            if ($user['activation_token'] && !$user['is_active']) {
                echo "<a href='{$activation_link}' target='_blank'>Activate Now</a>";
            } else {
                echo $user['is_active'] ? 'Already Active' : 'No Token';
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in database.</p>";
    }
    
    // Check email log
    echo "<h2>Email Log</h2>";
    $logFile = '../logs/email_log.txt';
    if (file_exists($logFile)) {
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
        echo htmlspecialchars(file_get_contents($logFile));
        echo "</pre>";
    } else {
        echo "<p>Email log file does not exist yet at: <code>{$logFile}</code></p>";
        echo "<p>It will be created automatically when the first registration email is sent.</p>";
    }
    
    // Test token generation
    echo "<h2>Test Token Generation</h2>";
    $testToken = generateToken();
    echo "<p>Sample token generated: <code>" . substr($testToken,0, 40) . "...</code> (length: " . strlen($testToken) . ")</p>";
    
    // Environment info
    echo "<h2>Environment Information</h2>";
    echo "<ul>";
    echo "<li><strong>BASE_URL:</strong> " . BASE_URL . "</li>";
    echo "<li><strong>ENVIRONMENT:</strong> " . ENVIRONMENT . "</li>";
    echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h2>Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr><p><a href='register.php'>Go to Registration</a> | <a href='login.php'>Go to Login</a></p>";
echo "</body></html>";
?>
