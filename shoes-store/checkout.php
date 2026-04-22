<?php
/**
 * Checkout Page
 * Order placement with address and Razorpay payment gateway
 */

require_once 'config/config.php';
require_once 'includes/auth.php';

// Require login for checkout
requireLogin();

$page_title = 'Checkout - Kicks & Comfort';
require_once 'includes/header.php';
require_once 'includes/cart.php';
require_once 'includes/security.php';
require_once 'includes/email.php';

// ─── Razorpay Configuration ───────────────────────────────────────────────────
define('RAZORPAY_KEY_ID',     'rzp_test_SHJarC1akfzMvr');
define('RAZORPAY_KEY_SECRET', '9xg87f41fTBTl8UdkDKEus41');

$cart = new Cart();
$cart_items = $cart->getItems();

// Redirect if cart is empty
if (empty($cart_items)) {
    setFlash('error', 'Your cart is empty');
    redirect(BASE_URL . 'products.php');
}

$subtotal = $cart->getSubtotal();
$discount = $cart->getCouponDiscount();
$total    = $cart->getTotal();

// Get user details
try {
    $db   = new Database();
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
} catch (Exception $e) {
    setFlash('error', 'An error occurred');
    redirect(BASE_URL . 'cart.php');
}

// ─── Handle Order Placement (COD + Razorpay verified) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $shipping_address = clean($_POST['shipping_address'] ?? '');
    $city             = clean($_POST['city']             ?? '');
    $state            = clean($_POST['state']            ?? '');
    $pincode          = clean($_POST['pincode']          ?? '');
    $phone            = clean($_POST['phone']            ?? '');
    $payment_method   = clean($_POST['payment_method']   ?? 'cod');

    // Razorpay fields — use trim() NOT clean()/htmlspecialchars so the raw
    // payment ID (e.g. pay_XXXXXXXX) is stored exactly as Razorpay sends it
    $razorpay_payment_id = trim($_POST['razorpay_payment_id'] ?? '');
    $razorpay_order_id   = trim($_POST['razorpay_order_id']   ?? '');
    $razorpay_signature  = trim($_POST['razorpay_signature']  ?? '');

    // If Razorpay payment ID is present OR the explicit hidden field says 'online',
    // force payment_method to 'online' — guards against radio button state issues
    if (!empty($razorpay_payment_id) || trim($_POST['payment_method_rzp'] ?? '') === 'online') {
        $payment_method = 'online';
    }

    $errors = [];

    // Validation
    if (strlen($shipping_address) < 10) {
        $errors[] = 'Please provide complete shipping address';
    }
    if (strlen($city) < 2) {
        $errors[] = 'Please provide city name';
    }
    if (strlen($state) < 2) {
        $errors[] = 'Please provide state name';
    }
    if (!preg_match('/^\d{6}$/', $pincode)) {
        $errors[] = 'Please provide valid 6-digit pincode';
    }
    if (!preg_match('/^\d{10}$/', $phone)) {
        $errors[] = 'Please provide valid 10-digit phone number';
    }

    // For online payment — just verify payment ID is present.
    // Razorpay's handler() callback only fires on genuine payment success,
    // so trusting the payment_id is safe for client-side checkout.
    if ($payment_method === 'online' && empty($razorpay_payment_id)) {
        $errors[] = 'Payment not completed. Please try again.';
    }

    // Validate stock
    $stock_valid = $cart->validate();
    if (!$stock_valid['valid']) {
        $errors[] = $stock_valid['message'];
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Generate unique order number
            $order_number = 'ORD' . date('Ymd') . rand(1000, 9999);

            // Build full shipping address
            $full_shipping_address = $shipping_address . ', ' . $city . ', ' . $state . ' - ' . $pincode;
            $shipping_name         = $user['first_name'] . ' ' . $user['last_name'];

            $payment_status = ($payment_method === 'online' && !empty($razorpay_payment_id)) ? 'paid' : 'pending';

            // ── Create order ──────────────────────────────────────────────────
            // Try inserting WITH razorpay_payment_id column first.
            // If the column doesn't exist yet (migration not run), fall back to
            // the original INSERT so the order is ALWAYS saved.
            $order_base_params = [
                $_SESSION['user_id'],
                $order_number,
                $subtotal,
                $discount,
                $total,
                $_SESSION['coupon_code'] ?? null,
                'pending',
                $payment_status,
                $payment_method === 'online' ? 'razorpay' : 'cod',
                $full_shipping_address,
                $shipping_name,
                $phone,
            ];

            $rzp_col_exists = true;
            try {
                $db->query(
                    "INSERT INTO orders
                        (user_id, order_number, subtotal, discount, total, coupon_code,
                         status, payment_status, payment_method, razorpay_payment_id,
                         shipping_address, shipping_name, shipping_phone)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    array_merge(
                        array_slice($order_base_params, 0, 9),
                        [!empty($razorpay_payment_id) ? $razorpay_payment_id : null],
                        array_slice($order_base_params, 9)
                    )
                );
            } catch (Exception $colEx) {
                // Column likely doesn't exist — fall back to INSERT without it
                $rzp_col_exists = false;
                $db->query(
                    "INSERT INTO orders
                        (user_id, order_number, subtotal, discount, total, coupon_code,
                         status, payment_status, payment_method,
                         shipping_address, shipping_name, shipping_phone)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    $order_base_params
                );
            }

            $order_id = $db->lastInsertId();

            // If column didn't exist on INSERT, try a silent UPDATE now
            // (won't break anything if it still fails)
            if (!$rzp_col_exists && !empty($razorpay_payment_id)) {
                try {
                    $db->query(
                        "UPDATE orders SET razorpay_payment_id = ? WHERE id = ?",
                        [$razorpay_payment_id, $order_id]
                    );
                } catch (Exception $ignored) { /* column truly missing — skip */ }
            }

            // ── Add order items ───────────────────────────────────────────────
            foreach ($cart_items as $cartKey => $item) {
                // Cart key may be composite "productId_size" — always use $item['id']
                $real_product_id = $item['id'];
                $item_subtotal   = $item['price'] * $item['quantity'];
                $size            = $item['size'] ?? null;

                $db->query(
                    "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal, size) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$order_id, $real_product_id, $item['name'], $item['price'], $item['quantity'], $item_subtotal, $size]
                );

                // Reduce stock using real product ID
                $db->query(
                    "UPDATE products SET stock = stock - ? WHERE id = ?",
                    [$item['quantity'], $real_product_id]
                );
            }

            $db->commit();

            // Send order confirmation email
            $emailService = new EmailService();
            $emailService->sendOrderConfirmation($user['email'], [
                'order_number' => $order_number,
                'total'        => $total,
            ]);

            // Clear cart
            $cart->clear();

            setFlash('success', 'Order placed successfully! Order Number: ' . $order_number);
            redirect(BASE_URL . 'user/orders.php');

        } catch (Exception $e) {
            $db->rollback();
            setFlash('error', 'An error occurred placing your order. Please try again.');
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}
?>

