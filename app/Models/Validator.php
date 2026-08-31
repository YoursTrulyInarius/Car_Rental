<?php
namespace App\Models;

/**
 * Validation & Business Rules Handler
 * Implements all business logic requirements from SRS
 */
class Validator {
    private $db;
    private $errors = [];

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Add error
     */
    public function addError($message) {
        $this->errors[] = $message;
    }

    /**
     * Clear errors
     */
    public function clearErrors() {
        $this->errors = [];
    }

    /**
     * Validate email format
     */
    public function validateEmail($email) {
        if (empty($email)) {
            $this->addError("Email is required.");
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError("Please enter a valid email address.");
            return false;
        }
        
        return true;
    }

    /**
     * Validate password
     */
    public function validatePassword($password, $confirm_password = null) {
        if (empty($password)) {
            $this->addError("Password is required.");
            return false;
        }
        
        if (strlen($password) < 6) {
            $this->addError("Password must be at least 6 characters long.");
            return false;
        }
        
        if ($confirm_password !== null && $password !== $confirm_password) {
            $this->addError("Passwords do not match.");
            return false;
        }
        
        return true;
    }

    /**
     * Validate user registration input
     */
    public function validateUserRegistration($data) {
        $this->clearErrors();
        
        if (empty($data['name'])) {
            $this->addError("Name is required.");
        } elseif (strlen($data['name']) < 2) {
            $this->addError("Name must be at least 2 characters long.");
        }
        
        $this->validateEmail($data['email'] ?? '');
        $this->validatePassword($data['password'] ?? '', $data['confirm_password'] ?? '');
        
        if (!empty($data['phone']) && !preg_match('/^[0-9\-\+\(\)\s]{7,}$/', $data['phone'])) {
            $this->addError("Please enter a valid phone number.");
        }
        
        return empty($this->errors);
    }

    /**
     * Validate rental period (dates)
     */
    public function validateRentalPeriod($start_date, $end_date) {
        $this->clearErrors();
        
        if (empty($start_date) || empty($end_date)) {
            $this->addError("Both start and end dates are required.");
            return false;
        }
        
        $start = strtotime($start_date);
        $end = strtotime($end_date);
        
        if ($start === false || $end === false) {
            $this->addError("Please enter valid dates.");
            return false;
        }
        
        if ($start >= $end) {
            $this->addError("End date must be after start date.");
            return false;
        }
        
        $today = strtotime('today');
        if ($start < $today) {
            $this->addError("Start date cannot be in the past.");
            return false;
        }
        
        return true;
    }

    /**
     * Validate vehicle availability
     */
    public function validateVehicleAvailability($car_id, $start_date, $end_date) {
        $this->clearErrors();
        
        $car_id = (int)$car_id;
        
        // Check if vehicle exists
        $stmt = $this->db->prepare("SELECT id, status FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $car = $stmt->get_result()->fetch_assoc();
        
        if (!$car) {
            $this->addError("Vehicle not found.");
            return false;
        }
        
        if ($car['status'] === Car::STATUS_MAINTENANCE) {
            $this->addError("This vehicle is currently under maintenance.");
            return false;
        }
        
        // Check for overlapping rentals
        $rental_query = "SELECT COUNT(*) as count FROM rentals 
                        WHERE car_id = ? 
                        AND status IN ('approved', 'pending')
                        AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))";
        
        $stmt = $this->db->prepare($rental_query);
        $stmt->bind_param("issssss", $car_id, $end_date, $start_date, $end_date, $start_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $this->addError("Vehicle is already rented during the selected period.");
            return false;
        }
        
