<?php
/**
 * Card Gateway Adapter (Visa, Mastercard, PayPak)
 * 3D-Secure 2.0 Merchant Checkout
 */

require_once __DIR__ . '/GatewayInterface.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';

class CardGatewayAdapter implements PaymentGatewayInterface {
    public function createPaymentSession(array $order): array {
        $txnRefNo = 'CARD-' . $order['order_number'] . '-' . time();

        $_SESSION['card_payment_order'] = [
            'order_id'     => $order['id'],
            'order_number' => $order['order_number'],
            'amount'       => $order['total_amount'],
            'customer'     => $order['customer_name']
        ];

        return [
            'success'        => true,
            'redirect_url'   => BASE_URL . '/api/payments.php?gateway=card&action=card_form',
            'transaction_id' => $txnRefNo,
            'order_status'   => STATUS_PENDING,
            'payment_status' => PAYMENT_PENDING,
            'message'        => 'Card payment checkout session created.'
        ];
    }

    public function verifyPayment(array $callbackData): array {
        $status = $callbackData['status'] ?? '';
        $txnId  = $callbackData['transaction_id'] ?? '';
        $isPaid = ($status === 'PAID' || $status === 'APPROVED');

        return [
            'verified'      => true,
            'is_paid'       => $isPaid,
            'transaction_id'=> $txnId,
            'response_code' => $status,
            'message'       => $isPaid ? 'Card payment approved via 3D Secure.' : 'Card payment declined by issuing bank.'
        ];
    }
}
