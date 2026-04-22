<?php
/**
 * Product Detail Page
 * Show detailed product information, images, reviews, and add to cart
 */

require_once 'config/config.php';
require_once 'includes/header.php';

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    redirect(BASE_URL . 'products.php');
}

try {
    $db = new Database();
    
    
    // Handle Review Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
        require_once 'includes/security.php';
        checkCsrf();
        
        if (!isLoggedIn()) {
            redirect(BASE_URL . 'user/login.php');
        }
        
        $rating = intval($_POST['rating'] ?? 0);
        $review_text = clean($_POST['review'] ?? '');
        
        if ($rating < 1 || $rating > 5) {
            setFlash('error', 'Please select a valid rating (1-5 stars)');
        } elseif (empty($review_text)) {
            setFlash('error', 'Please write a review');
        } else {
            // Check if already reviewed
            $existing = $db->fetchOne(
                "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?",
                [$_SESSION['user_id'], $product_id]
            );
            // Allow review on any purchase (pending/processing/shipped/delivered)
            $purchased = $db->fetchOne(
                "SELECT o.id FROM orders o
                 JOIN order_items oi ON o.id = oi.order_id
                 WHERE o.user_id = ? AND oi.product_id = ?
                 LIMIT 1",
                [$_SESSION['user_id'], $product_id]
            );

            if ($existing) {
                setFlash('error', 'You have already reviewed this product.');
            } elseif (!$purchased) {
                setFlash('error', 'You must purchase this product to write a review.');
            } else {
                try {
                    $db->query(
                        "INSERT INTO reviews (user_id, product_id, rating, comment, is_approved, created_at) VALUES (?, ?, ?, ?, 1, NOW())",
                        [$_SESSION['user_id'], $product_id, $rating, $review_text]
                    );
                    setFlash('success', 'Thank you! Your review has been submitted.');
                    redirect(BASE_URL . "product-detail.php?id=$product_id");
                } catch (Exception $e) {
                    setFlash('error', 'Error submitting review. Please try again.');
                }
            }
        }
    }
    
    // Get product details
    $product = $db->fetchOne(
        "SELECT p.*, c.name as category_name FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.id = ? AND p.is_active = 1",
        [$product_id]
    );
    
    if (!$product) {
        redirect(BASE_URL . 'products.php');
    }
    
    // Prepare image gallery (combine main image + gallery images, max 4)
    $images = [];
    if (!empty($product['image'])) {
        $images[] = $product['image']; // Main image first
    }
    
    // Add gallery images if available
    if (!empty($product['gallery'])) {
        $gallery_images = json_decode($product['gallery'], true);
        if (is_array($gallery_images)) {
            $images = array_merge($images, $gallery_images);
        }
    }
    
    // Limit to 4 images maximum
    $images = array_slice($images, 0, 4);
    
    // If no images at all, use placeholder
    if (empty($images)) {
        $images[] = 'placeholder.jpg';
    }
    
    // Get related products
    $related_products = $db->fetchAll(
        "SELECT * FROM products 
         WHERE category_id = ? AND id != ? AND is_active = 1 
         ORDER BY RAND() LIMIT 4",
        [$product['category_id'], $product_id]
    );
    
    // Get reviews (is_approved=1 auto-set on insert)
    $reviews = $db->fetchAll(
        "SELECT r.*, u.first_name, u.last_name FROM reviews r
         INNER JOIN users u ON r.user_id = u.id
         WHERE r.product_id = ?
         ORDER BY r.created_at DESC LIMIT 10",
        [$product_id]
    );
    
    $page_title = $product['name'] . ' - Kicks & Comfort';
} catch (Exception $e) {
    redirect(BASE_URL . 'products.php');
}
?>

