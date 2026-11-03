<?php
/**
 * Products API Endpoint
 * Live Search, Category Filter, and Product Details
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'search';

if (!$pdo) {
    // Return sample data if database not connected yet
    jsonResponse([
        'success' => true,
        'products' => [
            ['id' => 1, 'name' => 'Royal Navy Egyptian Cotton Kurta', 'price' => 3450, 'main_image' => 'https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=800&auto=format&fit=crop&q=80'],
            ['id' => 3, 'name' => 'ProBass ANC Wireless Earbuds (v5.3)', 'price' => 4999, 'main_image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80'],
            ['id' => 5, 'name' => 'Authentic Kaptaan Chappal (Double Sole)', 'price' => 3950, 'main_image' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=800&auto=format&fit=crop&q=80']
        ]
    ]);
}

if ($action === 'search') {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) {
        jsonResponse(['success' => true, 'products' => []]);
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id, name, slug, price, compare_at_price, main_image 
            FROM products 
            WHERE is_active = 1 AND (name LIKE ? OR short_description LIKE ? OR sku LIKE ?)
            LIMIT 8
        ");
        $term = "%{$q}%";
        $stmt->execute([$term, $term, $term]);
        $products = $stmt->fetchAll();

        jsonResponse(['success' => true, 'products' => $products]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

jsonResponse(['error' => 'Unsupported action'], 400);