        // Check for overlapping reservations
        $reservation_query = "SELECT COUNT(*) as count FROM reservations 
                            WHERE car_id = ? 
                            AND status IN ('pending', 'confirmed')
                            AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))";
        
        $stmt = $this->db->prepare($reservation_query);
        $stmt->bind_param("issssss", $car_id, $end_date, $start_date, $end_date, $start_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $this->addError("Vehicle has reservations during the selected period.");
            return false;
        }
        
        return true;
    }

    /**
     * Validate payment
     */
    public function validatePayment($rental_id, $amount) {
        $this->clearErrors();
        
        $rental_id = (int)$rental_id;
        
        // Check if rental exists
        $stmt = $this->db->prepare("SELECT id, total_price, status FROM rentals WHERE id = ?");
        $stmt->bind_param("i", $rental_id);
        $stmt->execute();
        $rental = $stmt->get_result()->fetch_assoc();
        
        if (!$rental) {
            $this->addError("Rental not found.");
            return false;
        }
        
        if ($rental['status'] !== Rental::STATUS_APPROVED) {
            $this->addError("Payment can only be made for approved rentals.");
            return false;
        }
        
        if ($amount <= 0) {
            $this->addError("Payment amount must be greater than zero.");
            return false;
        }
        
        if (abs($amount - $rental['total_price']) > 0.01) {
            $this->addError("Payment amount does not match rental total.");
            return false;
        }
        
        return true;
    }

    /**
     * Validate vehicle information
     */
    public function validateVehicle($data, $for_update = false) {
        $this->clearErrors();
        
        if (empty($data['plate_number'])) {
            $this->addError("Plate number is required.");
        } else {
            // Check if plate number is unique (or matches existing for update)
            $stmt = $this->db->prepare("SELECT id FROM cars WHERE plate_number = ? AND id != ?");
            $car_id = $for_update ? (int)$data['id'] : 0;
            $stmt->bind_param("si", $data['plate_number'], $car_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $this->addError("This plate number is already registered.");
            }
        }
        
        if (empty($data['brand'])) {
            $this->addError("Brand is required.");
        }
        
        if (empty($data['model'])) {
            $this->addError("Model is required.");
        }
        
        if (empty($data['year']) || !is_numeric($data['year']) || $data['year'] < 1900 || $data['year'] > date('Y') + 1) {
            $this->addError("Please enter a valid year.");
        }
        
        if (empty($data['price_per_day']) || !is_numeric($data['price_per_day']) || $data['price_per_day'] <= 0) {
            $this->addError("Please enter a valid price per day.");
        }
        
        if (!empty($data['type']) && !in_array($data['type'], Car::TYPES)) {
            $this->addError("Invalid vehicle type.");
        }
        
        return empty($this->errors);
    }

    /**
     * Calculate rental price
     */
    public function calculateRentalPrice($start_date, $end_date, $price_per_day) {
        $start = strtotime($start_date);
        $end = strtotime($end_date);
        
        $days = ceil(($end - $start) / (60 * 60 * 24));
        if ($days < 1) {
            $days = 1;
        }
        
        return round($days * $price_per_day, 2);
    }

    /**
     * Calculate rental duration in days
     */
    public function getRentalDays($start_date, $end_date) {
        $start = strtotime($start_date);
        $end = strtotime($end_date);
        
        $days = ceil(($end - $start) / (60 * 60 * 24));
        return max($days, 1);
    }

    /**
     * Validate authorization
     */
    public function authorizeRental($user_id, $rental_id) {
        $stmt = $this->db->prepare("SELECT user_id FROM rentals WHERE id = ?");
        $stmt->bind_param("i", $rental_id);
        $stmt->execute();
        $rental = $stmt->get_result()->fetch_assoc();
        
        if (!$rental) {
            $this->addError("Rental not found.");
            return false;
        }
        
        if ($rental['user_id'] != $user_id) {
            $this->addError("You are not authorized to access this rental.");
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can modify reservation
     */
    public function canModifyReservation($user_id, $reservation_id) {
        $stmt = $this->db->prepare("SELECT user_id, status FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $reservation = $stmt->get_result()->fetch_assoc();
        
        if (!$reservation) {
            $this->addError("Reservation not found.");
            return false;
        }
        
        if ($reservation['user_id'] != $user_id) {
            $this->addError("You are not authorized to modify this reservation.");
            return false;
        }
        
        if ($reservation['status'] === Reservation::STATUS_COMPLETED || 
            $reservation['status'] === Reservation::STATUS_CANCELLED) {
            $this->addError("This reservation cannot be modified.");
            return false;
        }
        
        return true;
    }

    /**
     * Get available vehicles for period with price range
     */
    public function getAvailableVehicles($start_date, $end_date, $price_min = null, $price_max = null, $type = null) {
        $query = "SELECT c.* FROM cars c WHERE c.status = 'available' 
                  AND c.id NOT IN (
                    SELECT DISTINCT car_id FROM rentals 
                    WHERE status IN ('approved', 'pending') 
                    AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))
                  )
                  AND c.id NOT IN (
                    SELECT DISTINCT car_id FROM reservations 
                    WHERE status IN ('pending', 'confirmed') 
                    AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))
                  )";
        
        if ($price_min !== null) {
            $price_min = (float)$price_min;
            $query .= " AND c.price_per_day >= $price_min";
        }
        
        if ($price_max !== null) {
            $price_max = (float)$price_max;
            $query .= " AND c.price_per_day <= $price_max";
        }
        
        if ($type !== null && in_array($type, Car::TYPES)) {
            $query .= " AND c.type = '$type'";
        }
        
        $query .= " ORDER BY c.price_per_day ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssssssssssss", $start_date, $end_date, $end_date, $start_date, $start_date, $end_date,
                         $start_date, $end_date, $end_date, $start_date, $start_date, $end_date);
        $stmt->execute();
        
        return $stmt->get_result();
    }

    /**
     * Check if user is owner of vehicle
     */
    public function isVehicleOwner($user_id, $car_id) {
        $stmt = $this->db->prepare("SELECT id FROM cars WHERE id = ? AND owner_id = ?");
        $stmt->bind_param("ii", $car_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Validate rental cancellation
     */
    public function canCancelRental($rental_id) {
        $stmt = $this->db->prepare("SELECT status, start_date FROM rentals WHERE id = ?");
        $stmt->bind_param("i", $rental_id);
        $stmt->execute();
        $rental = $stmt->get_result()->fetch_assoc();
        
        if (!$rental) {
            $this->addError("Rental not found.");
            return false;
        }
        
        if ($rental['status'] === Rental::STATUS_COMPLETED || 
            $rental['status'] === Rental::STATUS_CANCELLED) {
            $this->addError("This rental cannot be cancelled.");
            return false;
        }
        
        // Can't cancel if rental starts today or has already started
        if (strtotime($rental['start_date']) <= strtotime('today')) {
            $this->addError("Cannot cancel rental that has already started.");
            return false;
        }
        
        return true;
    }
}
