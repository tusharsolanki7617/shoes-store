<?php
/**
 * Reset Password Page
 * Reset password with valid token
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
requireGuest();

$page_title = 'Reset Password - Kicks & Comfort';
require_once '../includes/header.php';
require_once '../includes/security.php';

// Check if OTP verified
if (!isset($_SESSION['reset_verified']) || !isset($_SESSION['reset_email'])) {
    redirect(BASE_URL . 'user/forgot-password.php');
}

$email = $_SESSION['reset_email'];

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    $password_check = validatePassword($password);
    if (!$password_check['valid']) {
        $errors[] = $password_check['message'];
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        try {
            $hashed_password = hashPassword($password);
            
            $db = new Database();
            
            // Update password and clear reset token
            $db->query(
                "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?",
                [$hashed_password, $email]
            );
            
            // Clear session
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp_sent']);
            unset($_SESSION['reset_verified']);
            
            setFlash('success', 'Password reset successful! You can now login with your new password.');
            redirect(BASE_URL . 'user/login.php');
            
        } catch (Exception $e) {
            setFlash('error', 'An error occurred. Please try again.');
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card-glass p-5">
                <div class="text-center mb-4">
                    <h2 class="text-gradient">Reset Password</h2>
                    <p class="text-muted">Enter your new password</p>
                </div>
                
                <form method="POST" id="resetPasswordForm">
                    <?= csrfField() ?>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">
                            Must be at least 8 characters with uppercase, lowercase, and numbers
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
