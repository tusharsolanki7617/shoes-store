<?php
/**
 * Admin Users Management
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$page_title = 'Users - ' . SITE_NAME;
require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';

$db = new Database();

$search = clean($_GET['search'] ?? '');
$params = [];
$where  = '';
if ($search) {
    $s = "%$search%";
    $where = "WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";
    $params = [$s, $s, $s];
}

$users = $db->fetchAll("SELECT * FROM users $where ORDER BY created_at DESC", $params);

$total_customers = $db->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='customer'")['c'];
$total_admins    = $db->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='admin'")['c'];
$active_users    = $db->fetchOne("SELECT COUNT(*) as c FROM users WHERE is_active=1")['c'];
?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
    <div>
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Manage registered customers and administrators</p>
    </div>
    <form method="GET" class="search-bar" style="width:100%;max-width:280px;">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
    </form>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-4 animate-in">
        <div class="stat-card stat-indigo">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-indigo mb-0" style="margin-bottom:0!important;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-label">Customers</div>
                    <div class="stat-value" style="font-size:1.5rem;"><?= $total_customers ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4 animate-in">
        <div class="stat-card stat-violet">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-violet mb-0" style="margin-bottom:0!important;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div class="stat-label">Admins</div>
                    <div class="stat-value" style="font-size:1.5rem;"><?= $total_admins ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4 animate-in">
        <div class="stat-card stat-emerald">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-emerald mb-0" style="margin-bottom:0!important;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="stat-label">Active</div>
                    <div class="stat-value" style="font-size:1.5rem;"><?= $active_users ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($search): ?>
    <div class="mb-3">
        <a href="users.php" class="btn-ghost" style="font-size:0.8rem;">
            <i class="fas fa-times me-1"></i> Clear search
        </a>
    </div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="d-none d-sm-table-cell">ID</th>
                    <th>User</th>
                    <th class="d-none d-lg-table-cell">Contact</th>
                    <th class="d-none d-md-table-cell">Role</th>
                    <th>Status</th>
                    <th class="d-none d-md-table-cell text-end">Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="fas fa-users fa-2x d-block mb-2 opacity-25"></i>
                            No users found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user):
                        $initial = strtoupper(substr($user['first_name'], 0, 1));
                    ?>
                        <tr>
                            <td class="d-none d-sm-table-cell" style="font-size:0.8rem;color:var(--text-muted);">#<?= $user['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 avatar-<?= strtolower($initial) ?>"
                                         style="width:38px;height:38px;font-size:0.85rem;font-weight:700;overflow:hidden;">
                                        <?php if (!empty($user['profile_photo'])): ?>
                                            <img src="<?= BASE_URL ?>uploads/profiles/<?= $user['profile_photo'] ?>"
                                                 style="width:100%;height:100%;object-fit:cover;" alt="">
                                        <?php else: ?>
                                            <?= $initial ?>
                                        <?php endif; ?>
                                    </div>
                                    <div style="min-width:0;">
                                        <div style="font-weight:600;font-size:0.875rem;" class="text-truncate">
                                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                        </div>
                                        <div style="font-size:0.73rem;color:var(--text-muted);" class="text-truncate d-lg-none">
                                            <?= htmlspecialchars($user['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <div style="font-size:0.85rem;" class="text-truncate" style="max-width:180px;"><?= htmlspecialchars($user['email']) ?></div>
                                <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="status-badge badge-violet">Admin</span>
                                <?php else: ?>
                                    <span class="status-badge badge-secondary">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $user['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell text-end" style="font-size:0.8rem;color:var(--text-muted);">
                                <?= date('M j, Y', strtotime($user['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
