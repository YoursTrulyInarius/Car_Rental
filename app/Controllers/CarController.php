<?php
namespace App\Controllers;

use App\Models\Car;
use App\Models\User;

class CarController {
    private $db;
    private $carModel;
    private $userModel;

    public function __construct($mysqli) {
        $this->db = $mysqli;
        $this->carModel = new Car($mysqli);
        $this->userModel = new User($mysqli);
    }

    public function carDetails() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        if (!isset($_GET['id'])) {
            header("Location: " . BASE_URL . "index.php");
            exit;
        }

        $id = (int)$_GET['id'];
        $car = $this->carModel->getById($id);

        if (!$car) {
            header("Location: " . BASE_URL . "index.php");
            exit;
        }

        require_once __DIR__ . '/../Views/cars/details.php';
    }

    public function adminManageCars() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'owner' && $_SESSION['role'] !== 'staff')) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        $error = '';
        $owner_id_filter = $_GET['owner_id'] ?? null;
        $owner_name = "";

        if ($owner_id_filter) {
            $owner = $this->userModel->getById($owner_id_filter);
            if ($owner) $owner_name = $owner['name'];
        }

        // Handle Delete
        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            $this->carModel->deleteCar($id);
            header("Location: " . BASE_URL . "admin/cars.php");
            exit;
        }

        // Handle Add/Edit
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = $_POST['model'] ?? '';
            $year = $_POST['year'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $desc = $_POST['description'] ?? '';
            $status = $_POST['status'] ?? 'available';
            $quantity = $_POST['quantity'] ?? 1;
            $car_id = $_POST['car_id'] ?? null;
            $owner_id = !empty($_POST['owner_id']) ? $_POST['owner_id'] : null;

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
                $success = $this->carModel->updateCar($car_id, $model, $year, $price, $desc, $image, $status, $quantity, $owner_id);
            } else {
                $success = $this->carModel->addCar($model, $year, $price, $desc, $image, $status, $quantity, $owner_id);
            }

            if ($success) {
                header("Location: " . BASE_URL . "admin/cars.php");
                exit;
            } else {
                $error = "Database Error while saving vehicle.";
            }
        }

        $cars = $this->carModel->getAll($owner_id_filter);
        $all_owners = $this->userModel->getOwners();
        $mysqli = $this->db;

        require_once __DIR__ . '/../Views/cars/admin_cars.php';
    }
}
