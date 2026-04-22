<?php
/**
 * Email Activation Handler
 */

require_once '../config/config.php';
$page_title = 'Activate Account - Kicks & Comfort';
require_once '../includes/header.php';

$token = $_GET['token'] ?? '';
$success = false;
$message = '';

if ($token) {
    try {
        $db = new Database();
        $user = $db->fetchOne("SELECT * FROM users WHERE activation_token = ? AND is_active = 0", [$token]);
        
        if ($user) {
            $db->query("UPDATE users SET is_active = 1, activation_token = NULL WHERE id = ?", [$user['id']]);
            $success = true;
            $message = 'Your account has been activated successfully! You can now login.';
        } else {
            $message = 'Invalid or expired activation link.';
        }
    } catch (Exception $e) {
        $message = 'An error occurred. Please try again.';
    }
} else {
    $message = 'No activation token provided.';
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card-glass p-5 text-center">
                <?php if ($success): ?>
                    <i class="fas fa-check-circle text-success fa-5x mb-4"></i>
                    <h2>Account Activated!</h2>
                    <p class="lead"><?= $message ?></p>
                    <a href="login.php" class="btn btn-primary btn-lg mt-3">
                        <i class="fas fa-sign-in-alt"></i> Login Now
                    </a>
                <?php else: ?>
                    <i class="fas fa-times-circle text-danger fa-5x mb-4"></i>
                    <h2>Activation Failed</h2>
                    <p class="lead"><?= $message ?></p>
                    <a href="<?= BASE_URL ?>" class="btn btn-primary mt-3">
                        <i class="fas fa-home"></i> Go Home
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
