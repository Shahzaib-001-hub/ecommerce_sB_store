<?php
/**
 * Payment Gateway Adapter Interface
 * BazarPK - Pakistani Custom eCommerce Platform
 */

interface PaymentGatewayInterface {
    /**
     * Create or initialize a payment session for an order
     *
     * @param array $order Order record containing id, order_number, total_amount, customer_phone, etc.
     * @return array ['success' => bool, 'redirect_url' => string|null, 'transaction_id' => string|null, 'message' => string]
     */
    public function createPaymentSession(array $order): array;

    /**
     * Verify payment status strictly on the server side
     * (Mandatory: Never trust client redirect parameters alone)
     *
     * @param array $callbackData Raw payload or GET/POST parameters received from gateway
     * @return array ['verified' => bool, 'is_paid' => bool, 'transaction_id' => string, 'response_code' => string, 'message' => string]
     */
    public function verifyPayment(array $callbackData): array;
}
