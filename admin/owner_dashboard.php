<?php
require_once __DIR__ . '/../config.php';

$controller = new App\Controllers\DashboardController($mysqli);
$controller->ownerDashboard();
