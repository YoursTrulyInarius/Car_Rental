<?php
namespace App\Models;

class Reservation {
    private $db;
    
    // Define reservation statuses
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    /**
     * Create new reservation
     */
    public function create($user_id, $car_id, $start_date, $end_date, $notes = '') {
        $status = self::STATUS_PENDING;
        
        $stmt = $this->db->prepare("INSERT INTO reservations (user_id, car_id, start_date, end_date, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $user_id, $car_id, $start_date, $end_date, $status, $notes);
        return $stmt->execute();
    }

    /**
     * Get reservation by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT r.*, u.name as user_name, u.email, c.model as car_model, c.brand, c.plate_number 
                                   FROM reservations r 
                                   LEFT JOIN users u ON r.user_id = u.id 
                                   LEFT JOIN cars c ON r.car_id = c.id 
                                   WHERE r.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all reservations
     */
    public function getAll($status = null, $limit = null, $offset = 0) {
        $query = "SELECT r.*, u.name as user_name, u.email, c.model as car_model, c.brand, c.plate_number 
                  FROM reservations r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  LEFT JOIN cars c ON r.car_id = c.id";
        
        if ($status && in_array($status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_COMPLETED])) {
            $query .= " WHERE r.status = '$status'";
        }
        
        $query .= " ORDER BY r.start_date ASC";
        
        if ($limit) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $query .= " LIMIT $offset, $limit";
        }
        
        return $this->db->query($query);
    }

    /**
     * Get reservations for user
     */
    public function getByUserId($user_id, $status = null) {
        $user_id = (int)$user_id;
        
        $query = "SELECT r.*, c.model as car_model, c.brand, c.plate_number, c.price_per_day 
                  FROM reservations r 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE r.user_id = ?";
        
        if ($status && in_array($status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_COMPLETED])) {
            $query .= " AND r.status = ?";
        }
        
        $query .= " ORDER BY r.start_date DESC";
        
        $stmt = $this->db->prepare($query);
        
        if ($status) {
            $stmt->bind_param("is", $user_id, $status);
        } else {
            $stmt->bind_param("i", $user_id);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get reservations for vehicle
     */
    public function getByCarId($car_id, $status = null) {
        $car_id = (int)$car_id;
        
        $query = "SELECT r.*, u.name as user_name, u.email, u.phone 
                  FROM reservations r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  WHERE r.car_id = ?";
        
        if ($status && in_array($status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_COMPLETED])) {
            $query .= " AND r.status = ?";
        }
        
        $query .= " ORDER BY r.start_date ASC";
        
        $stmt = $this->db->prepare($query);
        
        if ($status) {
            $stmt->bind_param("is", $car_id, $status);
        } else {
            $stmt->bind_param("i", $car_id);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Update reservation status
     */
    public function updateStatus($id, $status) {
        $validStatuses = [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_COMPLETED];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /**
     * Update reservation information
     */
    public function update($id, $start_date, $end_date, $notes = '') {
        $stmt = $this->db->prepare("UPDATE reservations SET start_date = ?, end_date = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("sssi", $start_date, $end_date, $notes, $id);
        return $stmt->execute();
    }

    /**
     * Cancel reservation
     */
    public function cancel($id) {
        return $this->updateStatus($id, self::STATUS_CANCELLED);
    }

    /**
     * Confirm reservation
     */
    public function confirm($id) {
        return $this->updateStatus($id, self::STATUS_CONFIRMED);
    }

    /**
     * Complete reservation
     */
    public function complete($id) {
        return $this->updateStatus($id, self::STATUS_COMPLETED);
    }

    /**
     * Check if vehicle is available for reservation period
     */
    public function isAvailableForPeriod($car_id, $start_date, $end_date, $exclude_reservation_id = null) {
        $car_id = (int)$car_id;
        
        // Check for overlapping reservations
        $query = "SELECT COUNT(*) as count FROM reservations 
                  WHERE car_id = ? 
                  AND status IN ('pending', 'confirmed')
                  AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))";
        
        if ($exclude_reservation_id) {
            $exclude_reservation_id = (int)$exclude_reservation_id;
            $query .= " AND id != $exclude_reservation_id";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("issssss", $car_id, $end_date, $start_date, $end_date, $start_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
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
        
        return $result['count'] == 0;
    }

    /**
     * Get pending reservations count
     */
    public function getPendingCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM reservations WHERE status = '" . self::STATUS_PENDING . "'");
        return $result->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get total reservations count
     */
    public function getTotalCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM reservations");
        return $result->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get count by status
     */
    public function getCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM reservations WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Delete reservation
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Get upcoming reservations (within next N days)
     */
    public function getUpcoming($days = 7) {
        $days = (int)$days;
        $query = "SELECT r.*, u.name as user_name, u.email, u.phone, c.model as car_model, c.brand 
                  FROM reservations r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE r.status IN ('pending', 'confirmed') 
                  AND r.start_date <= DATE_ADD(CURDATE(), INTERVAL $days DAY)
                  AND r.start_date >= CURDATE()
                  ORDER BY r.start_date ASC";
        
        return $this->db->query($query);
    }

    /**
     * Convert reservation to rental
     */
    public function convertToRental($reservation_id) {
        // Get reservation details
        $reservation = $this->getById($reservation_id);
        if (!$reservation) {
            return false;
        }
        
        // Create rental record
        $rentalModel = new Rental($this->db);
        $days = (new \DateTime($reservation['end_date']))->diff(new \DateTime($reservation['start_date']))->days;
        if ($days == 0) $days = 1;
        
        $total_price = $days * 0; // Need to get price from car
        $carModel = new Car($this->db);
        $car = $carModel->getById($reservation['car_id']);
        $total_price = $days * $car['price_per_day'];
        
        $result = $rentalModel->createRental(
            $reservation['user_id'],
            $reservation['car_id'],
            $reservation['start_date'],
            $reservation['end_date'],
            $total_price
        );
        
        // If rental created, complete the reservation
        if ($result) {
            $this->complete($reservation_id);
        }
        
        return $result;
    }
}
