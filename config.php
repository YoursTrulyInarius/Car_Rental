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

// Global Constants
define('BASE_URL', 'http://localhost/Car_Rental/');

// Set Timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Start Session
session_start();
?>
