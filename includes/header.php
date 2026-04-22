<?php
/**
 * Header Template
 * Shared across all pages
 */

// Force UTF-8 content type — must come before any HTML output
// This ensures the hosting server sends the correct charset header
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

// Ensure config is loaded
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// Load required files
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Check session timeout for logged in users
if (isLoggedIn()) {
    checkSessionTimeout();
}

// Get page title
$page_title = $page_title ?? 'Kicks & Comfort - Premium Shoes Store';

// Generate CSRF token for this session
if (!function_exists('generateCsrfToken')) {
    require_once __DIR__ . '/security.php';
}
$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kicks & Comfort - Your premium destination for quality shoes. Shop the latest collection of men's, women's, kids and sports shoes.">
    <meta name="csrf-token" content="<?= $csrf_token ?>">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>images/site/favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/animations.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    
    <!-- Mobile Responsive Navbar Styles -->
    <style>
        /* Always show icons next to hamburger on mobile */
        .navbar-mobile-icons {
            display: none;
        }
        @media (max-width: 991px) {
            .navbar-mobile-icons {
                display: flex;
                align-items: center;
                gap: 4px;
                margin-left: auto;
                margin-right: 8px;
            }
            /* Hide icons from collapse menu on mobile (shown in top bar instead) */
            .navbar-icons-collapse {
                display: none !important;
            }
            /* Full-width search in mobile collapse */
            .navbar-search-form {
                width: 100%;
            }
            .navbar-search-form .input-group {
                width: 100%;
            }
            /* Style mobile nav links */
            #navbarNav .navbar-nav .nav-link {
                padding: 10px 0;
                border-bottom: 1px solid rgba(0,0,0,0.06);
                font-size: 1rem;
            }
            #navbarNav .navbar-nav:last-child .nav-link {
                border-bottom: none;
            }
        }
        @media (min-width: 992px) {
            .navbar-mobile-icons {
                display: none !important;
            }
            .navbar-icons-collapse {
                display: flex !important;
            }
        }

        /* Professional Notification Badges */
        .cart-badge {
            position: absolute;
            top: 10px;
            right: -6px;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            color: #fff;
            box-shadow: 0 0 0 2px #fff;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            pointer-events: none;
        }
        .wishlist-count {
            background-color: #ff3b30 !important; /* Premium Red */
        }
        .cart-count {
            background-color: #111 !important; /* Premium Black */
            right: -8px; /* Slight adjustment for wider cart icon */
        }
    </style>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">

            <!-- Brand -->
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <i class="fas fa-shoe-prints"></i> KICKS &amp; COMFORT
            </a>

            <!-- Mobile: icons always visible next to hamburger -->
            <div class="navbar-mobile-icons">
                <!-- User icon -->
                <?php if (isLoggedIn()): ?>
                    <a class="nav-link px-2 position-relative" href="<?= BASE_URL ?>user/profile.php">
                        <?php if (isset($_SESSION['user_profile_photo']) && $_SESSION['user_profile_photo']): ?>
                            <img src="<?= PROFILE_IMAGES_URL . $_SESSION['user_profile_photo'] ?>" alt="Profile" class="rounded-circle" style="width:28px !important; height:28px !important; object-fit:cover; flex-shrink: 0;">
                        <?php else: ?>
                            <i class="far fa-user"></i>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a class="nav-link px-2" href="<?= BASE_URL ?>user/login.php"><i class="far fa-user"></i></a>
                <?php endif; ?>

                <!-- Wishlist icon -->
                <a class="nav-link px-2 position-relative wishlist-icon" href="<?= BASE_URL ?>user/wishlist.php">
                    <i class="far fa-heart"></i>
                    <?php if (isLoggedIn() && getWishlistCount() > 0): ?>
                        <span class="cart-badge wishlist-count rounded-pill"><?= getWishlistCount() ?></span>
                    <?php endif; ?>
                </a>

                <!-- Cart icon -->
                <a class="nav-link px-2 position-relative cart-icon" href="<?= BASE_URL ?>cart.php">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if (getCartCount() > 0): ?>
                        <span class="cart-badge cart-count rounded-pill"><?= getCartCount() ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Hamburger toggler -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Collapsible content -->
            <div class="collapse navbar-collapse" id="navbarNav">

                <!-- Nav Links -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($_SERVER['PHP_SELF'] == '/AI/shoes-store/index.php' || $_SERVER['PHP_SELF'] == '/AI/shoes-store/') ? 'active' : '' ?>" href="<?= BASE_URL ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'products.php') !== false) ? 'active' : '' ?>" href="<?= BASE_URL ?>products.php">New Arrivals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'about.php') !== false) ? 'active' : '' ?>" href="<?= BASE_URL ?>about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'services.php') !== false) ? 'active' : '' ?>" href="<?= BASE_URL ?>services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'contact.php') !== false) ? 'active' : '' ?>" href="<?= BASE_URL ?>contact.php">Contact Us</a>
                    </li>
                </ul>

                <!-- Right side: search + icons (desktop only for icons) -->
                <ul class="navbar-nav ms-auto align-items-center">

                    <!-- Search Bar -->
                    <li class="nav-item me-2 w-100 w-lg-auto">
                        <form action="<?= BASE_URL ?>products.php" method="GET" class="d-flex navbar-search-form my-2 my-lg-0">
                            <div class="input-group">
                                <button class="btn" type="submit"><i class="fas fa-search"></i></button>
                                <input class="form-control" type="search" name="q" placeholder="Search shoes…" aria-label="Search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            </div>
                        </form>
                    </li>

                    <!-- Desktop-only: User Menu, Wishlist, Cart -->
                    <div class="navbar-icons-collapse d-lg-flex align-items-center">

                        <!-- User Menu -->
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <?php if (isset($_SESSION['user_profile_photo']) && $_SESSION['user_profile_photo']): ?>
                                        <img src="<?= PROFILE_IMAGES_URL . $_SESSION['user_profile_photo'] ?>" alt="Profile" class="rounded-circle" style="width:28px !important; height:28px !important; object-fit:cover; flex-shrink: 0;">
                                    <?php else: ?>
                                        <i class="far fa-user"></i>
                                    <?php endif; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                    <?php if (isAdmin()): ?>
                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/">Dashboard</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>user/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>user/orders.php">Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>user/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>user/login.php" style="white-space: nowrap;">Sign In</a>
                            </li>
                        <?php endif; ?>

                        <!-- Wishlist -->
                        <li class="nav-item">
                            <a class="nav-link position-relative wishlist-icon" href="<?= BASE_URL ?>user/wishlist.php">
                                <i class="far fa-heart"></i>
                                <?php if (isLoggedIn() && getWishlistCount() > 0): ?>
                                    <span class="cart-badge wishlist-count rounded-pill"><?= getWishlistCount() ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <!-- Cart -->
                        <li class="nav-item">
                            <a class="nav-link position-relative cart-icon" href="<?= BASE_URL ?>cart.php">
                                <i class="fas fa-shopping-bag"></i>
                                <?php if (getCartCount() > 0): ?>
                                    <span class="cart-badge cart-count rounded-pill"><?= getCartCount() ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                    </div><!-- /navbar-icons-collapse -->
                </ul>
            </div><!-- /collapse -->
        </div>
    </nav>
    
    <!-- Main Content Wrapper -->
    <main>
        <?= displayFlash() ?>