<div class="container my-5 pt-4">
    <div class="row g-5">
        <!-- Product Images (Left Side) -->
        <div class="col-lg-7">
            <div class="row g-2">
                <!-- Main Image - Full Width on Mobile, Large on Desktop -->
                <div class="col-12 mb-2">
                    <div class="bg-light rounded-0 d-flex align-items-center justify-content-center" style="min-height: 500px;">
                        <img src="<?= PRODUCT_IMAGES_URL . $images[0] ?>" 
                             alt="<?= e($product['name']) ?>" 
                             id="mainImage" 
                             class="img-fluid" 
                             style="max-height: 600px; width: auto;"
                             onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                    </div>
                </div>
                
                <!-- Thumbnails Grid -->
                <?php if (count($images) > 1): ?>
                    <?php foreach (array_slice($images, 0, 4) as $index => $img): ?>
                        <div class="col-3">
                            <div class="bg-light rounded-0 d-flex align-items-center justify-content-center p-2" 
                                 style="height: 100px; cursor: pointer;"
                                 onclick="changeMainImage('<?= PRODUCT_IMAGES_URL . $img ?>')">
                                <img src="<?= PRODUCT_IMAGES_URL . $img ?>" 
                                     class="img-fluid" 
                                     style="max-height: 100%;"
                                     onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Description & Reviews (Desktop: Below Images) -->
            <div class="mt-5 pt-5 border-top d-none d-lg-block">
                 <h4 class="mb-4 text-uppercase fw-bold">Description</h4>
                 <p class="lead text-secondary mb-5"><?= e($product['description']) ?></p>
                 
                 <h4 class="mb-4 text-uppercase fw-bold">Reviews (<?= count($reviews) ?>)</h4>
                 <!-- Reviews Section -->
                 <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="mb-4 border-bottom pb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0"><?= e($review['first_name']) ?></h6>
                                <div>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star fs-xs <?= $i <= $review['rating'] ? 'text-black' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-secondary mb-1"><?= e($review['comment']) ?></p>
                            <small class="text-muted fs-xs"><?= timeAgo($review['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                 <?php else: ?>
                    <p class="text-secondary">No reviews yet.</p>
                 <?php endif; ?>
                 
                 <!-- Review Button -->
                 <?php if (isLoggedIn()): ?>
                    <button class="btn btn-outline-primary mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#reviewFormCollapse">
                        Write a Review
                    </button>
                    <div class="collapse mt-4" id="reviewFormCollapse">
                        <div class="card card-body border-0 bg-light">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="submit_review">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Rating</label>
                                    <select name="rating" class="form-select w-auto">
                                        <option value="5">5 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="2">2 Stars</option>
                                        <option value="1">1 Star</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Review</label>
                                    <textarea name="review" class="form-control" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                 <?php else: ?>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>user/login.php" class="text-decoration-underline">Log in to write a review</a>
                    </div>
                 <?php endif; ?>
            </div>
        </div>
        
        <!-- Product Info (Right Side - Sticky) -->
        <div class="col-lg-5">
            <div class="ps-lg-5 sticky-top" style="top: 100px; z-index: 1;">
                <nav aria-label="breadcrumb" class="mb-3">
                     <small class="text-muted text-uppercase fw-bold">
                        <a href="<?= BASE_URL ?>products.php" class="text-muted">Products</a> / 
                        <?= e($product['category_name']) ?>
                     </small>
                </nav>
            
                <h1 class="display-6 fw-black text-uppercase mb-2"><?= e($product['name']) ?></h1>
                
                <div class="mb-4">
                    <?php if ($product['discount_price']): ?>
                        <span class="fs-4 fw-bold me-2"><?= formatPrice($product['discount_price']) ?></span>
                        <span class="text-muted text-decoration-line-through"><?= formatPrice($product['price']) ?></span>
                        <span class="text-success small fw-bold ms-2"><?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>% OFF</span>
                    <?php else: ?>
                        <span class="fs-4 fw-bold"><?= formatPrice($product['price']) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Stock -->
                <?php if ($product['stock'] > 0): ?>
                    <div class="mb-4">
                        <form method="POST" action="<?= BASE_URL ?>cart.php" class="d-grid gap-3">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            
                            <!-- Size Selector -->
                            <div class="mb-4">
                                <label class="fw-bold small mb-2">Select Size (UK)</label>
                                <div class="d-flex flex-wrap gap-2" id="sizeSelector">
                                    <?php 
                                    $sizes = [6, 7, 8, 9, 10, 11];
                                    foreach ($sizes as $size): 
                                    ?>
                                        <input type="radio" class="btn-check" name="size" id="size_<?= $size ?>" value="<?= $size ?>" autocomplete="off">
                                        <label class="btn btn-outline-dark rounded-0 px-4" for="size_<?= $size ?>"><?= $size ?></label>
                                    <?php endforeach; ?>
                                </div>
                                <div id="sizeError" class="text-danger small mt-2 d-none">Please select a size</div>
                            </div>
                            
                            <button type="button" class="btn btn-primary btn-lg rounded-pill py-3 add-to-cart" data-product-id="<?= $product['id'] ?>">
                                Add to Bag
                            </button>
                            
                            <button type="button" id="productDetailWishlistBtn" class="btn btn-outline-secondary btn-lg rounded-pill py-3 wishlist-btn" 
                                    data-product-id="<?= $product['id'] ?>">
                                Favourite <i class="<?= isInWishlist($product['id']) ? 'fas text-danger' : 'far' ?> fa-heart ms-2"></i>
                            </button>
                        </form>
                    </div>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const btn = document.getElementById('productDetailWishlistBtn');
                        if(btn) {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation(); // Stop bubbling to prevent form issues
                                
                                const productId = this.dataset.productId;
                                const icon = this.querySelector('i');
                                
                                // Add animation class
                                icon.classList.add('fa-beat');
                                
                                fetch('ajax/toggle-wishlist.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'product_id=' + productId
                                })
                                .then(response => response.json())
                                .then(data => {
                                    icon.classList.remove('fa-beat');
                                    
                                    if (data.success) {
                                        if (data.action === 'added') {
                                            icon.classList.remove('far');
                                            icon.classList.add('fas', 'text-danger');
                                            if(typeof showToast === 'function') showToast('success', 'Added to Wishlist');
                                        } else {
                                            icon.classList.remove('fas', 'text-danger');
                                            icon.classList.add('far');
                                            if(typeof showToast === 'function') showToast('success', 'Removed from Wishlist');
                                        }
                                        
                                        // Update header count if function exists, else manual
                                        const badge = document.querySelector('.wishlist-count');
                                        if(badge) badge.textContent = data.count;
                                         if(data.count == 0 && badge) badge.remove();
                                    } else {
                                        if (data.message.includes('login')) {
                                            window.location.href = 'user/login.php';
                                        } else {
                                            if(typeof showToast === 'function') showToast('error', data.message);
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    icon.classList.remove('fa-beat');
                                });
                            });
                        }
                    });
                    </script>
                    <p class="small text-secondary"><i class="fas fa-circle text-success fs-xs me-1"></i> In Stock & Ready to Ship</p>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100 rounded-pill" disabled>Out of Stock</button>
                <?php endif; ?>
                
                <div class="mt-5 pt-4 border-top">
                    <p class="mb-2"><strong class="text-uppercase fs-sm">Product Details</strong></p>
                    <ul class="list-unstyled text-secondary small mb-4">
                        <li class="mb-1">Style: Casual / Sport</li>
                        <li class="mb-1">Material: Premium Synthetic / Mesh</li>
                        <li class="mb-1">Sole: Rubber non-slip</li>
                        <li>Imported</li>
                    </ul>
                    
                    <div class="accordion" id="shippingAccordion">
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 shadow-none bg-transparent fw-bold text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShipping">
                                    Free Delivery & Returns
                                </button>
                            </h2>
                            <div id="collapseShipping" class="accordion-collapse collapse" data-bs-parent="#shippingAccordion">
                                <div class="accordion-body px-0 text-secondary">
                                    Free standard delivery on all orders. Returns are accepted within 30 days of purchase.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Description/Reviews (Visible only on mobile) -->
        <div class="col-12 d-lg-none mt-5">
            <h4 class="mb-3 fw-bold">Description</h4>
            <p class="text-secondary"><?= e($product['description']) ?></p>
        </div>
    </div>
</div>

<script>
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
}
</script>

<?php require_once 'includes/footer.php'; ?>
