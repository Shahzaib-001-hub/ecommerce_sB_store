<?php
/**
 * Customer Registration Page
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Create Account";
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
        $error = "Session security check failed. Please refresh.";
    } else {
        $name     = sanitize($_POST['name'] ?? '');
        $email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone    = sanitize($_POST['phone'] ?? '');
        $city     = sanitize($_POST['city'] ?? '');
        $province = sanitize($_POST['province'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || !$email || empty($phone) || strlen($password) < 6) {
            $error = "Please fill in all fields. Password must be at least 6 characters.";
        } elseif (!isValidPakistaniPhone($phone)) {
            $error = "Please provide a valid 11-digit Pakistani mobile number (e.g. 03001234567).";
        } elseif ($pdo) {
            try {
                // Check if email already taken
                $check = $pdo->prepare("SELECT id FROM `users` WHERE email = ?");
                $check->execute([$email]);
                if ($check->fetch()) {
                    $error = "An account with this email address already exists.";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $ins = $pdo->prepare("
                        INSERT INTO `users` (`name`, `email`, `phone`, `city`, `province`, `password_hash`, `role`, `status`)
                        VALUES (?, ?, ?, ?, ?, ?, 'customer', 'active')
                    ");
                    $ins->execute([$name, $email, formatPakistaniPhone($phone), $city, $province, $hash]);

                    $newId = (int)$pdo->lastInsertId();
                    session_regenerate_id(true);
                    $_SESSION['customer_id']    = $newId;
                    $_SESSION['customer_name']  = $name;
                    $_SESSION['customer_email'] = $email;
                    $_SESSION['customer_phone'] = $phone;

                    setFlash('success', "Welcome to BazarPK, {$name}! Your account has been created.");
                    header('Location: ' . BASE_URL . '/account.php');
                    exit;
                }
            } catch (Exception $e) {
                $error = "Registration error: " . $e->getMessage();
            }
        } else {
            // Preview mode fallback
            $_SESSION['customer_id'] = 99;
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_email'] = $email;
            header('Location: ' . BASE_URL . '/account.php');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 48px 16px 80px 16px; max-width: 480px;">
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 36px 32px; box-shadow: var(--shadow-sm);">
        <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 6px; text-align: center;">Create Account</h1>
        <p style="font-size: 13px; color: #64748b; text-align: center; margin-bottom: 24px;">Join thousands of satisfied shoppers across Pakistan</p>

        <?php if ($error): ?>
            <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/register.php">
            <?= csrf_field() ?>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Full Name *</label>
                <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Hamza Tariq" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Email Address *</label>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@domain.com" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Pakistani Mobile Number *</label>
                <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="03001234567" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">City</label>
                    <input type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" placeholder="Karachi / Lahore" style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Province</label>
                    <input type="text" name="province" value="<?= e($_POST['province'] ?? '') ?>" placeholder="Punjab / Sindh" style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Create Password *</label>
                <input type="password" name="password" placeholder="At least 6 characters" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
            </div>

            <button type="submit" class="btn-checkout-drawer" style="padding: 12px; font-size: 15px; border-radius: 8px; margin-bottom: 20px;">
                Create Free Account
            </button>
        </form>

        <div style="text-align: center; font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 18px;">
            Already have an account? 
            <a href="<?= BASE_URL ?>/login.php" style="color: #0f766e; font-weight: 700;">Sign In &rarr;</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