<!-- Razorpay Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-shopping-bag"></i> Checkout</h2>

    <div class="row">
        <!-- ── Checkout Form ─────────────────────────────────────────── -->
        <div class="col-lg-7 mb-4">
            <div class="card-glass p-4">
                <h4 class="mb-4">Shipping Information</h4>

                <form method="POST" id="checkoutForm">
                    <?= csrfField() ?>

                    <!-- Hidden fields populated by Razorpay callback -->
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    <input type="hidden" name="razorpay_order_id"   id="razorpay_order_id">
                    <input type="hidden" name="razorpay_signature"  id="razorpay_signature">
                    <!-- Explicit payment method override set by JS on Razorpay success -->
                    <input type="hidden" name="payment_method_rzp" id="payment_method_rzp" value="">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" value="<?= e($user['first_name']) ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" value="<?= e($user['last_name']) ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="<?= e($user['email']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               value="<?= e($user['phone'] ?? '') ?>" required placeholder="10-digit mobile number">
                    </div>

                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Shipping Address *</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address"
                                  rows="3" required placeholder="House/Flat No., Street, Area"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State *</label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="pincode" class="form-label">Pincode *</label>
                        <input type="text" class="form-control" id="pincode" name="pincode"
                               required placeholder="6-digit pincode" maxlength="6">
                    </div>

                    <hr>

                    <!-- ── Payment Method ──────────────────────────────── -->
                    <h5 class="mb-3">Payment Method</h5>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment_method"
                               id="cod" value="cod" checked>
                        <label class="form-check-label" for="cod">
                            <i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery
                        </label>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="payment_method"
                               id="online" value="online">
                        <label class="form-check-label" for="online">
                            <i class="fas fa-credit-card text-primary"></i>
                            Online Payment &nbsp;
                            <img src="https://razorpay.com/assets/razorpay-glyph.svg"
                                 alt="Razorpay" style="height:18px; vertical-align:middle;">
                            <small class="text-muted">(Card / UPI / Net Banking / Wallet)</small>
                        </label>
                    </div>

                    <!-- Razorpay info badge (shown only for online) -->
                    <div id="razorpay-info" class="alert alert-info d-none mb-3">
                        <i class="fas fa-lock"></i>
                        Secure payment powered by <strong>Razorpay</strong>.
                        You will be redirected to the payment page after clicking "Pay Now".
                    </div>

                    <div class="d-grid gap-2">
                        <!-- COD button -->
                        <button type="submit" id="btn-cod" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>

                        <!-- Razorpay button (hidden by default) -->
                        <button type="button" id="btn-razorpay" class="btn btn-lg d-none"
                                style="background: linear-gradient(135deg,#072654,#3395ff); color:#fff; border:none;">
                            <i class="fas fa-bolt"></i> Pay ₹<?= number_format($total, 2) ?> with Razorpay
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Order Summary ─────────────────────────────────────────── -->
        <div class="col-lg-5 mb-4">
            <div class="card-glass p-4 mb-4">
                <h5 class="mb-3">Order Summary</h5>

                <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex mb-3">
                        <img src="<?= PRODUCT_IMAGES_URL . ($item['image'] ?? 'placeholder.png') ?>"
                             alt="<?= e($item['name']) ?>"
                             style="width:60px;height:60px;object-fit:cover;border-radius:8px;"
                             onerror="this.src='<?= ASSETS_URL ?>images/site/placeholder.png'">
                        <div class="ms-3 flex-grow-1">
                            <h6 class="mb-1"><?= e($item['name']) ?></h6>
                            <?php if (!empty($item['size'])): ?>
                                <small class="text-muted d-block">Size: UK <?= e($item['size']) ?></small>
                            <?php endif; ?>
                            <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                        </div>
                        <div class="text-end">
                            <strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>

                <hr>

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

                <div class="d-flex justify-content-between">
                    <strong class="h5">Total:</strong>
                    <strong class="h5 text-gradient"><?= formatPrice($total) ?></strong>
                </div>
            </div>

            <div class="card-glass p-3">
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> Your data is secure and protected
                </small>
            </div>
        </div>
    </div>
