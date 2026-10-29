# ElectroHub - E-Commerce Electronics Store
## Complete Setup Guide for XAMPP

---

## 📋 Project Overview

**ElectroHub** is a full-featured e-commerce web application built for electronic products. It includes a customer-facing storefront and a complete admin panel.

---

## 🛠 Requirements

- XAMPP (Apache + MySQL + PHP 7.4+)
- VS Code (recommended)
- Web Browser (Chrome, Firefox, Edge)

---

## 🚀 Installation Steps

### Step 1 — Copy Project Files

1. Download and extract the project ZIP
2. Copy the entire `ecommerce` folder to your XAMPP htdocs directory:
   - **Windows:** `C:\xampp\htdocs\ecommerce`
   - **Mac:** `/Applications/XAMPP/htdocs/ecommerce`
   - **Linux:** `/opt/lampp/htdocs/ecommerce`

### Step 2 — Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** (click Start)
3. Start **MySQL** (click Start)
4. Verify both show green status

### Step 3 — Create the Database

1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in the left sidebar
3. Enter database name: `ecommerce_electronics`
4. Click **"Create"**
5. Click the **"Import"** tab at the top
6. Click **"Choose File"** and select `ecommerce/database/ecommerce.sql`
7. Click **"Go"** at the bottom

> ✅ The SQL file will create all tables and insert sample products automatically.

### Step 4 — Configure Database (if needed)

Open `ecommerce/includes/config.php` and verify these settings match your XAMPP:

```php
define('DB_HOST', 'localhost');   // Usually 'localhost'
define('DB_USER', 'root');        // Default XAMPP username
define('DB_PASS', '');            // Default XAMPP password (empty)
define('DB_NAME', 'ecommerce_electronics');
define('SITE_URL', 'http://localhost/ecommerce');
```

### Step 5 — Access the Application

| URL | Description |
|-----|-------------|
| `http://localhost/ecommerce` | Main storefront (Home) |
| `http://localhost/ecommerce/admin` | Admin panel |

---

## 🔐 Default Login Credentials

### Customer Account
| Field | Value |
|-------|-------|
| Email | `john@example.com` |
| Password | `password` |

### Admin Account
| Field | Value |
|-------|-------|
| Email | `admin@electrohub.com` |
| Password | `password` |

> ⚠️ **Important:** Change these passwords immediately after setup for any production use.

---

## 📁 Folder Structure

```
ecommerce/
├── index.php                   # Home page
├── products.php                # Product listing / shop
├── product.php                 # Single product detail
├── cart.php                    # Shopping cart
├── checkout.php                # Checkout
├── order-confirmation.php      # Order success
├── login.php                   # User login
├── register.php                # User registration
├── dashboard.php               # User dashboard
├── orders.php                  # User order history
├── profile.php                 # Edit profile
├── about.php                   # About Us page
├── contact.php                 # Contact page
├── logout.php                  # Logout handler
│
├── assets/
│   ├── css/
│   │   └── style.css           # Main stylesheet
│   ├── js/
│   │   └── main.js             # Main JavaScript
│   └── images/
│       ├── no-image.png        # Fallback image
│       └── products/           # Product images go here
│
├── includes/
│   ├── config.php              # DB config + site constants
│   ├── functions.php           # All helper functions
│   ├── header.php              # Navigation header
│   └── footer.php              # Footer
│
├── admin/
│   ├── login.php               # Admin login
│   ├── logout.php              # Admin logout
│   ├── header.php              # Admin layout header
│   ├── footer.php              # Admin layout footer
│   ├── dashboard.php           # Admin dashboard + stats
│   ├── products.php            # Product list (CRUD)
│   ├── add-product.php         # Add new product
│   ├── edit-product.php        # Edit product
│   ├── categories.php          # Category management
│   ├── orders.php              # Order management
│   ├── view-order.php          # View/update single order
│   ├── users.php               # User management
│   └── reports.php             # Sales reports + charts
│
├── ajax/
│   └── cart.php                # AJAX cart handler
│
└── database/
    └── ecommerce.sql           # Full database dump
```

---

## ✨ Features Overview

### 👤 Customer Features
- User Registration & Login (hashed passwords)
- User Dashboard with order stats
- Profile Management (update info, change password)
- Browse all products with filters (category, brand, price)
- Product search
- Product detail pages with specs tabs
- Related products
- Add to Cart (AJAX, no page reload)
- Update/Remove cart items
- Checkout with shipping form
- Multiple payment methods (COD, Card, PayPal, Bank)
- Order placement & confirmation
- Order history with tracking status
- Responsive design (mobile-friendly)

### 🛡️ Admin Features
- Secure admin login (separate session)
- Dashboard with revenue charts, top products, recent orders
- Product management (add/edit/delete with image upload)
- Specifications builder
- Category management with icons
- Order management (filter by status, date range)
- Quick order status updates
- Customer management (view, suspend/activate)
- Sales reports with date range filtering
- Revenue by category & payment method breakdowns

---

## 🔒 Security Features

| Feature | Implementation |
|---------|---------------|
| Password Hashing | PHP `password_hash()` / `password_verify()` |
| SQL Injection Prevention | `real_escape_string()` on all inputs |
| XSS Prevention | `htmlspecialchars()` on all outputs |
| Session Management | Separate user/admin sessions |
| Form Validation | Client-side + server-side validation |
| CSRF Protection | POST forms with server-side validation |

---

## 🎨 Adding Product Images

Product images should be placed in: `assets/images/products/`

- Recommended size: **600×600 pixels** or larger (square)
- Supported formats: **JPG, PNG, WebP**
- The image filename must match what's stored in the database

When adding products through the admin panel, images are uploaded automatically.

---

## 📦 Sample Data

The database includes **20 sample products** across 8 categories:
- 📱 Mobile Phones (iPhone 15 Pro Max, Samsung Galaxy S24 Ultra, Pixel 8 Pro, OnePlus 12)
- 💻 Laptops (MacBook Pro 16 M3 Max, Dell XPS 15, ASUS ROG Zephyrus, ThinkPad X1)
- 🎧 Headphones (Sony WH-1000XM5, AirPods Pro 2, Bose QC45)
- ⌚ Smart Watches (Apple Watch Ultra 2, Samsung Galaxy Watch 6)
- 📟 Tablets (iPad Pro M2, Samsung Galaxy Tab S9 Ultra)
- 🎮 Gaming (PlayStation 5, Xbox Series X)
- 📷 Cameras (Sony Alpha A7 IV)
- 🔌 Accessories (Anker charging station, Logitech MX Master 3S)

---

## 🐛 Troubleshooting

**Blank page / errors:**
- Check `config.php` DB credentials
- Ensure XAMPP Apache & MySQL are running
- Check PHP error logs in XAMPP

**Images not showing:**
- Place images in `assets/images/products/`
- Check file permissions (should be readable)

**Session issues:**
- Clear browser cookies
- Ensure session_start() isn't called twice

**Admin can't login:**
- Verify you ran the SQL file which inserts the admin account
- The default password hash in the SQL is for `password`

---

## 📞 Support

This project is ready to run on XAMPP localhost. For production deployment, additional security hardening, HTTPS, and email configuration would be recommended.

---

*Built with PHP, MySQL, Bootstrap 5, Font Awesome 6*
