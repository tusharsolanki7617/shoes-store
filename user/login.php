<?php
/**
 * User Login Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
requireGuest();

$page_title = 'Login - Kicks & Comfort';
require_once '../includes/header.php';
require_once '../includes/security.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    // Rate limiting
    if (!checkRateLimit('login', 5, 300)) {
        $remaining = getRateLimitRemaining('login', 300);
        setFlash('error', 'Too many login attempts. Please try again in ' . ceil($remaining / 60) . ' minutes.');
    } else {
        $email = clean($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        try {
            $db = new Database();
            $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
            
            if ($user && verifyPassword($password, $user['password'])) {
                if (!$user['is_active']) {
                    setFlash('error', 'Please activate your account first. Check your email for activation link.');
                } else {
                    // Login successful
                    login($user['id'], $user['role'], $user['email'], $user['profile_photo']);
                    setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
                    
                    // Redirect
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect_url = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        redirect($redirect_url);
                    } elseif ($user['role'] === 'admin') {
                        redirect(BASE_URL . 'admin/');
                    } else {
                        redirect(BASE_URL);
                    }
                }
            } else {
                setFlash('error', 'Invalid email or password');
            }
        } catch (Exception $e) {
            setFlash('error', 'An error occurred. Please try again.');
        }
    }
}
?>

<div class="container my-5" style="min-height: 60vh; display: flex; align-items: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-5 col-lg-4">
            <div class="p-4 p-md-5">
                <div class="text-center mb-5">
                    <h2 class="fw-black text-uppercase ls-1 mb-3">Your Account</h2>
                    <p class="text-secondary">Sign in to access your orders and profile</p>
                </div>
                
                <form method="POST" id="loginForm">
                    <?= csrfField() ?>
                    
                    <div class="mb-3">
                        <input type="email" class="form-control form-control-lg rounded-0" id="email" name="email" required placeholder="Email Address">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-4">
                        <input type="password" class="form-control form-control-lg rounded-0" id="password" name="password" required placeholder="Password">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input md-checkbox" id="remember" name="remember">
                            <label class="form-check-label small text-secondary" for="remember">Keep me signed in</label>
                        </div>
                        <a href="forgot-password.php" class="small text-secondary text-decoration-underline">Forgot?</a>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill text-uppercase fw-bold">
                            Sign In
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-secondary small mb-0">Not a member? <a href="register.php" class="text-black fw-bold text-decoration-underline">Join Us</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
