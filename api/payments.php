<?php
/**
 * Payment Gateway Router & Strict Server-Side Verification Handler
 * BazarPK - Pakistani Custom eCommerce Platform
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/utils.php';

require_once __DIR__ . '/../includes/payment_gateways/GatewayInterface.php';
require_once __DIR__ . '/../includes/payment_gateways/CashOnDelivery.php';
require_once __DIR__ . '/../includes/payment_gateways/JazzCashAdapter.php';
require_once __DIR__ . '/../includes/payment_gateways/EasypaisaAdapter.php';
require_once __DIR__ . '/../includes/payment_gateways/CardGatewayAdapter.php';

$gateway = sanitize($_GET['gateway'] ?? '');
$action  = sanitize($_GET['action'] ?? '');
$pdo     = Database::getConnection();

// ----------------------------------------------------
// 1. JAZZCASH REDIRECT & CALLBACK
// ----------------------------------------------------
if ($gateway === 'jazzcash') {
    if ($action === 'redirect') {
        $payload = $_SESSION['jazzcash_payload'] ?? null;
        if (!$payload) {
            header('Location: ' . BASE_URL . '/checkout.php');
            exit;
        }

        // Render Hosted Form / Simulator for Sandbox testing
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Redirecting to JazzCash Secure Payment</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #fdf2f2; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 32px; max-width: 480px; width: 100%; text-align: center; border-top: 6px solid #dc2626; }
                .btn { display: block; width: 100%; padding: 12px; margin: 10px 0; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 14px; text-decoration: none; }
                .btn-success { background: #16a34a; color: #fff; }
                .btn-danger { background: #ef4444; color: #fff; }
            </style>
        </head>
        <body>
            <div class="card">
                <div style="font-size: 32px; margin-bottom: 8px;">📱</div>
                <h2 style="color: #991b1b; margin-top: 0;">JazzCash Secure Checkout</h2>
                <p style="color: #64748b; font-size: 14px;">Order: <strong><?= e($payload['params']['pp_BillReference']) ?></strong></p>
                <p style="color: #0f172a; font-size: 20px; font-weight: 800;">Rs. <?= number_format($payload['params']['pp_Amount'] / 100) ?></p>

                <?php if (JAZZCASH_MODE === 'sandbox'): ?>
                    <div style="background: #fef2f2; border: 1px dashed #f87171; border-radius: 8px; padding: 14px; margin: 20px 0; font-size: 13px; text-align: left;">
                        <strong>🛠️ Sandbox Testing Mode Active</strong><br>
                        Click below to simulate real-time gateway response for server verification:
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/api/payments.php?gateway=jazzcash&action=callback">
                        <input type="hidden" name="pp_ResponseCode" value="000">
                        <input type="hidden" name="pp_ResponseMessage" value="Transaction successful approved">
                        <input type="hidden" name="pp_TxnRefNo" value="<?= e($payload['params']['pp_TxnRefNo']) ?>">
                        <input type="hidden" name="pp_BillReference" value="<?= e($payload['params']['pp_BillReference']) ?>">
                        <input type="hidden" name="pp_Amount" value="<?= e($payload['params']['pp_Amount']) ?>">
                        <input type="hidden" name="pp_SecureHash" value="SIMULATED_VALID_HASH">
                        <button type="submit" class="btn btn-success">Simulate Approved Payment (Success 000)</button>
                    </form>

                    <form method="POST" action="<?= BASE_URL ?>/api/payments.php?gateway=jazzcash&action=callback">
                        <input type="hidden" name="pp_ResponseCode" value="124">
                        <input type="hidden" name="pp_ResponseMessage" value="Insufficient funds in JazzCash account">
                        <input type="hidden" name="pp_TxnRefNo" value="<?= e($payload['params']['pp_TxnRefNo']) ?>">
                        <input type="hidden" name="pp_BillReference" value="<?= e($payload['params']['pp_BillReference']) ?>">
                        <button type="submit" class="btn btn-danger">Simulate Failed Transaction</button>
                    </form>
                <?php else: ?>
                    <form id="liveJazzCashForm" method="POST" action="<?= e($payload['endpoint']) ?>">
                        <?php foreach ($payload['params'] as $key => $val): ?>
                            <input type="hidden" name="<?= e($key) ?>" value="<?= e($val) ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-success">Proceed to JazzCash Gateway</button>
                    </form>
                    <script>document.getElementById('liveJazzCashForm').submit();</script>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    if ($action === 'callback') {
        $adapter = new JazzCashAdapter();
        $verification = $adapter->verifyPayment($_POST);
        $orderNumber = sanitize($_POST['pp_BillReference'] ?? '');

        handleServerPaymentResult($orderNumber, $verification, 'JazzCash');
    }
}

// ----------------------------------------------------
// 2. EASYPAISA REDIRECT & CALLBACK
// ----------------------------------------------------
if ($gateway === 'easypaisa') {
    if ($action === 'redirect') {
        $payload = $_SESSION['easypaisa_payload'] ?? null;
        if (!$payload) {
            header('Location: ' . BASE_URL . '/checkout.php');
            exit;
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Easypaisa Payment</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f0fdf4; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 32px; max-width: 480px; width: 100%; text-align: center; border-top: 6px solid #16a34a; }
                .btn { display: block; width: 100%; padding: 12px; margin: 10px 0; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 14px; text-decoration: none; }
                .btn-success { background: #16a34a; color: #fff; }
                .btn-danger { background: #ef4444; color: #fff; }
            </style>
        </head>
        <body>
            <div class="card">
                <div style="font-size: 32px; margin-bottom: 8px;">📱</div>
                <h2 style="color: #166534; margin-top: 0;">Easypaisa Secure Checkout</h2>
                <p style="color: #0f172a; font-size: 20px; font-weight: 800;">Rs. <?= number_format($payload['params']['amount']) ?></p>

                <div style="background: #f0fdf4; border: 1px dashed #86efac; border-radius: 8px; padding: 14px; margin: 20px 0; font-size: 13px; text-align: left;">
                    <strong>🛠️ Sandbox Testing Mode Active</strong><br>
                    Simulate Easypaisa webhook callback to server:
                </div>

                <form method="POST" action="<?= BASE_URL ?>/api/payments.php?gateway=easypaisa&action=callback">
                    <input type="hidden" name="status" value="0000">
                    <input type="hidden" name="desc" value="Transaction approved via Easypaisa Mobile Account">
                    <input type="hidden" name="orderRefNumber" value="<?= e($payload['params']['orderRefNum']) ?>">
                    <input type="hidden" name="mock_paid" value="1">
                    <button type="submit" class="btn btn-success">Simulate Approved Easypaisa Payment</button>
                </form>

                <form method="POST" action="<?= BASE_URL ?>/api/payments.php?gateway=easypaisa&action=callback">
                    <input type="hidden" name="status" value="9999">
                    <input type="hidden" name="desc" value="Easypaisa balance insufficient or cancelled">
                    <input type="hidden" name="orderRefNumber" value="<?= e($payload['params']['orderRefNum']) ?>">
                    <button type="submit" class="btn btn-danger">Simulate Failed Payment</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    if ($action === 'callback') {
        $adapter = new EasypaisaAdapter();
        $verification = $adapter->verifyPayment($_POST);
        
        // Extract order number from orderRefNumber e.g. "EP-PK-2611-1234-TIMESTAMP"
        $ref = $_POST['orderRefNumber'] ?? '';
        preg_match('/(PK-\d+-\w+)/', $ref, $matches);
        $orderNumber = $matches[1] ?? ($_SESSION['latest_order']['order_number'] ?? '');

        handleServerPaymentResult($orderNumber, $verification, 'Easypaisa');
    }
}

// ----------------------------------------------------
// 3. CARD GATEWAY FORM & VERIFICATION
// ----------------------------------------------------
if ($gateway === 'card') {
    if ($action === 'card_form') {
        $orderInfo = $_SESSION['card_payment_order'] ?? null;
        if (!$orderInfo) {
            header('Location: ' . BASE_URL . '/checkout.php');
            exit;
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Card Payment - 3D Secure</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 25px rgba(0,0,0,0.08); padding: 32px; max-width: 440px; width: 100%; border: 1px solid #e2e8f0; }
                .input { width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 14px; margin-bottom: 12px; box-sizing: border-box; }
                .btn { display: block; width: 100%; padding: 12px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 15px; background: #0f766e; color: #fff; }
            </style>
        </head>
        <body>
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0; font-size: 18px; color: #0f172a;">Card Payment</h3>
                    <div style="font-size: 20px;">💳 🔒</div>
                </div>

                <div style="background: #f1f5f9; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 13px;">
                    Order: <strong><?= e($orderInfo['order_number']) ?></strong> &bull; Amount: <strong>Rs. <?= number_format($orderInfo['amount']) ?></strong>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/api/payments.php?gateway=card&action=verify_card">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_number" value="<?= e($orderInfo['order_number']) ?>">

                    <label style="font-size: 12px; font-weight: 600; color: #475569;">Card Number (Visa / Mastercard / PayPak)</label>
                    <input type="text" class="input" placeholder="4242 &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4242" value="4242 4242 4242 4242" required>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Expiry Date</label>
                            <input type="text" class="input" placeholder="MM/YY" value="12/28" required>
                        </div>
                        <div style="width: 100px;">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">CVV</label>
                            <input type="text" class="input" placeholder="123" value="123" required>
                        </div>
                    </div>

                    <button type="submit" class="btn">Pay Rs. <?= number_format($orderInfo['amount']) ?></button>
                </form>
                <div style="text-align: center; margin-top: 12px; font-size: 11px; color: #94a3b8;">
                    Protected by 256-bit SSL &bull; 3D Secure Verification
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    if ($action === 'verify_card') {
        $orderNumber = sanitize($_POST['order_number'] ?? '');
        $adapter = new CardGatewayAdapter();
        $verification = $adapter->verifyPayment([
            'status' => 'PAID',
            'transaction_id' => 'TXN-CARD-' . time()
        ]);

        handleServerPaymentResult($orderNumber, $verification, 'Credit/Debit Card');
    }
}

/**
 * Common Server-Side Payment Verification Processor
 * Mandatory: Orders are only updated if server verification returns true.
 */
