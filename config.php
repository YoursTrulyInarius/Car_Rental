<?php
// Database Credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'car_rental');

// Attempt to connect to MySQL server and ensure the app database exists.
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if ($mysqli->connect_error) {
    die("ERROR: Could not connect to MySQL server. " . $mysqli->connect_error);
}

// Create the application database if it does not already exist.
$createDbQuery = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$mysqli->query($createDbQuery)) {
    die("ERROR: Could not create database " . DB_NAME . ". " . $mysqli->error);
}

if (!$mysqli->select_db(DB_NAME)) {
    die("ERROR: Could not select database " . DB_NAME . ". " . $mysqli->error);
}

// Ensure the required application schema exists.
try {
    $mysqli->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        role ENUM('owner', 'staff', 'customer') DEFAULT 'customer',
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (email),
        INDEX (role),
        INDEX (status)
    ) ENGINE=InnoDB");

    $mysqli->query("CREATE TABLE IF NOT EXISTS cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plate_number VARCHAR(50) NOT NULL UNIQUE,
        brand VARCHAR(50) NOT NULL,
        model VARCHAR(100) NOT NULL,
        year INT NOT NULL,
        description TEXT,
        image TEXT,
        owner_id INT,
        price_per_day DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        quantity INT DEFAULT 1,
        type VARCHAR(50) NOT NULL DEFAULT 'sedan',
        status ENUM('available', 'reserved', 'rented', 'maintenance') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX (plate_number),
        INDEX (owner_id),
        INDEX (status),
        INDEX (brand, model),
        INDEX (price_per_day)
    ) ENGINE=InnoDB");

    $mysqli->query("CREATE TABLE IF NOT EXISTS rentals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        car_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        actual_return_date DATE,
        status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
        INDEX (user_id),
        INDEX (car_id),
        INDEX (status),
        INDEX (start_date),
        INDEX (end_date),
        UNIQUE KEY unique_rental_period (car_id, start_date, end_date)
    ) ENGINE=InnoDB");

    $mysqli->query("CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        car_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
        INDEX (user_id),
        INDEX (car_id),
        INDEX (status),
        INDEX (start_date),
        INDEX (end_date)
    ) ENGINE=InnoDB");

    $mysqli->query("CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rental_id INT NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        payment_method ENUM('cash', 'credit_card', 'debit_card', 'bank_transfer', 'online') DEFAULT 'cash',
        payment_status ENUM('pending', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
        transaction_id VARCHAR(100),
        reference_number VARCHAR(100),
        payment_date DATETIME,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX (rental_id),
        INDEX (user_id),
        INDEX (payment_status),
        INDEX (payment_date),
        UNIQUE KEY unique_rental_payment (rental_id)
    ) ENGINE=InnoDB");

    $mysqli->query("CREATE TABLE IF NOT EXISTS audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        entity_type VARCHAR(50),
        entity_id INT,
        old_value TEXT,
        new_value TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX (user_id),
        INDEX (entity_type),
        INDEX (action),
        INDEX (created_at)
    ) ENGINE=InnoDB");

    $colsRes = $mysqli->query("SHOW COLUMNS FROM cars");
    if ($colsRes) {
        $existingCols = [];
        while ($colRow = $colsRes->fetch_assoc()) {
            $existingCols[] = $colRow['Field'];
        }

        if (!in_array('price_per_day', $existingCols)) {
            $mysqli->query("ALTER TABLE cars ADD COLUMN price_per_day DECIMAL(10,2) NOT NULL DEFAULT 0.00");
        }
        if (!in_array('quantity', $existingCols)) {
            $mysqli->query("ALTER TABLE cars ADD COLUMN quantity INT DEFAULT 1");
        }
        if (!in_array('type', $existingCols)) {
            $mysqli->query("ALTER TABLE cars ADD COLUMN type VARCHAR(50) NOT NULL DEFAULT 'sedan'");
        }
        $mysqli->query("ALTER TABLE cars MODIFY COLUMN image TEXT");
    }
} catch (\Throwable $e) {
    // Ignore schema bootstrap issues in this pass; the app will still fail if the database is not valid.
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

// Helper function to parse multiple car images
if (!function_exists('getCarImagesList')) {
    function getCarImagesList($imgString) {
        if (empty($imgString)) return [];
        $decoded = json_decode($imgString, true);
        if (is_array($decoded)) return array_values(array_filter($decoded));
        return array_values(array_filter(array_map('trim', explode(',', $imgString))));
    }
}
?>
