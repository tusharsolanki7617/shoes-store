<?php
/**
 * Homepage
 * Features hero banner, featured products, and category showcase
 */

require_once 'config/config.php';
require_once 'includes/header.php';

// It's get featured products
try {
    $db = new Database();

    // Primary: featured active products
    $featured_products = $db->fetchAll(
        "SELECT p.*, c.name as category_name FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.is_featured = 1 AND p.is_active = 1 
         ORDER BY p.created_at DESC 
         LIMIT 8"
    );

    // Fallback: if no featured products, show all active products
    if (empty($featured_products)) {
        $featured_products = $db->fetchAll(
            "SELECT p.*, c.name as category_name FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.is_active = 1 
             ORDER BY p.created_at DESC 
             LIMIT 8"
        );
    }

    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
} catch (Exception $e) {
    $featured_products = [];
    $categories = [];
}

$page_title = 'Home - Kicks & Comfort';
?>

<!-- Hero Section -->
<style>
    .hero-section {
        min-height: 100vh;
        width: 100%;
    }
    .hero-bg {
        background: url('<?= ASSETS_URL ?>images/site/hero-home.png') no-repeat center center / cover;
        /* Removed fixed attachment — breaks on iOS Safari */
    }
    .hero-content {
        padding-top: 120px;
        padding-bottom: 60px;
        max-width: 600px;
    }
    .hero-content h1 {
        font-size: clamp(3rem, 10vw, 6rem);
        line-height: 0.9;
    }
    .hero-content h5 {
        font-size: clamp(0.7rem, 3vw, 0.9rem);
        letter-spacing: 3px;
    }
    .hero-content p.lead {
        font-size: clamp(0.9rem, 3vw, 1.1rem);
    }
    .hero-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .hero-buttons .btn {
        flex: 1 1 140px;
        text-align: center;
    }

    /* Mobile adjustments */
    @media (max-width: 575px) {
        .hero-section {
            min-height: 40vh;
        }
        .hero-bg {
            /* Use a separate portrait photo on mobile */
            background-image: url('<?= ASSETS_URL ?>images/site/hero-home-mobile.png');
            background-position: center top;
            background-size: cover;
        }
        .hero-content {
            padding-top: 90px;
            padding-bottom: 40px;
        }
        .hero-content p.lead {
            margin-bottom: 1.5rem !important;
        }
        .hero-buttons .btn {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            font-size: 0.9rem;
        }
    }

    /* Tablet */
    @media (min-width: 576px) and (max-width: 991px) {
        .hero-bg {
            background-position: center top;
        }
        .hero-section {
            min-height: 80vh;
        }
    }
</style>

<section class="hero-section position-relative overflow-hidden">
    <!-- Background -->
    <div class="hero-bg position-absolute top-0 start-0 w-100 h-100" style="z-index: 0;">
        <div style="background: linear-gradient(to right, rgba(0,0,0,0.75), rgba(0,0,0,0.15)); width: 100%; height: 100%;"></div>
    </div>

    <!-- Content -->
    <div class="position-relative w-100 h-100 d-flex align-items-center" style="z-index: 1;">
        <div class="container px-4 px-lg-5">
            <div class="text-white fade-in hero-content">
                <h5 class="text-uppercase fw-bold mb-3 text-warning">New Season</h5>
                <h1 class="fw-black text-uppercase fst-italic mb-4" style="color: #ffffffa1;">Speed<br>Defined</h1>
                <p class="lead mb-5 fw-light text-light opacity-75">
                    Engineered for those who refuse to stop. Experience the next generation of speed and comfort.
                </p>
                <div class="hero-buttons">
                    <a href="<?= BASE_URL ?>products.php" class="btn btn-lg rounded-pill px-5 fw-bold"
                       style="background-color: #f5f5f0c0; color: #111111;">Shop Now</a>
                    <a href="<?= BASE_URL ?>contact.php" class="btn btn-lg rounded-pill px-5 fw-bold"
                       style="border: 2px solid #f5f5f0cd; color: #f5f5f0; background: transparent;">
                        <i class="fas fa-envelope me-2"></i> Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Shop By Sport Section -->
<style>
    .sport-scroll-wrapper {
        display: flex;
        gap: 12px;
        padding: 0 16px;
    }
    .sport-scroll-item {
        flex: 1 1 0;
        min-width: 0;
    }
    .sport-img-box {
        height: 550px;
        overflow: hidden;
        border-radius: 4px;
    }
    .sport-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .sport-img-box img:hover {
        transform: scale(1.04);
    }
    /* Tablet */
    @media (max-width: 991px) and (min-width: 576px) {
        .sport-img-box {
            height: 380px;
        }
    }
    /* Mobile: horizontal scroll */
    @media (max-width: 575px) {
        .sport-scroll-wrapper {
            overflow-x: auto;
            flex-wrap: nowrap;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x mandatory;
            padding: 0 16px 8px;
            gap: 10px;
        }
        .sport-scroll-wrapper::-webkit-scrollbar {
            display: none;
        }
        .sport-scroll-item {
            flex: 0 0 75vw;
            scroll-snap-align: start;
        }
        .sport-img-box {
            height: 300px;
        }
    }
