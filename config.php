<?php
// Database Credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'car_rental');

// Attempt to connect to MySQL database
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($mysqli === false){
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

// Set Timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
