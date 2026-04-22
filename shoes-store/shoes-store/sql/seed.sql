-- ========================================
-- Seed Data for Shoes Store
-- ========================================

-- ========================================
-- Default Admin User
-- Email: admin@shoesstore.com
-- Password: Admin@123
-- ========================================
INSERT INTO users (email, password, first_name, last_name, role, is_active) VALUES
('admin@shoesstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 1);

-- ========================================
-- Sample Customer Accounts
-- Password for all: Test@123
-- ========================================
INSERT INTO users (email, password, first_name, last_name, phone, address, role, is_active) VALUES
('john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+91 98765 43210', '123 Main Street, Mumbai, Maharashtra 400001', 'customer', 1),
('jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '+91 98765 43211', '456 Park Avenue, Delhi, Delhi 110001', 'customer', 1);

-- ========================================
-- Categories
-- ========================================
INSERT INTO categories (name, slug, description) VALUES
('Men\'s Shoes', 'mens-shoes', 'Premium footwear collection for men including formal, casual, and sports shoes'),
('Women\'s Shoes', 'womens-shoes', 'Stylish and comfortable shoes for women featuring heels, flats, and sneakers'),
('Kids Shoes', 'kids-shoes', 'Durable and fun footwear for children of all ages'),
('Sports Shoes', 'sports-shoes', 'High-performance athletic shoes for running, training, and outdoor activities');

-- ========================================
-- Sample Products
-- ========================================
INSERT INTO products (category_id, name, slug, description, price, discount_price, stock, image, is_featured, is_active) VALUES
-- Men's Shoes
(1, 'Classic Leather Oxfords', 'classic-leather-oxfords', 'Timeless leather oxford shoes crafted from premium full-grain leather. Perfect for formal occasions and business meetings. Features cushioned insole and rubber sole for comfort.', 3999.00, 3499.00, 50, 'oxford-brown.jpg', 1, 1),
(1, 'Canvas Casual Sneakers', 'canvas-casual-sneakers', 'Lightweight canvas sneakers ideal for everyday wear. Breathable fabric upper with padded collar and comfortable rubber sole. Available in multiple colors.', 1899.00, NULL, 75, 'canvas-sneaker-blue.jpg', 0, 1),
(1, 'Premium Loafers', 'premium-loafers', 'Sophisticated slip-on loafers made from genuine leather. Perfect blend of comfort and style for both casual and semi-formal settings.', 2999.00, 2699.00, 40, 'loafer-black.jpg', 1, 1),
(1, 'Athletic Training Shoes', 'athletic-training-shoes-men', 'High-performance training shoes with responsive cushioning and excellent grip. Ideal for gym workouts and cross-training.', 4499.00, NULL, 60, 'training-men.jpg', 0, 1),

-- Women's Shoes
(2, 'Elegant High Heels', 'elegant-high-heels', 'Stunning stiletto heels perfect for parties and special occasions. Features cushioned footbed and non-slip sole for confident strides.', 3499.00, 2999.00, 35, 'heels-red.jpg', 1, 1),
(2, 'Comfort Flats', 'comfort-flats', 'All-day comfort flats with memory foam insole. Versatile design suitable for office wear and casual outings.', 1799.00, NULL, 80, 'flats-beige.jpg', 0, 1),
(2, 'Women\'s Running Shoes', 'womens-running-shoes', 'Lightweight running shoes with superior cushioning and arch support. Engineered for long-distance running and daily jogs.', 4999.00, 4499.00, 45, 'running-women.jpg', 1, 1),
(2, 'Fashion Ankle Boots', 'fashion-ankle-boots', 'Trendy ankle boots with block heel and side zipper. Perfect for autumn and winter fashion.', 3799.00, NULL, 30, 'boots-brown.jpg', 0, 1),

-- Kids Shoes
(3, 'Kids Sport Sneakers', 'kids-sport-sneakers', 'Durable and colorful sneakers designed for active kids. Features easy velcro straps and cushioned sole for growing feet.', 1499.00, 1299.00, 100, 'kids-sneaker.jpg', 1, 1),
(3, 'School Shoes Black', 'school-shoes-black', 'Classic black school shoes with reinforced toe and heel. Comfortable for all-day wear with easy-clean material.', 1299.00, NULL, 120, 'school-black.jpg', 0, 1),
(3, 'Cartoon Character Shoes', 'cartoon-character-shoes', 'Fun light-up shoes featuring popular cartoon characters. Kids will love the LED lights and comfortable fit.', 1699.00, NULL, 90, 'cartoon-shoes.jpg', 0, 1),

-- Sports Shoes
(4, 'Pro Running Shoes', 'pro-running-shoes', 'Professional-grade running shoes with advanced cushioning technology. Lightweight mesh upper for breathability and carbon rubber outsole.', 5999.00, 5499.00, 50, 'running-pro.jpg', 1, 1),
(4, 'Basketball Shoes Elite', 'basketball-shoes-elite', 'High-top basketball shoes with ankle support and excellent traction. Designed for explosive movements and quick cuts.', 6499.00, NULL, 35, 'basketball.jpg', 1, 1),
(4, 'Hiking Trail Shoes', 'hiking-trail-shoes', 'Rugged outdoor shoes built for trails and rough terrain. Waterproof construction with superior grip and toe protection.', 4799.00, 4299.00, 40, 'hiking.jpg', 0, 1),
(4, 'Tennis Court Shoes', 'tennis-court-shoes', 'Specialized tennis shoes with lateral support and durable outsole. Designed specifically for hard court surfaces.', 3999.00, NULL, 45, 'tennis.jpg', 0, 1);

-- ========================================
-- Sample Coupons
-- ========================================
INSERT INTO coupons (code, discount_type, discount_value, min_purchase, max_uses, valid_from, valid_until, is_active) VALUES
('WELCOME10', 'percentage', 10.00, 2000.00, 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1),
('FLAT500', 'fixed', 500.00, 5000.00, 100, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 1),
('SUMMER20', 'percentage', 20.00, 3000.00, 50, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 45 DAY), 1);
