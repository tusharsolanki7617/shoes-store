<?php
/**
 * User Registration Page  
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
requireGuest();

$page_title = 'Register - Kicks & Comfort';
require_once '../includes/header.php';
require_once '../includes/security.php';
require_once '../includes/email.php';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $first_name = clean($_POST['first_name'] ?? '');
    $last_name = clean($_POST['last_name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (strlen($first_name) < 2) {
        $errors[] = 'First name must be at least 2 characters';
    }
    
    if (strlen($last_name) < 2) {
        $errors[] = 'Last name must be at least 2 characters';
    }
    
    if (!isValidEmail($email)) {
        $errors[] = 'Please provide a valid email address';
    }
    
    $password_check = validatePassword($password);
    if (!$password_check['valid']) {
        $errors[] = $password_check['message'];
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        try {
            $db = new Database();
            
            // Check if email exists
            $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                setFlash('error', 'Email address already registered');
            } else {
                // Generate activation token & hash password
                $activation_token = bin2hex(random_bytes(32));
                $hashed_password  = hashPassword($password);

                // Insert user as inactive until email confirmed
                $db->query(
                    "INSERT INTO users (email, password, first_name, last_name, activation_token, is_active) VALUES (?, ?, ?, ?, ?, 0)",
                    [$email, $hashed_password, $first_name, $last_name, $activation_token]
                );

                // Build activation link
                $activation_link = BASE_URL . 'user/activate.php?token=' . $activation_token;

                // Try to send email (best-effort)
                $emailService = new EmailService();
                $emailService->sendActivationEmail($email, $activation_token);

                redirect(BASE_URL . 'user/activate-pending.php');

            }
        } catch (Exception $e) {
            setFlash('error', 'An error occurred: ' . $e->getMessage());
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}
?>

<style>
/* ── Register form validation styles ───────────────────────────── */
#registerForm .form-control {
    transition: border-color 0.2s, box-shadow 0.2s;
}
#registerForm .form-control.is-valid {
    border-color: #10b981;
    background-image: none;
}
#registerForm .form-control.is-invalid {
    border-color: #ef4444;
    background-image: none;
}

