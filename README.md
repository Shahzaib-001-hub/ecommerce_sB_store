# BazarPK - Custom Pakistani eCommerce Platform
### High-Performance PHP 8.x + MySQL + Vanilla JavaScript Storefront & Super Admin Dashboard

A custom-coded, zero-dependency eCommerce platform built specifically for the **Pakistani Market** and optimized for **Hostinger Shared Hosting** (Apache/LiteSpeed).

---

## 🌟 Key Features

### 🛒 Pakistani Customer Storefront
- **Currency & Formatting:** Standard Pakistani Rupees (`Rs. 3,450`) formatted throughout catalog, cart, checkout, and invoices.
- **Cart Drawer:** Modern slide-over offcanvas cart with live PKR subtotal and dynamic **Free Shipping across Pakistan** progress bar (orders over Rs. 3,500).
- **Search & Auto-Suggest:** Real-time Vanilla JS debounced product search.
- **Shop Catalog & Filters:** Filter products by Pakistani categories, price sliders in PKR, flash sales, and sorting options.
- **Product Detail Pages:** Multi-image gallery, interactive size/color variant selectors, live stock indicators, product reviews with star ratings, and instant "Buy Now with Cash on Delivery" checkout trigger.
- **Pakistani Address & Courier Forms:** Pre-configured provinces (Sindh, Punjab, KPK, Balochistan, Islamabad, AJK, GB) and major cities (Karachi, Lahore, Islamabad, Rawalpindi, Faisalabad, Multan, Peshawar, etc.) with accurate transit time estimates (1-2 days vs 2-3 days).
- **Live Order Tracking:** Real-time visual timeline stepper tracking parcels by **Order ID** (`PK-2611-1001`) or **Pakistani Mobile Number** (`03XXXXXXXXX`).
- **Customer Account Portal:** Order history, tracking status, profile management, and saved delivery addresses.
- **Wishlist:** Dedicated wishlist with one-click "Move to Cart".
- **Direct WhatsApp Widget:** Pre-filled Pakistani WhatsApp chat link for instant order assistance.

---

### 💳 Complete Pakistani Payment Architecture
Supports multi-channel payment flows with **strict server-side cryptographic verification**:
1. **Cash on Delivery (COD):** Most popular nationwide payment method with instant order confirmation.
2. **JazzCash Mobile Account & Card:** HMAC-SHA256 signature generation and server callback verification.
3. **Easypaisa Mobile Account & Direct Debit:** Direct wallet checkout integration with IPN verification.
4. **Debit / Credit Card Gateway Adapter:** 3D Secure 2.0 encrypted card checkout (Visa, Mastercard, PayPak).

> **Important:** Online orders are **never** marked paid solely because the browser redirected to a success page. The server verifies the callback payload and cryptographic hash before advancing order status.

---

### ⚙️ Super Admin Dashboard
A single, secure administrative control panel designed for store owners:
- **Overview & KPIs:** Total Sales in PKR, Active Orders, Registered Customers, Low Stock Alerts, and weekly revenue bar chart.
- **Product & Inventory Management:** Add/Edit/Delete products, SKU codes, pricing, compare pricing, cost price, stock adjustments, and featured toggles.
- **Order Processing:** Filter orders by status (`pending`, `confirmed`, `processing`, `shipped`, `delivered`, `cancelled`), assign Pakistani courier partners (**Trax Logistics**, **TCS Express**, **CallCourier**, **Leopard**), update tracking numbers, and view complete customer order invoices.
- **Customer Management:** View buyer contact details, cities, order counts, and lifetime spend.
- **Coupon Manager:** Configure promo codes (`WELCOME10`, `PKFREESHIP`, `AZADI500`), percentage discounts, minimum spend thresholds, and expiry dates.
- **Store Settings:** Customize WhatsApp customer support number, standard shipping fees, free shipping thresholds, and toggle payment methods.

---

## 📁 Project Structure

