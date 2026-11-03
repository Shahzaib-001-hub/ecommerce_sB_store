<?php
/**
 * Super Admin Overview Dashboard
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$adminTitle = "Overview";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Default stats
$totalSales = 0;
$totalOrders = 0;
$totalCustomers = 0;
$lowStockCount = 0;
$recentOrders = [];
$lowStockProducts = [];

if ($pdo) {
    try {
        // Total Sales
        $salesStmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM `orders` WHERE order_status != 'cancelled'");
        $totalSales = (float)$salesStmt->fetchColumn();

        // Total Orders
        $ordStmt = $pdo->query("SELECT COUNT(*) FROM `orders`");
        $totalOrders = (int)$ordStmt->fetchColumn();

        // Total Customers
        $custStmt = $pdo->query("SELECT COUNT(*) FROM `users` WHERE role = 'customer'");
        $totalCustomers = (int)$custStmt->fetchColumn();

        // Low stock products
        $stockStmt = $pdo->query("SELECT COUNT(*) FROM `products` WHERE stock_quantity <= low_stock_threshold");
        $lowStockCount = (int)$stockStmt->fetchColumn();

        // Recent 5 Orders
        $recStmt = $pdo->query("SELECT * FROM `orders` ORDER BY id DESC LIMIT 5");
        $recentOrders = $recStmt->fetchAll();

        // Low stock items
        $lowProdStmt = $pdo->query("SELECT id, name, sku, stock_quantity, low_stock_threshold FROM `products` WHERE stock_quantity <= low_stock_threshold ORDER BY stock_quantity ASC LIMIT 4");
        $lowStockProducts = $lowProdStmt->fetchAll();
    } catch (Exception $e) {}
} else {
    // Preview mode stats
    $totalSales = 485900;
    $totalOrders = 124;
    $totalCustomers = 89;
    $lowStockCount = 2;
}
?>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <span class="kpi-title">Total Revenue (Pakistan)</span>
        <div class="kpi-value" style="color: #0f766e;"><?= formatPrice($totalSales) ?></div>
        <div class="kpi-change kpi-positive">&uarr; 18.4% vs last month</div>
    </div>

    <div class="kpi-card">
        <span class="kpi-title">Total Orders Placed</span>
        <div class="kpi-value"><?= number_format($totalOrders) ?></div>
        <div class="kpi-change kpi-positive">&uarr; 12 new today</div>
    </div>

    <div class="kpi-card">
        <span class="kpi-title">Registered Customers</span>
        <div class="kpi-value"><?= number_format($totalCustomers) ?></div>
        <div class="kpi-change" style="color: #64748b;">Karachi, Lahore &amp; ISB</div>
    </div>

    <div class="kpi-card">
        <span class="kpi-title">Low Stock Alert</span>
        <div class="kpi-value" style="color: <?= $lowStockCount > 0 ? '#dc2626' : '#16a34a' ?>;">
            <?= $lowStockCount ?> <?= $lowStockCount > 0 ? '⚠️' : '✓' ?>
        </div>
        <div class="kpi-change kpi-warning">Action recommended</div>
    </div>
</div>

<!-- Sales Analytics Chart & Logistics Status -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 28px;">
    <!-- Sales Overview Card with Pure SVG Chart -->
    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card-header">
            <div>
                <h3 class="admin-card-title">Weekly Revenue Trends (PKR)</h3>
                <span style="font-size: 13px; color: #64748b;">Daily dispatch volumes across Pakistan</span>
            </div>
            <span style="background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">Last 7 Days</span>
        </div>

        <!-- Pure Responsive SVG Bar Chart (zero external dependencies) -->
        <div style="height: 180px; width: 100%; display: flex; align-items: flex-end; gap: 20px; padding: 20px 0 10px 0; border-bottom: 1px solid #e2e8f0;">
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #0f766e;">42k</span>
                <div style="width: 100%; height: 80px; background: #ccfbf1; border-radius: 4px; border-top: 3px solid #0f766e;"></div>
                <span style="font-size: 11px; color: #64748b;">Mon</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #0f766e;">68k</span>
                <div style="width: 100%; height: 110px; background: #ccfbf1; border-radius: 4px; border-top: 3px solid #0f766e;"></div>
                <span style="font-size: 11px; color: #64748b;">Tue</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #0f766e;">95k</span>
                <div style="width: 100%; height: 140px; background: #0f766e; border-radius: 4px;"></div>
                <span style="font-size: 11px; color: #0f766e; font-weight: 700;">Wed</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #0f766e;">54k</span>
                <div style="width: 100%; height: 95px; background: #ccfbf1; border-radius: 4px; border-top: 3px solid #0f766e;"></div>
                <span style="font-size: 11px; color: #64748b;">Thu</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #0f766e;">120k</span>
                <div style="width: 100%; height: 160px; background: #0f766e; border-radius: 4px;"></div>
                <span style="font-size: 11px; color: #0f766e; font-weight: 700;">Fri (Jumma)</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #0f766e;">84k</span>
                <div style="width: 100%; height: 125px; background: #ccfbf1; border-radius: 4px; border-top: 3px solid #0f766e;"></div>
                <span style="font-size: 11px; color: #64748b;">Sat</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #0f766e;">72k</span>
                <div style="width: 100%; height: 105px; background: #ccfbf1; border-radius: 4px; border-top: 3px solid #0f766e;"></div>
                <span style="font-size: 11px; color: #64748b;">Sun</span>
            </div>
        </div>
    </div>

    <!-- Payment Channels Distribution -->
    <div class="admin-card" style="margin-bottom: 0;">
        <h3 class="admin-card-title" style="margin-bottom: 16px;">Payment Breakdown</h3>
        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13px;">
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>💵 Cash on Delivery (COD)</strong>
                    <span style="font-weight: 700;">72%</span>
                </div>
                <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #0f766e; height: 100%; width: 72%;"></div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>📱 JazzCash Wallet</strong>
                    <span style="font-weight: 700;">16%</span>
                </div>
                <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #dc2626; height: 100%; width: 16%;"></div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>📱 Easypaisa Direct</strong>
                    <span style="font-weight: 700;">8%</span>
                </div>
                <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #16a34a; height: 100%; width: 8%;"></div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>💳 Cards (Visa/Mastercard)</strong>
                    <span style="font-weight: 700;">4%</span>
                </div>
                <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #2563eb; height: 100%; width: 4%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h3 class="admin-card-title">Recent Customer Orders</h3>
            <span style="font-size: 13px; color: #64748b;">Latest transactions requiring verification &amp; fulfillment</span>
        </div>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="btn-admin" style="font-size: 12px; padding: 7px 14px;">
            View All Orders &rarr;
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Destination</th>
                    <th>Method</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 24px;">No orders found yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $ro): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/order-detail.php?id=<?= $ro['id'] ?>" style="font-weight: 700; color: #0f766e;">
                                    <?= e($ro['order_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;"><?= e($ro['customer_name']) ?></div>
                                <div style="font-size: 11px; color: #64748b;">📞 <?= e($ro['customer_phone']) ?></div>
                            </td>
                            <td><?= e($ro['shipping_city']) ?></td>
                            <td><span style="text-transform: uppercase; font-weight: 700; font-size: 11px;"><?= e($ro['payment_method']) ?></span></td>
                            <td>
                                <span class="badge-status badge-<?= e($ro['payment_status']) ?>">
                                    <?= e($ro['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status badge-<?= e($ro['order_status']) ?>">
                                    <?= str_replace('_', ' ', e($ro['order_status'])) ?>
                                </span>
                            </td>
                            <td style="font-weight: 800; color: #0f766e;"><?= formatPrice($ro['total_amount']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/order-detail.php?id=<?= $ro['id'] ?>" class="btn-admin-secondary" style="padding: 4px 10px; font-size: 12px;">
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
