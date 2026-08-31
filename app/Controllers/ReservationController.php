<?php
namespace App\Controllers;

use App\Models\Reservation;
use App\Models\Car;

class ReservationController {
    private $db;
    private $reservationModel;
    private $carModel;

    public function __construct($mysqli) {
        $this->db = $mysqli;
        $this->reservationModel = new Reservation($mysqli);
        $this->carModel = new Car($mysqli);
    }

    /**
     * Create new reservation
     */
    public function createReservation() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $car_id = $_POST['car_id'] ?? 0;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $notes = $_POST['notes'] ?? '';

            // Validate dates
            if ($start_date >= $end_date) {
                header("Location: " . BASE_URL . "?error=invalid_dates");
                exit;
            }

            // Check vehicle availability
            if (!$this->reservationModel->isAvailableForPeriod($car_id, $start_date, $end_date)) {
                header("Location: " . BASE_URL . "?error=vehicle_not_available");
                exit;
            }

            if ($this->reservationModel->create($user_id, $car_id, $start_date, $end_date, $notes)) {
                $_SESSION['swal_success'] = "Reservation created successfully!";
                header("Location: " . BASE_URL . "my_reservations.php");
                exit;
            } else {
                header("Location: " . BASE_URL . "?error=reservation_failed");
                exit;
            }
        } else {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }
    }

    /**
     * Get user reservations
     */
    public function myReservations() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $reservations = $this->reservationModel->getByUserId($user_id);

        require_once __DIR__ . '/../Views/rentals/my_reservations.php';
    }

    /**
     * Get reservation details
     */
    public function getReservationDetails($reservation_id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        $reservation = $this->reservationModel->getById($reservation_id);
        
        if (!$reservation || ($reservation['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'owner')) {
            header("Location: " . BASE_URL . "?error=unauthorized");
            exit;
        }

        return $reservation;
    }

    /**
     * Update reservation
     */
    public function updateReservation() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $reservation_id = $_POST['reservation_id'] ?? 0;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $notes = $_POST['notes'] ?? '';

            $reservation = $this->reservationModel->getById($reservation_id);
            
            if (!$reservation || ($reservation['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'owner')) {
                header("Location: " . BASE_URL . "?error=unauthorized");
                exit;
            }

            // Validate dates
            if ($start_date >= $end_date) {
                header("Location: " . BASE_URL . "my_reservations.php?error=invalid_dates");
                exit;
            }

            // Check availability (exclude current reservation)
            if (!$this->reservationModel->isAvailableForPeriod($reservation['car_id'], $start_date, $end_date, $reservation_id)) {
                header("Location: " . BASE_URL . "my_reservations.php?error=vehicle_not_available");
                exit;
            }

            if ($this->reservationModel->update($reservation_id, $start_date, $end_date, $notes)) {
                $_SESSION['swal_success'] = "Reservation updated successfully!";
                header("Location: " . BASE_URL . "my_reservations.php");
            } else {
                header("Location: " . BASE_URL . "my_reservations.php?error=update_failed");
            }
            exit;
        }
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $reservation_id = $_POST['reservation_id'] ?? 0;

            $reservation = $this->reservationModel->getById($reservation_id);
            
            if (!$reservation || ($reservation['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'owner')) {
                header("Location: " . BASE_URL . "?error=unauthorized");
                exit;
            }

            if ($this->reservationModel->cancel($reservation_id)) {
                $_SESSION['swal_success'] = "Reservation cancelled.";
                header("Location: " . BASE_URL . "my_reservations.php");
            } else {
                $_SESSION['swal_error'] = "Failed to cancel reservation.";
                header("Location: " . BASE_URL . "my_reservations.php");
            }
            exit;
        }
    }

    /**
     * Admin: Manage reservations
     */
    public function adminManageReservations() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        // Handle actions
        if (isset($_POST['action']) && isset($_POST['reservation_id'])) {
            $reservation_id = $_POST['reservation_id'];
            $action = $_POST['action'];

            $reservation = $this->reservationModel->getById($reservation_id);
            if ($reservation) {
                if ($action === 'confirm') {
                    $this->reservationModel->confirm($reservation_id);
                    $_SESSION['swal_success'] = "Reservation confirmed.";
                } elseif ($action === 'cancel') {
                    $this->reservationModel->cancel($reservation_id);
                    $_SESSION['swal_success'] = "Reservation cancelled.";
                } elseif ($action === 'complete') {
                    $this->reservationModel->complete($reservation_id);
                    $_SESSION['swal_success'] = "Reservation completed.";
                }
            }

            header("Location: " . BASE_URL . "admin/reservations.php");
            exit;
        }

        $reservations = $this->reservationModel->getAll();
        require_once __DIR__ . '/../Views/rentals/admin_reservations.php';
    }

    /**
     * Convert reservation to rental
     */
    public function convertToRental() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'owner') {
            $reservation_id = $_POST['reservation_id'] ?? 0;

            if ($this->reservationModel->convertToRental($reservation_id)) {
                $_SESSION['swal_success'] = "Reservation converted to rental.";
            } else {
                $_SESSION['swal_error'] = "Failed to convert reservation.";
            }

            header("Location: " . BASE_URL . "admin/reservations.php");
            exit;
        }
    }

    /**
     * Get upcoming reservations
     */
    public function getUpcomingReservations($days = 7) {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            return null;
        }

        return $this->reservationModel->getUpcoming($days);
    }
}
