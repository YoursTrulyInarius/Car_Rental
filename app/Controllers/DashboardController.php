<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Car;
use App\Models\Rental;

class DashboardController {
    private $db;
    private $userModel;
    private $carModel;
    private $rentalModel;

    public function __construct($mysqli) {
        $this->db = $mysqli;
        $this->userModel = new User($mysqli);
        $this->carModel = new Car($mysqli);
        $this->rentalModel = new Rental($mysqli);
    }

    public function home() {
        $type = isset($_GET['type']) ? strtolower(trim((string) $_GET['type'])) : '';
        $cars = $this->carModel->getAvailableCars($type);
        require_once __DIR__ . '/../Views/dashboard/home.php';
    }

    public function customerDashboard() {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner') {
            header("Location: " . BASE_URL . "admin/dashboard.php");
            exit;
        }

        $owner_id = $_GET['owner_id'] ?? null;
        $selected_type = isset($_GET['type']) ? strtolower(trim((string) $_GET['type'])) : '';
        $owner_name = "";
        $cars_result = null;
        $owners = null;

        if ($owner_id) {
            $owner = $this->userModel->getById($owner_id);
            if ($owner) $owner_name = $owner['name'];
            $cars_result = $this->carModel->getAvailableByOwner($owner_id);
        } elseif (isset($_SESSION['user_id'])) {
            $owner_name = "Available Cars";
            $cars_result = $this->carModel->getAvailableCars($selected_type);
            $owners = null;
        } else {
            $owner_name = "Available Cars";
            $cars_result = $this->carModel->getAvailableCars($selected_type);
        }

        $mysqli = $this->db;
        $carModel = $this->carModel;
        require_once __DIR__ . '/../Views/dashboard/customer.php';
    }

    public function adminDashboard() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        $error_msg = '';

        $total_cars = $this->carModel->getTotalCarsCount();
        $pending_rentals = $this->rentalModel->getPendingCount();
        $active_rentals = $this->rentalModel->getActiveCount();
        $total_revenue = $this->rentalModel->getTotalRevenue();
        $recent_rentals = $this->rentalModel->getRecentRentals(5);
        $owners = $this->userModel->getOwners();

        $mysqli = $this->db;
        $carModel = $this->carModel;
        require_once __DIR__ . '/../Views/dashboard/admin.php';
    }

    public function ownerDashboard() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        $owner_id = $_GET['id'] ?? null;
        if (!$owner_id) {
            header("Location: " . BASE_URL . "admin/dashboard.php");
            exit;
        }

        $owner = $this->userModel->getById($owner_id);
        if (!$owner) {
            header("Location: " . BASE_URL . "admin/dashboard.php");
            exit;
        }
        $owner_name = $owner['name'];

        $error = '';
        // Handle Delete
        if (isset($_GET['delete'])) {
            $id = $_GET['delete'];
            $this->carModel->deleteCar($id);
            header("Location: " . BASE_URL . "admin/owner_dashboard.php?id=$owner_id");
            exit;
        }

        // Handle Add/Edit
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $brand = trim((string)($_POST['brand'] ?? ''));
            $plate_number = trim((string)($_POST['plate_number'] ?? ''));
            $model = trim((string)($_POST['model'] ?? ''));
            $year = $_POST['year'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $desc = $_POST['description'] ?? '';
            $status = $_POST['status'] ?? 'available';
            $car_id = $_POST['car_id'] ?? null;
            $quantity = $_POST['quantity'] ?? 1;

            $image = $_POST['current_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = __DIR__ . "/../../uploads/";
                $filename = time() . "_" . basename($_FILES["image"]["name"]);
                $target_file = $target_dir . $filename;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $filename;
                }
            }

            if ($car_id) {
                $success = $this->carModel->updateCarForOwner($car_id, $owner_id, $model, $year, $price, $desc, $image, $status, $quantity, 'sedan', $brand, $plate_number);
            } else {
                $success = $this->carModel->addCar($model, $year, $price, $desc, $image, $status, $quantity, $owner_id, 'sedan', $brand, $plate_number);
            }

            if ($success) {
                header("Location: " . BASE_URL . "admin/owner_dashboard.php?id=$owner_id");
                exit;
            } else {
                $error = "Database Error updating car.";
            }
        }

        $cars = $this->carModel->getByOwner($owner_id);
        require_once __DIR__ . '/../Views/dashboard/owner.php';
    }
}
