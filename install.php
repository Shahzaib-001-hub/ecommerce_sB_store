<?php
/**
 * One-Click Database Setup & Diagnostics Utility
 * BazarPK - Hostinger Shared Hosting Helper
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/utils.php';

$pdo = Database::getConnection();
$message = '';
$status = '';

// Check if locked
$lockFile = __DIR__ . '/database/.installed';
$isLocked = file_exists($lockFile);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && !$isLocked) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = "Invalid CSRF token. Please refresh and try again.";
        $status = "error";
    } elseif ($_POST['action'] === 'run_migration') {
        if (!$pdo) {
            $message = "Cannot connect to database: " . Database::getError() . ". Please check config/config.php credentials.";
            $status = "error";
        } else {
            try {
                // Read and run schema.sql
                $schemaSql = file_get_contents(__DIR__ . '/database/schema.sql');
                $pdo->exec($schemaSql);

                // Read and run seed.sql
                $seedSql = file_get_contents(__DIR__ . '/database/seed.sql');
                $pdo->exec($seedSql);

                // Update admin password with native current PHP password_hash
                $newAdminHash = password_hash('Admin@12345', PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE `users` SET `password_hash` = ? WHERE `email` = 'admin@store.pk'");
                $stmt->execute([$newAdminHash]);

                // Create lock file
                file_put_contents($lockFile, date('c'));

                $message = "Success! Database tables created and Pakistani sample catalog seeded successfully.";
                $status = "success";
                $isLocked = true;
            } catch (Exception $e) {
                $message = "Database setup error: " . $e->getMessage();
                $status = "error";
            }
        }
    } elseif ($_POST['action'] === 'unlock') {
        if (file_exists($lockFile)) {
            unlink($lockFile);
            $isLocked = false;
            $message = "Installer unlocked. You can now re-run migrations if needed.";
            $status = "info";
        }
    }
}

// System checks
$phpVersion = phpversion();
$isPhp8 = version_compare($phpVersion, '8.0.0', '>=');
$hasPdo = extension_loaded('pdo_mysql');
$hasMbstring = extension_loaded('mbstring');
$hasJson = extension_loaded('json');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup & Diagnostics - <?= STORE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px 20px; }
        .setup-container { max-width: 680px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 32px; }
        .badge-pill { padding: 4px 10px; border-radius: 9999px; font-size: 13px; font-weight: 600; display: inline-block; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .check-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .alert-box { padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-info { background: #e0f2fe; color: #075985; border: 1px solid #7dd3fc; }
        .cred-box { background: #f1f5f9; border-radius: 8px; padding: 16px; margin: 20px 0; font-size: 14px; }
        .btn-submit { background: #0f766e; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; width: 100%; transition: background 0.2s; }
        .btn-submit:hover { background: #0d9488; }
        .btn-unlock { background: #64748b; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>
<div class="setup-container">
    <div style="text-align: center; margin-bottom: 24px;">
        <h1 style="color: #0f766e; margin-bottom: 4px; font-size: 26px;">🇵🇰 <?= STORE_NAME ?></h1>
        <p style="color: #64748b; margin: 0;">Automated Hostinger Shared Hosting Database Setup</p>
    </div>

    <?php if ($message): ?>
        <div class="alert-box alert-<?= $status ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <h3 style="margin-top: 0; font-size: 18px; color: #1e293b;">Server Environment Requirements</h3>
    <div style="margin-bottom: 24px;">
        <div class="check-item">
            <span>PHP 8.0+ (Installed: <?= e($phpVersion) ?>)</span>
            <span class="badge-pill <?= $isPhp8 ? 'badge-success' : 'badge-danger' ?>"><?= $isPhp8 ? 'Passed' : 'Failed' ?></span>
        </div>
        <div class="check-item">
            <span>PDO MySQL Extension</span>
            <span class="badge-pill <?= $hasPdo ? 'badge-success' : 'badge-danger' ?>"><?= $hasPdo ? 'Active' : 'Missing' ?></span>
        </div>
        <div class="check-item">
            <span>Multibyte String (mbstring)</span>
            <span class="badge-pill <?= $hasMbstring ? 'badge-success' : 'badge-danger' ?>"><?= $hasMbstring ? 'Active' : 'Missing' ?></span>
        </div>
        <div class="check-item">
            <span>Database Connection (<?= DB_USER ?>@<?= DB_HOST ?>:<?= DB_PORT ?>)</span>
            <span class="badge-pill <?= $pdo ? 'badge-success' : 'badge-danger' ?>"><?= $pdo ? 'Connected' : 'Connection Failed' ?></span>
        </div>
    </div>

    <?php if (!$pdo): ?>
        <div class="alert-box alert-error">
            <strong>Database Connection Error:</strong><br>
            <?= e(Database::getError()) ?><br><br>
            <em>Please open <code>config/config.php</code> and set your Hostinger MySQL database name, user, and password created in hPanel.</em>
        </div>
    <?php elseif ($isLocked): ?>
        <div class="alert-box alert-success">
            <strong>✅ Database is already set up and locked!</strong><br>
            Your store is ready to accept customers and orders.
        </div>

        <div class="cred-box">
            <strong>Default Super Admin Credentials:</strong><br>
            Email: <code>admin@store.pk</code><br>
            Password: <code>Admin@12345</code><br>
            Admin Panel URL: <a href="admin/login.php" style="color: #0f766e; font-weight: 600;">Go to Admin Login &rarr;</a><br>
            Storefront URL: <a href="index.php" style="color: #0f766e; font-weight: 600;">Open Customer Store &rarr;</a>
        </div>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="unlock">
            <button type="submit" class="btn-unlock">Unlock Installer (Re-install schema)</button>
        </form>
    <?php else: ?>
        <div class="cred-box">
            <strong>What this installer will do:</strong>
            <ul style="margin: 8px 0 0 20px; padding: 0;">
                <li>Creates 13 relational tables (Products, Variants, Orders, Coupons, etc.)</li>
                <li>Seeds initial Pakistani categories and products in PKR (Rs.)</li>
                <li>Creates default Super Admin account (<code>admin@store.pk</code> / <code>Admin@12345</code>)</li>
                <li>Sets up Pakistani shipping zones (Karachi, Lahore, Islamabad, etc.)</li>
            </ul>
        </div>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="run_migration">
            <button type="submit" class="btn-submit">Install Database & Seed Pakistani Catalog</button>
        </form>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 24px; font-size: 13px; color: #94a3b8;">
        Hostinger Shared Hosting &bull; PHP 8.x PDO &bull; BazarPK eCommerce
    </div>
</div>
</body>
</html>
