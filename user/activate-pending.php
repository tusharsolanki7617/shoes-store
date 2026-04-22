<?php
/**
 * Activation Pending Page
 * Shown after registration — always displays the activation link on-screen
 */

require_once '../config/config.php';
$page_title = 'Activate Your Account - Kicks & Comfort';
require_once '../includes/header.php';

$activation_link = $_SESSION['activation_link'] ?? null;
unset($_SESSION['activation_link']); // consume once shown
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">

            <i class="fas fa-envelope-open-text fa-5x text-primary mb-4"></i>
            <h2 class="fw-black text-uppercase mb-3">One More Step!</h2>
            <p class="lead text-secondary mb-4">
                You're almost in. Activate your account to start shopping.
            </p>

            <!-- Email Verification Instructions -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
                <p class="fw-bold fs-5 mb-4">
                    We've sent an activation link to your email address.
                </p>
                <div class="p-3 bg-light rounded text-start mb-4">
                    <p class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Please click the link in the email to activate your account.</p>
                    <p class="mb-0 small text-secondary"><i class="fas fa-exclamation-triangle text-warning me-2"></i> If you don't see it, be sure to check your <strong>spam or junk</strong> folder.</p>
                </div>
            </div>

            <a href="<?= BASE_URL ?>user/login.php" class="btn btn-outline-secondary rounded-pill px-4 mt-3">
                <i class="fas fa-sign-in-alt me-1"></i> Go to Login
            </a>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
