<?php
/**
 * Admin Messages Management
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/security.php';

requireAdmin();

$page_title = 'Messages - ' . SITE_NAME;
require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';

$db = new Database();

// Handle delete
if (isset($_POST['delete_message'])) {
    checkCsrf();
    $id = intval($_POST['message_id']);
    try {
        $db->query("DELETE FROM contact_messages WHERE id = ?", [$id]);
        setFlash('success', 'Message deleted successfully');
        redirect(BASE_URL . 'admin/messages.php');
    } catch (Exception $e) {
        setFlash('error', 'Error deleting message');
    }
}

$messages = $db->fetchAll("SELECT * FROM contact_messages ORDER BY created_at DESC");
?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
    <div>
        <h1 class="page-title">Messages</h1>
        <p class="page-subtitle">Customer inquiries and support requests</p>
    </div>
    <span class="status-badge badge-indigo" style="font-size:0.82rem;padding:0.45rem 1rem;">
        <i class="fas fa-envelope me-1"></i> <?= count($messages) ?> Messages
    </span>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Sender</th>
                    <th class="d-none d-md-table-cell">Email</th>
                    <th>Subject</th>
                    <th class="d-none d-lg-table-cell">Preview</th>
                    <th class="d-none d-md-table-cell">Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>
                            No messages yet
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg):
                        $initial = strtoupper(substr($msg['name'], 0, 1));
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 avatar-<?= strtolower($initial) ?>"
                                         style="width:34px;height:34px;font-size:0.78rem;font-weight:700;">
                                        <?= $initial ?>
                                    </div>
                                    <div style="font-weight:600;font-size:0.875rem;"><?= htmlspecialchars($msg['name']) ?></div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <a href="mailto:<?= htmlspecialchars($msg['email']) ?>"
                                   style="color:var(--accent);text-decoration:none;font-size:0.85rem;">
                                    <?= htmlspecialchars($msg['email']) ?>
                                </a>
                            </td>
                            <td style="font-weight:600;font-size:0.875rem;"><?= htmlspecialchars($msg['subject']) ?></td>
                            <td class="d-none d-lg-table-cell" style="color:var(--text-muted);font-size:0.82rem;max-width:220px;">
                                <span class="text-truncate d-block"><?= htmlspecialchars(substr($msg['message'], 0, 70)) ?>...</span>
                            </td>
                            <td class="d-none d-md-table-cell" style="font-size:0.8rem;color:var(--text-muted);white-space:nowrap;">
                                <?= date('M j, Y', strtotime($msg['created_at'])) ?><br>
                                <span style="font-size:0.72rem;"><?= date('h:i A', strtotime($msg['created_at'])) ?></span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button"
                                            class="btn-ghost"
                                            style="padding:0.35rem 0.75rem;font-size:0.78rem;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#msgModal<?= $msg['id'] ?>"
                                            title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button"
                                            class="btn-danger-ghost delete-msg-btn"
                                            style="padding:0.35rem 0.75rem;font-size:0.78rem;"
                                            data-id="<?= $msg['id'] ?>"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <!-- Hidden delete form -->
                                <form id="delete-form-<?= $msg['id'] ?>" method="POST" class="d-none">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                    <input type="hidden" name="delete_message" value="1">
                                </form>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="msgModal<?= $msg['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-envelope me-2 text-indigo"></i>
                                            <?= htmlspecialchars($msg['subject']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex justify-content-between align-items-start mb-4 gap-3 flex-wrap">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 avatar-<?= strtolower($initial) ?>"
                                                     style="width:44px;height:44px;font-size:1rem;font-weight:700;">
                                                    <?= $initial ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight:700;"><?= htmlspecialchars($msg['name']) ?></div>
                                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" style="color:var(--accent);font-size:0.85rem;">
                                                        <?= htmlspecialchars($msg['email']) ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <div style="font-size:0.8rem;color:var(--text-muted);">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('d M Y, h:i A', strtotime($msg['created_at'])) ?>
                                            </div>
                                        </div>
                                        <div style="background:#f8f9fc;border:1px solid var(--border);border-radius:12px;padding:1.25rem;line-height:1.8;color:var(--text-primary);">
                                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>"
                                           class="btn-accent">
                                            <i class="fas fa-reply me-1"></i> Reply via Email
                                        </a>
                                        <button type="button" class="btn-ghost" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-msg-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Delete Message?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        });
    });
});
</script>
