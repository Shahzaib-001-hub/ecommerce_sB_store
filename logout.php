<?php
/**
 * Customer Logout
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/utils.php';

unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);
unset($_SESSION['customer_phone']);

setFlash('info', 'You have been logged out successfully.');
header('Location: ' . BASE_URL . '/login.php');
exit;
