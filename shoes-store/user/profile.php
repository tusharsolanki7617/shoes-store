<?php
/**
 * User Profile Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

requireLogin();

$page_title = 'My Profile - Kicks & Comfort';
require_once '../includes/header.php';
require_once '../includes/security.php';

$db = new Database();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $first_name = clean($_POST['first_name'] ?? '');
    $last_name = clean($_POST['last_name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    
    $errors = [];
    
    if (strlen($first_name) < 2) {
        $errors[] = 'First name must be at least 2 characters';
    }
    if (strlen($last_name) < 2) {
        $errors[] = 'Last name must be at least 2 characters';
    }
    if ($phone && !preg_match('/^\d{10}$/', $phone)) {
        $errors[] = 'Phone must be 10 digits';
    }
    
    if (empty($errors)) {
        try {
            $db->query(
                "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?",
                [$first_name, $last_name, $phone, $_SESSION['user_id']]
            );
            setFlash('success', 'Profile updated successfully!');
            redirect(BASE_URL . 'user/profile.php');
        } catch (Exception $e) {
            setFlash('error', 'An error occurred');
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
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
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
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
                            
                            <button class="btn btn-sm btn-white rounded-circle position-absolute bottom-0 end-0 shadow-sm border" 
                                    data-bs-toggle="modal" data-bs-target="#updatePhotoModal"
                                    title="Update Photo">
                                <i class="fas fa-camera text-dark"></i>
                            </button>
                        </div>
                        
                        <h5 class="fw-bold mb-1"><?= e($user['first_name']) ?> <?= e($user['last_name']) ?></h5>
                        <p class="text-secondary small mb-0"><?= e($user['email']) ?></p>
                    </div>
                </div>

                <div class="list-group list-group-flush rounded-4 overflow-hidden border-0 shadow-sm">
                    <a href="profile.php" class="list-group-item list-group-item-action active bg-black border-0 py-3 px-4 fw-bold">
                        <i class="fas fa-user me-2"></i> Profile Details
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-secondary">
                        <i class="fas fa-box me-2"></i> Order History
                    </a>
                    <a href="wishlist.php" class="list-group-item list-group-item-action border-0 py-3 px-4 text-secondary">
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
                    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
                        <h2 class="h3 fw-bold mb-0">Personal Information</h2>
                        <button class="btn btn-dark rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-pen me-2"></i> Edit Profile
                        </button>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border border-light">
                                <label class="small text-secondary fw-bold text-uppercase mb-2">First Name</label>
                                <p class="fs-5 fw-medium mb-0 text-dark"><?= e($user['first_name']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border border-light">
                                <label class="small text-secondary fw-bold text-uppercase mb-2">Last Name</label>
                                <p class="fs-5 fw-medium mb-0 text-dark"><?= e($user['last_name']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border border-light">
                                <label class="small text-secondary fw-bold text-uppercase mb-2">Email Address</label>
                                <p class="fs-5 fw-medium mb-0 text-dark"><?= e($user['email']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border border-light">
                                <label class="small text-secondary fw-bold text-uppercase mb-2">Phone Number</label>
                                <p class="fs-5 fw-medium mb-0 text-dark"><?= e($user['phone'] ?: 'Not provided') ?></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border border-light">
                                <label class="small text-secondary fw-bold text-uppercase mb-2">Account Status</label>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success rounded-pill px-3 py-2 me-3">Active Member</span>
                                    <span class="text-secondary small">Member since <?= date('F d, Y', strtotime($user['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="profileForm">
                <div class="modal-body pt-4">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label fw-bold small text-uppercase">First Name</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" name="first_name" 
                                   value="<?= e($user['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label fw-bold small text-uppercase">Last Name</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" name="last_name" 
                                   value="<?= e($user['last_name']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label fw-bold small text-uppercase">Phone Number</label>
                            <input type="tel" class="form-control form-control-lg bg-light border-0" name="phone" 
                                   value="<?= e($user['phone'] ?? '') ?>" placeholder="10-digit mobile number">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Photo Modal -->
<div class="modal fade" id="updatePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Update Profile Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4">
                <form id="profilePhotoForm" enctype="multipart/form-data">
                    <div class="mb-4 text-center">
                        <div id="photoPreview" class="d-inline-block position-relative mb-3" style="display: none;">
                            <img id="previewImage" src="" alt="Preview" 
                                 class="rounded-circle shadow-sm"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        
                        <div class="input-group">
                            <input type="file" class="form-control" id="profilePhotoInput" 
                                   accept="image/*" name="profile_photo">
                        </div>
                        <small class="text-muted mt-2 d-block">Recommended: Square image, max 5MB</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <?php if (!empty($user['profile_photo'])): ?>
                    <button type="button" id="removePhotoBtn" class="btn btn-outline-danger rounded-pill px-4 me-auto">
                        <i class="fas fa-trash me-2"></i> Remove
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="uploadPhotoBtn" class="btn btn-dark rounded-pill px-4" disabled>
                    <i class="fas fa-upload me-2"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$base_url = BASE_URL;
$extra_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Photo preview
    $('#profilePhotoInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                if(typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'File size must be less than 5MB', 'error');
                } else {
                    alert('File size must be less than 5MB');
                }
                this.value = '';
                return;
            }
            
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
        // Get CSRF token safely
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if(csrfToken) formData.append('csrf_token', csrfToken);
        
        // Show loading state
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        
        $.ajax({
            url: '{$base_url}ajax/upload-profile-photo.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#updatePhotoModal').modal('hide');
                    if(typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Profile photo updated successfully!',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        alert('Profile photo updated successfully!');
                        location.reload();
                    }
                } else {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire('Error', response.message, 'error');
                    } else {
                        alert(response.message);
                    }
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Upload error:', xhr.responseText);
                if(typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'An error occurred while uploading', 'error');
                } else {
                    alert('An error occurred while uploading. Check console for details.');
                }
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Remove photo
    $('#removePhotoBtn').on('click', function() {
        const confirmAction = () => {
             $.ajax({
                url: '{$base_url}ajax/remove-profile-photo.php',
                type: 'POST',
                data: { csrf_token: $('meta[name="csrf-token"]').attr('content') },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#updatePhotoModal').modal('hide');
                        if(typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Removed!',
                                text: 'Your profile photo has been removed.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert('Profile photo removed.');
                            location.reload();
                        }
                    } else {
                         if(typeof Swal !== 'undefined') {
                            Swal.fire('Error', response.message, 'error');
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Failed to remove photo', 'error');
                    } else {
                        alert('Failed to remove photo');
                    }
                }
            });
        };

        if(typeof Swal !== 'undefined') {
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
                    confirmAction();
                }
            });
        } else {
            if(confirm('Are you sure you want to remove your profile photo?')) {
                confirmAction();
            }
        }
    });
});
</script>
EOT;
require_once '../includes/footer.php'; 
?>
