<?php
/**
 * Order Detail & Fulfillment Action
 * BazarPK - Super Admin Dashboard
 */

$adminTitle = "Process Order";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$order = null;
$items = [];
$history = [];

if ($pdo && $orderId > 0) {
    // Handle status update POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Security check failed. Please refresh.');
        } elseif ($_POST['action'] === 'update_status') {
            $newStatus = sanitize($_POST['order_status'] ?? '');
            $newPayment = sanitize($_POST['payment_status'] ?? '');
            $courierName = sanitize($_POST['courier_name'] ?? '');
            $trackingCode = sanitize($_POST['tracking_code'] ?? '');
            $adminNotes = sanitize($_POST['admin_notes'] ?? '');

            try {
                $curStmt = $pdo->prepare("SELECT order_status FROM `orders` WHERE id = ?");
                $curStmt->execute([$orderId]);
                $oldStatus = $curStmt->fetchColumn();

                $upStmt = $pdo->prepare("
                    UPDATE `orders` 
                    SET `order_status` = ?, `payment_status` = ?, `courier_name` = ?, `tracking_code` = ?, `admin_notes` = ? 
                    WHERE `id` = ?
                ");
                $upStmt->execute([$newStatus, $newPayment, $courierName, $trackingCode, $adminNotes, $orderId]);

                if ($oldStatus !== $newStatus) {
                    $hist = $pdo->prepare("
                        INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `comment`, `created_by`)
                        VALUES (?, ?, ?, ?, 'Super Admin')
                    ");
                    $hist->execute([$orderId, $oldStatus, $newStatus, $adminNotes ?: 'Order status updated by admin.']);
                }

                setFlash('success', 'Order status updated successfully.');
            } catch (Exception $e) {
                setFlash('error', 'Error updating order: ' . $e->getMessage());
            }
        }
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order) {
            $itemStmt = $pdo->prepare("SELECT * FROM `order_items` WHERE order_id = ?");
            $itemStmt->execute([$orderId]);
            $items = $itemStmt->fetchAll();

            $histStmt = $pdo->prepare("SELECT * FROM `order_status_history` WHERE order_id = ? ORDER BY id DESC");
            $histStmt->execute([$orderId]);
            $history = $histStmt->fetchAll();
        }
    } catch (Exception $e) {}
}

