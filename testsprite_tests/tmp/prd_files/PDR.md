# Product Requirements Document (PRD)
## PHP/MySQL Shoes Store Website

### Executive Summary
A full-stack e-commerce shoes store with user authentication, shopping cart, admin panel, and email notifications. Built with simplified single-file page architecture for easy development and seamless localhost-to-production deployment.

---

## 1. INTEGRATION STRATEGIES

### 1.1 Development-to-Production Integration Strategy

**Core Principle**: Zero-code-change deployment from XAMPP (localhost) to Panel (production)

#### Configuration-Driven Architecture
- **Single source of truth**: `config/config.php` controls all environment-specific settings
- **Auto-detection**: System automatically detects environment (localhost vs production)
- **Dynamic paths**: All file paths, URLs, and database connections reference config variables

#### Environment Detection Pattern
```php
// config/config.php determines environment automatically
$is_localhost = (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']));

if ($is_localhost) {
    // XAMPP settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'shoes_store');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', 'http://localhost/shoes-store/');
} else {
    // Production Panel settings (edit these before upload)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'production_db_name');
    define('DB_USER', 'production_user');
    define('DB_PASS', 'production_password');
    define('BASE_URL', 'https://yourdomain.com/');
}
```

#### Deployment Workflow
1. **Local Development**: Work in XAMPP with `DB_PASS = ''`
2. **Pre-Upload**: Edit only production block in `config/config.php`
3. **Upload**: Transfer entire project folder to Panel
4. **Auto-Run**: Website detects production environment and uses production credentials

---

### 1.2 Code Organization Integration Strategy

**Principle**: Each page is self-contained but shares common templates

#### Single-File Page Structure
Each page (e.g., `product-detail.php`) contains:
1. **Config inclusion**: `require_once 'config/config.php';`
2. **Backend logic**: Database queries, session handling, form processing
3. **Template usage**: Include reusable header/footer
4. **Frontend rendering**: HTML output with embedded PHP

#### Template Reusability Pattern
```
Common elements created once, reused everywhere:
- includes/header.php (navigation, CSS links)
- includes/footer.php (scripts, copyright)
- includes/db.php (database connection class)
- includes/functions.php (helper functions)
- includes/email.php (email sender class)
```

#### Page Implementation Example Structure
```php
<?php
// product-detail.php - Single file contains everything

// 1. BACKEND LOGIC SECTION
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$product_id = $_GET['id'] ?? 0;
$db = new Database();
$product = $db->query("SELECT * FROM products WHERE id = ?", [$product_id])->fetch();

// 2. TEMPLATE INCLUSION
include 'includes/header.php';
?>

<!-- 3. FRONTEND RENDERING -->
<div class="product-detail">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <!-- Product details here -->
</div>

<?php include 'includes/footer.php'; ?>
```

---

### 1.3 Database Integration Strategy

#### Migration-Free Schema Management
- **Single SQL file**: `sql/schema.sql` creates entire database structure
- **Seeding data**: `sql/seed.sql` populates initial data (admin user, sample products)
- **Version control**: Track schema changes in dated migration files

#### Import Process
```sql
-- For XAMPP: Import via phpMyAdmin
-- For Panel: Import via hosting control panel's MySQL tool
-- Both use same schema.sql file
```

---

### 1.4 Third-Party Service Integration

#### Email Service Integration
```php
// includes/email.php - Unified email handler
class EmailService {
    private $mailer_type; // 'smtp' or 'sendmail'
    
    public function __construct() {
        // Auto-select based on environment
        $this->mailer_type = IS_LOCALHOST ? 'sendmail' : 'smtp';
    }
    
    public function send($to, $subject, $body) {
        if ($this->mailer_type === 'smtp') {
            // Use PHPMailer for production
        } else {
            // Use PHP mail() for localhost testing
        }
    }
}
```

#### Payment Gateway Integration (Placeholder)
- **MVP**: Simulated payment with order status update
- **Enhancement**: Integrate Stripe/PayPal SDK
- **Strategy**: Payment gateway config in `config/config.php`

---

## 2. SYSTEM ARCHITECTURE

### 2.1 Technology Stack
- **Backend**: PHP 7.4+ / 8.x
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, Bootstrap 5.x
- **JavaScript**: jQuery 3.6+
- **Server**: Apache (XAMPP locally, cPanel/Plesk production)
- **Email**: PHPMailer (SMTP for production, mail() for localhost)

