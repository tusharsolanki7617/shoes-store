<?php
/**
 * Services Page
 * Shipping, returns, and customer support information
 */

require_once 'config/config.php';
$page_title = 'Our Services - Kicks & Comfort';
require_once 'includes/header.php';
?>

<style>
    .page-hero {
        min-height: 325px;
        display: flex;
        align-items: center;
        background: url('<?= ASSETS_URL ?>images/site/hero-services.png') no-repeat center center / cover;
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
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index: 0;"></div>
    <div class="container text-center text-white position-relative" style="z-index: 1; padding: 40px 15px;">
        <h1 class="display-4 fw-black text-uppercase mb-3 ls-1 text-white">Our Services</h1>
        <p class="lead opacity-75 mb-0">Everything you need to know about shopping with us</p>
    </div>
</div>

<div class="container my-5">
    <!-- Shipping Information -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-4">
            <div class="card-glass p-4 h-100">
                <h3 class="mb-4"><i class="fas fa-shipping-fast text-primary"></i> Shipping Information</h3>
                <h5>Free Shipping</h5>
                <p>Enjoy free shipping on all orders above ₹2000 across India!</p>
                
                <h5 class="mt-4">Delivery Time</h5>
                <ul>
                    <li>Metro Cities: 3-5 business days</li>
                    <li>Other Cities: 5-7 business days</li>
                    <li>Remote Areas: 7-10 business days</li>
                </ul>
                
                <h5 class="mt-4">Shipping Partners</h5>
                <p>We partner with trusted courier services including Blue Dart, DTDC, and Delhivery to ensure safe and timely delivery.</p>
                
                <h5 class="mt-4">Order Tracking</h5>
                <p>Track your order anytime from your account dashboard. You'll receive tracking information via email and SMS.</p>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card-glass p-4 h-100">
                <h3 class="mb-4"><i class="fas fa-undo-alt text-primary"></i> Returns & Exchange</h3>
                <h5>30-Day Return Policy</h5>
                <p>Not satisfied with your purchase? Return it within 30 days for a full refund, no questions asked!</p>
                
                <h5 class="mt-4">Easy Returns Process</h5>
                <ol>
                    <li>Login to your account and go to Order History</li>
                    <li>Select the order you want to return</li>
                    <li>Choose return reason and submit request</li>
                    <li>Our team will arrange pickup within 2-3 days</li>
                    <li>Refund will be processed within 7-10 days</li>
                </ol>
                
                <h5 class="mt-4">Exchange Policy</h5>
                <p>Want to exchange for a different size or color? We offer free exchange within 15 days of delivery!</p>
                
                <div class="alert alert-info mt-3">
                    <strong>Note:</strong> Products must be unused, unworn, and in original packaging with tags intact.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card-glass p-4">
                <h3 class="mb-4"><i class="fas fa-credit-card text-primary"></i> Payment Methods</h3>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h5><i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery</h5>
                        <p>Pay when you receive your order. Available on all orders.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5><i class="fas fa-university text-primary"></i> Net Banking</h5>
                        <p>Pay securely using your bank account. All major banks supported.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5><i class="fas fa-wallet text-warning"></i> UPI & Wallets</h5>
                        <p>GooglePay, PhonePe, Paytm, and other UPI apps accepted.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5><i class="fas fa-credit-card text-danger"></i> Debit/Credit Cards</h5>
                        <p>Visa, Mastercard, Rupay, and Amex cards accepted.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5><i class="fas fa-shield-alt text-info"></i> 100% Secure</h5>
                        <p>All transactions are encrypted and completely secure.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5><i class="fas fa-receipt text-secondary"></i> E-Invoicing</h5>
                        <p>Get instant invoice via email for all your orders.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer Support -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card-glass p-4">
                <h3 class="mb-4"><i class="fas fa-headset text-primary"></i> Customer Support</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h5>24/7 Support</h5>
                        <p>Our dedicated customer support team is available round the clock to assist you with any queries or concerns.</p>
                        
                        <h6 class="mt-3">Contact Us:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-phone text-primary"></i> Phone: +91 93161 09130</li>
                            <li><i class="fas fa-envelope text-primary"></i> Email: <?= SITE_EMAIL ?></li>
                            <li><i class="fab fa-whatsapp text-success"></i> WhatsApp: +91 93161 09130</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <h5>FAQs</h5>
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        How do I track my order?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Login to your account and visit the 'My Orders' section to track your shipment in real-time.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        What if the product doesn't fit?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You can exchange or return the product within 30 days of delivery for free!
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Do you ship internationally?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Currently, we only ship within India. International shipping coming soon!
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
