<?php
/**
 * Customer Management
 * BazarPK - Super Admin Dashboard
 */

$adminTitle = "Customers";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();
$customers = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT u.*, 
                   COUNT(o.id) as total_orders, 
                   COALESCE(SUM(o.total_amount), 0) as total_spent 
            FROM `users` u 
            LEFT JOIN `orders` o ON o.customer_id = u.id 
            WHERE u.role = 'customer' 
            GROUP BY u.id 
            ORDER BY u.id DESC
        ");
        $customers = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h1 class="admin-card-title">Registered Customers (Pakistan)</h1>
            <span style="font-size: 13px; color: #64748b;">Buyer profiles, contact numbers, order frequency, and lifetime spend</span>
        </div>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Contact</th>
                <th>Location</th>
                <th>Orders</th>
                <th>Lifetime Spent</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No customer records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($c['name']) ?></div>
                            <span class="badge-status badge-delivered" style="font-size: 10px;">Verified Customer</span>
                        </td>
                        <td>
                            <div>📞 <a href="tel:<?= e($c['phone']) ?>" style="color: #0f766e; font-weight: 600;"><?= e($c['phone']) ?></a></div>
                            <div style="font-size: 12px; color: #64748b;"><?= e($c['email']) ?></div>
                        </td>
                        <td>
                            <strong><?= e($c['city'] ?: 'Karachi') ?></strong>, <?= e($c['province'] ?: 'Sindh') ?>
                        </td>
                        <td style="font-weight: 700;"><?= $c['total_orders'] ?> orders</td>
                        <td style="font-weight: 800; color: #0f766e;"><?= formatPrice($c['total_spent']) ?></td>
                        <td style="font-size: 12px; color: #64748b;"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                        <td>
                            <a href="https://wa.me/<?= formatPakistaniPhone($c['phone'] ?? '') ?>" target="_blank" class="btn-admin-secondary" style="font-size: 11px; padding: 4px 8px; color: #15803d;">
                                💬 WhatsApp
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
