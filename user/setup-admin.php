<?php
/**
 * Quick Admin Setup Script
 * Creates or updates an admin user for testing
 */

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Admin credentials
$admin_email = 'admin@shoesstore.com';
$admin_password = 'admin123';
$admin_first_name = 'Admin';
$admin_last_name = 'User';

try {
    $db = new Database();
    
    // Check if admin exists
    $existing = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$admin_email]);
    
    if ($existing) {
        // Update existing user to admin
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $db->query(
            "UPDATE users SET password = ?, role = 'admin', is_active = 1, activation_token = NULL WHERE email = ?",
            [$hashed_password, $admin_email]
        );
        $message = "✅ Existing user updated to admin!";
    } else {
        // Create new admin user
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $db->query(
            "INSERT INTO users (email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, 'admin', 1)",
            [$admin_email, $hashed_password, $admin_first_name, $admin_last_name]
        );
        $message = "✅ New admin user created!";
    }
    
    // Verify admin was created/updated
    $admin = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$admin_email]);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Setup Complete</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; border-radius: 5px; }
            .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin-top: 20px; }
            code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f8f9fa; }
            .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
            .btn:hover { background: #0056b3; }
        </style>
    </head>
    <body>
        <div class="success">
            <h2><?= $message ?></h2>
        </div>
        
        <div class="info">
            <h3>Admin Login Credentials</h3>
            <table>
                <tr>
                    <th>Email:</th>
                    <td><code><?= $admin_email ?></code></td>
                </tr>
                <tr>
                    <th>Password:</th>
                    <td><code><?= $admin_password ?></code></td>
                </tr>
                <tr>
                    <th>Role:</th>
                    <td><strong><?= $admin['role'] ?></strong></td>
                </tr>
                <tr>
                    <th>Active:</th>
                    <td><?= $admin['is_active'] ? '✅ Yes' : '❌ No' ?></td>
                </tr>
            </table>
        </div>
        
        <div class="info">
            <h3>Next Steps</h3>
            <ol>
                <li>Go to the login page</li>
                <li>Enter the credentials above</li>
                <li>You'll be automatically redirected to the admin dashboard</li>
            </ol>
            
            <a href="login.php" class="btn">Go to Login Page</a>
        </div>
        
        <hr>
        <p style="color: #666; font-size: 14px;">
            <strong>Note:</strong> This script created/updated an admin user in your database. 
            You can delete this file (setup-admin.php) after use for security.
        </p>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    echo "<!DOCTYPE html><html><body>";
    echo "<h1 style='color: red;'>Error</h1>";
    echo "<p>Failed to create admin user: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
    echo "</body></html>";
}
?>
