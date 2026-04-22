<?php
/**
 * Admin Reviews Management
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

requireAdmin();

$page_title = 'Reviews - ' . SITE_NAME;
$db = new Database();

// Handle Actions
if (isset($_POST['action'])) {
    checkCsrf();
    $action = $_POST['action'];
    $id     = intval($_POST['id']);
    try {
        if ($action === 'approve') {
            $db->query("UPDATE reviews SET is_approved = 1 WHERE id = ?", [$id]);
            setFlash('success', 'Review approved');
        } elseif ($action === 'delete') {
            $db->query("DELETE FROM reviews WHERE id = ?", [$id]);
            setFlash('success', 'Review deleted');
        }
    } catch (Exception $e) {
        setFlash('error', 'Error processing action');
    }
    redirect('reviews.php');
}

$reviews = $db->fetchAll(
    "SELECT r.*, p.name as product_name, u.first_name, u.last_name
     FROM reviews r
     JOIN products p ON r.product_id = p.id
     JOIN users u ON r.user_id = u.id
     ORDER BY r.created_at DESC"
);

$pending_count  = count(array_filter($reviews, fn($r) => !$r['is_approved']));
$approved_count = count(array_filter($reviews, fn($r) => $r['is_approved']));

require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';
?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
    <div>
        <h1 class="page-title">Reviews</h1>
        <p class="page-subtitle">Manage customer feedback and ratings</p>
    </div>
    <div class="d-flex gap-2">
        <span class="status-badge badge-warning" style="padding:0.45rem 0.9rem;font-size:0.8rem;">
            <?= $pending_count ?> Pending
        </span>
        <span class="status-badge badge-success" style="padding:0.45rem 0.9rem;font-size:0.8rem;">
            <?= $approved_count ?> Approved
        </span>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reviewer</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th class="d-none d-lg-table-cell">Comment</th>
                    <th class="d-none d-md-table-cell">Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="fas fa-star fa-2x d-block mb-2 opacity-25"></i>
                            No reviews yet
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $review):
                        $initial = strtoupper(substr($review['first_name'], 0, 1));
                    ?>
                        <tr style="<?= !$review['is_approved'] ? 'background:#fffbeb;' : '' ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 avatar-<?= strtolower($initial) ?>"
                                         style="width:34px;height:34px;font-size:0.78rem;font-weight:700;">
                                        <?= $initial ?>
                                    </div>
                                    <div style="font-weight:600;font-size:0.875rem;white-space:nowrap;">
                                        <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:0.85rem;max-width:160px;" class="text-truncate">
                                <?= htmlspecialchars($review['product_name']) ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1" style="color:#f59e0b;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $review['rating'] ? '' : '-o' ?>" style="font-size:0.75rem;<?= $i > $review['rating'] ? 'opacity:0.25;' : '' ?>"></i>
                                    <?php endfor; ?>
                                    <span style="font-size:0.8rem;font-weight:700;color:var(--text-primary);margin-left:4px;"><?= $review['rating'] ?></span>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell" style="color:var(--text-secondary);font-size:0.82rem;max-width:240px;">
                                <span class="d-block text-truncate"><?= htmlspecialchars($review['comment']) ?></span>
                            </td>
                            <td class="d-none d-md-table-cell" style="font-size:0.8rem;color:var(--text-muted);white-space:nowrap;">
                                <?= date('M j, Y', strtotime($review['created_at'])) ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $review['is_approved'] ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $review['is_approved'] ? 'Approved' : 'Pending' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <?php if (!$review['is_approved']): ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                            <button type="submit" class="btn-ghost" style="padding:0.35rem 0.75rem;font-size:0.78rem;color:#10b981;border-color:#a7f3d0;" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this review?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                        <button type="submit" class="btn-danger-ghost" style="padding:0.35rem 0.75rem;font-size:0.78rem;" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
