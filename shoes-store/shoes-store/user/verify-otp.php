<?php
/**
 * Verify OTP Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

// Redirect if not coming from forgot password
if (!isset($_SESSION['reset_email'])) {
    redirect(BASE_URL . 'user/forgot-password.php');
}

$page_title = 'Verify OTP - Kicks & Comfort';
require_once '../includes/header.php';
require_once '../includes/security.php';

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $otp = clean($_POST['otp'] ?? '');
    $email = $_SESSION['reset_email'];
    
    if (empty($otp)) {
        setFlash('error', 'Please enter the OTP');
    } else {
        try {
            $db = new Database();
            $user = $db->fetchOne(
                "SELECT * FROM users WHERE email = ? AND reset_token = ? AND reset_token_expiry > NOW()",
                [$email, $otp]
            );
            
            if ($user) {
                // OTP verified
                $_SESSION['reset_verified'] = true;
                setFlash('success', 'OTP verified successfully. Please set your new password.');
                redirect(BASE_URL . 'user/reset-password.php');
            } else {
                setFlash('error', 'Invalid or expired OTP');
            }
        } catch (Exception $e) {
            setFlash('error', 'An error occurred. Please try again.');
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card-glass p-5">
                <div class="text-center mb-4">
                    <h2 class="text-gradient">Verify OTP</h2>
                    <p class="text-muted">Enter the 6-digit code sent to<br><strong><?= e($_SESSION['reset_email']) ?></strong></p>
                </div>
                
                <form method="POST" id="verifyOtpForm">
                    <?= csrfField() ?>
                    
                    <div class="mb-4">
                        <label for="otp" class="form-label text-center w-100">Enter OTP Code</label>
                        <input type="text" class="form-control text-center text-spacing-5 fs-4" id="otp" name="otp" 
                               maxlength="6" pattern="[0-9]{6}" required autofocus autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle"></i> Verify Code
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Didn't receive code? <a href="forgot-password.php" class="fw-bold">Resend OTP</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
