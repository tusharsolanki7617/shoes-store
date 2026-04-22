<?php
/**
 * Forgot Password Page
 * Request password reset via email
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
requireGuest();

$page_title = 'Forgot Password - Kicks & Comfort';
require_once '../includes/header.php';
require_once '../includes/security.php';
require_once '../includes/email.php';

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $email = clean($_POST['email'] ?? '');
    
    if (!isValidEmail($email)) {
        setFlash('error', 'Please provide a valid email address');
    } else {
        try {
            $db = new Database();
            $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
            
            if ($user) {
                // Generate 6-digit OTP
                $otp = random_int(100000, 999999);
                $expiry = date('Y-m-d H:i:s', time() + 900); // 15 minutes
                
                // Update user with OTP
                $db->query(
                    "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?",
                    [$otp, $expiry, $user['id']]
                );
                
                // Send OTP email
                $emailService = new EmailService();
                $emailService->sendPasswordResetEmail($email, $otp);
                
                // Store email in session for verification
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_otp_sent'] = true;
                
                redirect(BASE_URL . 'user/verify-otp.php');
            }
            
            // If email doesn't exist, generic message (security)
            // But for OTP flow, we usually just show form again or redirect.
            // Let's stick to generic message but for this specific flow, 
            // the user expects to enter OTP if email was valid.
            // If invalid, we can just show error or fake it.
            // For simplicity and UX in this project context:
            setFlash('error', 'Email address not found.');
            redirect(BASE_URL . 'user/forgot-password.php');
            
        } catch (Exception $e) {
            setFlash('error', 'An error occurred. Please try again.');
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="p-4 p-md-5">
                <div class="text-center mb-5">
                    <h2 class="fw-black text-uppercase ls-1 mb-3">Reset Password</h2>
                    <p class="text-secondary">Enter your email to receive instructions</p>
                </div>
                
                <form method="POST" id="forgotPasswordForm">
                    <?= csrfField() ?>
                    
                    <div class="mb-4">
                        <input type="email" class="form-control form-control-lg rounded-0" id="email" name="email" required placeholder="Email Address">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill text-uppercase fw-bold">
                            Send Link
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-secondary small mb-0">Remember it? <a href="login.php" class="text-black fw-bold text-decoration-underline">Sign In</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
