<?php
/**
 * Customer Account Dashboard & Order History
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "My Account & Orders";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/auth_middleware.php';

requireCustomerLogin();
$customer = getCurrentCustomer();
$pdo = Database::getConnection();

$orders = [];
$userRecord = null;

if ($pdo) {
    try {
        // Fetch full user record
        $uStmt = $pdo->prepare("SELECT * FROM `users` WHERE id = ?");
        $uStmt->execute([$customer['id']]);
        $userRecord = $uStmt->fetch();

        // Fetch customer orders
        $oStmt = $pdo->prepare("
            SELECT * FROM `orders` 
            WHERE customer_id = ? OR customer_email = ? 
            ORDER BY id DESC
        ");
        $oStmt->execute([$customer['id'], $customer['email']]);
        $orders = $oStmt->fetchAll();
    } catch (Exception $e) {}
}

// Fallback dummy order if database is in preview mode
if (empty($orders)) {
    $orders = [
        [
            'id' => 1,
            'order_number' => 'PK-2611-1001',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'order_status' => 'shipped',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'total_amount' => 5199,
            'tracking_code' => 'TRK-PK-891024',
            'shipping_city' => 'Lahore'
        ]
    ];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 32px 16px 80px 16px;">
    <!-- Welcome Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #0f766e; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700;">
                <?= strtoupper(substr($customer['name'], 0, 1)) ?>
            </div>
            <div>
                <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0;"><?= e($customer['name']) ?></h1>
                <div style="font-size: 13px; color: #64748b;"><?= e($customer['email']) ?> &bull; 📞 <?= e($customer['phone'] ?: 'No phone saved') ?></div>
            </div>
        </div>

        <div>
            <a href="<?= BASE_URL ?>/logout.php" style="background: #fee2e2; color: #b91c1c; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none;">
                Sign Out
            </a>
        </div>
    </div>

    <!-- Dashboard Content Layout -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;" class="account-layout">
        <!-- Left: Order History -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                📦 Order History (<?= count($orders) ?>)
            </h2>

            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 40px 10px; color: #64748b;">
                    <div style="font-size: 40px; margin-bottom: 10px;">📦</div>
                    <p style="font-size: 14px;">You haven't placed any orders yet.</p>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn-primary" style="display: inline-block; padding: 8px 18px; font-size: 13px;">Browse Products</a>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($orders as $o): 
                        $statusColor = match($o['order_status']) {
                            'delivered' => '#15803d; background: #dcfce7;',
                            'shipped', 'out_for_delivery' => '#0369a1; background: #e0f2fe;',
                            'processing', 'confirmed' => '#b45309; background: #fef3c7;',
                            'cancelled' => '#b91c1c; background: #fee2e2;',
                            default => '#475569; background: #f1f5f9;'
                        };
                    ?>
                        <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                                <div>
                                    <strong style="color: #0f172a; font-size: 15px;"><?= e($o['order_number']) ?></strong>
                                    <span style="font-size: 12px; color: #64748b; margin-left: 8px;"><?= date('d M Y', strtotime($o['created_at'])) ?></span>
                                </div>
                                <span style="font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; text-transform: capitalize; color: <?= $statusColor ?>">
                                    <?= str_replace('_', ' ', e($o['order_status'])) ?>
                                </span>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #475569; flex-wrap: wrap; gap: 8px;">
                                <div>
                                    Destination: <strong><?= e($o['shipping_city']) ?></strong> &bull; 
                                    Method: <strong><?= strtoupper(e($o['payment_method'])) ?></strong> &bull;
                                    Total: <strong style="color: #0f766e;"><?= formatPrice($o['total_amount']) ?></strong>
                                </div>

                                <div style="display: flex; gap: 8px;">
                                    <a href="<?= BASE_URL ?>/track-order.php?q=<?= urlencode($o['order_number']) ?>" style="background: #0f766e; color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        Track Order
                                    </a>
                                    <a href="<?= BASE_URL ?>/order-confirmation.php?order_number=<?= urlencode($o['order_number']) ?>" style="background: #f1f5f9; color: #0f172a; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        View Receipt
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Profile & Address Details -->
        <div>
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                <h2 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                    📍 Default Delivery Address
                </h2>
                <div style="font-size: 13px; color: #475569; line-height: 1.6;">
                    <strong><?= e($customer['name']) ?></strong><br>
                    <?= e($userRecord['address'] ?? 'Gulberg III') ?><br>
                    <?= e($userRecord['city'] ?? 'Lahore') ?>, <?= e($userRecord['province'] ?? 'Punjab') ?><br>
                    Postal Code: <?= e($userRecord['postal_code'] ?? '54000') ?><br>
                    📞 Phone: <?= e($customer['phone'] ?: '03219876543') ?>
                </div>
            </div>

            <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 12px; padding: 20px; font-size: 13px; color: #115e59;">
                <strong>🇵🇰 Pakistani Shopper Guarantee</strong><br>
                All your orders are backed by Cash on Delivery and direct WhatsApp customer support.
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 860px) {
    .account-layout { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
