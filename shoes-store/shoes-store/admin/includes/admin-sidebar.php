<?php
// Ensure required functions are available
if (!function_exists('e')) {
    require_once __DIR__ . '/../../includes/functions.php';
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Helper: avatar color class based on first letter
function avatarColorClass(string $letter): string {
    return 'avatar-' . strtolower($letter);
}
?>

<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>admin/" class="sidebar-brand-logo">
            <div class="sidebar-brand-icon">
                <i class="fas fa-shoe-prints"></i>
            </div>
            <div>
                <div class="sidebar-brand-text">Kicks &amp;</div>
                <div class="sidebar-brand-sub">Admin Panel</div>
            </div>
        </a>
        <button type="button" class="btn p-0 d-lg-none border-0 text-white opacity-50" onclick="toggleAdminSidebar()" style="background:none;" title="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- Store Section -->
        <div class="sidebar-section-label">Store</div>

        <a href="<?= BASE_URL ?>admin/index.php"
           class="sidebar-nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-pie nav-icon"></i>
            Dashboard
        </a>

        <a href="<?= BASE_URL ?>admin/products.php"
           class="sidebar-nav-link <?= in_array($current_page, ['products.php','add-product.php','edit-product.php']) ? 'active' : '' ?>">
            <i class="fas fa-box-open nav-icon"></i>
            Products
        </a>

        <a href="<?= BASE_URL ?>admin/orders.php"
           class="sidebar-nav-link <?= in_array($current_page, ['orders.php','order-detail.php']) ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag nav-icon"></i>
            Orders
        </a>

        <a href="<?= BASE_URL ?>admin/users.php"
           class="sidebar-nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users nav-icon"></i>
            Users
        </a>

        <a href="<?= BASE_URL ?>admin/reviews.php"
           class="sidebar-nav-link <?= $current_page === 'reviews.php' ? 'active' : '' ?>">
            <i class="fas fa-star nav-icon"></i>
            Reviews
        </a>

        <!-- Manage Section -->
        <div class="sidebar-section-label">Manage</div>

        <a href="<?= BASE_URL ?>admin/coupons.php"
           class="sidebar-nav-link <?= $current_page === 'coupons.php' ? 'active' : '' ?>">
            <i class="fas fa-ticket-alt nav-icon"></i>
            Coupons
        </a>

        <a href="<?= BASE_URL ?>admin/messages.php"
           class="sidebar-nav-link <?= $current_page === 'messages.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope nav-icon"></i>
            Messages
        </a>

        <div class="sidebar-divider"></div>

        <!-- Account Section -->
        <a href="<?= BASE_URL ?>admin/profile.php"
           class="sidebar-nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user-circle nav-icon"></i>
            My Profile
        </a>

        <a href="<?= BASE_URL ?>" target="_blank" class="sidebar-nav-link">
            <i class="fas fa-external-link-alt nav-icon"></i>
            Visit Store
        </a>
    </nav>

    <!-- Footer with Admin Info -->
    <div class="sidebar-footer">
        <div class="sidebar-admin-info">
            <div class="sidebar-admin-avatar <?= avatarColorClass(substr($_SESSION['user_email'] ?? 'A', 0, 1)) ?>">
                <?php if (!empty($_SESSION['user_profile_photo'])): ?>
                    <img src="<?= BASE_URL ?>uploads/profiles/<?= $_SESSION['user_profile_photo'] ?>" alt="">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['user_email'] ?? 'A', 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="min-w-0" style="overflow:hidden">
                <div class="sidebar-admin-name">
                    <?= htmlspecialchars(explode('@', $_SESSION['user_email'] ?? 'admin')[0]) ?>
                </div>
                <div class="sidebar-admin-role">Administrator</div>
            </div>
            <a href="<?= BASE_URL ?>user/logout.php" class="sidebar-logout-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>

<!-- Main Content Wrapper -->
<main class="admin-content">
    <?php if (function_exists('hasFlash') && hasFlash()): ?>
        <div class="alert alert-<?= getFlash('type') ?> alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-<?= getFlash('type') === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
            <?= getFlash('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
