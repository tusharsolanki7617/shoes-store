<?php
/**
 * About Us Page
 */

require_once 'config/config.php';
$page_title = 'About Us - Kicks & Comfort';
require_once 'includes/header.php';
?>

<style>
    .page-hero {
        min-height: 325px;
        display: flex;
        align-items: center;
        background: url('<?= ASSETS_URL ?>images/site/hero-about3.png') no-repeat center center / cover;
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
        <h1 class="display-4 fw-black text-uppercase mb-3 ls-1 text-white">About Us</h1>
        <p class="lead opacity-75 mb-0">Your trusted partner for premium footwear since 2020</p>
    </div>
</div>

<div class="container my-5">
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4">
            <h2 class="mb-4">Our Story</h2>
            <p>Kicks & Comfort was born from a simple belief: everyone deserves footwear that combines style, comfort, and quality. Founded in 2020, we've grown from a small local store to a trusted online destination for shoe enthusiasts across India.</p>
            <p>We curate only the finest collection of shoes for men, women, and children, ensuring that every step you take is comfortable and confident. Our commitment to quality and customer satisfaction has made us a preferred choice for thousands of happy customers.</p>
            <p>Whether you're looking for formal shoes for the office, casual sneakers for the weekend, or sports shoes for your active lifestyle, we have something special for everyone.</p>
        </div>
        <div class="col-lg-6 mb-4 text-center">
            <i class="fas fa-store fa-10x text-primary" style="opacity: 0.2;"></i>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-4 mb-4 text-center">
            <div class="card-glass p-4 h-100">
                <i class="fas fa-bullseye text-primary fa-3x mb-3"></i>
                <h4>Our Mission</h4>
                <p>To provide high-quality, comfortable footwear that enhances every step of your journey while maintaining affordable prices and exceptional customer service.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4 text-center">
            <div class="card-glass p-4 h-100">
                <i class="fas fa-eye text-primary fa-3x mb-3"></i>
                <h4>Our Vision</h4>
                <p>To become India's most trusted and loved footwear brand, known for quality, innovation, and customer-first approach in everything we do.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4 text-center">
            <div class="card-glass p-4 h-100">
                <i class="fas fa-heart text-primary fa-3x mb-3"></i>
                <h4>Our Values</h4>
                <p>Integrity, quality, customer satisfaction, and sustainability guide everything we do. We believe in building lasting relationships through trust and excellence.</p>
            </div>
        </div>
    </div>
    
    <div class="row bg-light rounded p-5 mb-5">
        <div class="col-12 text-center mb-4">
            <h3>Why Choose Us?</h3>
        </div>
        <div class="col-md-3 col-6 mb-3 text-center">
            <h2 class="text-gradient fw-bold">5000+</h2>
            <p class="text-muted">Happy Customers</p>
        </div>
        <div class="col-md-3 col-6 mb-3 text-center">
            <h2 class="text-gradient fw-bold">1000+</h2>
            <p class="text-muted">Products Available</p>
        </div>
        <div class="col-md-3 col-6 mb-3 text-center">
            <h2 class="text-gradient fw-bold">50+</h2>
            <p class="text-muted">Brands</p>
        </div>
        <div class="col-md-3 col-6 mb-3 text-center">
            <h2 class="text-gradient fw-bold">99%</h2>
            <p class="text-muted">Satisfaction Rate</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
