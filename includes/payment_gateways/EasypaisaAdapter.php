<?php
/**
 * Easypaisa Payment Gateway Adapter
 * Pakistan Mobile Account / Direct MA Checkout
 */

require_once __DIR__ . '/GatewayInterface.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';

class EasypaisaAdapter implements PaymentGatewayInterface {
    private string $storeId;
    private string $hashKey;
    private string $mode;
    private string $endpoint;

    public function __construct() {
        $this->storeId = EASYPAISA_STORE_ID;
        $this->hashKey = EASYPAISA_HASH_KEY;
        $this->mode    = EASYPAISA_MODE;

        $this->endpoint = ($this->mode === 'live')
            ? 'https://easypay.easypaisa.com.pk/easypay/Index.jsf'
            : 'https://easypaystg.easypaisa.com.pk/easypay/Index.jsf';
    }

    public function createPaymentSession(array $order): array {
        $txnRefNo = 'EP-' . $order['order_number'] . '-' . time();
        $amount = number_format((float)$order['total_amount'], 2, '.', '');
        $expiryDate = date('Ymd His', strtotime('+24 hours'));

        $params = [
            'storeId'          => $this->storeId,
            'amount'           => $amount,
            'postBackURL'      => BASE_URL . '/api/payments.php?gateway=easypaisa&action=callback',
            'orderRefNum'      => $txnRefNo,
            'expiryDate'       => $expiryDate,
            'merchantHashedReq'=> '',
            'autoRedirect'     => '1',
            'paymentMethod'    => 'MA_PAYMENT_METHOD',
            'emailAddr'        => $order['customer_email'] ?? '',
            'mobileNum'        => $order['customer_phone'] ?? ''
        ];

        // Store session payload
        $_SESSION['easypaisa_payload'] = [
            'endpoint' => $this->endpoint,
            'params'   => $params
        ];

        return [
            'success'        => true,
            'redirect_url'   => BASE_URL . '/api/payments.php?gateway=easypaisa&action=redirect',
            'transaction_id' => $txnRefNo,
            'order_status'   => STATUS_PENDING,
            'payment_status' => PAYMENT_PENDING,
            'message'        => 'Easypaisa payment session created.'
        ];
    }

    public function verifyPayment(array $callbackData): array {
        $status = $callbackData['status'] ?? $callbackData['responseCode'] ?? '';
        $txnRefNo = $callbackData['orderRefNumber'] ?? $callbackData['orderRefNum'] ?? '';

        // In Easypaisa, '0000' or '000' indicates approved transaction
        $isSuccess = in_array($status, ['0000', '000', 'PAID', 'SUCCESS'], true);

        // In sandbox dummy mode, allow mock test verification
        if ($this->mode === 'sandbox' && $this->storeId === 'EP_STORE_101') {
            $isSuccess = ($status === '0000' || isset($callbackData['mock_paid']));
        }

        return [
            'verified'      => true,
            'is_paid'       => $isSuccess,
            'transaction_id'=> $txnRefNo,
            'response_code' => $status ?: 'UNKNOWN',
            'message'       => $callbackData['desc'] ?? ($isSuccess ? 'Payment verified successfully.' : 'Payment failed or cancelled.')
        ];
    }
}
