<?php
/**
 * Customer Sign In Page
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Customer Sign In";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/auth_middleware.php';

if (isCustomerLoggedIn()) {
    header('Location: ' . BASE_URL . '/account.php');
    exit;
}

$error = '';
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = "Session expired. Please try again.";
    } else {
        $loginInput = sanitize($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($loginInput) || empty($password)) {
            $error = "Please enter your email/phone and password.";
        } elseif ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM `users` WHERE (email = ? OR phone = ?) AND role = 'customer' AND status = 'active' LIMIT 1");
                $stmt->execute([$loginInput, formatPakistaniPhone($loginInput)]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);

                    $_SESSION['customer_id']    = $user['id'];
                    $_SESSION['customer_name']  = $user['name'];
                    $_SESSION['customer_email'] = $user['email'];
                    $_SESSION['customer_phone'] = $user['phone'];

                    setFlash('success', "Welcome back, " . $user['name'] . "!");
                    $redirect = $_SESSION['login_redirect'] ?? (BASE_URL . '/account.php');
                    unset($_SESSION['login_redirect']);
                    header('Location: ' . $redirect);
                    exit;
                } else {
                    $error = "Invalid email, phone number, or password.";
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            // Preview mode fallback
            if ($loginInput === 'bilal@example.pk') {
                $_SESSION['customer_id'] = 2;
                $_SESSION['customer_name'] = 'Muhammad Bilal';
                $_SESSION['customer_email'] = 'bilal@example.pk';
                $_SESSION['customer_phone'] = '03219876543';
                header('Location: ' . BASE_URL . '/account.php');
                exit;
            } else {
                $error = "Invalid credentials. You can use demo account: bilal@example.pk";
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 48px 16px 80px 16px; max-width: 440px;">
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 36px 32px; box-shadow: var(--shadow-sm);">
        <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 6px; text-align: center;">Welcome Back</h1>
        <p style="font-size: 13px; color: #64748b; text-align: center; margin-bottom: 24px;">Sign in to track orders, manage addresses, and checkout faster</p>

        <?php if ($error): ?>
            <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login.php">
            <?= csrf_field() ?>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Email or Mobile Number</label>
                <input type="text" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@domain.com or 03001234567" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="font-size: 13px; font-weight: 700; color: #334155;">Password</label>
                    <a href="#" style="font-size: 12px; color: #0f766e;">Forgot?</a>
                </div>
                <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
            </div>

            <button type="submit" class="btn-checkout-drawer" style="padding: 12px; font-size: 15px; border-radius: 8px; margin-bottom: 20px;">
                Sign In to Account
            </button>
        </form>

        <div style="text-align: center; font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 18px;">
            Don't have an account yet? 
            <a href="<?= BASE_URL ?>/register.php" style="color: #0f766e; font-weight: 700;">Create one now &rarr;</a>
        </div>

        <div style="margin-top: 20px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; font-size: 12px; color: #475569;">
            <strong>Demo Customer Account:</strong><br>
            Email: <code>bilal@example.pk</code><br>
            Password: <code>Customer@123</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