```text
ecommerce_sB_store/
├── .htaccess                 # Apache / LiteSpeed security headers, gzip, asset caching
├── index.php                 # Customer storefront homepage
├── shop.php                  # Product catalog with filter sidebar
├── product.php               # Product detail view with variant selector & reviews
├── cart.php                  # Full shopping cart with coupon engine
├── checkout.php              # Pakistani address checkout & payment method selector
├── order-confirmation.php    # Order success invoice receipt
├── track-order.php           # Real-time parcel tracking page
├── login.php                 # Customer authentication
├── register.php              # Customer registration
├── logout.php                # Customer logout
├── account.php               # Customer dashboard & order history
├── wishlist.php              # Saved wishlist items
├── contact.php               # Customer support with WhatsApp direct link
├── terms.php                 # Terms of service
├── refund-policy.php         # 7-day exchange & refund policy
├── install.php               # One-click Hostinger database installer & diagnostics
├── config/
│   ├── config.php            # Store configuration, DB credentials, payment constants
│   ├── constants.php         # Order & payment status constants, PK provinces & cities
│   └── database.php          # Robust PDO database wrapper
├── database/
│   ├── schema.sql            # Complete MySQL / MariaDB schema (13 tables)
│   └── seed.sql              # Pakistani sample products, categories, coupons, and admin user
├── includes/
│   ├── header.php            # Storefront navigation, search, and cart drawer markup
│   ├── footer.php            # Storefront footer, WhatsApp widget, and toast alerts
│   ├── utils.php             # CSRF tokens, PKR formatting, sanitization, slugify
│   ├── auth_middleware.php   # Session auth verification helpers
│   └── payment_gateways/     # Adapter Pattern payment modules
│       ├── GatewayInterface.php
│       ├── CashOnDelivery.php
│       ├── JazzCashAdapter.php
│       ├── EasypaisaAdapter.php
│       └── CardGatewayAdapter.php
├── api/
│   ├── cart.php              # Cart session sync
│   ├── products.php          # Live search & filter JSON endpoint
│   ├── checkout.php          # Order creation and payment session dispatcher
│   ├── payments.php          # Gateway redirect and server-side verification handler
│   ├── tracking.php          # Parcel tracking lookup
│   ├── coupons.php           # Promo code validation
│   └── reviews.php           # Customer reviews submission
├── admin/
│   ├── index.php             # Executive dashboard & sales analytics
│   ├── login.php             # Secure Super Admin login
│   ├── logout.php            # Admin session termination
│   ├── products.php          # Catalog & inventory manager
│   ├── orders.php            # Order fulfillment and status processor
│   ├── order-detail.php      # Full order invoice, WhatsApp customer link & audit history
│   ├── categories.php        # Category tree manager
│   ├── customers.php         # Customer directory & lifetime value
│   ├── coupons.php           # Coupon & discount manager
│   ├── settings.php          # Store shipping rates & gateway credentials
│   └── includes/
│       ├── header.php        # Admin sidebar & topbar
│       └── footer.php        # Admin scripts & modal helpers
└── assets/
    ├── css/
    │   ├── style.css         # Responsive storefront styling
    │   └── admin.css         # Clean admin dashboard layout
    └── js/
        └── app.js            # Vanilla JS cart, wishlist, live search & notifications
```

---

## 🚀 Hostinger Shared Hosting Deployment Guide

### Step 1: Upload Files to Hostinger
1. Log in to **Hostinger hPanel**.
2. Go to **File Manager** (or connect via FTP / Git).
3. Navigate to your domain's `public_html` directory.
4. Upload all files from this project into `public_html/`.

### Step 2: Create a MySQL Database in Hostinger
1. In **hPanel**, navigate to **Databases** &rarr; **Management**.
2. Click **Create a New MySQL Database and Database User**.
3. Fill in:
   - **Database Name** (e.g., `u123456789_store`)
   - **Username** (e.g., `u123456789_admin`)
   - **Password** (choose a strong password)
4. Note down these three values.

### Step 3: Configure Database Credentials
Open `config/config.php` in Hostinger File Manager Code Editor and update:

```php
define('DB_HOST', 'localhost'); // Hostinger MySQL Host
define('DB_PORT', '3306');
define('DB_NAME', 'u123456789_store'); // Your Hostinger DB Name
define('DB_USER', 'u123456789_admin'); // Your Hostinger DB User
define('DB_PASS', 'YourSecretPassword'); // Your Hostinger DB Password
```

### Step 4: Run the One-Click Installer
1. Open your browser and visit: `https://yourdomain.com/install.php`
2. The diagnostics system will verify PHP 8.x, PDO MySQL, and database connectivity.
3. Click **Install Database & Seed Pakistani Catalog**.
4. The tables and sample Pakistani products will be created and locked automatically!

*(Alternatively, you can manually import `database/schema.sql` followed by `database/seed.sql` inside **phpMyAdmin**).*

---

## 🔐 Default Access Credentials

### Super Admin Portal
- **URL:** `https://yourdomain.com/admin/login.php`
- **Email:** `admin@store.pk`
- **Password:** `Admin@12345`

### Demo Customer Account
- **URL:** `https://yourdomain.com/login.php`
- **Email:** `bilal@example.pk`
- **Password:** `Customer@123`

### Pre-Configured Coupons
- `WELCOME10`: 10% discount on orders over Rs. 1,500
- `PKFREESHIP`: Free shipping voucher across Pakistan
- `AZADI500`: Flat Rs. 500 off on orders over Rs. 5,000

---

## 🛡️ Security Features
- **SQL Injection Prevention:** 100% PDO prepared statements with strict parameter binding.
- **XSS Protection:** Output sanitization through `htmlspecialchars()`.
- **CSRF Defense:** Cryptographic CSRF tokens on all POST forms.
- **Secure Sessions:** `httponly`, `samesite=Lax`, and `secure` cookie flags.
- **Password Hashing:** Native `password_hash()` with `PASSWORD_BCRYPT`.
- **Payment Verification:** Server-to-server HMAC and secret validation before confirming paid status.