### 2.2 File Structure

```
shoes-store/
│
├── config/
│   └── config.php                 # GLOBAL CONFIGURATION (only file to edit for deployment)
│
├── includes/
│   ├── header.php                 # Common header template
│   ├── footer.php                 # Common footer template
│   ├── db.php                     # Database connection class
│   ├── functions.php              # Helper functions
│   ├── auth.php                   # Authentication functions
│   ├── cart.php                   # Cart management class
│   ├── email.php                  # Email service class
│   └── security.php               # CSRF, validation helpers
│
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── style.css              # Custom global styles
│   │   └── animations.css         # CSS animations
│   ├── js/
│   │   ├── jquery.min.js
│   │   ├── bootstrap.bundle.min.js
│   │   ├── validation.js          # jQuery form validation
│   │   └── main.js                # Custom JS
│   └── images/
│       ├── products/              # Product images
│       ├── users/                 # User profile photos
│       └── site/                  # Site assets (logo, banners)
│
├── uploads/
│   ├── products/                  # Uploaded product images
│   └── profiles/                  # User/admin profile photos
│
├── sql/
│   ├── schema.sql                 # Database structure
│   └── seed.sql                   # Sample data
│
├── admin/
│   ├── index.php                  # Admin dashboard
│   ├── login.php                  # Admin login
│   ├── products.php               # Product CRUD (list/add/edit/delete in one file)
│   ├── categories.php             # Category CRUD
│   ├── orders.php                 # Order management
│   ├── users.php                  # User management
│   ├── profile.php                # Admin profile with photo
│   └── change-password.php        # Admin password change
│
├── user/
│   ├── register.php               # User registration
│   ├── login.php                  # User login
│   ├── logout.php                 # User logout
│   ├── activate.php               # Email activation handler
│   ├── forgot-password.php        # Request password reset (email OTP)
│   ├── reset-password.php         # Reset password with OTP
│   ├── profile.php                # User profile with editable photo
│   └── change-password.php        # User password change
│
├── index.php                      # Homepage (storefront, featured products, search)
├── products.php                   # Product listing (filtering, sorting)
├── product-detail.php             # Individual product page (add-to-cart, reviews)
├── cart.php                       # Shopping cart management
├── checkout.php                   # Checkout process (coupon, payment placeholder)
├── services.php                   # Services page
├── about.php                      # About Us page
├── contact.php                    # Contact form (jQuery validation, email notification)
├── search.php                     # Search results page
└── .htaccess                      # URL rewriting (optional)
```

---

## 3. DATABASE SCHEMA

### 3.1 Entity Relationship Overview

**Core Entities**:
- Users (customers and admins)
- Products
- Categories
- Orders
- Order Items
- Reviews
- Coupons
- Cart (session-based, optional DB persistence)

### 3.2 Table Definitions

