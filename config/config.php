<?php
/**
 * Application Configuration
 * Compatible with Hostinger Shared Hosting (PHP 8.x)
 */

// Prevent multiple session starts
if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookie settings
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
               || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Environment: 'development' or 'production'
define('APP_ENV', 'development');

// Base URL auto-detection (works on local or Hostinger subdirectories)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseDir = rtrim(preg_replace('#/(api|admin|pages|includes).*$#', '', $scriptDir), '/');
define('BASE_URL', $protocol . $host . $baseDir);
define('BASE_PATH', realpath(__DIR__ . '/..'));

// Store Info
define('STORE_NAME', 'BazarPK');
define('STORE_TAGLINE', 'Pakistan\'s Premier Online Store');
define('STORE_EMAIL', 'support@bazarpk.com');
define('STORE_PHONE', '+92 300 1234567');
define('STORE_WHATSAPP', '923001234567');
define('STORE_ADDRESS', 'Suite #402, Trade Tower, I.I. Chundrigar Road, Karachi, Pakistan');

// Currency Settings
define('CURRENCY_CODE', 'PKR');
define('CURRENCY_SYMBOL', 'Rs. ');

// Shipping Defaults
define('DEFAULT_SHIPPING_FEE', 250); // Rs. 250 standard shipping across Pakistan
define('FREE_SHIPPING_THRESHOLD', 3500); // Free delivery on orders over Rs. 3,500

// Database Configuration (Update for Hostinger MySQL Database in hPanel)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'ecommerce_pk_store');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Payment Gateway Configuration
define('ENABLE_COD', true);
define('ENABLE_JAZZCASH', true);
define('ENABLE_EASYPAISA', true);
define('ENABLE_CARD_GATEWAY', true);

// JazzCash Sandbox/Live Settings
define('JAZZCASH_MERCHANT_ID', getenv('JAZZCASH_MERCHANT_ID') ?: 'MC12345');
define('JAZZCASH_PASSWORD', getenv('JAZZCASH_PASSWORD') ?: 'password123');
define('JAZZCASH_INTEGERITY_SALT', getenv('JAZZCASH_SALT') ?: 'salt123456');
define('JAZZCASH_MODE', 'sandbox'); // sandbox or live

// Easypaisa Sandbox/Live Settings
define('EASYPAISA_STORE_ID', getenv('EASYPAISA_STORE_ID') ?: 'EP_STORE_101');
define('EASYPAISA_HASH_KEY', getenv('EASYPAISA_HASH_KEY') ?: 'EP_HASH_KEY_XYZ');
define('EASYPAISA_MODE', 'sandbox');

// Timezone for Pakistan (PKT is UTC+5)
date_default_timezone_set('Asia/Karachi');
