<?php
namespace App\Controllers;

use App\Models\Rental;

class RentalController {
    private $db;
    private $rentalModel;

    public function __construct($mysqli) {
        $this->db = $mysqli;
        $this->rentalModel = new Rental($mysqli);
    }

    public function processRental() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $car_id = $_POST['car_id'] ?? 0;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $price_per_day = $_POST['price'] ?? 0;

            if ($start_date > $end_date) {
                die("Invalid Date Range");
            }

            $start = new \DateTime($start_date);
            $end = new \DateTime($end_date);
            $days = $end->diff($start)->format("%a");
            if ($days == 0) $days = 1;
            
            $total_price = $days * $price_per_day;

            if ($this->rentalModel->createRental($user_id, $car_id, $start_date, $end_date, $total_price)) {
                header("Location: " . BASE_URL . "my_rentals.php?msg=success");
                exit;
            } else {
                echo "Error processing rental request.";
            }
        } else {
            header("Location: " . BASE_URL . "index.php");
            exit;
        }
    }

    public function myRentals() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $result = $this->rentalModel->getUserRentals($user_id);

        require_once __DIR__ . '/../Views/rentals/my_rentals.php';
    }

    public function adminManageRentals() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        // Handle Status Change
        if (isset($_POST['action']) && isset($_POST['rental_id'])) {
            $rental_id = $_POST['rental_id'];
            $new_status = $_POST['action'];

            $rental = $this->rentalModel->getRentalDetails($rental_id);
            if ($rental) {
                if ($this->rentalModel->updateStatus($rental_id, $new_status)) {
                    $dates = date('M d', strtotime($rental['start_date'])) . ' - ' . date('M d', strtotime($rental['end_date']));
                    $totalPrice = '₱' . number_format($rental['total_price'], 2);

                    // Send Email using PHPMailer & config.php constants
                    $this->rentalModel->sendStatusEmail($rental['email'], $rental['user_name'], $rental['car_model'], $new_status, $dates, $totalPrice);

                    $_SESSION['swal_success'] = "Rental request has been " . $new_status . " successfully.";
                }
            }
            header("Location: " . BASE_URL . "admin/rentals.php");
            exit;
        }

        $rentals = $this->rentalModel->getAllRentals();
        require_once __DIR__ . '/../Views/rentals/admin_rentals.php';
    }

    public function checkPending() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'not authorized']);
            exit;
        }

        $count = $this->rentalModel->getPendingCount();
        header('Content-Type: application/json');
        echo json_encode(['pending_count' => (int)$count]);
        exit;
    }
}
