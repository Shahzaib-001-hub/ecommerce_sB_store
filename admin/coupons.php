<?php
/**
 * Coupons & Promo Code Management
 * BazarPK - Super Admin Dashboard
 */

$adminTitle = "Coupons & Discounts";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security check failed. Please refresh.');
    } else {
        $action = sanitize($_POST['action'] ?? '');

        if ($action === 'add_coupon') {
            $code      = strtoupper(sanitize($_POST['code'] ?? ''));
            $type      = sanitize($_POST['discount_type'] ?? 'percentage');
            $value     = (float)($_POST['discount_value'] ?? 0);
            $minSpend  = (float)($_POST['min_spend'] ?? 0);
            $limit     = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
            $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] . ' 23:59:59' : null;

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_spend`, `usage_limit`, `expires_at`, `is_active`)
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$code, $type, $value, $minSpend, $limit, $expiresAt]);
                setFlash('success', "Coupon code '{$code}' created successfully.");
            } catch (Exception $e) {
                setFlash('error', 'Error creating coupon: ' . $e->getMessage());
            }
        } elseif ($action === 'delete_coupon') {
            $cId = (int)($_POST['coupon_id'] ?? 0);
            try {
                $stmt = $pdo->prepare("DELETE FROM `coupons` WHERE id = ?");
                $stmt->execute([$cId]);
                setFlash('success', 'Coupon deleted.');
            } catch (Exception $e) {
                setFlash('error', 'Error deleting coupon: ' . $e->getMessage());
            }
        }
    }
}

$coupons = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `coupons` ORDER BY id DESC");
        $coupons = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h1 class="admin-card-title">Coupons &amp; Discount Vouchers</h1>
            <span style="font-size: 13px; color: #64748b;">Configure promotional codes, percentage savings, and free shipping vouchers</span>
        </div>
        <button type="button" class="btn-admin" onclick="openModal('addCouponModal')">
            + Create Coupon
        </button>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Type</th>
                <th>Discount Value</th>
                <th>Min Spend</th>
                <th>Usage Count</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coupons as $c): ?>
                <tr>
                    <td><strong style="color: #0f766e; font-size: 14px;"><?= e($c['code']) ?></strong></td>
                    <td style="text-transform: capitalize;"><?= str_replace('_', ' ', e($c['discount_type'])) ?></td>
                    <td style="font-weight: 700;">
                        <?= $c['discount_type'] === 'percentage' ? ((int)$c['discount_value'] . '%') : formatPrice($c['discount_value']) ?>
                    </td>
                    <td><?= formatPrice($c['min_spend']) ?></td>
                    <td><?= $c['times_used'] ?> / <?= $c['usage_limit'] ?: '&infin;' ?></td>
                    <td style="font-size: 12px; color: #64748b;">
                        <?= $c['expires_at'] ? date('d M Y', strtotime($c['expires_at'])) : 'No Expiry' ?>
                    </td>
                    <td>
                        <span class="badge-status <?= $c['is_active'] ? 'badge-delivered' : 'badge-cancelled' ?>">
                            <?= $c['is_active'] ? 'Active' : 'Disabled' ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this coupon?');" style="display: inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_coupon">
                            <input type="hidden" name="coupon_id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn-admin-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Coupon Modal -->
<div id="addCouponModal" class="modal-overlay">
    <div class="modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: #0f172a;">Create New Coupon</h3>
            <button type="button" onclick="closeModal('addCouponModal')" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_coupon">

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Coupon Code *</label>
                <input type="text" name="code" required placeholder="e.g. EIDMUBARAK20" class="admin-input" style="text-transform: uppercase;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Discount Type *</label>
                    <select name="discount_type" class="admin-input">
                        <option value="percentage">Percentage (%) Off</option>
                        <option value="fixed">Fixed Amount (PKR) Off</option>
                        <option value="free_shipping">Free Shipping Voucher</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Discount Value *</label>
                    <input type="number" step="0.01" name="discount_value" required placeholder="10 or 500" class="admin-input">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Minimum Order Amount (PKR)</label>
                    <input type="number" name="min_spend" value="1500" class="admin-input">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Usage Limit (Times)</label>
                    <input type="number" name="usage_limit" placeholder="500" class="admin-input">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Expiration Date</label>
                <input type="date" name="expires_at" class="admin-input">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('addCouponModal')" class="btn-admin-secondary">Cancel</button>
                <button type="submit" class="btn-admin">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