#### users
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    profile_photo VARCHAR(255),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    is_active TINYINT(1) DEFAULT 0,
    activation_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### categories
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### products
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    stock INT DEFAULT 0,
    image VARCHAR(255),
    gallery JSON,  -- Array of additional images
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### orders
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    coupon_code VARCHAR(50),
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order_number (order_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### order_items
```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### reviews
```sql
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### coupons
```sql
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_purchase DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT 0,  -- 0 = unlimited
    used_count INT DEFAULT 0,
    valid_from DATE,
    valid_until DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### contact_messages
```sql
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 4. PHASED DEVELOPMENT PLAN

### Phase 1: MVP (Minimal Viable Product)
**Timeline**: Weeks 1-3

#### Deliverables
1. **Core Infrastructure**
   - Global config file with environment detection
   - Database connection class with prepared statements
   - Header/footer templates with Bootstrap
   - Basic responsive navigation
   - Authentication helpers

2. **User-Facing Pages**
   - Homepage (featured products, search bar)
   - Product listing (basic display, pagination)
   - Product detail (add-to-cart button)
   - Shopping cart (view, update quantity, remove)
   - Checkout (collect shipping info, payment placeholder)
   - Contact form (jQuery validation, email notification)
   - About Us (static content)
   - Services (static content)

3. **User Authentication**
   - Registration with email activation link
   - Login/logout
   - Forgot password (email OTP)
   - Reset password with OTP
   - Basic profile page

4. **Admin Panel**
   - Admin login (separate from user login)
   - Dashboard (basic stats: products count, orders count)
   - Product CRUD (list, add, edit, delete)
   - Category management
   - Admin profile with password change

5. **Core Functionality**
   - Session-based cart management
   - Basic search (product name only)
   - Password hashing (password_hash/password_verify)
   - SQL injection prevention (prepared statements)
   - Input validation and sanitization

#### MVP Success Criteria
- User can browse products and add to cart
- User can register, activate account via email, and login
- User can complete checkout (simulated payment)
- Admin can manage products and categories
- Contact form sends emails
- Works on both XAMPP and production Panel without code changes

---

### Phase 2: Enhancements
**Timeline**: Weeks 4-6

#### Feature Additions

1. **Advanced Product Features**
   - Product filtering (category, price range, size)
   - Sorting (price low-to-high, popularity, newest)
   - Product image gallery (multiple images per product)
   - Stock management (prevent overselling)
   - Featured products section

2. **Reviews and Ratings**
   - Star rating system (1-5 stars)
   - User reviews with moderation (admin approval)
   - Average rating display on product cards
   - Review form with jQuery validation

3. **Coupon System**
   - Coupon CRUD in admin panel
   - Apply coupon at checkout
   - Validate coupon (expiry date, usage limits, min purchase)
   - Discount calculation (percentage or fixed amount)

4. **User Profile Enhancements**
   - Profile photo upload and crop
   - Order history view
   - Change password functionality
   - Update shipping address

5. **Admin Enhancements**
   - Order management (view, update status)
   - User management (list, view, deactivate)
   - Admin profile photo upload
   - Sales reports (total revenue, orders per day)
   - Contact message management

6. **UI/UX Polish**
   - CSS animations (fade-in, slide-in effects)
   - Responsive design for mobile/tablet
   - Loading spinners for AJAX actions
   - Toast notifications (success/error messages)
   - Breadcrumb navigation

7. **Security Hardening**
   - CSRF token implementation for forms
   - Session timeout and regeneration
   - Rate limiting for login attempts
   - XSS prevention (htmlspecialchars on outputs)
   - File upload validation (image types, size limits)

---

### Phase 3: Advanced Features (Optional)
**Timeline**: Weeks 7-8

1. **Payment Integration**
   - Stripe or PayPal SDK integration
   - Real payment processing
   - Payment confirmation emails

2. **Inventory Management**
   - Low stock alerts
   - Product variants (sizes, colors)
   - Bulk import/export

3. **Marketing Features**
   - Newsletter subscription
   - Abandoned cart emails
   - Product recommendations

4. **Analytics**
   - Google Analytics integration
   - Admin dashboard charts (sales trends)
   - Popular products tracking

---

## 5. KEY WORKFLOWS AND CODE PATTERNS

### 5.1 User Registration with Email Activation

**File**: `user/register.php`

**Workflow**:
1. User submits registration form
2. System validates input (jQuery frontend + PHP backend)
3. Password is hashed using `password_hash()`
4. Activation token generated (random 32-character string)
5. User record inserted with `is_active = 0`
6. Activation email sent with link: `activate.php?token=xxx`
7. User clicks link → `activate.php` verifies token → sets `is_active = 1`

**Code Pattern**:
```php
// register.php - Backend logic section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $activation_token = bin2hex(random_bytes(16));
    
    $db->query(
        "INSERT INTO users (email, password, activation_token, is_active) VALUES (?, ?, ?, 0)",
        [$email, $password, $activation_token]
    );
    
    $activation_link = BASE_URL . "user/activate.php?token=" . $activation_token;
    $emailService->send($email, "Activate Your Account", "Click here: " . $activation_link);
    
    redirect('register.php?success=1');
}
```

---

### 5.2 Password Reset via Email OTP

**Files**: `user/forgot-password.php`, `user/reset-password.php`

**Workflow**:
1. User enters email on forgot-password page
2. System generates 6-digit OTP and expiry timestamp (15 min)
3. OTP stored in `reset_token`, expiry in `reset_token_expiry`
4. OTP sent via email
5. User enters OTP on reset-password page
6. System validates OTP and expiry
7. User sets new password → OTP cleared

**Code Pattern**:
```php
// forgot-password.php - OTP generation
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

$db->query(
    "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?",
    [$otp, $expiry, $email]
);

$emailService->send($email, "Password Reset OTP", "Your OTP is: " . $otp);
```

```php
// reset-password.php - OTP validation
$user = $db->query(
    "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()",
    [$otp]
)->fetch();

if ($user) {
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $db->query(
        "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?",
        [$new_password, $user['id']]
    );
}
```

---

### 5.3 Shopping Cart Operations

**File**: `includes/cart.php` (Cart class)

**Pattern**: Session-based cart with optional localStorage sync

**Structure**:
```php
// Session cart structure
$_SESSION['cart'] = [
    'product_id_1' => ['quantity' => 2, 'price' => 59.99],
    'product_id_2' => ['quantity' => 1, 'price' => 89.99],
];
```

**Operations**:
```php
class Cart {
    public function addItem($product_id, $quantity = 1) {
        // Fetch product details from database
        // Add or update quantity in session
    }
    
    public function updateQuantity($product_id, $quantity) {
        // Update quantity in session
    }
    
    public function removeItem($product_id) {
        // Remove from session
    }
    
    public function getTotal() {
        // Calculate total from all items
    }
    
    public function applyCoupon($code) {
        // Validate coupon and apply discount
    }
    
    public function clear() {
        // Empty cart after order placement
    }
}
```

---

### 5.4 Product CRUD (Admin)

**File**: `admin/products.php` (Single file handles list/add/edit/delete)

**Pattern**: Action-based routing within same file

```php
// admin/products.php
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        // Display products table with edit/delete buttons
        break;
    
    case 'add':
        // Show add form (within same file)
        if ($_POST) {
            // Handle image upload
            // Insert into database
            redirect('products.php?action=list');
        }
        break;
    
    case 'edit':
        $id = $_GET['id'];
        // Fetch product
        // Show edit form
        if ($_POST) {
            // Update database
            redirect('products.php?action=list');
        }
        break;
    
    case 'delete':
        $id = $_GET['id'];
        // Delete product and associated images
        redirect('products.php?action=list');
        break;
}
```

---

### 5.5 Search and Filtering

**File**: `products.php`

**Pattern**: Dynamic SQL query building based on parameters

```php
// products.php - Backend logic
$category = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 99999;
$sort = $_GET['sort'] ?? 'newest';
$search = $_GET['q'] ?? '';

$query = "SELECT * FROM products WHERE is_active = 1";
$params = [];

if ($category) {
    $query .= " AND category_id = ?";
    $params[] = $category;
}

if ($search) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " AND price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;

switch ($sort) {
    case 'price_low': $query .= " ORDER BY price ASC"; break;
    case 'price_high': $query .= " ORDER BY price DESC"; break;
    default: $query .= " ORDER BY created_at DESC";
}

$products = $db->query($query, $params)->fetchAll();
```

---

### 5.6 Admin Access Control

**File**: `includes/auth.php`

**Pattern**: Session-based middleware

```php
// includes/auth.php
function requireAdmin() {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        redirect(BASE_URL . 'admin/login.php');
        exit;
    }
}

// Usage in admin pages
// admin/products.php
require_once '../config/config.php';
require_once '../includes/auth.php';
requireAdmin(); // Blocks non-admins
```

---

### 5.7 File Upload (Profile Photos)

**Pattern**: Validate, resize, save with unique name

```php
// user/profile.php - Photo upload
if (isset($_FILES['profile_photo'])) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $file = $_FILES['profile_photo'];
    
    if (in_array($file['type'], $allowed) && $file['size'] < 2000000) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('profile_') . '.' . $ext;
        $upload_path = '../uploads/profiles/' . $new_name;
        
        move_uploaded_file($file['tmp_name'], $upload_path);
        
        // Optional: Resize image using GD library
        // Update database
        $db->query("UPDATE users SET profile_photo = ? WHERE id = ?", [$new_name, $user_id]);
    }
}
```

---

## 6. RESPONSIVE DESIGN AND ANIMATIONS

### 6.1 Responsive Strategy
- **Bootstrap 5 Grid**: Mobile-first responsive layout
- **Breakpoints**: 
  - Mobile: < 576px
  - Tablet: 576px - 992px
  - Desktop: > 992px

### 6.2 CSS Animations

**File**: `assets/css/animations.css`

```css
/* Fade-in on page load */
.fade-in {
    animation: fadeIn 0.6s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Product card hover effect */
.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

/* Cart icon bounce on add */
.cart-bounce {
    animation: bounce 0.5s ease;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Loading spinner */
.spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
```

---

## 7. SECURITY IMPLEMENTATION

### 7.1 SQL Injection Prevention
- **Always use prepared statements**
- Never concatenate user input into SQL queries

```php
// BAD - Vulnerable to SQL injection
$query = "SELECT * FROM users WHERE email = '" . $_POST['email'] . "'";

// GOOD - Safe with prepared statements
$query = "SELECT * FROM users WHERE email = ?";
$result = $db->query($query, [$_POST['email']]);
```

### 7.2 XSS Prevention
- **Escape all user-generated output** with `htmlspecialchars()`

```php
// Display user input safely
echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
```

### 7.3 CSRF Protection

**File**: `includes/security.php`

```php
// Generate CSRF token
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Add to forms
<input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

// Validate on submission
if (!validateCsrfToken($_POST['csrf_token'])) {
    die('Invalid CSRF token');
}
```

### 7.4 Password Security
- **Use PHP's native password functions**

```php
// Hashing (registration)
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Verification (login)
if (password_verify($input_password, $stored_hash)) {
    // Login successful
}
```

---

## 8. JQUERY FORM VALIDATION

**File**: `assets/js/validation.js`

```javascript
// Contact form validation
$(document).ready(function() {
    $('#contactForm').on('submit', function(e) {
        let isValid = true;
        
        // Name validation
        let name = $('#name').val().trim();
        if (name.length < 3) {
            showError('#name', 'Name must be at least 3 characters');
            isValid = false;
        }
        
        // Email validation
        let email = $('#email').val().trim();
        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('#email', 'Invalid email address');
            isValid = false;
        }
        
        // Message validation
        let message = $('#message').val().trim();
        if (message.length < 10) {
            showError('#message', 'Message must be at least 10 characters');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    function showError(selector, message) {
        $(selector).addClass('is-invalid');
        $(selector).siblings('.invalid-feedback').text(message);
    }
});
```

---

## 9. DEPLOYMENT INSTRUCTIONS

### 9.1 Localhost Setup (XAMPP)

1. **Install XAMPP**
   - Download from https://www.apachefriends.org/
   - Install Apache and MySQL modules

2. **Project Setup**
   - Clone/copy project to `C:\xampp\htdocs\shoes-store\`
   - Start Apache and MySQL in XAMPP Control Panel

3. **Database Setup**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create database: `shoes_store`
   - Import `sql/schema.sql`
   - Import `sql/seed.sql` (optional sample data)

4. **Configuration**
   - `config/config.php` automatically detects localhost
   - No changes needed for local development

5. **Access Application**
   - Frontend: http://localhost/shoes-store/
   - Admin: http://localhost/shoes-store/admin/

6. **Default Admin Login** (from seed.sql)
   - Email: admin@shoesstore.com
   - Password: Admin@123

### 9.2 Production Deployment (Panel)

1. **Pre-Upload Preparation**
   - Edit `config/config.php` production block:
     ```php
     define('DB_NAME', 'your_cpanel_db_name');
     define('DB_USER', 'your_cpanel_db_user');
     define('DB_PASS', 'your_cpanel_db_password');
     define('BASE_URL', 'https://yourdomain.com/');
     ```

2. **Upload Files**
   - Compress project folder to ZIP
   - Upload via cPanel File Manager or FTP
   - Extract in public_html/ or subdomain directory

3. **Database Setup**
   - Create MySQL database in cPanel
   - Create MySQL user and grant all privileges
   - Import `sql/schema.sql` via phpMyAdmin
   - Import `sql/seed.sql` (if needed)

4. **Permissions**
   - Set `uploads/` folder to 755
   - Set `uploads/products/` and `uploads/profiles/` to 755

5. **Email Configuration**
   - Update SMTP settings in `config/config.php` for production emails
   - Or use hosting provider's mail() function

6. **Testing**
   - Access website via domain URL
   - Test user registration (email activation)
   - Test admin login
   - Verify file uploads work

7. **SSL Certificate**
   - Install free SSL via cPanel (Let's Encrypt)
   - Force HTTPS in `.htaccess`

---

## 10. CODE ORGANIZATION BEST PRACTICES

### 10.1 Database Class Pattern

**File**: `includes/db.php`

```php
class Database {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
```

### 10.2 Helper Functions Pattern

**File**: `includes/functions.php`

```php
// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Flash message
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Sanitize input
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Format currency
function formatPrice($price) {
    return '$' . number_format($price, 2);
}
```

### 10.3 Template Inclusion Pattern

**File**: `includes/header.php`

```php
<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Shoes Store' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/animations.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <!-- Navigation content -->
    </nav>
```

**File**: `includes/footer.php`

```php
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?= date('Y') ?> Shoes Store. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="<?= BASE_URL ?>assets/js/jquery.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/validation.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
```

---

## 11. SAMPLE DATA (seed.sql)

```sql
-- Admin user (password: Admin@123)
INSERT INTO users (email, password, first_name, last_name, role, is_active) VALUES
('admin@shoesstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 1);

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Men\'s Shoes', 'mens-shoes', 'Footwear for men'),
('Women\'s Shoes', 'womens-shoes', 'Footwear for women'),
('Kids Shoes', 'kids-shoes', 'Footwear for children'),
('Sports Shoes', 'sports-shoes', 'Athletic and sports footwear');

-- Sample products
INSERT INTO products (category_id, name, slug, description, price, stock, is_featured) VALUES
(1, 'Classic Oxford Shoes', 'classic-oxford-shoes', 'Elegant leather oxford shoes', 129.99, 50, 1),
(2, 'High Heel Pumps', 'high-heel-pumps', 'Stylish high heel pumps', 89.99, 30, 1),
(3, 'Kids Sneakers', 'kids-sneakers', 'Comfortable sneakers for kids', 49.99, 100, 0),
(4, 'Running Shoes Pro', 'running-shoes-pro', 'Professional running shoes', 159.99, 40, 1);

-- Sample coupon
INSERT INTO coupons (code, discount_type, discount_value, min_purchase, valid_from, valid_until) VALUES
('WELCOME10', 'percentage', 10.00, 50.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY));
```

---

## 12. SUCCESS METRICS

### MVP Success Criteria
- [ ] User can register and activate account via email
- [ ] User can browse and filter products
- [ ] User can add products to cart and checkout
- [ ] Admin can login and manage products
- [ ] Contact form sends emails successfully
- [ ] Zero code changes needed between localhost and production
- [ ] All forms have jQuery validation
- [ ] Responsive on mobile, tablet, desktop

### Enhancement Success Criteria
- [ ] Reviews system functional with moderation
- [ ] Coupon system working with validation
- [ ] Advanced search and filtering operational
- [ ] Profile photo uploads working
- [ ] Admin can manage orders and users
- [ ] CSS animations enhance UX
- [ ] Security measures implemented (CSRF, XSS prevention)

---

## 13. MAINTENANCE AND EXTENSIBILITY

### Code Maintenance Tips
1. **Single Configuration**: Only edit `config/config.php` for environment changes
2. **Template Reuse**: Update header/footer once, affects all pages
3. **Database Changes**: Add new SQL files in `sql/` folder with version numbers
4. **Security Updates**: Regularly update password hashing algorithm if needed

### Future Extensibility
- **API Layer**: Add `api/` folder for REST endpoints (mobile app integration)
- **Multi-language**: Add language files in `lang/` folder
- **Wishlist Feature**: Add `wishlists` table and user wishlist page
- **Social Login**: Integrate OAuth (Google, Facebook) in `includes/auth.php`

---

## 14. CONCLUSION

This PRD provides a complete blueprint for building a production-ready PHP/MySQL shoes store with:

✅ **Zero-code-change deployment** from localhost to production  
✅ **Single-file page architecture** for easy development  
✅ **Phased development** (MVP → Enhancements)  
✅ **Security-first approach** (prepared statements, password hashing, CSRF)  
✅ **Template reusability** to avoid code duplication  
✅ **Responsive design** with CSS animations  
✅ **Complete database schema** with relationships  
✅ **Clear folder structure** for maintainability  

**Next Steps**:
1. Set up XAMPP environment
2. Create database and import schema
3. Build MVP features (Weeks 1-3)
4. Test thoroughly on localhost
5. Deploy to production Panel
6. Implement enhancements (Weeks 4-6)

---

**Document Version**: 1.0  
**Last Updated**: February 8, 2026  
**Author**: Claude (Anthropic)