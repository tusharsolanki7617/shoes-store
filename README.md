# Kicks & Comfort - Shoes Store Website

## Built Features

### ✅ Core Infrastructure
- Environment auto-detection (localhost/production)
- PDO database connection class
- Helper functions (sanitization, formatting, redirects, etc.)
- Authentication system with session management
- Security features (CSRF, XSS protection, rate limiting)
- Email service with templated emails
- Shopping cart system with coupon support

### ✅ Database
- 8 database tables
- Sample data with admin user and products
- Foreign key relationships

### ✅ Frontend Assets
- Modern premium CSS with gradients and glassmorphism
- Comprehensive animations library
- jQuery form validation
- AJAX cart operations
- Responsive design

### ✅ User Pages
- Homepage with hero and featured products
- Product listing with filters and sorting
- Product detail with reviews
- Shopping cart
- About Us
- Services/FAQ
- Contact form with email
- Search functionality

### ✅ Authentication
- User registration with email activation
- Login with rate limiting
- Logout
- Account activation handler

### ⚠️ In Progress / TODO
- Password reset flow (forgot/reset pages)
- User profile and change password pages
- Checkout page
- Admin panel (all pages)
- Reviews system
- Additional AJAX features

## Quick Start

### 1. Database Setup

```sql
-- Create database
CREATE DATABASE shoes_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema
mysql -u root shoes_store < sql/schema.sql

-- Import seed data
mysql -u root shoes_store < sql/seed.sql
```

### 2. Configuration

The `config/config.php` automatically detects environment:
- **Localhost**: Uses 'localhost' detection
- **Production**: Update database credentials for your hosting

### 3. Default Admin Credentials

```
Email: admin@shoesstore.com
Password: Admin@123
```

### 4. Folder Permissions

```bash
chmod 755 uploads/products
chmod 755 uploads/profiles
```

### 5. Email Configuration

For **production**, update SMTP settings in `config/config.php`:
- SMTP host, port, username, password

For **localhost**, emails are simulated (check activation links in browser).

## Project Structure

```
shoes-store/
├── config/              # Configuration
├── includes/            # Core PHP files
├── assets/              # CSS, JS, images
├── ajax/                # AJAX handlers
├── sql/                 # Database files
├── user/                # User authentication pages
├── admin/               # Admin panel (TODO)
├── uploads/             # User uploads
├── index.php            # Homepage
├── products.php         # Product listing
├── product-detail.php   # Product details
├── cart.php             # Shopping cart
├── search.php           # Search results
├── contact.php          # Contact form
├── about.php            # About page
└── services.php         # Services/FAQ
```

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: jQuery 3.6+
- **Icons**: Font Awesome 6

##Zero-Code-Change Deployment

Simply upload files to production server - no code changes needed!
The system automatically detects the environment and adjusts settings.

## Sample Coupons

- `WELCOME10` - 10% off
- `FLAT500` - ₹500 flat discount  
- `SUMMER20` - 20% off

## Next Steps

1. ✅ Test database import
2. ✅ Verify homepage loads
3. ⚠️ Complete checkout flow
4. ⚠️ Build admin panel
5. ⚠️ Generate/upload product images
6. ⚠️ Configure production email

---

**Note**: This is a work-in-progress build. Core functionality is complete, but admin panel and some user features are still being developed.
