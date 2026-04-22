<?php
/**
 * Admin Edit Product Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

requireAdmin();

$page_title = 'Edit Product - Admin';
$db = new Database();

// Get product ID
$product_id = intval($_GET['id'] ?? 0);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $name = clean($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $description = clean($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $stock = intval($_POST['stock'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    if (strlen($name) < 3) {
        $errors[] = 'Product name must be at least 3 characters';
    }
    if ($category_id <= 0) {
        $errors[] = 'Please select a category';
    }
    if ($price <= 0) {
        $errors[] = 'Price must be greater than 0';
    }
    
    // Handle individual image uploads (each slot is independent)
    // We use separate named fields: image_0 (main), image_1, image_2, image_3
    $current_product_imgs = $db->fetchOne("SELECT image, gallery FROM products WHERE id = ?", [$product_id]);
    $existing_main    = $current_product_imgs['image'];
    $existing_gallery = json_decode($current_product_imgs['gallery'] ?? '[]', true) ?: [];

    // Ensure gallery array has 3 slots
    while (count($existing_gallery) < 3) $existing_gallery[] = null;

    $new_images = [
        0 => $existing_main,
        1 => $existing_gallery[0] ?? null,
        2 => $existing_gallery[1] ?? null,
        3 => $existing_gallery[2] ?? null,
    ];

    for ($i = 0; $i < 4; $i++) {
        $field = 'image_' . $i;
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES[$field], 'products');
            if ($upload_result['success']) {
                // Delete old file if it existed
                $old = $new_images[$i];
                if ($old && file_exists(PRODUCT_IMAGE_PATH . $old)) {
                    unlink(PRODUCT_IMAGE_PATH . $old);
                }
                $new_images[$i] = $upload_result['filename'];
            } else {
                $errors[] = "Image " . ($i + 1) . ": " . $upload_result['message'];
            }
        }
    }

    
    // Final image values from the per-slot logic above
    if (empty($errors)) {
        $image_name  = $new_images[0];
        $gallery_json = json_encode(array_values(array_filter([
            $new_images[1], $new_images[2], $new_images[3]
        ])));
    }

    
    if (empty($errors)) {
        try {
            $db->query(
                "UPDATE products SET 
                    name = ?, 
                    slug = ?, 
                    description = ?, 
                    price = ?, 
                    discount_price = ?, 
                    category_id = ?, 
                    stock = ?, 
                    image = ?, 
                    gallery = ?,
                    is_featured = ?,
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?",
                [
                    $name,
                    slugify($name),
                    $description,
                    $price,
                    $discount_price,
                    $category_id,
                    $stock,
                    $image_name,
                    $gallery_json,
                    $is_featured,
                    $is_active,
                    $product_id
                ]
            );
            setFlash('success', 'Product updated successfully!');
            redirect(BASE_URL . 'admin/products.php');
        } catch (Exception $e) {
            setFlash('error', 'Error updating product: ' . $e->getMessage());
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}

// Get product data
$product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$product_id]);
if (!$product) {
    setFlash('error', 'Product not found');
    redirect(BASE_URL . 'admin/products.php');
}

// Prepare current images array
$current_images = [];
if (!empty($product['image'])) {
    $current_images[] = $product['image'];
}
if (!empty($product['gallery'])) {
    $gallery_imgs = json_decode($product['gallery'], true);
    if (is_array($gallery_imgs)) {
        $current_images = array_merge($current_images, $gallery_imgs);
    }
}
// Ensure we have 4 slots (fill with null if less)
while (count($current_images) < 4) {
    $current_images[] = null;
}

// Get categories
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

require_once 'includes/admin-header.php';
require_once 'includes/admin-sidebar.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="fas fa-edit"></i> Edit Product</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/products.php">Products</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card-glass p-4">
                <form method="POST" enctype="multipart/form-data" id="productForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="current_image" value="<?= e($product['image']) ?>">
                    <input type="hidden" name="current_gallery" value="<?= e($product['gallery']) ?>">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?= e($product['name']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="4" required><?= e($product['description']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price *</label>
                            <input type="number" class="form-control" name="price" 
                                   value="<?= $product['price'] ?>" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Discount Price</label>
                            <input type="number" class="form-control" name="discount_price" 
                                   value="<?= $product['discount_price'] ?>" min="0" step="0.01">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock *</label>
                            <input type="number" class="form-control" name="stock" 
                                   value="<?= $product['stock'] ?>" min="0" required>
                        </div>
                    </div>
                    
                    
                    <!-- Individual Image Upload Slots -->
                    <div class="mb-3">
                        <label class="form-label">Product Images</label>
                        <div class="row g-3">
                            <?php 
                            $slot_labels = ['Image 1 (Main)', 'Image 2', 'Image 3', 'Image 4'];
                            foreach ($current_images as $index => $img): 
                            ?>
                            <div class="col-md-6">
                                <div class="border rounded p-2 text-center bg-light mb-2" style="min-height:130px; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                                    <?php if ($img): ?>
                                        <img src="<?= PRODUCT_IMAGES_URL . $img ?>" 
                                             alt="<?= $slot_labels[$index] ?>" 
                                             id="preview<?= $index ?>"
                                             class="img-fluid rounded mb-1" 
                                             style="max-height:120px; object-fit:cover;"
                                             onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                                    <?php else: ?>
                                        <img src="" id="preview<?= $index ?>" class="img-fluid rounded mb-1 d-none" style="max-height:120px; object-fit:cover;">
                                        <i class="fas fa-image fa-3x text-muted" id="placeholder<?= $index ?>"></i>
                                    <?php endif; ?>
                                    <small class="text-muted d-block mt-1"><?= $slot_labels[$index] ?></small>
                                </div>
                                <label class="form-label text-muted small">Replace <?= $slot_labels[$index] ?></label>
                                <input type="file" class="form-control form-control-sm" 
                                       name="image_<?= $index ?>" 
                                       id="imageInput<?= $index ?>" 
                                       accept="image/*">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle"></i> Upload only the images you want to replace. Empty slots keep the current image.
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_featured" 
                                   id="isFeatured" <?= $product['is_featured'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isFeatured">Featured Product</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                   id="isActive" <?= $product['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="<?= BASE_URL ?>admin/products.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card-glass p-4 mb-3">
                <h5>Product Info</h5>
                <hr>
                <p class="text-muted small mb-2"><strong>ID:</strong> <?= $product['id'] ?></p>
                <p class="text-muted small mb-2"><strong>Created:</strong> <?= date('M d, Y', strtotime($product['created_at'])) ?></p>
                <p class="text-muted small mb-0"><strong>Last Updated:</strong> <?= date('M d, Y', strtotime($product['updated_at'])) ?></p>
            </div>
            
            <div class="card-glass p-4">
                <h5>Quick Actions</h5>
                <hr>
                <a href="<?= BASE_URL ?>product-detail.php?id=<?= $product['id'] ?>" 
                   class="btn btn-outline-primary w-100 mb-2" target="_blank">
                    <i class="fas fa-eye"></i> View on Site
                </a>
                <a href="<?= BASE_URL ?>admin/products.php?delete=<?= $product['id'] ?>" 
                   class="btn btn-outline-danger w-100" 
                   onclick="return confirm('Delete this product?')">
                    <i class="fas fa-trash"></i> Delete Product
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Live preview for individual image slots
for (let i = 0; i < 4; i++) {
    const input = document.getElementById('imageInput' + i);
    if (input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                const preview     = document.getElementById('preview' + i);
                const placeholder = document.getElementById('placeholder' + i);
                if (preview) {
                    preview.src = ev.target.result;
                    preview.classList.remove('d-none');
                }
                if (placeholder) placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
}

$(document).ready(function() {
    // Custom method to ensure discount price is less than regular price
    $.validator.addMethod("lessThanPrice", function(value, element) {
        let price = $("input[name='price']").val();
        if (!value || !price) return true; // skip if either is empty
        return parseFloat(value) < parseFloat(price);
    }, "Discount price must be less than regular price");

    // Custom method for image file extensions
    $.validator.addMethod("validImageExtension", function(value, element) {
        if (element.files.length === 0) return true; // not selected
        let ext = element.files[0].name.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext);
    }, "Please upload a valid image file (JPG, PNG, WEBP, GIF)");

    // Custom method for image file size (max 2MB)
    $.validator.addMethod("validImageSize", function(value, element) {
        if (element.files.length === 0) return true;
        let sizeInMB = element.files[0].size / (1024 * 1024);
        return sizeInMB <= 2;
    }, "Image size must not exceed 2MB");

    // Initialize form validation
    $('#productForm').validate({
        rules: {
            name: { required: true, minlength: 3 },
            category_id: { required: true },
            description: { required: true, minlength: 10 },
            price: { required: true, number: true, min: 0.01 },
            discount_price: { number: true, min: 0, lessThanPrice: true },
            stock: { required: true, digits: true, min: 0 }
        },
        messages: {
            name: {
                required: "Please enter the product name",
                minlength: "Product name must be at least 3 characters"
            },
            category_id: "Please select a category",
            description: {
                required: "Please enter a product description",
                minlength: "Description must be at least 10 characters"
            },
            price: {
                required: "Please enter the product price",
                number: "Please enter a valid number",
                min: "Price must be greater than 0"
            },
            discount_price: {
                number: "Please enter a valid number",
                min: "Discount cannot be negative"
            },
            stock: {
                required: "Please enter the available stock",
                digits: "Stock must be a whole number",
                min: "Stock cannot be negative"
            }
        }
    });

    // Apply image rules to all individual image file inputs
    for (let i = 0; i < 4; i++) {
        $('#imageInput' + i).rules('add', {
            validImageExtension: true,
            validImageSize: true
        });
    }
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>
