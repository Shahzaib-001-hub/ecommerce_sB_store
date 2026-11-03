<?php
/**
 * Global Utility Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Format amount into Pakistani Rupees currency string
 */
function formatPrice(float|int|string $amount): string {
    $num = is_numeric($amount) ? (float)$amount : 0.0;
    return CURRENCY_SYMBOL . number_format($num, 0, '.', ',');
}

/**
 * Format Pakistani phone number to standard 03XXXXXXXXX format
 */
function formatPakistaniPhone(string $phone): string {
    // Strip non-digit characters
    $digits = preg_replace('/\D/', '', $phone);
    
    // Convert 923001234567 -> 03001234567
    if (str_starts_with($digits, '92') && strlen($digits) === 12) {
        return '0' . substr($digits, 2);
    }
    return $digits;
}

/**
 * Validate Pakistani Mobile Number (0300 to 0349, 11 digits)
 */
function isValidPakistaniPhone(string $phone): bool {
    $cleaned = formatPakistaniPhone($phone);
    return (bool)preg_match('/^03[0-9]{9}$/', $cleaned);
}

/**
 * Sanitize text output for HTML display
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize string input
 */
function sanitize(string $input): string {
    return trim(filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS));
}

/**
 * Generate or retrieve CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output hidden CSRF input field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Send JSON response and exit
 */
function jsonResponse(array $data, int $statusCode = 200): never {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Set flash alert message
 */
function setFlash(string $type, string $message): void {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash messages
 */
function getFlash(): array {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Generate unique Order Number for Pakistan (e.g., PK-2611-8942)
 */
function generateOrderNumber(): string {
    $prefix = 'PK-' . date('ym') . '-';
    $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    return $prefix . $random;
}

/**
 * Generate unique Courier Tracking Code (e.g. TRK-PK-98234)
 */
function generateTrackingCode(): string {
    return 'TRK-PK-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

/**
 * Generate URL-friendly slug
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'n-a');
}
