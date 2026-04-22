<?php
/**
 * Shopping Cart Page
 */

require_once 'config/config.php';
$page_title = 'Shopping Cart - Kicks & Comfort';
require_once 'includes/header.php';
require_once 'includes/cart.php';

$cart = new Cart();
$cart_items = $cart->getItems();
$subtotal = $cart->getSubtotal();
$discount = $cart->getCouponDiscount();
$total = $cart->getTotal();
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-shopping-cart"></i> Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        <!-- Empty Cart -->
        <div class="card-glass p-5 text-center">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Add some amazing shoes to get started!</p>
            <a href="<?= BASE_URL ?>products.php" class="btn btn-primary btn-lg mt-3">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <!-- Cart Items -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card-glass p-4">
                    <!-- Desktop Header -->
                    <div class="d-none d-md-flex text-muted small text-uppercase fw-bold mb-3 px-2 border-bottom pb-2">
                        <div style="flex: 2;">Product</div>
                        <div style="flex: 1;" class="text-center">Price</div>
                        <div style="flex: 1;" class="text-center">Quantity</div>
                        <div style="flex: 1;" class="text-end">Subtotal</div>
                        <div style="width: 40px;"></div>
                    </div>
                    
                    <!-- Cart Items Grid -->
                    <div class="cart-items-wrapper">
                        <?php foreach ($cart_items as $item_id => $item): ?>
                            <div class="d-flex flex-column flex-md-row align-items-md-center py-3 border-bottom position-relative cart-item-row">
                                <!-- Mobile Delete Button (Top Right) -->
                                <button class="btn btn-sm btn-outline-danger remove-from-cart position-absolute top-0 end-0 mt-3 d-md-none border-0" data-item-key="<?= $item_id ?>">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <!-- Product Info -->
                                <div style="flex: 2;" class="d-flex align-items-center mb-3 mb-md-0 pe-4 pe-md-0">
                                    <img src="<?= PRODUCT_IMAGES_URL . ($item['image'] ?? 'placeholder.png') ?>" 
                                         alt="<?= e($item['name']) ?>" 
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                                         loading="lazy"
                                         onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                                    <div class="ms-3">
                                        <h6 class="mb-1 text-dark"><?= e($item['name']) ?></h6>
                                        <?php if (!empty($item['size'])): ?>
                                            <small class="text-muted d-block">Size: UK <?= e($item['size']) ?></small>
                                        <?php endif; ?>
                                        <span class="d-md-none small text-primary fw-bold mt-1 d-block"><?= formatPrice($item['price']) ?></span>
                                    </div>
                                </div>

                                <!-- Controls Wrapper -->
                                <div class="d-flex justify-content-between align-items-center w-100" style="flex: 3;">
                                    
                                    <!-- Desktop Price -->
                                    <div style="flex: 1;" class="text-center text-muted fw-medium d-none d-md-block">
                                        <?= formatPrice($item['price']) ?>
                                    </div>
                                    
                                    <!-- Quantity Input -->
                                    <div style="flex: 1;" class="d-flex justify-content-start justify-content-md-center">
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary btn-sm qty-decrease" type="button">-</button>
                                            <input type="number" class="form-control form-control-sm cart-quantity qty-input" 
                                                   value="<?= $item['quantity'] ?>" min="1" max="100"
                                                   data-item-key="<?= $item_id ?>"
                                                   style="color: #000 !important; -webkit-text-fill-color: #000 !important; text-align: center; width: 40px !important; padding: 0.25rem !important; font-size: 15px !important;">
                                            <button class="btn btn-outline-secondary btn-sm qty-increase" type="button">+</button>
                                        </div>
                                    </div>

                                    <!-- Desktop Subtotal -->
                                    <div style="flex: 1;" class="text-end fw-bold d-none d-md-block">
                                        <?= formatPrice($item['price'] * $item['quantity']) ?>
                                    </div>
                                    
                                    <!-- Desktop Delete -->
                                    <div style="width: 40px;" class="text-end d-none d-md-block">
                                        <button class="btn btn-sm text-danger remove-from-cart border-0 bg-transparent" data-item-key="<?= $item_id ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>products.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-lg-4 mb-4">
                <div class="card-glass p-4">
                    <h5 class="mb-4">Order Summary</h5>
                    
                    <!-- Coupon Code -->
                    <div class="mb-4">
                        <label class="form-label">Have a coupon?</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="couponCode" placeholder="Enter code">
                            <button class="btn btn-outline-primary" type="button" id="applyCoupon">Apply</button>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Price Breakdown -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?= formatPrice($subtotal) ?></span>
                    </div>
                    
                    <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount:</span>
                            <span>-<?= formatPrice($discount) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span class="text-success">Free</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <strong class="h5">Total:</strong>
                        <strong class="h5 text-gradient"><?= formatPrice($total) ?></strong>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>checkout.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt"></i> Secure Checkout
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Fix quantity input visibility - override .text-gradient inheritance */
.cart-quantity.qty-input,
.cart-quantity.qty-input:focus,
.cart-quantity.qty-input:active,
input.cart-quantity.qty-input[type="number"] {
    color: #212529 !important;
    background-color: #fff !important;
    -webkit-text-fill-color: #212529 !important;
    -webkit-background-clip: initial !important;
    background-clip: initial !important;
    background: #fff !important;
    opacity: 1 !important;
    font-weight: 500 !important;
}

.cart-quantity.qty-input::-webkit-inner-spin-button,
.cart-quantity.qty-input::-webkit-outer-spin-button {
    opacity: 1 !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>
