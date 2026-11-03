<?php
/**
 * Super Admin Header Template
 * BazarPK - Pakistani Custom eCommerce Platform
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth_middleware.php';

requireAdminLogin();
$admin = getCurrentAdmin();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? e($adminTitle) . ' | ' : '' ?>Super Admin Dashboard - <?= STORE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<!-- Sidebar Navigation -->
<aside class="admin-sidebar">
    <a href="<?= BASE_URL ?>/admin/index.php" class="admin-brand">
        <div class="admin-brand-icon">B</div>
        <div>
            <div style="font-weight: 800; font-size: 16px; color: #fff;"><?= STORE_NAME ?></div>
            <div style="font-size: 11px; color: #14b8a6; font-weight: 700;">Super Admin Panel</div>
        </div>
    </a>

    <nav class="admin-nav">
        <a href="<?= BASE_URL ?>/admin/index.php" class="admin-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <span class="admin-nav-icon">📊</span>
            <span>Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="admin-nav-item <?= in_array($currentPage, ['orders.php', 'order-detail.php']) ? 'active' : '' ?>">
            <span class="admin-nav-icon">📦</span>
            <span>Orders &amp; Processing</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/products.php" class="admin-nav-item <?= $currentPage === 'products.php' ? 'active' : '' ?>">
            <span class="admin-nav-icon">🏷️</span>
            <span>Products &amp; Stock</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/categories.php" class="admin-nav-item <?= $currentPage === 'categories.php' ? 'active' : '' ?>">
            <span class="admin-nav-icon">📁</span>
            <span>Categories</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/customers.php" class="admin-nav-item <?= $currentPage === 'customers.php' ? 'active' : '' ?>">
            <span class="admin-nav-icon">👥</span>
            <span>Customers</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/coupons.php" class="admin-nav-item <?= $currentPage === 'coupons.php' ? 'active' : '' ?>">
            <span class="admin-nav-icon">🎟️</span>
            <span>Coupons &amp; Promos</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/settings.php" class="admin-nav-item <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
            <span class="admin-nav-icon">⚙️</span>
            <span>Store Settings</span>
        </a>
    </nav>

    <div class="admin-user-box">
        <div>
            <div style="color: #fff; font-size: 13px; font-weight: 700;"><?= e($admin['name']) ?></div>
            <div style="color: #64748b; font-size: 11px;"><?= e($admin['email']) ?></div>
        </div>
        <a href="<?= BASE_URL ?>/admin/logout.php" title="Sign Out" style="color: #ef4444; font-size: 16px;">⏻</a>
    </div>
</aside>

<!-- Main Workspace -->
<div class="admin-main">
    <header class="admin-topbar">
        <div style="font-size: 14px; font-weight: 700; color: #475569;">
            🇵🇰 Pakistan Market &bull; Hostinger Shared Hosting Mode
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn-admin-secondary" style="padding: 6px 14px; font-size: 12px;">
                👁️ View Live Storefront
            </a>
            <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">
                PHP <?= phpversion() ?>
            </span>
        </div>
    </header>

    <div class="admin-content">
        <!-- Flash message display -->
        <?php foreach (getFlash() as $flash): ?>
            <div style="background: <?= $flash['type'] === 'error' ? '#fee2e2; color: #991b1b; border: 1px solid #fca5a5;' : '#dcfce7; color: #166534; border: 1px solid #86efac;' ?> padding: 12px 16px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px;">
                <?= e($flash['message']) ?>
            </div>
        <?php endforeach; ?>
