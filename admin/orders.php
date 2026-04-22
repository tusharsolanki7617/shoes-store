<?php
/**
 * Admin Orders Management
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/security.php';

requireAdmin();

$page_title = 'Orders - ' . SITE_NAME;
$db = new Database();

// Handle status update
if (isset($_POST['update_status'])) {
    checkCsrf();
    $order_id = intval($_POST['order_id']);
    $status   = clean($_POST['status']);
    try {
        if ($status === 'delivered') {
            $db->query("UPDATE orders SET status = ?, payment_status = 'paid' WHERE id = ?", [$status, $order_id]);
        } else {
            $db->query("UPDATE orders SET status = ? WHERE id = ?", [$status, $order_id]);
        }
        setFlash('success', 'Order status updated');
        redirect(BASE_URL . 'admin/orders.php');
    } catch (Exception $e) {
        setFlash('error', 'Error updating status');
    }
}

require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';
require_once '../includes/security.php';

// Search & Filter
$search      = clean($_GET['search'] ?? '');
$filter_status = clean($_GET['status'] ?? '');
$params      = [];
$where       = [];

if ($search) {
    $where[]    = "(o.id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $s          = "%$search%";
    $params     = array_merge($params, [$s, $s, $s, $s]);
}
if ($filter_status && $filter_status !== 'all') {
    $where[]  = "o.status = ?";
    $params[] = $filter_status;
}

$whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$orders = $db->fetchAll(
    "SELECT o.*, u.first_name, u.last_name, u.email FROM orders o
     INNER JOIN users u ON o.user_id = u.id
     $whereClause
     ORDER BY o.created_at DESC",
    $params
);

// Count per status
$counts = [];
foreach (['pending','processing','shipped','delivered','cancelled'] as $s) {
    $counts[$s] = $db->fetchOne("SELECT COUNT(*) as c FROM orders WHERE status = ?", [$s])['c'];
}
$counts['all'] = $db->fetchOne("SELECT COUNT(*) as c FROM orders")['c'];

$status_config = [
    'pending'    => ['class' => 'badge-warning',   'label' => 'Pending'],
    'processing' => ['class' => 'badge-info',       'label' => 'Processing'],
    'shipped'    => ['class' => 'badge-indigo',     'label' => 'Shipped'],
    'delivered'  => ['class' => 'badge-success',    'label' => 'Delivered'],
    'cancelled'  => ['class' => 'badge-danger',     'label' => 'Cancelled'],
];
?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
    <div>
        <h1 class="page-title">Orders</h1>
        <p class="page-subtitle">Track and manage customer orders</p>
    </div>
    <form method="GET" class="search-bar" style="width: 100%; max-width: 300px;">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="search" class="form-control" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
        <?php if ($filter_status): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
        <?php endif; ?>
    </form>
</div>

<!-- Status Filter Tabs -->
<div class="mb-3 d-flex gap-2 flex-wrap">
    <?php
    $tabs = ['all' => 'All'] + array_map(fn($c) => $c['label'], $status_config);
    foreach ($tabs as $key => $label):
        $isActive = ($filter_status === $key) || ($key === 'all' && !$filter_status);
    ?>
        <a href="?status=<?= $key ?><?= $search ? '&search='.urlencode($search) : '' ?>"
           style="font-size:0.8rem;font-weight:600;padding:0.4rem 0.9rem;border-radius:20px;text-decoration:none;transition:all 0.18s;
                  <?= $isActive
                      ? 'background:var(--accent);color:white;'
                      : 'background:white;color:var(--text-secondary);border:1px solid var(--border);' ?>">
            <?= $label ?>
            <span style="margin-left:4px;font-size:0.72rem;opacity:0.8;"><?= $counts[$key] ?? 0 ?></span>
        </a>
    <?php endforeach; ?>
    <?php if ($search || $filter_status): ?>
        <a href="orders.php" style="font-size:0.8rem;font-weight:600;padding:0.4rem 0.9rem;border-radius:20px;text-decoration:none;background:#fee2e2;color:#991b1b;">
            <i class="fas fa-times me-1"></i> Clear
        </a>
    <?php endif; ?>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th class="d-none d-lg-table-cell">Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>
                            No orders found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order):
                        $initial = strtoupper(substr($order['first_name'], 0, 1));
                        $sc = $status_config[$order['status']] ?? ['class'=>'badge-secondary','label'=>ucfirst($order['status'])];
                        $isDeliveredUnpaid = $order['status'] === 'delivered' && $order['payment_status'] !== 'paid';
                        $payBadge = $order['payment_status'] === 'paid' ? 'badge-success' : ($isDeliveredUnpaid ? 'badge-danger' : 'badge-warning');
                        $payLabel = $order['payment_status'] === 'paid' ? 'Paid' : ($isDeliveredUnpaid ? 'Unpaid' : ucfirst($order['payment_status']));
                    ?>
                        <tr>
                            <td>
                                <a href="order-detail.php?id=<?= $order['id'] ?>" style="font-weight:700;color:var(--accent);text-decoration:none;">
                                    #<?= $order['id'] ?>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 avatar-<?= strtolower($initial) ?>"
                                         style="width:32px;height:32px;font-size:0.75rem;font-weight:700;">
                                        <?= $initial ?>
                                    </div>
                                    <div style="min-width:0;">
                                        <div style="font-weight:600;font-size:0.85rem;" class="text-truncate"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></div>
                                        <div style="font-size:0.73rem;color:var(--text-muted);" class="text-truncate d-none d-md-block"><?= htmlspecialchars($order['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight:700;"><?= formatPrice($order['total']) ?></td>
                            <td>
                                <div style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;"><?= htmlspecialchars($order['payment_method']) ?></div>
                                <span class="status-badge <?= $payBadge ?>" style="font-size:0.68rem;margin-top:2px;"><?= $payLabel ?></span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status"
                                            class="form-select form-select-sm"
                                            style="width:130px;font-size:0.78rem;font-weight:600;border-radius:8px;padding:0.35rem 0.6rem;"
                                            onchange="this.form.submit()">
                                        <?php foreach ($status_config as $sv => $scfg): ?>
                                            <option value="<?= $sv ?>" <?= $order['status'] === $sv ? 'selected' : '' ?>>
                                                <?= $scfg['label'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td class="d-none d-lg-table-cell" style="font-size:0.8rem;color:var(--text-muted);">
                                <?= date('M j, Y', strtotime($order['created_at'])) ?><br>
                                <span style="font-size:0.72rem;"><?= date('h:i A', strtotime($order['created_at'])) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn-ghost" style="font-size:0.78rem;padding:0.35rem 0.75rem;">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
