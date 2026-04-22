<?php
/**
 * Contact Page
 * Contact form with jQuery validation and email notification
 */

require_once 'config/config.php';
$page_title = 'Contact Us - Kicks & Comfort';
require_once 'includes/header.php';
require_once 'includes/security.php';
require_once 'includes/email.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $subject = clean($_POST['subject'] ?? '');
    $message = clean($_POST['message'] ?? '');
    
    $errors = [];
    
    // Validation
    if (strlen($name) < 3) {
        $errors[] = 'Name must be at least 3 characters';
    }
    
    if (!isValidEmail($email)) {
        $errors[] = 'Please provide a valid email address';
    }
    
    if (strlen($subject) < 5) {
        $errors[] = 'Subject must be at least 5 characters';
    }
    
    if (strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters';
    }
    
    if (empty($errors)) {
        try {
            $db = new Database();
            
            // Save to database
            $db->query(
                "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)",
                [$name, $email, $subject, $message]
            );
            
            // Send email notification
            $emailService = new EmailService();
            $emailService->sendContactNotification([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);
            
            setFlash('success', 'Thank you for contacting us! We will get back to you soon.');
            redirect(BASE_URL . 'contact.php');
        } catch (Exception $e) {
            setFlash('error', 'An error occurred. Please try again.');
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}
?>

<style>
    .page-hero {
        min-height: 325px;
        display: flex;
        align-items: center;
        background: url('<?= ASSETS_URL ?>images/site/hero-contact.png') no-repeat center center / cover;
    }
    @media (max-width: 575px) {
        .page-hero {
            min-height: 260px;
            background-position: top center;
        }
        .page-hero h1 {
            font-size: 2.5rem;
        }
    }
</style>

<div class="page-hero position-relative">
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-black opacity-50" style="z-index: 0;"></div>
    <div class="container text-center text-white position-relative" style="z-index: 1; padding: 40px 15px;">
        <h1 class="display-4 fw-black text-uppercase mb-3 ls-1 text-white">Contact Us</h1>
        <p class="lead opacity-75 mb-0">We'd love to hear from you!</p>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Contact Form -->
        <div class="col-lg-7 mb-4">
            <div class="card-glass p-4">
                <h3 class="mb-4">Send us a Message</h3>
                
                <form method="POST" id="contactForm">
                    <?= csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Your Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Your Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="col-lg-5 mb-4">
            <div class="card-glass p-4 mb-4">
                <h4 class="mb-4">Get in Touch</h4>
                
                <div class="mb-4">
                    <h6><i class="fas fa-map-marker-alt text-primary"></i> Address</h6>
                    <p class="mb-0">13 Fashion Street<br>Rajkot, Gujarat 360001<br>India</p>
                </div>
                
                <div class="mb-4">
                    <h6><i class="fas fa-phone text-primary"></i> Phone</h6>
                    <p class="mb-0"><a href="tel:+919876543210">+91 93161 09130</a></p>
                </div>
                
                <div class="mb-4">
                    <h6><i class="fas fa-envelope text-primary"></i> Email</h6>
                    <p class="mb-0"><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
                </div>
                
                <div class="mb-4">
                    <h6><i class="fas fa-clock text-primary"></i> Business Hours</h6>
                    <p class="mb-0">
                        Monday - Saturday: 9:00 AM - 8:00 PM<br>
                        Sunday: 10:00 AM - 6:00 PM
                    </p>
                </div>
            </div>
            
            <div class="card-glass p-4">
                <h5 class="mb-3">Follow Us</h5>
                <div class="d-flex gap-3">
                    <a href="#" class="btn btn-outline-primary"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-primary"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-primary"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-primary"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
