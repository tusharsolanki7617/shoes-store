<?php
/**
 * Wishlist Page
 * Displays user's saved items
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

requireLogin();

$page_title = 'My Wishlist - Kicks & Comfort';
require_once '../includes/header.php';

// Fetch User Data for Sidebar
$db = new Database();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Get wishlist items
$wishlist_items = $db->fetchAll(
    "SELECT p.*, w.created_at as added_at 
     FROM wishlist w 
     JOIN products p ON w.product_id = p.id 
     WHERE w.user_id = ? 
     ORDER BY w.created_at DESC",
    [$_SESSION['user_id']]
);
?>

<div class="container my-5 pt-4">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="display-5 fw-black text-uppercase ls-1">My Account</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-secondary text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
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
                    <a href="wishlist.php" class="list-group-item list-group-item-action active bg-black border-0 py-3 px-4 fw-bold">
                         <i class="fas fa-heart me-2"></i> Wishlist
                    </a>
                    <a href="change-password.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-secondary">
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
                    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
                        <h2 class="h3 fw-bold mb-0">My Wishlist</h2>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2"><?= count($wishlist_items) ?> Items</span>
                    </div>
                    
                    <?php if (empty($wishlist_items)): ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="far fa-heart fa-4x text-muted opacity-25"></i>
                            </div>
                            <h4 class="fw-bold">Your wishlist is empty</h4>
                            <p class="text-secondary mb-4">Save items you love to buy later!</p>
                            <a href="<?= BASE_URL ?>products.php" class="btn btn-dark rounded-pill px-4">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($wishlist_items as $product): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-0 bg-light rounded-3 transition-hover product-card position-relative">
                                        <!-- Remove Button -->
                                        <button class="btn btn-sm btn-white position-absolute top-0 end-0 m-2 rounded-circle shadow-sm wishlist-remove z-2" 
                                                data-product-id="<?= $product['id'] ?>"
                                                title="Remove from wishlist">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        
                                        <div class="ratio ratio-1x1 position-relative overflow-hidden rounded-top-3">
                                            <a href="<?= BASE_URL ?>product-detail.php?id=<?= $product['id'] ?>">
                                                <img src="<?= PRODUCT_IMAGES_URL . ($product['image'] ?? 'placeholder.png') ?>" 
                                                     alt="<?= e($product['name']) ?>"
                                                     class="w-100 h-100 object-fit-cover"
                                                     onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                                            </a>
                                        </div>
                                        
                                        <div class="card-body d-flex flex-column p-3">
                                            <h5 class="product-title fs-6 mb-1 fw-bold">
                                                <a href="<?= BASE_URL ?>product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none text-dark">
                                                    <?= e($product['name']) ?>
                                                </a>
                                            </h5>
                                            <div class="product-price fw-bold mb-3 text-secondary">
                                                <?= formatPrice($product['discount_price'] ?? $product['price']) ?>
                                            </div>
                                            
                                            <button class="btn btn-dark btn-sm w-100 rounded-pill mt-auto add-to-cart" 
                                                    data-product-id="<?= $product['id'] ?>">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.05);
    background-color: white !important;
    border: 1px solid #eee !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Remove Button
    document.querySelectorAll('.wishlist-remove').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const cardCol = this.closest('.col-md-6');
            
            // We can use the toggle feature or a direct remove
            fetch('<?= BASE_URL ?>ajax/toggle-wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success && data.action === 'removed') {
                    // Animate removal
                    cardCol.style.transition = 'all 0.3s ease';
                    cardCol.style.opacity = '0';
                    cardCol.style.transform = 'scale(0.9)';
                    
                    setTimeout(() => {
                        cardCol.remove();
                        
                        // Update header count
                        const badge = document.querySelector('.wishlist-count');
                        if(badge) {
                            badge.textContent = data.count;
                            if(data.count == 0) badge.remove();
                        }
                        
                        // Show empty state if needed
                        const remainingItems = document.querySelectorAll('.product-card').length;
                        if(remainingItems === 0) {
                            location.reload(); 
                        }
                        
                        // Update item count badge
                        const countBadge = document.querySelector('.badge.bg-light');
                        if(countBadge) countBadge.textContent = data.count + ' Items';
                        
                        if(typeof showToast === 'function') showToast('success', 'Removed from Wishlist');
                    }, 300);
                }
            });
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
