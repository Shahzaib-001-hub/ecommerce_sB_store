<?php
/**
 * Authentication Middleware & Session Helpers
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/utils.php';

/**
 * Check if a customer is currently logged in
 */
function isCustomerLoggedIn(): bool {
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

/**
 * Get logged-in customer data array
 */
function getCurrentCustomer(): ?array {
    if (!isCustomerLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['customer_id'],
        'name'  => $_SESSION['customer_name'] ?? 'Customer',
        'email' => $_SESSION['customer_email'] ?? '',
        'phone' => $_SESSION['customer_phone'] ?? ''
    ];
}

/**
 * Require customer login, redirecting to login page if unauthenticated
 */
function requireCustomerLogin(string $redirectUrl = null): void {
    if (!isCustomerLoggedIn()) {
        $target = $redirectUrl ?: $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/index.php');
        $_SESSION['login_redirect'] = $target;
        setFlash('warning', 'Please sign in to your account to continue.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Check if Admin is logged in
 */
function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && ($_SESSION['admin_role'] ?? '') === ROLE_ADMIN;
}

/**
 * Get logged-in admin data
 */
function getCurrentAdmin(): ?array {
    if (!isAdminLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['admin_id'],
        'name'  => $_SESSION['admin_name'] ?? 'Super Admin',
        'email' => $_SESSION['admin_email'] ?? '',
        'role'  => $_SESSION['admin_role'] ?? ROLE_ADMIN
    ];
}

/**
 * Require admin authentication
 */
function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        setFlash('error', 'Administrator credentials required to access this area.');
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}
