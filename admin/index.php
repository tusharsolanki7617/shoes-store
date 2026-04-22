<?php
/**
 * Admin Dashboard
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$page_title = 'Dashboard - ' . SITE_NAME;
require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';

try {
    $db = new Database();

    $total_users    = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")['count'];
    $total_products = $db->fetchOne("SELECT COUNT(*) as count FROM products")['count'];
    $total_orders   = $db->fetchOne("SELECT COUNT(*) as count FROM orders")['count'];
    $total_revenue  = $db->fetchOne("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE payment_status = 'paid'")['total'];

    $pending_orders   = $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];
    $delivered_orders = $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")['count'];

    $recent_orders = $db->fetchAll(
        "SELECT o.*, u.first_name, u.last_name, u.email FROM orders o
         INNER JOIN users u ON o.user_id = u.id
         ORDER BY o.created_at DESC LIMIT 10"
    );

    $low_stock = $db->fetchAll(
        "SELECT * FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 8"
    );
} catch (Exception $e) {
    $total_users = $total_products = $total_orders = $total_revenue = 0;
    $pending_orders = $delivered_orders = 0;
    $recent_orders = $low_stock = [];
}

$status_colors = [
    'pending'    => 'badge-warning',
    'processing' => 'badge-info',
    'shipped'    => 'badge-indigo',
    'delivered'  => 'badge-success',
    'cancelled'  => 'badge-danger',
];
?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back! Here's what's happening in your store.</p>
    </div>
    <span class="badge rounded-pill px-3 py-2" style="background:#e0e7ff; color:#3730a3; font-size: 0.8rem; font-weight:600;">
        <i class="fas fa-calendar-alt me-1"></i> <?= date('F j, Y') ?>
    </span>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3 animate-in">
        <div class="stat-card stat-indigo">
            <div class="stat-icon bg-indigo">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-label">Total Customers</div>
            <div class="stat-value"><?= number_format($total_users) ?></div>
            <div class="stat-change" style="color: var(--text-secondary);">
                <i class="fas fa-arrow-up" style="color:#10b981"></i>
                Registered users
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 animate-in">
        <div class="stat-card stat-amber">
            <div class="stat-icon bg-amber">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="stat-label">Total Products</div>
            <div class="stat-value"><?= number_format($total_products) ?></div>
            <div class="stat-change" style="color: var(--text-secondary);">
                <i class="fas fa-layer-group" style="color:#f59e0b"></i>
                In catalog
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 animate-in">
        <div class="stat-card stat-blue">
            <div class="stat-icon bg-blue">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?= number_format($total_orders) ?></div>
            <div class="stat-change" style="color: var(--text-secondary);">
                <i class="fas fa-clock" style="color:#f59e0b"></i>
                <?= $pending_orders ?> pending
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 animate-in">
        <div class="stat-card stat-emerald">
            <div class="stat-icon bg-emerald">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value" style="font-size: 1.35rem;"><?= formatPrice($total_revenue) ?></div>
            <div class="stat-change" style="color: var(--text-secondary);">
                <i class="fas fa-check-circle" style="color:#10b981"></i>
                <?= $delivered_orders ?> delivered
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-xl-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="fas fa-shopping-bag text-indigo"></i>
                    Recent Orders
                </div>
                <a href="orders.php" class="btn-ghost btn-sm" style="font-size:0.78rem;">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="d-none d-md-table-cell">Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5" style="color: var(--text-muted);">
                                    <i class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>
                                    No orders yet
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order):
                                $initial = strtoupper(substr($order['first_name'], 0, 1));
                                $badgeClass = $status_colors[$order['status']] ?? 'badge-secondary';
                            ?>
                                <tr>
                                    <td>
                                        <span style="font-weight:700; color: var(--accent);">#<?= $order['id'] ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 avatar-<?= strtolower($initial) ?>" style="width:32px;height:32px;font-size:0.78rem;font-weight:700;">
                                                <?= $initial ?>
                                            </div>
                                            <div style="min-width:0">
                                                <div style="font-weight:600;font-size:0.82rem;" class="text-truncate"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></div>
                                                <div style="font-size:0.72rem;color:var(--text-muted);" class="text-truncate d-none d-sm-block"><?= htmlspecialchars($order['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight:700;"><?= formatPrice($order['total']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $badgeClass ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-md-table-cell" style="color:var(--text-muted);font-size:0.8rem;">
                                        <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn-ghost" style="font-size:0.78rem;padding:0.35rem 0.75rem;">
                                            Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Stock -->
    <div class="col-xl-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
                    Low Stock Alert
                </div>
                <a href="products.php" class="btn-ghost btn-sm" style="font-size:0.78rem;">
                    Manage
                </a>
            </div>
            <div>
                <?php if (empty($low_stock)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x mb-3" style="color:#10b981;opacity:0.5;"></i>
                        <p style="font-weight:600;color:var(--text-secondary);margin:0;">All stock is healthy!</p>
                        <small style="color:var(--text-muted);">No products running low.</small>
                    </div>
                <?php else: ?>
                    <div class="p-3 d-flex flex-column gap-2">
                        <?php foreach ($low_stock as $product): ?>
                            <div class="d-flex align-items-center gap-3 p-2 rounded" style="background: #fafbff; border: 1px solid var(--border);">
                                <?php if ($product['image']): ?>
                                    <img src="<?= BASE_URL ?>uploads/products/<?= $product['image'] ?>"
                                         class="rounded flex-shrink-0"
                                         style="width:40px;height:40px;object-fit:cover;border:1px solid var(--border);">
                                <?php else: ?>
                                    <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:#f1f5f9;border:1px solid var(--border);">
                                        <i class="fas fa-box" style="color:var(--text-muted);font-size:0.8rem;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1 min-w-0" style="overflow:hidden;">
                                    <div style="font-weight:600;font-size:0.82rem;" class="text-truncate"><?= htmlspecialchars($product['name']) ?></div>
                                    <div style="font-size:0.72rem;color:var(--text-muted);">ID #<?= $product['id'] ?></div>
                                </div>
                                <div class="flex-shrink-0">
                                    <?php if ($product['stock'] == 0): ?>
                                        <span class="status-badge badge-danger" style="font-size:0.68rem;">Out</span>
                                    <?php else: ?>
                                        <span class="status-badge badge-warning" style="font-size:0.68rem;"><?= $product['stock'] ?> left</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="p-3 pt-0">
                        <a href="products.php" class="btn-ghost w-100 justify-content-center" style="font-size:0.82rem;">
                            View All Products <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
