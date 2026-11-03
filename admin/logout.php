<?php
/**
 * Admin Logout Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/utils.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);

setFlash('info', 'Admin session terminated.');
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
