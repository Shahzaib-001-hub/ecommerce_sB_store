<?php
/**
 * Order Tracking API
 * Real-time order lookup by Order ID or Phone Number
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');
if (empty($query)) {
    jsonResponse(['success' => false, 'error' => 'Please enter an Order Number or Mobile Number.'], 400);
}

$pdo = Database::getConnection();

if (!$pdo) {
    // Demo response if database not initialized
    jsonResponse([
        'success' => true,
        'order' => [
            'order_number' => 'PK-2611-1001',
            'customer_name' => 'Muhammad Bilal',
            'shipping_city' => 'Lahore',
            'order_status' => 'shipped',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'total_amount' => 5199,
            'tracking_code' => 'TRK-PK-891024',
            'courier_name' => 'Trax Logistics',
            'created_at' => date('d M Y, h:i A', strtotime('-2 days')),
            'timeline' => [
                ['status' => 'Order Placed', 'done' => true, 'time' => date('d M, h:i A', strtotime('-2 days'))],
                ['status' => 'Confirmed & Packed', 'done' => true, 'time' => date('d M, h:i A', strtotime('-1 day'))],
                ['status' => 'Dispatched via Courier', 'done' => true, 'time' => date('d M, h:i A')],
                ['status' => 'Out for Delivery', 'done' => false, 'time' => 'Expected Tomorrow'],
                ['status' => 'Delivered', 'done' => false, 'time' => 'Pending']
            ]
        ]
    ]);
}

try {
    $cleanPhone = formatPakistaniPhone($query);
    
    $stmt = $pdo->prepare("
        SELECT id, order_number, customer_name, customer_phone, customer_email,
               shipping_city, shipping_province, payment_method, payment_status,
               subtotal, shipping_fee, discount_amount, total_amount, order_status,
               tracking_code, courier_name, created_at
        FROM `orders`
        WHERE order_number = ? OR tracking_code = ? OR customer_phone = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$query, $query, $cleanPhone]);
    $order = $stmt->fetch();

    if (!$order) {
        jsonResponse(['success' => false, 'error' => 'No order found matching "' . e($query) . '". Please check your details.'], 404);
    }

    // Build timeline progress
    $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered'];
    $currentIdx = array_search($order['order_status'], $statuses, true);
    if ($currentIdx === false) $currentIdx = 0;

    $timeline = [
        ['status' => 'Order Placed', 'done' => true, 'time' => date('d M Y, h:i A', strtotime($order['created_at']))],
        ['status' => 'Confirmed by Store', 'done' => ($currentIdx >= 1), 'time' => ($currentIdx >= 1 ? 'Verified' : 'Pending')],
        ['status' => 'Packed & QC Passed', 'done' => ($currentIdx >= 2), 'time' => ($currentIdx >= 2 ? 'Processed' : 'Pending')],
        ['status' => 'Dispatched (' . ($order['courier_name'] ?: 'Trax') . ')', 'done' => ($currentIdx >= 3), 'time' => ($currentIdx >= 3 ? 'In Transit' : 'Pending')],
        ['status' => 'Out for Delivery', 'done' => ($currentIdx >= 4), 'time' => ($currentIdx >= 4 ? 'Courier Agent Out' : 'Pending')],
        ['status' => 'Delivered & Completed', 'done' => ($currentIdx >= 5), 'time' => ($currentIdx >= 5 ? 'Delivered' : 'Pending')]
    ];

    $order['timeline'] = $timeline;
    $order['formatted_total'] = formatPrice($order['total_amount']);

    jsonResponse(['success' => true, 'order' => $order]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
