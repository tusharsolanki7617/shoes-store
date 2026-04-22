<?php
/**
 * Admin Coupons Management
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

requireAdmin();

$page_title = 'Coupons - ' . SITE_NAME;
$db = new Database();

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_coupon') {
    checkCsrf();
    $code        = strtoupper(clean($_POST['code']));
    $type        = clean($_POST['discount_type']);
    $value       = floatval($_POST['discount_value']);
    $min_purchase = floatval($_POST['min_purchase']);
    $valid_until = clean($_POST['valid_until']);
    $errors = [];
    if (empty($code)) $errors[] = "Code is required";
    if ($value <= 0)  $errors[] = "Discount value must be greater than 0";
    if (empty($valid_until)) $errors[] = "Valid until date is required";
    $existing = $db->fetchOne("SELECT id FROM coupons WHERE code = ?", [$code]);
    if ($existing) $errors[] = "Coupon code already exists";
    if (empty($errors)) {
        try {
            $db->query(
                "INSERT INTO coupons (code, discount_type, discount_value, min_purchase, valid_from, valid_until, is_active) VALUES (?, ?, ?, ?, CURDATE(), ?, 1)",
                [$code, $type, $value, $min_purchase, $valid_until]
            );
            setFlash('success', 'Coupon added successfully');
            redirect('coupons.php');
        } catch (Exception $e) {
            setFlash('error', 'Error adding coupon');
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    checkCsrf();
    $id = intval($_GET['delete']);
    try {
        $db->query("DELETE FROM coupons WHERE id = ?", [$id]);
        setFlash('success', 'Coupon deleted');
        redirect('coupons.php');
    } catch (Exception $e) {
        setFlash('error', 'Error deleting coupon');
    }
}

// Handle Toggle
if (isset($_GET['toggle'])) {
    checkCsrf();
    $id = intval($_GET['toggle']);
    try {
        $db->query("UPDATE coupons SET is_active = NOT is_active WHERE id = ?", [$id]);
        setFlash('success', 'Coupon status updated');
        redirect('coupons.php');
    } catch (Exception $e) {
        setFlash('error', 'Error updating status');
    }
}

require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';

$coupons = $db->fetchAll("SELECT * FROM coupons ORDER BY created_at DESC");
$active_count = $db->fetchOne("SELECT COUNT(*) as c FROM coupons WHERE is_active=1")['c'];
?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
    <div>
        <h1 class="page-title">Coupons</h1>
        <p class="page-subtitle">Manage discounts and promotional codes — <?= $active_count ?> active</p>
    </div>
    <button class="btn-accent" data-bs-toggle="modal" data-bs-target="#addCouponModal">
        <i class="fas fa-plus me-1"></i> New Coupon
    </button>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th class="d-none d-md-table-cell">Min Purchase</th>
                    <th class="d-none d-sm-table-cell">Usage</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coupons)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="fas fa-ticket-alt fa-2x d-block mb-2 opacity-25"></i>
                            No coupons yet
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($coupons as $coupon):
                        $expiry    = strtotime($coupon['valid_until']);
                        $isExpired = $expiry < time();
                        $isActiveAndValid = $coupon['is_active'] && !$isExpired;
                    ?>
                        <tr>
                            <td>
                                <span style="background:#e0e7ff;color:#3730a3;border-radius:8px;padding:4px 10px;font-family:monospace;font-weight:700;font-size:0.85rem;letter-spacing:1px;">
                                    <?= htmlspecialchars($coupon['code']) ?>
                                </span>
                            </td>
                            <td style="font-weight:700;font-size:0.95rem;">
                                <?= $coupon['discount_type'] === 'percentage'
                                    ? floatval($coupon['discount_value']) . '%'
                                    : formatPrice($coupon['discount_value']) ?>
                                <div style="font-size:0.72rem;color:var(--text-muted);font-weight:400;">
                                    <?= $coupon['discount_type'] === 'percentage' ? 'Percentage off' : 'Fixed amount' ?>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell"><?= formatPrice($coupon['min_purchase']) ?></td>
                            <td class="d-none d-sm-table-cell">
                                <span class="status-badge badge-secondary">
                                    <?= $coupon['used_count'] ?>
                                    <?= $coupon['max_uses'] > 0 ? '/ ' . $coupon['max_uses'] : '' ?>
                                    uses
                                </span>
                            </td>
                            <td>
                                <span style="font-size:0.85rem;<?= $isExpired ? 'color:#ef4444;font-weight:600;' : 'color:var(--text-secondary);' ?>">
                                    <?= date('M j, Y', $expiry) ?>
                                </span>
                                <?php if ($isExpired): ?>
                                    <div><span class="status-badge badge-danger" style="font-size:0.68rem;">Expired</span></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $isActiveAndValid ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $isActiveAndValid ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="?toggle=<?= $coupon['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                                       class="btn-ghost"
                                       style="padding:0.35rem 0.75rem;font-size:0.78rem;"
                                       title="<?= $coupon['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <button type="button"
                                            class="btn-danger-ghost delete-coupon-btn"
                                            style="padding:0.35rem 0.75rem;font-size:0.78rem;"
                                            data-url="?delete=<?= $coupon['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ticket-alt me-2 text-indigo"></i>New Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="couponForm">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_coupon">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" required
                               placeholder="e.g. SUMMER2024" style="text-transform:uppercase;font-family:monospace;font-weight:600;">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="discount_type" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="discount_value" step="0.01" min="0" required placeholder="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Minimum Purchase (₹)</label>
                        <input type="number" class="form-control" name="min_purchase" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="valid_until" required min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-accent">
                        <i class="fas fa-plus me-1"></i> Create Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-coupon-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            Swal.fire({
                title: 'Delete Coupon?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
            }).then(result => {
                if (result.isConfirmed) window.location.href = url;
            });
        });
    });
});
</script>
