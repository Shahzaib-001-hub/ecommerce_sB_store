<?php
/**
 * Store & Gateway Settings
 * BazarPK - Super Admin Dashboard
 */

$adminTitle = "Store & Payment Settings";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security check failed. Please refresh.');
    } else {
        $settingsToSave = [
            'store_name'               => sanitize($_POST['store_name'] ?? STORE_NAME),
            'store_phone'              => sanitize($_POST['store_phone'] ?? STORE_PHONE),
            'store_whatsapp'           => sanitize($_POST['store_whatsapp'] ?? STORE_WHATSAPP),
            'store_email'              => sanitize($_POST['store_email'] ?? STORE_EMAIL),
            'store_address'            => sanitize($_POST['store_address'] ?? STORE_ADDRESS),
            'default_shipping_fee'     => (float)($_POST['default_shipping_fee'] ?? DEFAULT_SHIPPING_FEE),
            'free_shipping_threshold'  => (float)($_POST['free_shipping_threshold'] ?? FREE_SHIPPING_THRESHOLD),
            'enable_cod'               => isset($_POST['enable_cod']) ? '1' : '0',
            'enable_jazzcash'          => isset($_POST['enable_jazzcash']) ? '1' : '0',
            'enable_easypaisa'         => isset($_POST['enable_easypaisa']) ? '1' : '0',
            'enable_card_gateway'      => isset($_POST['enable_card_gateway']) ? '1' : '0',
        ];

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `settings` (`setting_key`, `setting_value`) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)
            ");
            foreach ($settingsToSave as $k => $v) {
                $stmt->execute([$k, (string)$v]);
            }
            setFlash('success', 'Store configuration and gateway settings updated successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed saving settings: ' . $e->getMessage());
        }
    }
}

// Read current settings from DB
$dbSettings = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM `settings`");
        while ($row = $stmt->fetch()) {
            $dbSettings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {}
}

$get = fn($key, $default) => $dbSettings[$key] ?? $default;
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h1 class="admin-card-title">Store &amp; Payment Gateway Settings</h1>
            <span style="font-size: 13px; color: #64748b;">Configure logistics fees, customer helpline numbers, and payment gateways</span>
        </div>
    </div>

    <form method="POST">
        <?= csrf_field() ?>

        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
            🏬 Store Identity &amp; Pakistani Support
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Store Display Name</label>
                <input type="text" name="store_name" value="<?= e($get('store_name', STORE_NAME)) ?>" class="admin-input">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Official Support Email</label>
                <input type="email" name="store_email" value="<?= e($get('store_email', STORE_EMAIL)) ?>" class="admin-input">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Helpline Phone</label>
                <input type="text" name="store_phone" value="<?= e($get('store_phone', STORE_PHONE)) ?>" class="admin-input">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">WhatsApp Number (Without + sign, e.g. 923001234567)</label>
                <input type="text" name="store_whatsapp" value="<?= e($get('store_whatsapp', STORE_WHATSAPP)) ?>" class="admin-input">
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Physical Store / Warehouse Address</label>
            <input type="text" name="store_address" value="<?= e($get('store_address', STORE_ADDRESS)) ?>" class="admin-input">
        </div>

        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
            🚚 Nationwide Shipping Rules
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Standard Delivery Fee (PKR)</label>
                <input type="number" name="default_shipping_fee" value="<?= (int)$get('default_shipping_fee', DEFAULT_SHIPPING_FEE) ?>" class="admin-input">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Free Delivery Threshold (PKR)</label>
                <input type="number" name="free_shipping_threshold" value="<?= (int)$get('free_shipping_threshold', FREE_SHIPPING_THRESHOLD) ?>" class="admin-input">
            </div>
        </div>

        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
            💳 Payment Channels Activation
        </h3>

        <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 28px;">
            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #1e293b;">
                <input type="checkbox" name="enable_cod" value="1" <?= $get('enable_cod', '1') === '1' ? 'checked' : '' ?> style="accent-color: #0f766e; width: 18px; height: 18px;">
                Enable Cash on Delivery (COD) across Pakistan
            </label>

            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #1e293b;">
                <input type="checkbox" name="enable_jazzcash" value="1" <?= $get('enable_jazzcash', '1') === '1' ? 'checked' : '' ?> style="accent-color: #0f766e; width: 18px; height: 18px;">
                Enable JazzCash Payment Gateway (Mobile Account &amp; QR)
            </label>

            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #1e293b;">
                <input type="checkbox" name="enable_easypaisa" value="1" <?= $get('enable_easypaisa', '1') === '1' ? 'checked' : '' ?> style="accent-color: #0f766e; width: 18px; height: 18px;">
                Enable Easypaisa Payment Gateway (Direct Wallet &amp; OTP)
            </label>

            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #1e293b;">
                <input type="checkbox" name="enable_card_gateway" value="1" <?= $get('enable_card_gateway', '1') === '1' ? 'checked' : '' ?> style="accent-color: #0f766e; width: 18px; height: 18px;">
                Enable 3D-Secure Card Checkout (Visa, Mastercard, PayPak)
            </label>
        </div>

        <button type="submit" class="btn-admin" style="padding: 12px 28px; font-size: 14px;">
            Save All Settings
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