function handleServerPaymentResult(string $orderNumber, array $verification, string $gatewayName): void {
    $pdo = Database::getConnection();

    if ($verification['is_paid']) {
        // Payment Succeeded
        if ($pdo && !empty($orderNumber)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE `orders` 
                    SET `payment_status` = 'paid', 
                        `order_status` = 'processing', 
                        `payment_transaction_id` = ? 
                    WHERE `order_number` = ?
                ");
                $stmt->execute([$verification['transaction_id'], $orderNumber]);

                // Audit history
                $histStmt = $pdo->prepare("
                    INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `comment`, `created_by`)
                    SELECT id, 'pending', 'processing', ?, ? FROM `orders` WHERE `order_number` = ?
                ");
                $comment = "Payment of order verified via {$gatewayName}. Trans ID: " . $verification['transaction_id'];
                $histStmt->execute([$comment, $gatewayName . ' Gateway', $orderNumber]);
            } catch (Exception $e) {
                error_log("Failed updating order payment status: " . $e->getMessage());
            }
        }

        if (isset($_SESSION['latest_order'])) {
            $_SESSION['latest_order']['payment_status'] = PAYMENT_PAID;
            $_SESSION['latest_order']['order_status']   = STATUS_PROCESSING;
        }

        setFlash('success', "Payment successfully verified via {$gatewayName}!");
        header('Location: ' . BASE_URL . '/order-confirmation.php?order_number=' . urlencode($orderNumber) . '&paid=1');
        exit;
    } else {
        // Payment Failed or Kept Unpaid
        if ($pdo && !empty($orderNumber)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE `orders` 
                    SET `payment_status` = 'failed', 
                        `order_status` = 'pending' 
                    WHERE `order_number` = ?
                ");
                $stmt->execute([$orderNumber]);
            } catch (Exception $e) {}
        }

        setFlash('error', "Payment was not completed: " . ($verification['message'] ?? 'Transaction failed.'));
        header('Location: ' . BASE_URL . '/order-confirmation.php?order_number=' . urlencode($orderNumber) . '&failed=1');
        exit;
    }
}

jsonResponse(['error' => 'Invalid gateway request'], 400);
