# 👟 Kicks & Comfort - Shoes Store Website

A full-stack PHP + MySQL web application for an online shoes store with product management, user authentication, cart, wishlist, coupons, Razorpay payment, contact form, search, orders, and admin panel.

---
🌐 Live Demo
👉 Click here to visit the live site → https://kicks-comfort.wuaze.com/shoes-store/index.php
---


## 🚀 Features

- 🛍️ Product listing, search & detail pages
- 🔍 Search products by name or category
- 🔐 User registration, login, OTP verification & email activation
- 👤 User profile with photo upload/remove
- 🛒 Shopping cart (add, update, remove) via AJAX
- ❤️ Wishlist toggle
- 🎟️ Coupon / discount system
- 💳 Razorpay payment gateway integration
- 📦 Order management & order detail
- 📞 Contact Us form
- 💬 Messages & reviews
- 🔧 Admin panel (products, users, orders, coupons, reviews)
- 📧 Email notifications via PHPMailer
- 🔒 Security & session management

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Database | MySQL |
| Payment | Razorpay API |
| Email | PHPMailer |
| Server | Apache (XAMPP / WAMP) |

---

## ⚙️ Installation & Setup

### Prerequisites
- XAMPP / WAMP / MAMP installed
- PHP >= 7.4
- MySQL >= 5.7
- Razorpay account (for payment)

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/tusharsolanki7617/shoes-store.git
   ```

2. **Move to server directory**
   ```
   Copy the project folder to:
   - XAMPP: htdocs/shoes-store
   - WAMP:  www/shoes-store
   ```

3. **Create the database**
   ```sql
   CREATE DATABASE shoes_store;
   ```

4. **Import the database**
   - Open **phpMyAdmin**
   - Select `shoes_store` database
   - Click **Import** → select the `.sql` file from the `sql/` folder

5. **Configure database connection**
   - Open `config/config.php`
   - Update credentials:
   ```php
   $host     = "localhost";
   $user     = "root";
   $password = "";
   $database = "shoes_store";
   ```

6. **Configure Razorpay**
   - Open `config/config.php`
   - Add your Razorpay API keys:
   ```php
   define('RAZORPAY_KEY_ID',     'your_key_id_here');
   define('RAZORPAY_KEY_SECRET', 'your_key_secret_here');
   ```
   > Get your API keys from [Razorpay Dashboard](https://dashboard.razorpay.com/)

7. **Run the project**
   - Start Apache & MySQL from XAMPP
   - Visit: `http://localhost/shoes-store`

---

> ⚠️ Change the admin password after first login for security.

---

## 🎟️ Coupon Codes

| Coupon Code | Discount | Description |
|-------------|----------|-------------|
| `TUSHAR1000` | ₹1000 off | Special discount coupon |

> Admin can add/manage more coupons from the **Admin Panel → Coupons** section.

---

## 💳 Razorpay Payment

- Integrated with **Razorpay Payment Gateway**
- Supports UPI, Credit/Debit Cards, Net Banking, Wallets
- Test mode available using Razorpay test credentials
- Payment verification done server-side for security

**Test Card Details (Razorpay Test Mode):**
```
Card Number : 4111 1111 1111 1111
Expiry      : Any future date
CVV         : Any 3 digits
OTP         : 1234
```

---

## 🔍 Search Products

- Search bar available on the **header** of every page
- Search products by **name** or **category**
- Results shown on `search.php` with matching products
- Real-time filtering via `search.php?q=your+query`

---

## 📞 Contact Us

- Contact form available at `contact.php`
- Users can send messages directly from the website
- Messages are saved in the database
- Admin can view all messages from **Admin Panel → Messages**

---

## 📁 Project Structure

```
shoes-store/
│
├── admin/                      # Admin panel pages
│   ├── includes/
│   │   ├── admin-footer.php
│   │   ├── admin-header.php
│   │   └── admin-sidebar.php
│   ├── add-product.php
│   ├── coupons.php
│   ├── edit-product.php
│   ├── index.php
│   ├── messages.php
│   ├── order-detail.php
│   ├── orders.php
│   ├── products.php
│   ├── profile.php
│   ├── reviews.php
│   └── users.php
│
├── ajax/                       # AJAX handlers
│   ├── add-to-cart.php
│   ├── apply-coupon.php
│   ├── get-cart-count.php
│   ├── remove-from-cart.php
│   ├── remove-profile-photo.php
│   ├── toggle-wishlist.php
│   ├── update-cart.php
│   └── upload-profile-photo.php
│
├── assets/                     # Static assets
│   ├── css/
│   │   ├── animations.css
│   │   └── style.css
│   ├── images/
│   │   ├── products/
│   │   └── site/
│   └── js/
│       ├── lib/
│       ├── main.js
│       ├── validation.js
│       └── wishlist.js
│
├── config/
│   └── config.php              # DB, Razorpay & app configuration
│
├── includes/                   # Shared PHP components
│   ├── auth.php
│   ├── cart.php
│   ├── db.php
│   ├── email.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── security.php
│
├── sql/                        # Database SQL file
│
├── uploads/                    # User uploaded files
│   ├── products/
│   └── profiles/
│
├── user/                       # User account pages
│   ├── activate.php
│   ├── activate-pending.php
│   ├── change-password.php
│   ├── forgot-password.php
│   ├── login.php
│   ├── logout.php
│   ├── orders.php
│   ├── profile.php
│   ├── register.php
│   ├── reset-password.php
│   ├── setup-admin.php
│   ├── verify-otp.php
│   └── wishlist.php
│
├── vendor/                     # Composer dependencies (PHPMailer)
│
├── about.php
├── cart.php
├── checkout.php                # Razorpay payment integrated here
├── contact.php                 # Contact Us form
├── index.php
├── product-detail.php
├── products.php
├── search.php                  # Product search results
├── services.php
├── composer.json
└── README.md
```

---

## 👨‍💻 Author

**Tushar Solanki**
- GitHub: [@tusharsolanki7617](https://github.com/tusharsolanki7617)

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).
