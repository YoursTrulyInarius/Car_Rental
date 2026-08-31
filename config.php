<?php
// Database Credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'car_rental');

// Attempt to connect to MySQL database
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'youremail@.com');
define('SMTP_PASSWORD', 'yourpassword');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'youremail@.com');
define('SMTP_FROM_NAME', "Your Brand Name");

// Global Constants
define('BASE_URL', 'http://localhost/Car_Rental/');

// User Roles
define('ROLE_OWNER', 'owner');
define('ROLE_STAFF', 'staff');
define('ROLE_CUSTOMER', 'customer');

// User Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');

// Vehicle Status
define('VEHICLE_STATUS_AVAILABLE', 'available');
define('VEHICLE_STATUS_RESERVED', 'reserved');
define('VEHICLE_STATUS_RENTED', 'rented');
define('VEHICLE_STATUS_MAINTENANCE', 'maintenance');

// Rental Status
define('RENTAL_STATUS_PENDING', 'pending');
define('RENTAL_STATUS_APPROVED', 'approved');
define('RENTAL_STATUS_REJECTED', 'rejected');
define('RENTAL_STATUS_COMPLETED', 'completed');
define('RENTAL_STATUS_CANCELLED', 'cancelled');

// Payment Status
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_COMPLETED', 'completed');
define('PAYMENT_STATUS_CANCELLED', 'cancelled');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

// Payment Methods
define('PAYMENT_METHOD_CASH', 'cash');
define('PAYMENT_METHOD_CREDIT_CARD', 'credit_card');
define('PAYMENT_METHOD_DEBIT_CARD', 'debit_card');
define('PAYMENT_METHOD_BANK_TRANSFER', 'bank_transfer');
define('PAYMENT_METHOD_ONLINE', 'online');

// Reservation Status
define('RESERVATION_STATUS_PENDING', 'pending');
define('RESERVATION_STATUS_CONFIRMED', 'confirmed');
define('RESERVATION_STATUS_CANCELLED', 'cancelled');
define('RESERVATION_STATUS_COMPLETED', 'completed');

// Set Timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Autoload MVC classes (Models & Controllers)
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\', '/', $class);
    // Remove leading App/ if used
    if (strpos($classPath, 'App/') === 0) {
        $classPath = substr($classPath, 4);
    }
    $file = __DIR__ . '/app/' . $classPath . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
?>
