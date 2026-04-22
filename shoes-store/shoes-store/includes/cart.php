<?php
/**
 * Shopping Cart Class
 * Session-based cart management
 */

class Cart {
    private $db;
    
    public function __construct() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $this->db = new Database();
    }
    
    /**
     * Add item to cart
     * @param int $productId Product ID
     * @param int $quantity Quantity to add
     * @param string $size Size (optional)
     * @return bool Success status
     */
    public function addItem($productId, $quantity = 1, $size = null) {
        try {
            // Fetch product details
            $product = $this->db->fetchOne(
                "SELECT id, name, price, discount_price, stock, image FROM products WHERE id = ? AND is_active = 1",
                [$productId]
            );
            
            if (!$product) {
                return false;
            }
            
            // Check stock
            if ($product['stock'] < $quantity) {
                return false;
            }
            
            // Determine price (use discount price if available)
            $price = $product['discount_price'] ?? $product['price'];
            
            // Create unique key for product + size
            $cartKey = $productId;
            if ($size) {
                $cartKey .= '_' . $size;
            }

            // If item already exists, update quantity
            if (isset($_SESSION['cart'][$cartKey])) {
                $newQuantity = $_SESSION['cart'][$cartKey]['quantity'] + $quantity;
                
                // Check stock again (Note: This checks total stock of product, not per size as size stock isn't tracked yet)
                if ($newQuantity > $product['stock']) {
                    return false;
                }
                
                $_SESSION['cart'][$cartKey]['quantity'] = $newQuantity;
            } else {
                // Add new item
                $_SESSION['cart'][$cartKey] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'image' => $product['image'],
                    'size' => $size
                ];
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Update item quantity
     * @param string $itemKey Item Key (ID or Composite)
     * @param int $quantity New quantity
     * @return bool Success status
     */
    public function updateQuantity($itemKey, $quantity) {
        if (!isset($_SESSION['cart'][$itemKey])) {
            return false;
        }
        
        if ($quantity <= 0) {
            return $this->removeItem($itemKey);
        }
        
        try {
            // Get product ID from stored item
            $productId = $_SESSION['cart'][$itemKey]['id'];
            
            // Check stock
            $product = $this->db->fetchOne("SELECT stock FROM products WHERE id = ?", [$productId]);
            
            if (!$product || $quantity > $product['stock']) {
                return false;
            }
            
            $_SESSION['cart'][$itemKey]['quantity'] = $quantity;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Remove item from cart
     * @param string $itemKey Item Key
     * @return bool Success status
     */
    public function removeItem($itemKey) {
        if (isset($_SESSION['cart'][$itemKey])) {
            unset($_SESSION['cart'][$itemKey]);
            return true;
        }
        return false;
    }
    
    /**
     * Get all cart items
     * @return array Cart items
     */
    public function getItems() {
        return $_SESSION['cart'] ?? [];
    }
    
    /**
     * Get cart item count
     * @return int Item count
     */
    public function getCount() {
        return count($_SESSION['cart'] ?? []);
    }
    
    /**
     * Get cart subtotal
     * @return float Subtotal
     */
    public function getSubtotal() {
        $subtotal = 0;
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }
    
    /**
     * Apply coupon code
     * @param string $code Coupon code
     * @return array Result with success, discount, and message
     */
    public function applyCoupon($code) {
        try {
            $coupon = $this->db->fetchOne(
                "SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND valid_from <= CURDATE() AND valid_until >= CURDATE()",
                [$code]
            );
            
            if (!$coupon) {
                return ['success' => false, 'message' => 'Invalid or expired coupon code'];
            }
            
            // Check usage limit
            if ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses']) {
                return ['success' => false, 'message' => 'Coupon usage limit reached'];
            }
            
            // Check minimum purchase
            $subtotal = $this->getSubtotal();
            if ($subtotal < $coupon['min_purchase']) {
                return [
                    'success' => false,
                    'message' => 'Minimum purchase of ' . formatPrice($coupon['min_purchase']) . ' required'
                ];
            }
            
            // Calculate discount
            $discount = 0;
            if ($coupon['discount_type'] === 'percentage') {
                $discount = ($subtotal * $coupon['discount_value']) / 100;
            } else {
                $discount = $coupon['discount_value'];
            }
            
            // Store coupon in session
            $_SESSION['coupon'] = [
                'code' => $coupon['code'],
                'discount' => $discount
            ];
            
            return [
                'success' => true,
                'discount' => $discount,
                'message' => 'Coupon applied successfully!'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error applying coupon'];
        }
    }
    
    /**
     * Remove coupon
     */
    public function removeCoupon() {
        unset($_SESSION['coupon']);
    }
    
    /**
     * Get coupon discount
     * @return float Discount amount
     */
    public function getCouponDiscount() {
        return $_SESSION['coupon']['discount'] ?? 0;
    }
    
    /**
     * Get total (subtotal - discount)
     * @return float Total amount
     */
    public function getTotal() {
        return max(0, $this->getSubtotal() - $this->getCouponDiscount());
    }
    
    /**
     * Clear cart
     */
    public function clear() {
        $_SESSION['cart'] = [];
        unset($_SESSION['coupon']);
    }
    
    /**
     * Validate cart (check stock availability)
     * @return array Result with valid status and message
     */
    public function validate() {
        foreach ($_SESSION['cart'] ?? [] as $cartKey => $item) {
            // Cart key may be composite: "productId_size" — extract real product ID
            $realProductId = $item['id'] ?? explode('_', $cartKey)[0];

            $product = $this->db->fetchOne(
                "SELECT stock, is_active FROM products WHERE id = ?",
                [$realProductId]
            );
            
            if (!$product || !$product['is_active']) {
                return [
                    'valid' => false,
                    'message' => $item['name'] . ' is no longer available'
                ];
            }
            
            if ($product['stock'] < $item['quantity']) {
                return [
                    'valid' => false,
                    'message' => 'Insufficient stock for ' . $item['name']
                ];
            }
        }
        
        return ['valid' => true, 'message' => ''];
    }
}
