<?php
require_once '../config.php';

// Admin Access Control
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    echo json_encode(['error' => 'not authorized']);
    exit;
}

$query = "SELECT COUNT(*) as count FROM rentals WHERE status = 'pending'";
$result = $mysqli->query($query);
$row = $result->fetch_assoc();

echo json_encode(['pending_count' => (int)$row['count']]);
?>
