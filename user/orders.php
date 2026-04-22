<?php
/**
 * User Orders Page
 * View order history
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

requireLogin();

$page_title = 'My Orders - Kicks & Comfort';
require_once '../includes/header.php';

// Fetch User Data for Sidebar
$db = new Database();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Fetch Orders
try {
    $search = clean($_GET['search'] ?? '');
    $sql = "SELECT * FROM orders WHERE user_id = ?";
    $params = [$_SESSION['user_id']];
    
    if ($search) {
        $sql .= " AND (id LIKE ? OR status LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $orders = $db->fetchAll($sql, $params);
} catch (Exception $e) {
    $orders = [];
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
                    <li class="breadcrumb-item active" aria-current="page">Orders</li>
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
                    <a href="orders.php" class="list-group-item list-group-item-action active bg-black border-0 py-3 px-4 fw-bold">
                        <i class="fas fa-box me-2"></i> Order History
                    </a>
                    <a href="wishlist.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-secondary">
                         <i class="fas fa-heart me-2"></i> Wishlist
                    </a>
                    <a href="reset-password.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-secondary">
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
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom pb-3 gap-3">
                        <h2 class="h3 fw-bold mb-0">Order History</h2>
                        
                        <!-- Search -->
                        <form method="GET" class="d-flex">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control rounded-start-pill border-secondary" 
                                       placeholder="Search Order ID" value="<?= e($_GET['search'] ?? '') ?>">
                                <button type="submit" class="btn btn-dark rounded-end-pill px-3">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if (isset($_GET['search']) && $_GET['search']): ?>
                                    <a href="orders.php" class="btn btn-light border ms-2 rounded-pill px-3" title="Clear Search">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-shopping-bag fa-4x text-muted opacity-25"></i>
                            </div>
                            <h4 class="fw-bold">No orders found</h4>
                            <p class="text-secondary mb-4">Looks like you haven't made any purchases yet.</p>
                            <a href="<?= BASE_URL ?>products.php" class="btn btn-dark rounded-pill px-4">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($orders as $order): ?>
                                <div class="col-12">
                                    <div class="card border-0 bg-light rounded-3 transition-hover">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center g-3">
                                                <div class="col-md-3">
                                                    <span class="d-block small text-secondary text-uppercase fw-bold mb-1">Order ID</span>
                                                    <span class="fs-5 fw-bold text-dark">#<?= $order['id'] ?></span>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <span class="d-block small text-secondary text-uppercase fw-bold mb-1">Date</span>
                                                    <span class="fw-medium"><?= date('M j, Y', strtotime($order['created_at'])) ?></span>
                                                </div>
                                                <div class="col-6 col-md-2">
                                                    <span class="d-block small text-secondary text-uppercase fw-bold mb-1">Total</span>
                                                    <span class="fw-bold"><?= formatPrice($order['total']) ?></span>
                                                </div>
                                                <div class="col-6 col-md-2">
                                                    <span class="d-block small text-secondary text-uppercase fw-bold mb-1">Status</span>
                                                    <?php
                                                        $statusClass = match($order['status']) {
                                                            'delivered' => 'success',
                                                            'shipped' => 'info',
                                                            'cancelled' => 'danger',
                                                            'processing' => 'primary',
                                                            default => 'warning'
                                                        };
                                                        $statusIcon = match($order['status']) {
                                                            'delivered' => 'check-circle',
                                                            'shipped' => 'truck',
                                                            'cancelled' => 'times-circle',
                                                            'processing' => 'cog',
                                                            default => 'clock'
                                                        };
                                                    ?>
                                                    <span class="badge bg-<?= $statusClass ?> rounded-pill px-3 py-2 text-white border-0">
                                                        <i class="fas fa-<?= $statusIcon ?> me-1"></i> <?= ucfirst($order['status']) ?>
                                                    </span>
                                                </div>
                                             
                                            </div>
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

<?php require_once '../includes/footer.php'; ?>
