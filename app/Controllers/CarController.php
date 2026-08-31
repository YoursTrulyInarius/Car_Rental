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
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
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
            $type = $_POST['type'] ?? 'sedan';
            $car_id = $_POST['car_id'] ?? null;
            $owner_id = !empty($_POST['owner_id']) ? $_POST['owner_id'] : null;

            $uploaded_images = [];
            $target_dir = __DIR__ . "/../../uploads/";

            // 1. Handle multiple files upload array (images[])
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] == 0 && !empty($name)) {
                        $filename = time() . "_" . $key . "_" . basename($name);
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_dir . $filename)) {
                            $uploaded_images[] = $filename;
                        }
                    }
                }
            }

            // 2. Handle distinct file inputs (image1, image2, image3, image4)
            for ($i = 1; $i <= 4; $i++) {
                $field = 'image' . $i;
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0 && !empty($_FILES[$field]['name'])) {
                    $filename = time() . "_img" . $i . "_" . basename($_FILES[$field]['name']);
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_dir . $filename)) {
                        $uploaded_images[] = $filename;
                    }
                }
            }

            // 3. Fallback for single 'image' upload
            if (empty($uploaded_images) && isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
                $filename = time() . "_" . basename($_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
                    $uploaded_images[] = $filename;
                }
            }

            // Construct final image string or keep current
            if (!empty($uploaded_images)) {
                $image = json_encode($uploaded_images);
            } else {
                $image = $_POST['current_image'] ?? '';
            }

            if ($car_id) {
                $success = $this->carModel->updateCar($car_id, $model, $year, $price, $desc, $image, $status, $quantity, $owner_id, $type);
            } else {
                $success = $this->carModel->addCar($model, $year, $price, $desc, $image, $status, $quantity, $owner_id, $type);
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
