<?php
/**
 * Global Constants
 */

// Order Statuses
define('STATUS_PENDING', 'pending');
define('STATUS_CONFIRMED', 'confirmed');
define('STATUS_PROCESSING', 'processing');
define('STATUS_SHIPPED', 'shipped');
define('STATUS_OUT_FOR_DELIVERY', 'out_for_delivery');
define('STATUS_DELIVERED', 'delivered');
define('STATUS_CANCELLED', 'cancelled');
define('STATUS_RETURNED', 'returned');

// Payment Statuses
define('PAYMENT_UNPAID', 'unpaid');
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PAID', 'paid');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');

// Payment Methods
define('METHOD_COD', 'cod');
define('METHOD_JAZZCASH', 'jazzcash');
define('METHOD_EASYPAISA', 'easypaisa');
define('METHOD_CARD', 'card');

// User Roles
define('ROLE_CUSTOMER', 'customer');
define('ROLE_ADMIN', 'admin');

// Pakistani Provinces & Regions
const PK_PROVINCES = [
    'Punjab' => 'Punjab',
    'Sindh' => 'Sindh',
    'Khyber Pakhtunkhwa' => 'Khyber Pakhtunkhwa',
    'Balochistan' => 'Balochistan',
    'Islamabad Capital Territory' => 'Islamabad Capital Territory',
    'Azad Jammu & Kashmir' => 'Azad Jammu & Kashmir',
    'Gilgit-Baltistan' => 'Gilgit-Baltistan'
];

// Major Pakistani Cities with Estimated Transit Time
const PK_MAJOR_CITIES = [
    'Karachi' => ['province' => 'Sindh', 'days' => '1-2 Days', 'shipping' => 200],
    'Lahore' => ['province' => 'Punjab', 'days' => '1-2 Days', 'shipping' => 200],
    'Islamabad' => ['province' => 'Islamabad Capital Territory', 'days' => '1-2 Days', 'shipping' => 220],
    'Rawalpindi' => ['province' => 'Punjab', 'days' => '1-2 Days', 'shipping' => 220],
    'Faisalabad' => ['province' => 'Punjab', 'days' => '2-3 Days', 'shipping' => 250],
    'Multan' => ['province' => 'Punjab', 'days' => '2-3 Days', 'shipping' => 250],
    'Peshawar' => ['province' => 'Khyber Pakhtunkhwa', 'days' => '2-3 Days', 'shipping' => 250],
    'Quetta' => ['province' => 'Balochistan', 'days' => '3-4 Days', 'shipping' => 300],
    'Sialkot' => ['province' => 'Punjab', 'days' => '2-3 Days', 'shipping' => 250],
    'Gujranwala' => ['province' => 'Punjab', 'days' => '2-3 Days', 'shipping' => 250],
    'Hyderabad' => ['province' => 'Sindh', 'days' => '2-3 Days', 'shipping' => 220],
    'Abbottabad' => ['province' => 'Khyber Pakhtunkhwa', 'days' => '3-4 Days', 'shipping' => 280],
    'Bahawalpur' => ['province' => 'Punjab', 'days' => '2-3 Days', 'shipping' => 250],
    'Sargodha' => ['province' => 'Punjab', 'days' => '2-3 Days', 'shipping' => 250],
    'Sukkur' => ['province' => 'Sindh', 'days' => '2-3 Days', 'shipping' => 250],
    'Mardan' => ['province' => 'Khyber Pakhtunkhwa', 'days' => '3-4 Days', 'shipping' => 280],
    'Muzaffarabad' => ['province' => 'Azad Jammu & Kashmir', 'days' => '3-5 Days', 'shipping' => 300],
    'Gilgit' => ['province' => 'Gilgit-Baltistan', 'days' => '4-6 Days', 'shipping' => 350],
    'Other City' => ['province' => 'Punjab', 'days' => '3-5 Days', 'shipping' => 280]
];