</style>

<section class="my-5 py-3">
    <div class="container">
        <h4 class="fw-black text-uppercase mb-4" style="font-size: 1.15rem; letter-spacing: 0.5px;">Shop by Activity</h4>
    </div>
    <div class="sport-scroll-wrapper">
        <!-- Running -->
        <div class="sport-scroll-item">
            <a href="<?= BASE_URL ?>products.php?category=running" class="text-decoration-none text-dark">
                <div class="sport-img-box">
                    <img src="<?= ASSETS_URL ?>images/site/1.png" alt="Running">
                </div>
                <p class="fw-bold mt-2 mb-0" style="font-size: 0.95rem;">Running</p>
            </a>
        </div>

        <!-- Training -->
        <div class="sport-scroll-item">
            <a href="<?= BASE_URL ?>products.php?category=training" class="text-decoration-none text-dark">
                <div class="sport-img-box">
                    <img src="<?= ASSETS_URL ?>images/site/2.png" alt="Training">
                </div>
                <p class="fw-bold mt-2 mb-0" style="font-size: 0.95rem;">Training</p>
            </a>
        </div>

        <!-- Sportswear -->
        <div class="sport-scroll-item">
            <a href="<?= BASE_URL ?>products.php?category=sportswear" class="text-decoration-none text-dark">
                <div class="sport-img-box">
                    <img src="<?= ASSETS_URL ?>images/site/3.png" alt="Sportswear">
                </div>
                <p class="fw-bold mt-2 mb-0" style="font-size: 0.95rem;">Sportswear</p>
            </a>
        </div>
    </div>
</section>

<br><br>

<!-- Featured Products Section -->
<section class="container mb-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-black text-uppercase mb-0">Trending Now</h3>
        <div class="d-none d-md-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm rounded-circle p-2"><i class="fas fa-chevron-left"></i></button>
            <button class="btn btn-outline-secondary btn-sm rounded-circle p-2"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
    
    <div class="row g-4 product-grid-scroll">
        <?php if (empty($featured_products)): ?>
            <div class="col-12">
                <div class="alert alert-light text-center">
                    No products found.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($featured_products as $product): ?>
                <div class="col-6 col-md-3">
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['discount_price']): 
                                $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                            ?>
                                <span class="product-badge">-<?= $discount_percent ?>%</span>
                            <?php endif; ?>
                            
                                <img src="<?= PRODUCT_IMAGES_URL . ($product['image'] ?? 'placeholder.png') ?>" 
                                     alt="<?= e($product['name']) ?>" 
                                     loading="lazy" 
                                     onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                            </a>
                        </div>
                        
                        <div class="product-body">
                            <h5 class="product-title">
                                <a href="<?= BASE_URL ?>product-detail.php?id=<?= $product['id'] ?>">
                                    <?= e($product['name']) ?>
                                </a>
                            </h5>
                            <p class="product-category"><?= e($product['category_name']) ?></p>
                            
                            <div class="product-price">
                                <?= formatPrice($product['discount_price'] ?? $product['price']) ?>
                                <?php if ($product['discount_price']): ?>
                                    <span class="product-price-old"><?= formatPrice($product['price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Nike Banner Strip -->
<section class="my-0 overflow-hidden position-relative" style="height: 420px;">
    <img src="<?= ASSETS_URL ?>images/site/craig.jpg"
         alt="Just Do It - Nike"
         class="w-100 h-100 object-fit-cover"
         onerror="this.src='https://images.unsplash.com/photo-1607619056574-7b8d3ee536b2?w=1400&q=90'">
    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center"
         style="background: linear-gradient(to right, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.05) 65%);">
        <div class="container text-white">
            <p class="text-warning fw-bold text-uppercase ls-2 mb-2">Nike</p>
            <h2 class="display-3 fw-black text-uppercase mb-3" style="line-height:0.9; letter-spacing:-1px;">Just<br>Do It.</h2>
            <a href="<?= BASE_URL ?>products.php" class="btn btn-light rounded-pill px-5 fw-bold mt-2">Shop All</a>
        </div>
    </div>
</section>

<!-- Marketing / Do More -->
<section class="container my-5">
    <div class="bg-light p-5 text-center rounded-0" style="background-color: #F5F5F5;">
        <h2 class="text-uppercase fw-black mb-3">Join The Club</h2>
        <p class="mb-4 text-secondary" style="max-width: 600px; margin: 0 auto;">
            Sign up for exclusive access to the latest drops, special offers, and member-only events.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>user/register.php" class="btn btn-primary rounded-pill">Sign Up</a>
            <a href="<?= BASE_URL ?>user/login.php" class="btn btn-outline-primary rounded-pill">Log In</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
