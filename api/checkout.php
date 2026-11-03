<?php
/**
 * Checkout & Order Creation API Endpoint
 * Handles Order Persistence and Payment Routing
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

// Include Payment Gateways
require_once __DIR__ . '/../includes/payment_gateways/GatewayInterface.php';
require_once __DIR__ . '/../includes/payment_gateways/CashOnDelivery.php';
require_once __DIR__ . '/../includes/payment_gateways/JazzCashAdapter.php';
require_once __DIR__ . '/../includes/payment_gateways/EasypaisaAdapter.php';
require_once __DIR__ . '/../includes/payment_gateways/CardGatewayAdapter.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['error' => 'Invalid JSON input payload'], 400);
}

// 1. Verify CSRF Token
if (!verify_csrf($input['csrf_token'] ?? '')) {
    jsonResponse(['error' => 'Security token expired. Please refresh the checkout page.'], 403);
}

// 2. Validate Customer & Shipping Information
$customerName    = sanitize($input['customer_name'] ?? '');
$customerPhone   = sanitize($input['customer_phone'] ?? '');
$customerEmail   = filter_var($input['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
$shippingProvince= sanitize($input['shipping_province'] ?? '');
$shippingCity    = sanitize($input['shipping_city'] ?? '');
$shippingAddress = sanitize($input['shipping_address'] ?? '');
$shippingZip     = sanitize($input['shipping_postal_code'] ?? '');
$deliveryNotes   = sanitize($input['delivery_notes'] ?? '');
$paymentMethod   = sanitize($input['payment_method'] ?? 'cod');
$items           = $input['items'] ?? [];

if (empty($customerName) || empty($customerPhone) || empty($customerEmail) || empty($shippingAddress) || empty($shippingCity)) {
    jsonResponse(['error' => 'Please fill in all required delivery details (Name, Phone, Email, City, Address).'], 422);
}

if (!isValidPakistaniPhone($customerPhone)) {
    jsonResponse(['error' => 'Please enter a valid Pakistani mobile number (e.g. 03001234567).'], 422);
}

if (empty($items) || !is_array($items)) {
    jsonResponse(['error' => 'Your shopping cart is empty.'], 422);
}

// 3. Calculate Totals
$subtotal = 0.0;
foreach ($items as $item) {
    $qty = max(1, (int)($item['quantity'] ?? 1));
    $price = (float)($item['price'] ?? 0);
    $subtotal += ($qty * $price);
}

// Shipping Fee: Free if subtotal >= FREE_SHIPPING_THRESHOLD
$shippingFee = ($subtotal >= FREE_SHIPPING_THRESHOLD) ? 0.0 : (float)($input['shipping_fee'] ?? DEFAULT_SHIPPING_FEE);
$discountAmount = 0.0;
$totalAmount = max(0.0, ($subtotal + $shippingFee - $discountAmount));

$pdo = Database::getConnection();
$orderNumber = generateOrderNumber();
$trackingCode = generateTrackingCode();
$customerId = isCustomerLoggedIn() ? (int)$_SESSION['customer_id'] : null;

// Determine initial order & payment status
$initialOrderStatus   = ($paymentMethod === 'cod') ? STATUS_CONFIRMED : STATUS_PENDING;
$initialPaymentStatus = PAYMENT_UNPAID;

$orderId = null;

if ($pdo) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO `orders` (
                `order_number`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`,
                `shipping_address`, `shipping_city`, `shipping_province`, `shipping_postal_code`,
                `delivery_notes`, `payment_method`, `payment_status`, `subtotal`, `shipping_fee`,
                `discount_amount`, `total_amount`, `order_status`, `tracking_code`, `courier_name`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $courierName = in_array($shippingCity, ['Karachi', 'Hyderabad']) ? 'TCS Express' : 'Trax Logistics';

        $stmt->execute([
            $orderNumber, $customerId, $customerName, $customerEmail, $customerPhone,
            $shippingAddress, $shippingCity, $shippingProvince, $shippingZip,
            $deliveryNotes, $paymentMethod, $initialPaymentStatus, $subtotal, $shippingFee,
            $discountAmount, $totalAmount, $initialOrderStatus, $trackingCode, $courierName
        ]);

        $orderId = (int)$pdo->lastInsertId();

        // Insert Order Items & adjust stock
        $itemStmt = $pdo->prepare("
            INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `variant_info`, `price`, `quantity`, `total`)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stockStmt = $pdo->prepare("UPDATE `products` SET `stock_quantity` = GREATEST(0, stock_quantity - ?) WHERE `id` = ?");

        foreach ($items as $item) {
            $pId = !empty($item['id']) ? (int)$item['id'] : null;
            $pName = sanitize($item['name'] ?? 'Product');
            $pVariant = sanitize($item['variant'] ?? 'Standard');
            $pPrice = (float)($item['price'] ?? 0);
            $pQty = max(1, (int)($item['quantity'] ?? 1));
            $pTotal = $pPrice * $pQty;

            $itemStmt->execute([$orderId, $pId, $pName, $pVariant, $pPrice, $pQty, $pTotal]);

            if ($pId) {
                $stockStmt->execute([$pQty, $pId]);
            }
        }

        // Insert Audit Status History
        $histStmt = $pdo->prepare("
            INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `comment`, `created_by`)
            VALUES (?, '', ?, ?, 'Customer')
        ");
        $histComment = ($paymentMethod === 'cod') ? 'Order placed with Cash on Delivery.' : 'Order placed, awaiting online payment.';
        $histStmt->execute([$orderId, $initialOrderStatus, $histComment]);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse(['error' => 'Database error while saving order: ' . $e->getMessage()], 500);
    }
} else {
    // If running in preview mode before DB migration, generate mock IDs
    $orderId = rand(100, 999);
}

// 4. Select Payment Gateway Adapter
$gatewayAdapter = match ($paymentMethod) {
    'jazzcash'  => new JazzCashAdapter(),
    'easypaisa' => new EasypaisaAdapter(),
    'card'      => new CardGatewayAdapter(),
    default     => new CashOnDelivery(),
};

$orderData = [
    'id'             => $orderId,
    'order_number'   => $orderNumber,
    'total_amount'   => $totalAmount,
    'customer_name'  => $customerName,
    'customer_phone' => $customerPhone,
    'customer_email' => $customerEmail,
    'payment_method' => $paymentMethod
];

// Initialize payment session
$paymentSession = $gatewayAdapter->createPaymentSession($orderData);

// Save recent order info in session for confirmation receipt
$_SESSION['latest_order'] = [
    'order_id'      => $orderId,
    'order_number'  => $orderNumber,
    'tracking_code' => $trackingCode,
    'total_amount'  => $totalAmount,
    'customer_name' => $customerName,
    'customer_phone'=> $customerPhone,
    'shipping_city' => $shippingCity,
    'payment_method'=> $paymentMethod,
    'order_status'  => $initialOrderStatus,
    'payment_status'=> $initialPaymentStatus
];

// Clear server cart session
unset($_SESSION['cart_items']);
unset($_SESSION['cart_subtotal']);

// 5. Response
jsonResponse([
    'success'        => true,
    'order_id'       => $orderId,
    'order_number'   => $orderNumber,
    'tracking_code'  => $trackingCode,
    'payment_method' => $paymentMethod,
    'payment_url'    => $paymentSession['redirect_url'] ?? null,
    'message'        => $paymentSession['message']
]);
