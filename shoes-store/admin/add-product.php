<?php
/**
 * Add Product Handler
 * Requires 1 main image; images 2-4 are optional.
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();
require_once '../includes/security.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'admin/products.php');
}

checkCsrf();

$name          = clean($_POST['name'] ?? '');
$category_id   = intval($_POST['category_id'] ?? 0);
$description   = clean($_POST['description'] ?? '');
$price         = floatval($_POST['price'] ?? 0);
$discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
$stock         = intval($_POST['stock'] ?? 0);
$is_featured   = isset($_POST['is_featured']) ? 1 : 0;
$is_active     = 1; // Always active so products appear to users immediately

$errors = [];

// Basic field validation
if (strlen($name) < 3) {
    $errors[] = 'Product name must be at least 3 characters';
}
if ($category_id <= 0) {
    $errors[] = 'Please select a category';
}
if ($price <= 0) {
    $errors[] = 'Price must be greater than 0';
}

// Ensure at least 1 (main) image is provided
$mainFileProvided = isset($_FILES['images']) &&
                    isset($_FILES['images']['error'][0]) &&
                    $_FILES['images']['error'][0] === UPLOAD_ERR_OK;

if (!$mainFileProvided) {
    $errors[] = 'Please upload at least the main product image (Image 1)';
}

// Upload images — main is required, rest are optional
$uploaded_images = [];
$gallery_images  = [];

if (empty($errors) && isset($_FILES['images'])) {
    $total = count($_FILES['images']['name']); // Up to 4 inputs

    for ($i = 0; $i < $total; $i++) {
        $errCode = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;

        if ($errCode === UPLOAD_ERR_OK) {
            $file = [
                'name'     => $_FILES['images']['name'][$i],
                'type'     => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error'    => $_FILES['images']['error'][$i],
                'size'     => $_FILES['images']['size'][$i],
            ];

            $result = uploadImage($file, 'products');

            if ($result['success']) {
                if ($i === 0) {
                    $uploaded_images[] = $result['filename']; // main image slot
                } else {
                    $gallery_images[] = $result['filename'];
                }
            } else {
                // Non-fatal for optional images 2-4; fatal only for image 1
                if ($i === 0) {
                    $errors[] = 'Main image upload failed: ' . $result['message'];
                }
                // skip optional failed uploads silently
            }
        } elseif ($errCode !== UPLOAD_ERR_NO_FILE) {
            // A real error (not just "no file chosen")
            if ($i === 0) {
                $errors[] = 'Main image upload error (code ' . $errCode . ')';
            }
        }
        // UPLOAD_ERR_NO_FILE for images 2-4 is fine — they are optional
    }
}

if (empty($errors)) {
    try {
        $db = new Database();

        $main_image  = $uploaded_images[0] ?? 'placeholder.png';
        $gallery_json = json_encode($gallery_images);

        $db->query(
            "INSERT INTO products 
                (name, slug, description, price, discount_price, category_id, stock, image, gallery, is_featured, is_active, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $name,
                slugify($name),
                $description,
                $price,
                $discount_price,
                $category_id,
                $stock,
                $main_image,
                $gallery_json,
                $is_featured,
                $is_active,
            ]
        );

        setFlash('success', 'Product "' . $name . '" added successfully and is now visible to users!');
    } catch (Exception $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
} else {
    setFlash('error', implode('<br>', $errors));
}

redirect(BASE_URL . 'admin/products.php');
