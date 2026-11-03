<?php
/**
 * JazzCash Payment Gateway Adapter
 * Pakistan Mobile Account & Card Checkout
 */

require_once __DIR__ . '/GatewayInterface.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';

class JazzCashAdapter implements PaymentGatewayInterface {
    private string $merchantId;
    private string $password;
    private string $salt;
    private string $mode;
    private string $endpoint;

    public function __construct() {
        $this->merchantId = JAZZCASH_MERCHANT_ID;
        $this->password   = JAZZCASH_PASSWORD;
        $this->salt       = JAZZCASH_INTEGERITY_SALT;
        $this->mode       = JAZZCASH_MODE;

        $this->endpoint = ($this->mode === 'live')
            ? 'https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/'
            : 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/';
    }

    /**
     * Build JazzCash checkout session parameters with HMAC-SHA256 secure hash
     */
    public function createPaymentSession(array $order): array {
        $amountInPaisa = (int)(round((float)$order['total_amount'], 2) * 100);
        $txnDateTime   = date('YmdHis');
        $expiryDateTime= date('YmdHis', strtotime('+2 hours'));
        $txnRefNo      = 'T' . date('YmdHis') . rand(100, 999);

        $params = [
            'pp_Version'          => '1.1',
            'pp_TxnType'          => 'MPAY',
            'pp_Language'         => 'EN',
            'pp_MerchantID'       => $this->merchantId,
            'pp_SubMerchantID'    => '',
            'pp_Password'         => $this->password,
            'pp_TxnRefNo'         => $txnRefNo,
            'pp_Amount'           => (string)$amountInPaisa,
            'pp_DiscountedAmount' => '',
            'pp_TxnCurrency'      => 'PKR',
            'pp_TxnDateTime'      => $txnDateTime,
            'pp_BillReference'    => $order['order_number'],
            'pp_Description'      => 'Order payment for ' . $order['order_number'],
            'pp_TxnExpiryDateTime'=> $expiryDateTime,
            'pp_ReturnURL'        => BASE_URL . '/api/payments.php?gateway=jazzcash&action=callback',
            'pp_SecureHash'       => '',
            'ppmpf_1'             => (string)$order['id'],
            'ppmpf_2'             => $order['customer_phone'] ?? '',
            'ppmpf_3'             => $order['customer_email'] ?? '',
        ];

        // Generate HMAC-SHA256 hash
        $params['pp_SecureHash'] = $this->generateSecureHash($params);

        // Store session payload for form autosubmit
        $_SESSION['jazzcash_payload'] = [
            'endpoint' => $this->endpoint,
            'params'   => $params
        ];

        return [
            'success'        => true,
            'redirect_url'   => BASE_URL . '/api/payments.php?gateway=jazzcash&action=redirect',
            'transaction_id' => $txnRefNo,
            'order_status'   => STATUS_PENDING,
            'payment_status' => PAYMENT_PENDING,
            'message'        => 'JazzCash payment session created.'
        ];
    }

    /**
     * Strict Server-Side Verification of JazzCash Callback
     */
    public function verifyPayment(array $callbackData): array {
        if (empty($callbackData) || !isset($callbackData['pp_ResponseCode'])) {
            return [
                'verified'      => false,
                'is_paid'       => false,
                'transaction_id'=> '',
                'response_code' => 'INVALID_PAYLOAD',
                'message'       => 'No callback data received.'
            ];
        }

        $receivedHash = $callbackData['pp_SecureHash'] ?? '';
        unset($callbackData['pp_SecureHash']);

        // Recalculate hash with our secret salt
        $calculatedHash = $this->generateSecureHash($callbackData);

        // Verify cryptographic signature
        $hashValid = hash_equals(strtolower($calculatedHash), strtolower($receivedHash));
        $responseCode = $callbackData['pp_ResponseCode'] ?? '';
        $isSuccess = ($responseCode === '000' || $responseCode === '121');

        // Check sandbox override for testing when dummy credentials are used
        if ($this->mode === 'sandbox' && $this->merchantId === 'MC12345') {
            $hashValid = true;
            $isSuccess = ($responseCode === '000');
        }

        return [
            'verified'      => $hashValid,
            'is_paid'       => ($hashValid && $isSuccess),
            'transaction_id'=> $callbackData['pp_TxnRefNo'] ?? '',
            'response_code' => $responseCode,
            'message'       => $callbackData['pp_ResponseMessage'] ?? 'Transaction completed'
        ];
    }

    /**
     * Generate JazzCash sorted HMAC-SHA256 signature
     */
    private function generateSecureHash(array $params): string {
        ksort($params);
        $sortedString = $this->salt;
        foreach ($params as $key => $val) {
            if ($val !== '' && $key !== 'pp_SecureHash') {
                $sortedString .= '&' . $val;
            }
        }
        return hash_hmac('sha256', $sortedString, $this->salt);
    }
}
