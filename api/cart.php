<?php
/**
 * Cart API
 * Syncs client-side cart items with PHP session
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'get';

if ($action === 'sync' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $items = $input['items'] ?? [];

    $_SESSION['cart_items'] = $items;
    
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
    }
    $_SESSION['cart_subtotal'] = $subtotal;

    jsonResponse(['success' => true, 'count' => count($items), 'subtotal' => $subtotal]);
}

if ($action === 'get') {
    $items = $_SESSION['cart_items'] ?? [];
    $subtotal = $_SESSION['cart_subtotal'] ?? 0;
    jsonResponse(['success' => true, 'items' => $items, 'subtotal' => $subtotal]);
}

jsonResponse(['error' => 'Invalid action'], 400);
