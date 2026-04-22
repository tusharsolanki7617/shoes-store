<?php
/**
 * Change Password Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

requireLogin();

$page_title = 'Change Password - Kicks & Comfort';
require_once '../includes/header.php';
require_once '../includes/security.php';

// Fetch User Data for Sidebar
$db = new Database();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    try {
        $user_auth = $db->fetchOne("SELECT password FROM users WHERE id = ?", [$_SESSION['user_id']]);
        
        if (!verifyPassword($current_password, $user_auth['password'])) {
            $errors[] = 'Current password is incorrect';
        }
        
        $password_check = validatePassword($new_password);
        if (!$password_check['valid']) {
            $errors[] = $password_check['message'];
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match';
        }
        
        if (empty($errors)) {
            $hashed = hashPassword($new_password);
            $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashed, $_SESSION['user_id']]);
            setFlash('success', 'Password changed successfully!');
            // Redirect to profile or stay here
            redirect(BASE_URL . 'user/profile.php');
        } else {
            setFlash('error', implode('<br>', $errors));
        }
    } catch (Exception $e) {
        setFlash('error', 'An error occurred');
    }
}
?>

<div class="container my-5 pt-4">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="display-5 fw-black text-uppercase ls-1">My Account</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-secondary text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Password</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-5">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="sticky-top" style="top: 100px; z-index: 1;">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body text-center p-4">
                        <div class="profile-photo-wrapper d-inline-block position-relative mb-3">
                            <?php if (!empty($user['profile_photo'])): ?>
                                <img src="<?= BASE_URL ?>uploads/profiles/<?= $user['profile_photo'] ?>" 
                                     alt="Profile Photo" 
                                     class="rounded-circle"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 100px; height: 100px;">
                                    <i class="fas fa-user fa-3x text-secondary"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="fw-bold mb-1"><?= e($user['first_name']) ?> <?= e($user['last_name']) ?></h5>
                        <p class="text-secondary small mb-0"><?= e($user['email']) ?></p>
                    </div>
                </div>

                <div class="list-group list-group-flush rounded-4 overflow-hidden border-0 shadow-sm">
                    <a href="profile.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-secondary">
                        <i class="fas fa-user me-2"></i> Profile Details
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-secondary">
                        <i class="fas fa-box me-2"></i> Order History
                    </a>
                    <a href="wishlist.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-secondary">
                         <i class="fas fa-heart me-2"></i> Wishlist
                    </a>
                    <a href="change-password.php" class="list-group-item list-group-item-action active bg-black border-0 py-3 px-4 fw-bold">
                        <i class="fas fa-lock me-2"></i> Password
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-danger mt-2">
                        <i class="fas fa-sign-out-alt me-2"></i> Log Out
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-lg-5">
                    <div class="mb-5 border-bottom pb-3">
                        <h2 class="h3 fw-bold mb-0">Change Password</h2>
                        <p class="text-secondary small mb-0 mt-1">Ensure your account is secure with a strong password.</p>
                    </div>
                    
                    <form method="POST" id="changePasswordForm" style="max-width: 500px;">
                        <?= csrfField() ?>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-secondary"></i></span>
                                <input type="password" class="form-control form-control-lg bg-light border-0" id="current_password" name="current_password" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-key text-secondary"></i></span>
                                <input type="password" class="form-control form-control-lg bg-light border-0" id="new_password" name="new_password" required>
                            </div>
                            <small class="form-text text-secondary micro-text mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i> Min 8 chars, uppercase, lowercase, number
                            </small>
                        </div>
                        
                        <div class="mb-5">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-check-circle text-secondary"></i></span>
                                <input type="password" class="form-control form-control-lg bg-light border-0" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-dark rounded-pill px-5 py-3 fw-bold shadow-sm">
                                Update Password
                            </button>
                            <a href="profile.php" class="btn btn-link text-secondary text-decoration-none ms-3">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
