<?php
/**
 * Coupons API
 * Validates discount codes in Pakistani Rupees
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

$code = strtoupper(trim($_GET['code'] ?? ''));
$subtotal = (float)($_GET['subtotal'] ?? 0);

if (empty($code)) {
    jsonResponse(['success' => false, 'message' => 'Please enter a coupon code.'], 400);
}

$pdo = Database::getConnection();

if (!$pdo) {
    // Fallback verification for demo codes
    if ($code === 'WELCOME10') {
        $discount = round($subtotal * 0.10);
        jsonResponse(['success' => true, 'code' => 'WELCOME10', 'discount' => $discount, 'message' => '10% discount applied!']);
    } elseif ($code === 'PKFREESHIP') {
        jsonResponse(['success' => true, 'code' => 'PKFREESHIP', 'discount' => 250, 'message' => 'Free shipping voucher applied!']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid or expired promo code.'], 404);
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        jsonResponse(['success' => false, 'message' => 'Invalid or inactive promo code.'], 404);
    }

    // Check expiry
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
        jsonResponse(['success' => false, 'message' => 'This promo code has expired.'], 400);
    }

    // Check min spend
    if ($coupon['min_spend'] > 0 && $subtotal < $coupon['min_spend']) {
        jsonResponse([
            'success' => false, 
            'message' => 'Minimum spend of Rs. ' . number_format($coupon['min_spend']) . ' required for this coupon.'
        ], 400);
    }

    // Calculate discount
    $discount = 0.0;
    if ($coupon['discount_type'] === 'percentage') {
        $discount = ($subtotal * ((float)$coupon['discount_value'] / 100));
        if ($coupon['max_discount'] > 0 && $discount > $coupon['max_discount']) {
            $discount = (float)$coupon['max_discount'];
        }
    } elseif ($coupon['discount_type'] === 'fixed') {
        $discount = (float)$coupon['discount_value'];
    } elseif ($coupon['discount_type'] === 'free_shipping') {
        $discount = (float)$coupon['discount_value'];
    }

    jsonResponse([
        'success'  => true,
        'code'     => $coupon['code'],
        'type'     => $coupon['discount_type'],
        'discount' => round($discount),
        'message'  => 'Coupon applied successfully!'
    ]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
