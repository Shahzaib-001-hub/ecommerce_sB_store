<?php
/**
 * Order Confirmation & Invoice Receipt
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Order Confirmed";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();
$orderNumber = sanitize($_GET['order_number'] ?? '');
$order = null;
$orderItems = [];

if ($pdo && !empty($orderNumber)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();

        if ($order) {
            $itemStmt = $pdo->prepare("SELECT * FROM `order_items` WHERE order_id = ?");
            $itemStmt->execute([$order['id']]);
            $orderItems = $itemStmt->fetchAll();
        }
    } catch (Exception $e) {}
}

// Fallback to session latest order if database query is empty
if (!$order && isset($_SESSION['latest_order'])) {
    $order = $_SESSION['latest_order'];
}

if (!$order) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
?>

<div class="container" style="padding: 40px 16px 80px 16px; max-width: 760px;">
    <!-- Success Banner -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 36px 32px; text-align: center; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
        <div style="width: 64px; height: 64px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px auto;">
            ✓
        </div>
        <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Thank You! Your Order is Confirmed</h1>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">
            We have received your order and our logistics team in Pakistan is preparing your parcel for dispatch.
        </p>

        <div style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; padding: 8px 18px; border-radius: 9999px; font-size: 14px;">
            <span>Order Number:</span>
            <strong style="color: #0f766e;"><?= e($order['order_number']) ?></strong>
        </div>
    </div>

    <!-- Order Details Box -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; margin-bottom: 24px;">
        <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">Order Summary</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 13px; margin-bottom: 24px;">
            <div>
                <strong style="color: #64748b; display: block; margin-bottom: 4px;">Delivery Address:</strong>
                <div style="color: #0f172a; font-weight: 600;"><?= e($order['customer_name']) ?></div>
                <div style="color: #475569;"><?= e($order['shipping_address'] ?? 'City Address') ?></div>
                <div style="color: #475569;"><?= e($order['shipping_city'] ?? '') ?>, <?= e($order['shipping_province'] ?? 'Pakistan') ?></div>
                <div style="color: #0f766e; font-weight: 600; margin-top: 4px;">📞 <?= e($order['customer_phone']) ?></div>
            </div>

            <div>
                <strong style="color: #64748b; display: block; margin-bottom: 4px;">Payment &amp; Logistics:</strong>
                <div>Payment Method: <strong><?= strtoupper(e($order['payment_method'])) ?></strong></div>
                <div>
                    Payment Status: 
                    <?php if (($order['payment_status'] ?? '') === 'paid'): ?>
                        <span style="color: #16a34a; font-weight: 700;">Paid (Verified)</span>
                    <?php else: ?>
                        <span style="color: #d97706; font-weight: 700;">Cash Due on Delivery</span>
                    <?php endif; ?>
                </div>
                <div style="margin-top: 6px;">
                    Courier Partner: <strong><?= e($order['courier_name'] ?? 'Trax Logistics') ?></strong><br>
                    Tracking Code: <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?= e($order['tracking_code']) ?></code>
                </div>
            </div>
        </div>

        <?php if (!empty($orderItems)): ?>
            <div style="border-top: 1px solid #f1f5f9; padding-top: 16px; margin-bottom: 16px;">
                <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Items Ordered</h4>
                <?php foreach ($orderItems as $item): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; padding: 8px 0; border-bottom: 1px dashed #f1f5f9;">
                        <div>
                            <strong><?= e($item['product_name']) ?></strong> &times; <?= $item['quantity'] ?>
                            <div style="font-size: 11px; color: #64748b;"><?= e($item['variant_info']) ?></div>
                        </div>
                        <div style="font-weight: 700; color: #0f766e;"><?= formatPrice($item['total']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Total Breakdown -->
        <div style="border-top: 2px solid #f1f5f9; padding-top: 14px;">
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px;">
                <span style="color: #64748b;">Subtotal</span>
                <span><?= formatPrice($order['subtotal'] ?? ($order['total_amount'] - 200)) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px;">
                <span style="color: #64748b;">Shipping Fee</span>
                <span><?= formatPrice($order['shipping_fee'] ?? 200) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; color: #0f766e; border-top: 1px solid #f1f5f9; padding-top: 12px;">
                <span>Total Amount Due</span>
                <span><?= formatPrice($order['total_amount']) ?></span>
            </div>
        </div>
    </div>

    <!-- Actions Row -->
    <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/track-order.php?q=<?= urlencode($order['order_number']) ?>" class="btn-primary" style="padding: 12px 24px; font-size: 14px; border-radius: 8px;">
            📦 Track Order Status Live
        </a>
        <a href="https://wa.me/<?= STORE_WHATSAPP ?>?text=<?= urlencode('Assalam-o-Alaikum! Inquiring about my confirmed order ' . $order['order_number']) ?>" target="_blank" class="btn-secondary" style="background: #25d366; color: #fff; border: none; padding: 12px 24px; font-size: 14px; border-radius: 8px;">
            💬 WhatsApp Order Updates
        </a>
        <a href="<?= BASE_URL ?>/shop.php" class="btn-secondary" style="color: #0f172a; border-color: #cbd5e1; padding: 12px 24px; font-size: 14px; border-radius: 8px;">
            Continue Shopping
        </a>
    </div>
</div>

<script>
// Clear local storage cart once confirmation renders
localStorage.removeItem('bazarpk_cart');
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
