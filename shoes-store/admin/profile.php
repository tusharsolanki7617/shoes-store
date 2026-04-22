<?php
/**
 * Admin Profile Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

requireAdmin();

$page_title = 'My Profile - Admin';

$db = new Database();

// Get current admin data
$admin = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    checkCsrf();
    
    $action = $_POST['action'];
    
    // Update profile info
    if ($action === 'update_profile') {
        $first_name = clean($_POST['first_name'] ?? '');
        $last_name = clean($_POST['last_name'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        
        try {
            $db->query(
                "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?",
                [$first_name, $last_name, $phone, $_SESSION['user_id']]
            );
            setFlash('success', 'Profile updated successfully!');
            redirect(BASE_URL . 'admin/profile.php');
        } catch (Exception $e) {
            setFlash('error', 'Error updating profile');
        }
    }
    
    // Change password
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        // Verify current password
        if (!verifyPassword($current_password, $admin['password'])) {
            $errors[] = 'Current password is incorrect';
        }
        
        // Validate new password
        if (strlen($new_password) < 8) {
            $errors[] = 'New password must be at least 8 characters';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $db->query(
                    "UPDATE users SET password = ? WHERE id = ?",
                    [$hashed_password, $_SESSION['user_id']]
                );
                setFlash('success', 'Password changed successfully!');
                redirect(BASE_URL . 'admin/profile.php');
            } catch (Exception $e) {
                setFlash('error', 'Error changing password');
            }
        } else {
            setFlash('error', implode('<br>', $errors));
        }
    }
}

// Reload admin data after updates
$admin = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Get stats
$total_products = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1")['count'] ?? 0;
$total_orders = $db->fetchOne("SELECT COUNT(*) as count FROM orders")['count'] ?? 0;
$total_users = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")['count'] ?? 0;

require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';
?>

<div class="container-fluid my-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-black text-uppercase ls-1 mb-0">My Profile</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-secondary">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/" class="text-decoration-none text-secondary">Dashboard</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <!-- Profile Info -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if (!empty($admin['profile_photo'])): ?>
                            <img src="<?= BASE_URL ?>uploads/profiles/<?= $admin['profile_photo'] ?>" 
                                 alt="Admin Photo" 
                                 class="rounded-circle border"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center border" 
                                 style="width: 120px; height: 120px;">
                                <span class="display-4 fw-bold text-secondary"><?= strtoupper(substr($admin['first_name'], 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-2">
                             <span class="badge bg-dark rounded-pill px-3 py-2">Administrator</span>
                        </div>
                    </div>
                    
                    <h4 class="fw-bold mb-1 text-truncate" title="<?= e($admin['first_name'] ?? 'Admin') ?> <?= e($admin['last_name'] ?? '') ?>">
                        <?= e($admin['first_name'] ?? 'Admin') ?> <?= e($admin['last_name'] ?? '') ?>
                    </h4>
                    <p class="text-secondary text-truncate" title="<?= e($admin['email']) ?>"><?= e($admin['email']) ?></p>
                    
                    <hr class="my-4 opacity-10">
                    
                    <div class="row text-center g-2 g-md-3">
                        <div class="col-4">
                            <div class="p-1 p-sm-2 border rounded bg-light h-100 d-flex flex-column justify-content-center">
                                <h5 class="mb-0 fw-bold"><?= $total_products ?></h5>
                                <small class="text-secondary text-uppercase d-block text-truncate" style="font-size: 0.65rem; letter-spacing: 0.5px;" title="Products">Products</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-1 p-sm-2 border rounded bg-light h-100 d-flex flex-column justify-content-center">
                                <h5 class="mb-0 fw-bold"><?= $total_orders ?></h5>
                                <small class="text-secondary text-uppercase d-block text-truncate" style="font-size: 0.65rem; letter-spacing: 0.5px;" title="Orders">Orders</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-1 p-sm-2 border rounded bg-light h-100 d-flex flex-column justify-content-center">
                                <h5 class="mb-0 fw-bold"><?= $total_users ?></h5>
                                <small class="text-secondary text-uppercase d-block text-truncate" style="font-size: 0.65rem; letter-spacing: 0.5px;" title="Users">Users</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Forms -->
        <div class="col-lg-8">
            <!-- Update Profile Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-edit me-2 text-secondary"></i> Update Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary small text-uppercase fw-bold">First Name</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?= e($admin['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary small text-uppercase fw-bold">Last Name</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?= e($admin['last_name'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary small text-uppercase fw-bold">Email</label>
                                <input type="email" class="form-control bg-light" 
                                       value="<?= e($admin['email']) ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary small text-uppercase fw-bold">Phone</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?= e($admin['phone'] ?? '') ?>" 
                                       pattern="[0-9]{10}">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-dark px-4">
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Other settings like password and photo in a row -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-key me-2 text-secondary"></i> Change Password</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">New Password</label>
                                    <input type="password" class="form-control" name="new_password" 
                                           minlength="8" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">Confirm Password</label>
                                    <input type="password" class="form-control" name="confirm_password" 
                                           minlength="8" required>
                                </div>
                                
                                <button type="submit" class="btn btn-dark w-100">
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                         <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-camera me-2 text-secondary"></i> Profile Photo</h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            <form id="profilePhotoForm" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <div class="mb-3 text-start">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">Upload New Photo</label>
                                    <input type="file" class="form-control" id="profilePhotoInput" 
                                           accept="image/*" name="profile_photo">
                                </div>
                                
                                <div id="photoPreview" class="mb-3" style="display: none;">
                                    <img id="previewImage" src="" alt="Preview" 
                                         class="rounded-3 border"
                                         style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="button" id="uploadPhotoBtn" class="btn btn-primary" disabled>
                                        <i class="fas fa-upload me-2"></i> Upload Photo
                                    </button>
                                    
                                    <?php if (!empty($admin['profile_photo'])): ?>
                                        <button type="button" id="removePhotoBtn" class="btn btn-outline-danger">
                                            <i class="fas fa-trash me-2"></i> Remove Current Photo
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

<script>
$(document).ready(function() {
    // Photo preview
    $('#profilePhotoInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImage').attr('src', e.target.result);
                $('#photoPreview').show();
                $('#uploadPhotoBtn').prop('disabled', false);
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Upload photo
    $('#uploadPhotoBtn').on('click', function() {
        const formData = new FormData($('#profilePhotoForm')[0]);
        
        $.ajax({
            url: '<?= BASE_URL ?>ajax/upload-profile-photo.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Profile photo uploaded successfully!',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Upload error:', xhr.responseText);
                let msg = 'An error occurred';
                if (xhr.status === 0) msg = 'Network error - check your connection';
                else if (xhr.status === 404) msg = 'Upload script not found';
                else if (xhr.status === 500) msg = 'Server error';
                else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                else if (!xhr.responseJSON) {
                    console.error('Non-JSON response:', xhr.responseText);
                    msg = 'Invalid server response. Debug: ' + xhr.responseText.substring(0, 50);
                }
                
                Swal.fire('Error', msg, 'error');
            }
        });
    });
    
    // Remove photo
    // Remove photo
    $('#removePhotoBtn').on('click', function() {
        Swal.fire({
            title: 'Remove Photo?',
            text: "Are you sure you want to remove your profile photo?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= BASE_URL ?>ajax/remove-profile-photo.php',
                    type: 'POST',
                    data: { csrf_token: $('input[name="csrf_token"]').val() },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Removed!', 'Your profile photo has been removed.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Remove error:', xhr.responseText);
                        let msg = 'An error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        else if (!xhr.responseJSON) {
                            console.error('Non-JSON response:', xhr.responseText);
                            msg = 'Invalid server response. Debug: ' + xhr.responseText.substring(0, 50);
                        }
                        
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });
});
</script>
