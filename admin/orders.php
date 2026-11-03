<?php
/**
 * Orders Management & Fulfillment
 * BazarPK - Super Admin Dashboard
 */

$adminTitle = "Orders Management";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();
$statusFilter = sanitize($_GET['status'] ?? 'all');
$search = sanitize($_GET['q'] ?? '');

$orders = [];

if ($pdo) {
    try {
        $sql = "SELECT * FROM `orders` WHERE 1=1";
        $params = [];

        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $sql .= " AND order_status = ?";
            $params[] = $statusFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR tracking_code LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
    } catch (Exception $e) {}
}

// Quick status counts
$counts = ['all' => 0, 'pending' => 0, 'confirmed' => 0, 'processing' => 0, 'shipped' => 0, 'delivered' => 0, 'cancelled' => 0];
if ($pdo) {
    try {
        $cStmt = $pdo->query("SELECT order_status, COUNT(*) as cnt FROM `orders` GROUP BY order_status");
        $allCount = 0;
        while ($r = $cStmt->fetch()) {
            $counts[$r['order_status']] = (int)$r['cnt'];
            $allCount += (int)$r['cnt'];
        }
        $counts['all'] = $allCount;
    } catch (Exception $e) {}
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h1 class="admin-card-title">Customer Orders (Pakistan)</h1>
            <span style="font-size: 13px; color: #64748b;">Manage logistics dispatch, courier tracking, and payment verification</span>
        </div>

        <!-- Search Form -->
        <form method="GET" action="<?= BASE_URL ?>/admin/orders.php" style="display: flex; gap: 8px;">
            <?php if (!empty($statusFilter) && $statusFilter !== 'all'): ?>
                <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search Order #, Phone, Name..." class="admin-input" style="width: 260px; padding: 7px 12px; font-size: 13px;">
            <button type="submit" class="btn-admin" style="padding: 7px 14px; font-size: 13px;">Search</button>
        </form>
    </div>

    <!-- Status Tabs -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; overflow-x: auto;">
        <a href="<?= BASE_URL ?>/admin/orders.php?status=all" class="admin-nav-item <?= $statusFilter === 'all' ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 13px; border-radius: 9999px;">
            All (<?= $counts['all'] ?>)
        </a>
        <a href="<?= BASE_URL ?>/admin/orders.php?status=pending" class="admin-nav-item <?= $statusFilter === 'pending' ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 13px; border-radius: 9999px;">
            Pending (<?= $counts['pending'] ?>)
        </a>
        <a href="<?= BASE_URL ?>/admin/orders.php?status=confirmed" class="admin-nav-item <?= $statusFilter === 'confirmed' ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 13px; border-radius: 9999px;">
            Confirmed (<?= $counts['confirmed'] ?>)
        </a>
        <a href="<?= BASE_URL ?>/admin/orders.php?status=processing" class="admin-nav-item <?= $statusFilter === 'processing' ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 13px; border-radius: 9999px;">
            Processing (<?= $counts['processing'] ?>)
        </a>
        <a href="<?= BASE_URL ?>/admin/orders.php?status=shipped" class="admin-nav-item <?= $statusFilter === 'shipped' ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 13px; border-radius: 9999px;">
            Shipped (<?= $counts['shipped'] ?>)
        </a>
        <a href="<?= BASE_URL ?>/admin/orders.php?status=delivered" class="admin-nav-item <?= $statusFilter === 'delivered' ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 13px; border-radius: 9999px;">
            Delivered (<?= $counts['delivered'] ?>)
        </a>
    </div>

    <!-- Orders Table -->
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer Details</th>
                    <th>City / Province</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Tracking</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: #64748b; padding: 32px;">No orders found matching this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/order-detail.php?id=<?= $o['id'] ?>" style="font-weight: 700; color: #0f766e;">
                                    <?= e($o['order_number']) ?>
                                </a>
                            </td>
                            <td style="font-size: 12px; color: #64748b;">
                                <?= date('d M Y, h:i A', strtotime($o['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;"><?= e($o['customer_name']) ?></div>
                                <div style="font-size: 12px; color: #475569;">📞 <?= e($o['customer_phone']) ?></div>
                                <div style="font-size: 11px; color: #94a3b8;"><?= e($o['customer_email']) ?></div>
                            </td>
                            <td>
                                <strong><?= e($o['shipping_city']) ?></strong>
                                <div style="font-size: 11px; color: #64748b;"><?= e($o['shipping_province']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 700; text-transform: uppercase; font-size: 11.5px;"><?= e($o['payment_method']) ?></div>
                                <span class="badge-status badge-<?= e($o['payment_status']) ?>" style="font-size: 10.5px;">
                                    <?= e($o['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status badge-<?= e($o['order_status']) ?>">
                                    <?= str_replace('_', ' ', e($o['order_status'])) ?>
                                </span>
                            </td>
                            <td style="font-weight: 800; color: #0f766e;">
                                <?= formatPrice($o['total_amount']) ?>
                            </td>
                            <td style="font-size: 12px;">
                                <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?= e($o['tracking_code']) ?></code>
                                <div style="font-size: 10px; color: #64748b;"><?= e($o['courier_name'] ?? 'Trax Logistics') ?></div>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/order-detail.php?id=<?= $o['id'] ?>" class="btn-admin" style="padding: 6px 12px; font-size: 12px;">
                                    Process &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
