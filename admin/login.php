<?php
/**
 * Super Admin Login Portal
 * BazarPK - Pakistani Custom eCommerce Platform
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = "Session expired. Please try again.";
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Please enter your administrative email and password.";
        } elseif ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM `users` WHERE email = ? AND role = 'admin' AND status = 'active' LIMIT 1");
                $stmt->execute([$email]);
                $adminUser = $stmt->fetch();

                if ($adminUser && password_verify($password, $adminUser['password_hash'])) {
                    session_regenerate_id(true);

                    $_SESSION['admin_id']    = $adminUser['id'];
                    $_SESSION['admin_name']  = $adminUser['name'];
                    $_SESSION['admin_email'] = $adminUser['email'];
                    $_SESSION['admin_role']  = ROLE_ADMIN;

                    setFlash('success', "Welcome back, " . $adminUser['name'] . "! Super Admin Session Active.");
                    header('Location: ' . BASE_URL . '/admin/index.php');
                    exit;
                } else {
                    $error = "Access Denied: Invalid administrator credentials.";
                }
            } catch (Exception $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        } else {
            // Preview mode fallback
            if ($email === 'admin@store.pk' && $password === 'Admin@12345') {
                $_SESSION['admin_id']    = 1;
                $_SESSION['admin_name']  = 'Super Admin';
                $_SESSION['admin_email'] = 'admin@store.pk';
                $_SESSION['admin_role']  = ROLE_ADMIN;
                header('Location: ' . BASE_URL . '/admin/index.php');
                exit;
            } else {
                $error = "Invalid administrator credentials.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Portal - <?= STORE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body { background: #0f172a; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: #ffffff; border-radius: 16px; padding: 40px; max-width: 420px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
    </style>
</head>
<body>

<div class="login-card">
    <div style="text-align: center; margin-bottom: 28px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #0f766e; color: #fff; font-size: 24px; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
            B
        </div>
        <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0;"><?= STORE_NAME ?> Admin</h1>
        <p style="font-size: 13px; color: #64748b; margin: 0;">Restricted single Super Admin management console</p>
    </div>

    <?php if ($error): ?>
        <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/admin/login.php">
        <?= csrf_field() ?>

        <div style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Super Admin Email</label>
            <input type="email" name="email" value="<?= e($_POST['email'] ?? 'admin@store.pk') ?>" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 24px;">
            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Secret Password</label>
            <input type="password" name="password" value="Admin@12345" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
        </div>

        <button type="submit" class="btn-checkout-drawer" style="padding: 13px; font-size: 15px; border-radius: 8px; width: 100%; background: #0f766e; color: #fff; cursor: pointer; border: none; font-weight: 700;">
            Sign In to Super Admin Panel
        </button>
    </form>

    <div style="margin-top: 24px; padding: 12px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; font-size: 12px; color: #475569;">
        <strong>Default Hostinger Admin Credentials:</strong><br>
        Email: <code>admin@store.pk</code><br>
        Password: <code>Admin@12345</code>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="<?= BASE_URL ?>/index.php" style="font-size: 13px; color: #0f766e; text-decoration: underline;">&larr; Back to Customer Store</a>
    </div>
</div>

</body>
</html>
