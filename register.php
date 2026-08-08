<?php
require_once __DIR__ . '/config.php';

$controller = new App\Controllers\AuthController($mysqli);
$controller->register();