/* Error label */
.reg-error {
    display: block;
    font-size: 0.78rem;
    color: #dc2626;
    margin-top: 0.3rem;
    padding: 0.25rem 0.5rem;
    background: #fff1f2;
    border-left: 3px solid #ef4444;
    border-radius: 0 4px 4px 0;
    animation: regErrIn 0.18s ease;
}
@keyframes regErrIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Password wrapper (for toggle button) */
.pw-wrap { position: relative; }
.pw-toggle {
    position: absolute;
    right: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0;
    color: #9ca3af;
    cursor: pointer;
    font-size: 0.95rem;
    line-height: 1;
    transition: color 0.15s;
    z-index: 5;
}
.pw-toggle:hover { color: #374151; }

/* Password strength bar */
.pw-strength-wrap {
    margin-top: 0.5rem;
    display: none;
}
.pw-strength-bar {
    height: 4px;
    border-radius: 99px;
    background: #e5e7eb;
    overflow: hidden;
    margin-bottom: 0.3rem;
}
.pw-strength-fill {
    height: 100%;
    border-radius: 99px;
    width: 0%;
    transition: width 0.35s ease, background 0.35s ease;
}
.pw-strength-text {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.3px;
}
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="p-4 p-md-5">
                <div class="text-center mb-5">
                    <h2 class="fw-black text-uppercase ls-1 mb-3">Become a Member</h2>
                    <p class="text-secondary">Create your Kicks &amp; Comfort Member Profile</p>
                </div>
                
                <form method="POST" id="registerForm" novalidate>
                    <?= csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text"
                                   class="form-control form-control-lg rounded-0"
                                   id="first_name" name="first_name"
                                   placeholder="First Name"
                                   autocomplete="given-name">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <input type="text"
                                   class="form-control form-control-lg rounded-0"
                                   id="last_name" name="last_name"
                                   placeholder="Last Name"
                                   autocomplete="family-name">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <input type="email"
                               class="form-control form-control-lg rounded-0"
                               id="email" name="email"
                               placeholder="Email Address"
                               autocomplete="email">
                    </div>
                    
                    <div class="mb-3">
                        <!-- Password with toggle -->
                        <div class="pw-wrap">
                            <input type="password"
                                   class="form-control form-control-lg rounded-0 pe-5"
                                   id="password" name="password"
                                   placeholder="Password"
                                   autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="togglePassword" tabindex="-1" aria-label="Show password">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        <!-- Strength bar -->
                        <div class="pw-strength-wrap" id="pwStrengthWrap">
                            <div class="pw-strength-bar">
                                <div class="pw-strength-fill" id="pwStrengthFill"></div>
                            </div>
                            <span class="pw-strength-text" id="pwStrengthText"></span>
                        </div>
                        <small class="form-text text-secondary micro-text">
                            Min 8 chars, uppercase, lowercase &amp; number
                        </small>
                    </div>
                    
                    <div class="mb-4">
                        <!-- Confirm Password with toggle -->
                        <div class="pw-wrap">
                            <input type="password"
                                   class="form-control form-control-lg rounded-0 pe-5"
                                   id="confirm_password" name="confirm_password"
                                   placeholder="Confirm Password"
                                   autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="toggleConfirm" tabindex="-1" aria-label="Show confirm password">
                                <i class="fas fa-eye" id="toggleConfirmIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input md-checkbox" id="terms" name="terms">
                        <label class="form-check-label small text-secondary" for="terms">
                            I agree to the <a href="#" class="text-black text-decoration-underline">Terms &amp; Conditions</a>
                        </label>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill text-uppercase fw-bold" id="registerBtn">
                            Join Us
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-secondary small mb-0">Already a member? <a href="login.php" class="text-black fw-bold text-decoration-underline">Sign In</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// ── Inject validation script via footer's $extra_scripts hook ────────
// This runs AFTER jQuery + jQuery Validate are loaded by footer.php
$extra_scripts = <<<'HTML'
<script>
(function () {
    'use strict';

    /* ── Strength calculator ───────────────────────────────────── */
    function calcStrength(pw) {
        var score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[a-z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score; // 0 – 6
    }

    var strengthConfig = [
        { label: '',         color: '#e5e7eb', pct: 0   },
        { label: 'Very Weak',color: '#ef4444', pct: 17  },
        { label: 'Weak',     color: '#f97316', pct: 33  },
        { label: 'Fair',     color: '#eab308', pct: 50  },
        { label: 'Good',     color: '#84cc16', pct: 67  },
        { label: 'Strong',   color: '#10b981', pct: 83  },
        { label: 'Very Strong', color: '#059669', pct: 100 },
    ];

    /* ── Password toggle helper ────────────────────────────────── */
    function bindToggle(btnId, inputId, iconId) {
        var btn  = document.getElementById(btnId);
        var inp  = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (!btn || !inp) return;
        btn.addEventListener('click', function () {
            var isPassword = inp.type === 'password';
            inp.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    /* ── Live strength bar ─────────────────────────────────────── */
    function bindStrength() {
        var pwInput  = document.getElementById('password');
        var wrap     = document.getElementById('pwStrengthWrap');
        var fill     = document.getElementById('pwStrengthFill');
        var text     = document.getElementById('pwStrengthText');
        if (!pwInput) return;

        pwInput.addEventListener('input', function () {
            var pw  = this.value;
            if (!pw) { wrap.style.display = 'none'; return; }
            wrap.style.display = 'block';
            var score = calcStrength(pw);
            var cfg   = strengthConfig[score];
            fill.style.width      = cfg.pct + '%';
            fill.style.background = cfg.color;
            text.textContent      = cfg.label;
            text.style.color      = cfg.color;
        });
    }

    /* ── jQuery Validate setup ─────────────────────────────────── */
    function initValidation() {
        if (typeof $ === 'undefined' || typeof $.fn.validate === 'undefined') {
            return setTimeout(initValidation, 80);
        }

        /* Custom: password strength (must have upper + lower + digit, min 8) */
        $.validator.addMethod('pwStrength', function (value) {
            return value.length >= 8
                && /[A-Z]/.test(value)
                && /[a-z]/.test(value)
                && /[0-9]/.test(value);
        }, 'Password must be at least 8 characters and include uppercase, lowercase and a number.');

        /* Custom: passwords match */
        $.validator.addMethod('equalToField', function (value, element, param) {
            return value === $(param).val();
        }, 'Passwords do not match.');

        $('#registerForm').validate({
            rules: {
                first_name: {
                    required: true,
                    minlength: 2,
                    maxlength: 50,
                },
                last_name: {
                    required: true,
                    minlength: 2,
                    maxlength: 50,
                },
                email: {
                    required: true,
                    email: true,
                    maxlength: 100,
                },
                password: {
                    required: true,
                    pwStrength: true,
                },
                confirm_password: {
                    required: true,
                    equalToField: '#password',
                },
                terms: {
                    required: true,
                },
            },
            messages: {
                first_name: {
                    required:  'Please enter your first name.',
                    minlength: 'First name must be at least 2 characters.',
                    maxlength: 'First name is too long.',
                },
                last_name: {
                    required:  'Please enter your last name.',
                    minlength: 'Last name must be at least 2 characters.',
                    maxlength: 'Last name is too long.',
                },
                email: {
                    required:  'Please enter your email address.',
                    email:     'Please enter a valid email (e.g. name@example.com).',
                    maxlength: 'Email address is too long.',
                },
                password: {
                    required: 'Please create a password.',
                },
                confirm_password: {
                    required:     'Please confirm your password.',
                    equalToField: 'Passwords do not match.',
                },
                terms: {
                    required: 'You must agree to the Terms & Conditions to continue.',
                },
            },

            /* ── Where error labels appear ── */
            errorElement: 'span',
            errorClass: 'reg-error',
            errorPlacement: function (error, element) {
                // For checkbox, place after label
                if (element.attr('type') === 'checkbox') {
                    error.insertAfter(element.closest('.form-check'));
                } else if (element.closest('.pw-wrap').length) {
                    // Inside pw-wrap: add after the strength bar / helper text
                    error.insertAfter(element.closest('.pw-wrap').parent().children().last());
                } else {
                    error.insertAfter(element);
                }
            },

            /* ── Border colours ── */
            highlight: function (element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },

            /* ── Re-validate confirm password whenever password changes ── */
            onkeyup: function (element, event) {
                // Default onkeyup + keep confirm in sync
                this.defaultShowErrors();
                if (element.id === 'password') {
                    $('#confirm_password').valid();
                }
            },

            /* ── Submit: show loading state ── */
            submitHandler: function (form) {
                var btn = document.getElementById('registerBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Creating account…';
                }
                form.submit();
            },
        });
    }

    /* ── Init everything ───────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        bindToggle('togglePassword', 'password', 'togglePasswordIcon');
        bindToggle('toggleConfirm', 'confirm_password', 'toggleConfirmIcon');
        bindStrength();
        initValidation();
    });

})();
</script>
HTML;

require_once '../includes/footer.php';
?>