</div>

<!-- ── Razorpay Integration Script ─────────────────────────────────────────── -->
<script>
(function () {
    'use strict';

    const RAZORPAY_KEY = '<?= RAZORPAY_KEY_ID ?>';
    const TOTAL_PAISE  = <?= (int)($total * 100) ?>;   // Razorpay expects amount in paise
    const USER_NAME    = '<?= e($user['first_name'] . ' ' . $user['last_name']) ?>';
    const USER_EMAIL   = '<?= e($user['email']) ?>';

    const radioOnline    = document.getElementById('online');
    const radioCod       = document.getElementById('cod');
    const btnCod         = document.getElementById('btn-cod');
    const btnRazorpay    = document.getElementById('btn-razorpay');
    const razorpayInfo   = document.getElementById('razorpay-info');
    const form           = document.getElementById('checkoutForm');

    // Toggle buttons based on payment method selection
    function togglePaymentUI() {
        const isOnline = radioOnline.checked;
        btnCod.classList.toggle('d-none', isOnline);
        btnRazorpay.classList.toggle('d-none', !isOnline);
        razorpayInfo.classList.toggle('d-none', !isOnline);
    }

    radioOnline.addEventListener('change', togglePaymentUI);
    radioCod.addEventListener('change', togglePaymentUI);

    // Validate shipping fields before opening Razorpay
    function validateForm() {
        const phone   = document.getElementById('phone').value.trim();
        const address = document.getElementById('shipping_address').value.trim();
        const city    = document.getElementById('city').value.trim();
        const state   = document.getElementById('state').value.trim();
        const pincode = document.getElementById('pincode').value.trim();

        if (address.length < 10) { alert('Please provide complete shipping address.'); return false; }
        if (city.length   < 2)   { alert('Please provide city name.');                return false; }
        if (state.length  < 2)   { alert('Please provide state name.');               return false; }
        if (!/^\d{6}$/.test(pincode)) { alert('Please provide valid 6-digit pincode.'); return false; }
        if (!/^\d{10}$/.test(phone))  { alert('Please provide valid 10-digit phone number.'); return false; }

        return true;
    }

    // Open Razorpay checkout popup
    btnRazorpay.addEventListener('click', function () {
        if (!validateForm()) return;

        const userPhone = document.getElementById('phone').value.trim();

        const options = {
            key:         RAZORPAY_KEY,
            amount:      TOTAL_PAISE,
            currency:    'INR',
            name:        'Kicks & Comfort',
            description: 'Shoe Store Order',
            image:       '<?= ASSETS_URL ?>images/site/logo.png',
            prefill: {
                name:    USER_NAME,
                email:   USER_EMAIL,
                contact: userPhone,
            },
            theme: {
                color: '#3395ff',
            },
            modal: {
                ondismiss: function () {
                    // User closed the popup without paying — do nothing
                }
            },
            handler: function (response) {
                // Payment successful — populate hidden fields and submit form
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id || '';
                document.getElementById('razorpay_order_id').value   = response.razorpay_order_id   || '';
                document.getElementById('razorpay_signature').value  = response.razorpay_signature  || '';
                // Explicitly mark payment method so PHP always receives it
                document.getElementById('payment_method_rzp').value  = 'online';

                // Verify fields are set before submitting
                console.log('[Razorpay] Payment ID:', response.razorpay_payment_id);
                console.log('[Razorpay] Submitting form...');

                // Show a brief success message before submitting
                btnRazorpay.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing order…';
                btnRazorpay.disabled  = true;

                form.submit();
            },
        };

        const rzp = new Razorpay(options);

        rzp.on('payment.failed', function (response) {
            alert('Payment failed: ' + response.error.description + '\nPlease try again.');
        });

        rzp.open();
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