if (!$order) {
    echo '<div class="admin-card"><p>Order not found.</p><a href="' . BASE_URL . '/admin/orders.php" class="btn-admin">Back to Orders</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <a href="<?= BASE_URL ?>/admin/orders.php" style="font-size: 13px; color: #0f766e; text-decoration: none; font-weight: 600;">&larr; Back to All Orders</a>
        <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 4px 0 0 0;">
            Order <?= e($order['order_number']) ?>
        </h1>
    </div>

    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="window.print()" class="btn-admin-secondary">
            🖨️ Print Packing Slip / Invoice
        </button>
        <a href="https://wa.me/<?= formatPakistaniPhone($order['customer_phone']) ?>?text=<?= urlencode('Assalam-o-Alaikum ' . $order['customer_name'] . '! This is BazarPK regarding your order ' . $order['order_number'] . '.') ?>" target="_blank" class="btn-admin" style="background: #25d366;">
            💬 WhatsApp Customer
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px;">
    <!-- Left Column: Items & Customer Details -->
    <div>
        <!-- Order Items Card -->
        <div class="admin-card">
            <h3 class="admin-card-title" style="margin-bottom: 16px;">Items in this Order</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td style="font-weight: 700; color: #0f172a;"><?= e($item['product_name']) ?></td>
                            <td style="font-size: 12px; color: #64748b;"><?= e($item['variant_info']) ?></td>
                            <td><?= formatPrice($item['price']) ?></td>
                            <td style="font-weight: 700;"><?= $item['quantity'] ?></td>
                            <td style="font-weight: 800; color: #0f766e;"><?= formatPrice($item['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Financial Totals -->
            <div style="border-top: 2px solid #f1f5f9; margin-top: 16px; padding-top: 14px; max-width: 300px; margin-left: auto;">
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                    <span style="color: #64748b;">Subtotal:</span>
                    <span><?= formatPrice($order['subtotal']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                    <span style="color: #64748b;">Shipping Fee:</span>
                    <span><?= formatPrice($order['shipping_fee']) ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; color: #dc2626;">
                        <span>Discount:</span>
                        <span>- <?= formatPrice($order['discount_amount']) ?></span>
                    </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; color: #0f766e; border-top: 1px solid #e2e8f0; padding-top: 8px;">
                    <span>Grand Total:</span>
                    <span><?= formatPrice($order['total_amount']) ?></span>
                </div>
            </div>
        </div>

        <!-- Status History Audit Trail -->
        <div class="admin-card">
            <h3 class="admin-card-title" style="margin-bottom: 16px;">Order Audit Trail</h3>
            <?php if (empty($history)): ?>
                <p style="font-size: 13px; color: #64748b;">No status history recorded yet.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($history as $h): ?>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <strong style="color: #0f172a;"><?= e($h['new_status']) ?></strong>
                                <span style="font-size: 11px; color: #64748b;"><?= date('d M Y, h:i A', strtotime($h['created_at'])) ?> by <?= e($h['created_by']) ?></span>
                            </div>
                            <div style="color: #475569;"><?= e($h['comment']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Status Actions & Shipping Info -->
    <div>
        <!-- Order Processing Form -->
        <div class="admin-card">
            <h3 class="admin-card-title" style="margin-bottom: 16px;">Update Order Status</h3>

            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_status">

                <div style="margin-bottom: 14px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Order Status</label>
                    <select name="order_status" class="admin-input">
                        <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending Verification</option>
                        <option value="confirmed" <?= $order['order_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing / Packing</option>
                        <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped (In Transit)</option>
                        <option value="out_for_delivery" <?= $order['order_status'] === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                        <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered &amp; Settled</option>
                        <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="returned" <?= $order['order_status'] === 'returned' ? 'selected' : '' ?>>Returned to Origin</option>
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Payment Status</label>
                    <select name="payment_status" class="admin-input">
                        <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid (COD Pending)</option>
                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid (Verified)</option>
                        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Courier Service Partner</label>
                    <select name="courier_name" class="admin-input">
                        <option value="Trax Logistics" <?= ($order['courier_name'] ?? '') === 'Trax Logistics' ? 'selected' : '' ?>>Trax Logistics (Recommended)</option>
                        <option value="TCS Express" <?= ($order['courier_name'] ?? '') === 'TCS Express' ? 'selected' : '' ?>>TCS Express</option>
                        <option value="CallCourier" <?= ($order['courier_name'] ?? '') === 'CallCourier' ? 'selected' : '' ?>>CallCourier</option>
                        <option value="Leopard Courier" <?= ($order['courier_name'] ?? '') === 'Leopard Courier' ? 'selected' : '' ?>>Leopard Courier</option>
                        <option value="M&P Logistics" <?= ($order['courier_name'] ?? '') === 'M&P Logistics' ? 'selected' : '' ?>>M&amp;P Logistics</option>
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Tracking Number (AWB)</label>
                    <input type="text" name="tracking_code" value="<?= e($order['tracking_code']) ?>" class="admin-input">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Admin Internal Notes</label>
                    <textarea name="admin_notes" rows="2" class="admin-input"><?= e($order['admin_notes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-admin" style="width: 100%; justify-content: center; padding: 11px;">
                    Save Order Changes
                </button>
            </form>
        </div>

        <!-- Customer & Delivery Summary -->
        <div class="admin-card">
            <h3 class="admin-card-title" style="margin-bottom: 14px;">Delivery Information</h3>
            <div style="font-size: 13px; line-height: 1.6; color: #334155;">
                <strong style="color: #0f172a; font-size: 14px;"><?= e($order['customer_name']) ?></strong><br>
                📞 Phone: <a href="tel:<?= e($order['customer_phone']) ?>" style="color: #0f766e; font-weight: 700;"><?= e($order['customer_phone']) ?></a><br>
                ✉️ Email: <?= e($order['customer_email']) ?><br><br>

                <strong>Address:</strong><br>
                <?= e($order['shipping_address']) ?><br>
                <?= e($order['shipping_city']) ?>, <?= e($order['shipping_province']) ?><br>
                Postal Code: <?= e($order['shipping_postal_code'] ?: 'N/A') ?><br><br>

                <?php if (!empty($order['delivery_notes'])): ?>
                    <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 6px; padding: 8px 10px; font-size: 12px; color: #92400e;">
                        <strong>Customer Note:</strong> <?= e($order['delivery_notes']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
