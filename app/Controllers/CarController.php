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
            $owner_id = $_SESSION['user_id'] ?? $_POST['owner_id'] ?? null;
            $carRows = [];

            if (isset($_POST['cars']) && is_array($_POST['cars']) && !empty($_POST['cars'])) {
                $carRows = $_POST['cars'];
            } else {
                $carRows[] = [
                    'brand' => trim((string)($_POST['brand'] ?? '')),
                    'plate_number' => trim((string)($_POST['plate_number'] ?? '')),
                    'model' => trim((string)($_POST['model'] ?? '')),
                    'year' => $_POST['year'] ?? 0,
                    'price' => $_POST['price'] ?? 0,
                    'description' => $_POST['description'] ?? '',
                    'status' => $_POST['status'] ?? 'available',
                    'quantity' => $_POST['quantity'] ?? 1,
                    'type' => $_POST['type'] ?? 'sedan',
                    'car_id' => $_POST['car_id'] ?? null,
                    'current_image' => $_POST['current_image'] ?? '',
                ];
            }

            $target_dir = __DIR__ . "/../../uploads/";
            $allSaved = true;

            foreach ($carRows as $index => $entry) {
                $brand = trim((string)($entry['brand'] ?? ''));
                $plate_number = trim((string)($entry['plate_number'] ?? ''));
                $model = trim((string)($entry['model'] ?? ''));
                $year = $entry['year'] ?? 0;
                $price = $entry['price'] ?? 0;
                $desc = $entry['description'] ?? '';
                $status = $entry['status'] ?? 'available';
                $quantity = $entry['quantity'] ?? 1;
                $type = $entry['type'] ?? 'sedan';
                $car_id = $entry['car_id'] ?? null;
                $image = $entry['current_image'] ?? '';

                $uploaded_images = [];
                $inputName = 'images[' . $index . ']';

                if (isset($_FILES['images']) && is_array($_FILES['images']['name']) && isset($_FILES['images']['name'][$index])) {
                    $fileName = $_FILES['images']['name'][$index];
                    $fileTmp = $_FILES['images']['tmp_name'][$index] ?? null;
                    $fileError = $_FILES['images']['error'][$index] ?? 0;
                    if ($fileError == 0 && !empty($fileName) && $fileTmp) {
                        $filename = time() . "_" . $index . "_" . basename($fileName);
                        if (move_uploaded_file($fileTmp, $target_dir . $filename)) {
                            $uploaded_images[] = $filename;
                        }
                    }
                }

                if (empty($uploaded_images) && isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
                    $filename = time() . "_" . basename($_FILES['image']['name']);
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
                        $uploaded_images[] = $filename;
                    }
                }

                $uploaded_images = array_slice($uploaded_images, 0, 4);
                if (!empty($uploaded_images)) {
                    $image = json_encode($uploaded_images);
                }

                if ($car_id) {
                    $saved = $this->carModel->updateCar($car_id, $model, $year, $price, $desc, $image, $status, $quantity, $owner_id, $type, $brand, $plate_number);
                } else {
                    $saved = $this->carModel->addCar($model, $year, $price, $desc, $image, $status, $quantity, $owner_id, $type, $brand, $plate_number);
                }

                if (!$saved) {
                    $allSaved = false;
                }
            }

            if ($allSaved) {
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
