<?php
/**
 * Customer Reviews API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$name      = sanitize($_POST['name'] ?? '');
$city      = sanitize($_POST['city'] ?? 'Pakistan');
$rating    = max(1, min(5, (int)($_POST['rating'] ?? 5)));
$comment   = sanitize($_POST['comment'] ?? '');

if ($productId <= 0 || empty($name) || empty($comment)) {
    jsonResponse(['success' => false, 'error' => 'Please provide your name, rating, and review text.'], 422);
}

$pdo = Database::getConnection();
if (!$pdo) {
    jsonResponse(['success' => true, 'message' => 'Review recorded in preview mode.']);
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO `reviews` (`product_id`, `customer_name`, `customer_city`, `rating`, `comment`, `is_approved`)
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([$productId, $name, $city, $rating, $comment]);

    // Recalculate average rating & review count for product
    $avgStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as total_reviews FROM `reviews` WHERE product_id = ? AND is_approved = 1");
    $avgStmt->execute([$productId]);
    $stats = $avgStmt->fetch();

    $upStmt = $pdo->prepare("UPDATE `products` SET `rating_avg` = ?, `reviews_count` = ? WHERE `id` = ?");
    $upStmt->execute([$stats['avg_rating'], $stats['total_reviews'], $productId]);

    jsonResponse(['success' => true, 'message' => 'Review submitted successfully! Thank you for your feedback.']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
