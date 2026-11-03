<?php
/**
 * Cash On Delivery (COD) Payment Adapter
 * Pakistan's standard eCommerce payment flow
 */

require_once __DIR__ . '/GatewayInterface.php';
require_once __DIR__ . '/../../config/constants.php';

class CashOnDelivery implements PaymentGatewayInterface {
    public function createPaymentSession(array $order): array {
        return [
            'success'        => true,
            'redirect_url'   => null, // Direct order completion
            'transaction_id' => 'COD-' . $order['order_number'],
            'order_status'   => STATUS_CONFIRMED,
            'payment_status' => PAYMENT_UNPAID,
            'message'        => 'Cash on Delivery order placed successfully. Payment due upon parcel delivery.'
        ];
    }

    public function verifyPayment(array $callbackData): array {
        // For COD, payment is verified when courier delivers parcel and updates settlement
        return [
            'verified'      => true,
            'is_paid'       => false,
            'transaction_id'=> $callbackData['transaction_id'] ?? '',
            'response_code' => 'COD_PENDING',
            'message'       => 'Payment to be collected in cash upon delivery.'
        ];
    }
}
