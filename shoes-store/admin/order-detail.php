<?php
/**
 * Admin – Order Detail Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/security.php';

requireAdmin();

$db = new Database();

$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) {
    setFlash('error', 'Invalid order ID');
    redirect(BASE_URL . 'admin/orders.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    checkCsrf();
    $new_status = clean($_POST['status']);
    try {
        $db->query("UPDATE orders SET status = ? WHERE id = ?", [$new_status, $order_id]);
        setFlash('success', 'Order status updated successfully');
        redirect(BASE_URL . 'admin/order-detail.php?id=' . $order_id);
    } catch (Exception $e) {
        setFlash('error', 'Error updating order status');
    }
}

// Fetch order with user info
$order = $db->fetchOne(
    "SELECT o.*, u.first_name, u.last_name, u.email, u.phone as user_phone
     FROM orders o
     INNER JOIN users u ON o.user_id = u.id
     WHERE o.id = ?",
    [$order_id]
);

if (!$order) {
    setFlash('error', 'Order not found');
    redirect(BASE_URL . 'admin/orders.php');
}

// Fetch order items
$order_items = $db->fetchAll(
    "SELECT oi.*, p.image FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?",
    [$order_id]
);

$page_title = 'Order #' . $order_id . ' – Admin';

require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';
?>

<div class="container-fluid my-4">

    <!-- Back + Title -->
    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <a href="orders.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <div>
            <h2 class="fw-black text-uppercase ls-1 mb-0">Order #<?= $order_id ?></h2>
            <p class="text-secondary small mb-0"><?= date('M j, Y \a\t h:i A', strtotime($order['created_at'])) ?></p>
        </div>
        <span class="ms-auto badge fs-6 rounded-pill bg-<?= match($order['status']) {
            'pending'    => 'warning text-dark',
            'processing' => 'info text-dark',
            'shipped'    => 'primary',
            'delivered'  => 'success',
            'cancelled'  => 'danger',
            default      => 'secondary'
        } ?>">
            <?= ucfirst($order['status']) ?>
        </span>
    </div>

    <?php if ($flash = getFlash()): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4">
            <?= $flash['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ── Left: Order Items ─────────────────────────────────── -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary" style="font-size:.82rem;text-transform:uppercase;">
                            <tr>
                                <th class="ps-4 border-0">Product</th>
                                <th class="border-0">Size</th>
                                <th class="border-0">Price</th>
                                <th class="border-0">Qty</th>
                                <th class="pe-4 text-end border-0">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php
                                            $img = !empty($item['image'])
                                                ? PRODUCT_IMAGES_URL . $item['image']
                                                : ASSETS_URL . 'images/site/placeholder.png';
                                        ?>
                                        <img src="<?= $img ?>" alt="<?= e($item['product_name']) ?>"
                                             style="width:48px;height:48px;object-fit:cover;border-radius:8px;"
                                             onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                                        <span class="fw-bold"><?= e($item['product_name']) ?></span>
                                    </div>
                                </td>
                                <td><?= !empty($item['size']) ? 'UK ' . e($item['size']) : '—' ?></td>
                                <td><?= formatPrice($item['price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td class="pe-4 text-end fw-bold"><?= formatPrice($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="ps-4 text-end text-secondary">Subtotal</td>
                                <td class="pe-4 text-end fw-bold"><?= formatPrice($order['subtotal']) ?></td>
                            </tr>
                            <?php if ($order['discount'] > 0): ?>
                            <tr>
                                <td colspan="4" class="ps-4 text-end text-success">Discount
                                    <?= $order['coupon_code'] ? '(' . e($order['coupon_code']) . ')' : '' ?>
                                </td>
                                <td class="pe-4 text-end fw-bold text-success">-<?= formatPrice($order['discount']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="4" class="ps-4 text-end fw-bold">Total</td>
                                <td class="pe-4 text-end fw-bold fs-5 text-primary"><?= formatPrice($order['total']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ── Right: Info Panels ────────────────────────────────── -->
        <div class="col-lg-4 d-flex flex-column gap-4">

            <!-- Customer Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user me-2 text-primary"></i>Customer</h6>
                </div>
                <div class="card-body">
                    <p class="fw-bold mb-1"><?= e($order['first_name'] . ' ' . $order['last_name']) ?></p>
                    <p class="text-secondary small mb-1"><i class="fas fa-envelope me-1"></i><?= e($order['email']) ?></p>
                    <p class="text-secondary small mb-0"><i class="fas fa-phone me-1"></i><?= e($order['shipping_phone'] ?: $order['user_phone'] ?: '—') ?></p>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Shipping Address</h6>
                </div>
                <div class="card-body">
                    <p class="fw-bold mb-1"><?= e($order['shipping_name']) ?></p>
                    <p class="text-secondary small mb-0"><?= nl2br(e($order['shipping_address'])) ?></p>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-credit-card me-2 text-primary"></i>Payment</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Method</span>
                        <span class="fw-bold text-uppercase"><?= e($order['payment_method']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Status</span>
                        <span class="badge rounded-pill bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning text-dark' ?>">
                            <?= strtoupper($order['payment_status']) ?>
                        </span>
                    </div>
                    <?php if (!empty($order['razorpay_payment_id'])): ?>
                    <div class="mt-2 pt-2 border-top">
                        <span class="text-secondary small d-block mb-1">Razorpay Payment ID</span>
                        <code class="small text-break"><?= e($order['razorpay_payment_id']) ?></code>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Update Status -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Update Status</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="update_status" value="1">
                        <select name="status" class="form-select mb-3">
                            <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="fas fa-save me-1"></i> Save Status
                        </button>
                    </form>
                </div>
            </div>

        </div><!-- /col-lg-4 -->
    </div><!-- /row -->
</div>

<?php require_once 'includes/admin-footer.php'; ?>
