<?php
/**
 * Header Template
 * BazarPK - Pakistani Custom eCommerce Platform
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/auth_middleware.php';

$pdo = Database::getConnection();
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, name, slug FROM categories WHERE is_featured = 1 ORDER BY display_order ASC LIMIT 6");
        $categories = $stmt->fetchAll();
    } catch (Exception $e) {
        $categories = [];
    }
}

$currentCustomer = getCurrentCustomer();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= STORE_NAME ?> - <?= STORE_TAGLINE ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? e($pageDescription) : 'Shop premium fashion, electronics, and authentic products in Pakistan. Fast COD & Online payments.' ?>">
    
    <!-- Google Fonts & Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Top Announcement Bar -->
<div class="top-bar">
    <div class="container top-bar-content">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span>🇵🇰</span>
            <span><strong>Cash on Delivery Available Nationwide</strong> &bull; Free Express Delivery above Rs. <?= number_format(FREE_SHIPPING_THRESHOLD) ?></span>
        </div>
        <div class="top-bar-links">
            <a href="<?= BASE_URL ?>/track-order.php" class="top-bar-link">
                <span>📦</span> Track Order
            </a>
            <a href="https://wa.me/<?= STORE_WHATSAPP ?>" target="_blank" class="top-bar-link">
                <span>💬</span> WhatsApp: <?= STORE_PHONE ?>
            </a>
            <?php if (isAdminLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/admin/index.php" class="top-bar-link" style="color: #fef08a; font-weight: 700;">
                    <span>⚙️</span> Admin Panel
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Sticky Header -->
<header class="main-header">
    <div class="container header-inner">
        <!-- Logo -->
        <a href="<?= BASE_URL ?>/index.php" class="logo-wrapper">
            <div class="logo-icon">B</div>
            <div>
                <span class="logo-text">Bazar<span style="color: #0f766e;">PK</span></span>
                <span class="logo-badge">PK Store</span>
            </div>
        </a>

        <!-- Live Search Bar -->
        <div class="header-search">
            <form action="<?= BASE_URL ?>/shop.php" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Search for kurtas, earbuds, footwear, perfumes..." autocomplete="off">
                <button type="submit" class="search-btn" title="Search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>
            <div class="search-results-dropdown"></div>
        </div>

        <!-- Header Actions (Account, Wishlist, Cart) -->
        <div class="header-actions">
            <?php if ($currentCustomer): ?>
                <a href="<?= BASE_URL ?>/account.php" class="action-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span><?= e(explode(' ', $currentCustomer['name'])[0]) ?></span>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="action-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span>Account</span>
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/wishlist.php" class="action-btn" title="Wishlist">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <span class="badge-counter wishlist-counter-badge" style="display: none;">0</span>
            </a>

            <button type="button" class="action-btn" data-action="open-cart" title="Shopping Cart">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <span style="font-weight: 700;">Cart</span>
                <span class="badge-counter cart-counter-badge" style="display: none;">0</span>
            </button>
        </div>
    </div>
</header>

<!-- Navigation Bar -->
<nav class="nav-bar">
    <div class="container">
        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>/index.php" class="nav-link <?= empty($_GET['cat']) && basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= BASE_URL ?>/shop.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'shop.php' && empty($_GET['cat']) ? 'active' : '' ?>">All Products</a></li>
            <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="<?= BASE_URL ?>/shop.php?cat=<?= urlencode($cat['slug']) ?>" class="nav-link <?= (isset($_GET['cat']) && $_GET['cat'] === $cat['slug']) ? 'active' : '' ?>">
                        <?= e($cat['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li>
                <a href="<?= BASE_URL ?>/shop.php?sale=1" class="nav-link" style="color: #dc2626; font-weight: 700;">
                    🔥 Flash Deals <span class="nav-link-badge">Sale</span>
                </a>
            </li>
            <li><a href="<?= BASE_URL ?>/track-order.php" class="nav-link">Track Order</a></li>
        </ul>
    </div>
</nav>

<!-- Slide-over Cart Drawer -->
<div class="cart-drawer-overlay"></div>
<aside class="cart-drawer" aria-label="Cart Drawer">
    <div class="cart-drawer-header">
        <div class="cart-drawer-title">
            <span>🛒 Your Shopping Cart</span>
        </div>
        <button type="button" class="btn-close-drawer" data-action="close-cart" aria-label="Close cart">&times;</button>
    </div>

    <!-- Free Delivery Indicator for Pakistan -->
    <div class="shipping-progress-box">
        <div class="shipping-progress-text">Add items to qualify for <strong>FREE Delivery</strong> across Pakistan!</div>
        <div class="shipping-progress-track">
            <div class="shipping-progress-fill"></div>
        </div>
    </div>

    <!-- Items List (Dynamically updated via app.js) -->
    <div class="cart-drawer-items"></div>

    <!-- Footer with subtotal & checkout button -->
    <div class="cart-drawer-footer">
        <div class="cart-subtotal-row">
            <span style="color: #64748b;">Estimated Subtotal</span>
            <span class="cart-subtotal-amount">Rs. 0</span>
        </div>
        <a href="<?= BASE_URL ?>/checkout.php" class="btn-checkout-drawer">
            Proceed to Checkout &bull; Cash on Delivery
        </a>
        <div style="text-align: center; margin-top: 10px;">
            <a href="<?= BASE_URL ?>/cart.php" style="font-size: 13px; color: #0f766e; text-decoration: underline;">View Full Cart & Apply Coupon</a>
        </div>
    </div>
</aside>

<!-- Flash Alerts Container -->
<?php
$flashes = getFlash();
if (!empty($flashes)): ?>
    <div class="container" style="margin-top: 20px;">
        <?php foreach ($flashes as $flash): ?>
            <div class="toast toast-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>" style="position: static; margin-bottom: 10px; max-width: 100%;">
                <?= e($flash['message']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
