<?php
require_once 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $car_id = $_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $price_per_day = $_POST['price'];

    // Validation
    if($start_date > $end_date){
        die("Invalid Date Range");
    }

    // Calculate Total
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $end->diff($start)->format("%a");
    if($days == 0) $days = 1;
    
    $total_price = $days * $price_per_day;

    // Insert
    $stmt = $mysqli->prepare("INSERT INTO rentals (user_id, car_id, start_date, end_date, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iissd", $user_id, $car_id, $start_date, $end_date, $total_price);
    
    if($stmt->execute()){
        header("Location: my_rentals.php?msg=success");
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    header("Location: index.php");
}
?>
