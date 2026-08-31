<?php
namespace App\Models;

class Car {
    private $db;
    
    // Define vehicle statuses
    const STATUS_AVAILABLE = 'available';
    const STATUS_RESERVED = 'reserved';
    const STATUS_RENTED = 'rented';
    const STATUS_MAINTENANCE = 'maintenance';
    
    // Define vehicle types
    const TYPES = ['sedan', 'suv', 'truck', 'van', 'sports', 'other'];

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    /**
     * Get all vehicles with filters
     */
    public function getAll($owner_id_filter = null, $status_filter = null, $type_filter = null) {
        $query = "SELECT c.*, u.name as owner_name FROM cars c LEFT JOIN users u ON c.owner_id = u.id WHERE 1=1";
        
        if ($owner_id_filter) {
            $owner_id_filter = (int)$owner_id_filter;
            $query .= " AND c.owner_id = $owner_id_filter";
        }
        
        if ($status_filter && in_array($status_filter, [self::STATUS_AVAILABLE, self::STATUS_RESERVED, self::STATUS_RENTED, self::STATUS_MAINTENANCE])) {
            $query .= " AND c.status = '$status_filter'";
        }
        
        if ($type_filter && in_array($type_filter, self::TYPES)) {
            $query .= " AND c.type = '$type_filter'";
        }
        
        $query .= " ORDER BY c.created_at DESC";
        return $this->db->query($query);
    }

    /**
     * Get vehicle by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT c.*, u.name as owner_name FROM cars c LEFT JOIN users u ON c.owner_id = u.id WHERE c.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get vehicle by plate number
     */
    public function getByPlateNumber($plate_number) {
        $stmt = $this->db->prepare("SELECT * FROM cars WHERE plate_number = ?");
        $stmt->bind_param("s", $plate_number);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Create new vehicle
     */
    public function create($plate_number, $brand, $model, $year, $type, $price_per_day, $owner_id, $description = '', $image = '') {
        $status = self::STATUS_AVAILABLE;
        
        $stmt = $this->db->prepare("INSERT INTO cars (plate_number, brand, model, year, type, price_per_day, owner_id, status, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisisss", $plate_number, $brand, $model, $year, $type, $price_per_day, $owner_id, $status, $description, $image);
        return $stmt->execute();
    }

    /**
     * Update vehicle information
     */
    public function update($id, $plate_number, $brand, $model, $year, $type, $price_per_day, $description = '', $image = '') {
        $stmt = $this->db->prepare("UPDATE cars SET plate_number = ?, brand = ?, model = ?, year = ?, type = ?, price_per_day = ?, description = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssssisissi", $plate_number, $brand, $model, $year, $type, $price_per_day, $description, $image, $id);
        return $stmt->execute();
    }

    /**
     * Update vehicle status
     */
    public function updateStatus($id, $status) {
        $validStatuses = [self::STATUS_AVAILABLE, self::STATUS_RESERVED, self::STATUS_RENTED, self::STATUS_MAINTENANCE];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE cars SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /**
     * Get vehicles by owner
     */
    public function getByOwner($owner_id, $status = null) {
        $owner_id = (int)$owner_id;
        
        if ($status && in_array($status, [self::STATUS_AVAILABLE, self::STATUS_RESERVED, self::STATUS_RENTED, self::STATUS_MAINTENANCE])) {
            $query = "SELECT * FROM cars WHERE owner_id = $owner_id AND status = '$status' ORDER BY created_at DESC";
        } else {
            $query = "SELECT * FROM cars WHERE owner_id = $owner_id ORDER BY created_at DESC";
        }
        
        return $this->db->query($query);
    }

    /**
     * Get available vehicles
     */
    public function getAvailable($start_date = null, $end_date = null, $price_max = null) {
        $query = "SELECT c.* FROM cars c WHERE c.status = ?";
        
        // If dates provided, check for no overlapping rentals/reservations
        if ($start_date && $end_date) {
            $query .= " AND c.id NOT IN (
                SELECT DISTINCT car_id FROM rentals 
                WHERE status IN ('approved', 'pending') 
                AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))
            ) AND c.id NOT IN (
                SELECT DISTINCT car_id FROM reservations 
                WHERE status IN ('pending', 'confirmed') 
                AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))
            )";
        }
        
        if ($price_max) {
            $price_max = (float)$price_max;
            $query .= " AND c.price_per_day <= $price_max";
        }
        
        $query .= " ORDER BY c.price_per_day ASC";
        
        $stmt = $this->db->prepare($query);
        
        $status = self::STATUS_AVAILABLE;
        if ($start_date && $end_date) {
            $stmt->bind_param("ssssss", $status, $start_date, $end_date, $end_date, $start_date, $start_date, $end_date, $end_date, $start_date, $start_date, $end_date);
        } else {
            $stmt->bind_param("s", $status);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get available vehicles for owner
     */
    public function getAvailableByOwner($owner_id) {
        $owner_id = (int)$owner_id;
        return $this->db->query("SELECT * FROM cars WHERE status = '" . self::STATUS_AVAILABLE . "' AND owner_id = $owner_id ORDER BY created_at DESC");
    }

    /**
     * Check if vehicle is available for rental period
     */
    public function isAvailableForPeriod($car_id, $start_date, $end_date) {
        $car_id = (int)$car_id;
        
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
        
        return $result['count'] == 0;
    }

    /**
     * Search vehicles
     */
    public function search($searchTerm, $filters = []) {
        $searchTerm = '%' . $searchTerm . '%';
        
        $query = "SELECT c.*, u.name as owner_name FROM cars c 
                  LEFT JOIN users u ON c.owner_id = u.id
                  WHERE (c.brand LIKE ? OR c.model LIKE ? OR c.plate_number LIKE ?)";
        
        $params = ["sss", $searchTerm, $searchTerm, $searchTerm];
        
        if (!empty($filters['status'])) {
            $query .= " AND c.status = ?";
            $params[0] .= "s";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['type'])) {
            $query .= " AND c.type = ?";
            $params[0] .= "s";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['price_max'])) {
            $query .= " AND c.price_per_day <= ?";
            $params[0] .= "d";
            $params[] = (float)$filters['price_max'];
        }
        
        if (!empty($filters['price_min'])) {
            $query .= " AND c.price_per_day >= ?";
            $params[0] .= "d";
            $params[] = (float)$filters['price_min'];
        }
        
        $query .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(...$params);
        $stmt->execute();
        
        return $stmt->get_result();
    }

    /**
     * Get total vehicles count
     */
    public function getTotalCount() {
        return $this->db->query("SELECT COUNT(*) as count FROM cars")->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get vehicles count by status
     */
    public function getCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM cars WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get vehicles count by owner
     */
    public function getCountByOwner($owner_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM cars WHERE owner_id = ?");
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get stock count by owner (alias)
     */
    public function getStockCountByOwner($owner_id) {
        return $this->getCountByOwner($owner_id);
    }

    /**
     * Delete vehicle
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM cars WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Get total count of cars
     */
    public function getTotalCarsCount() {
        return $this->getTotalCount();
    }

    /**
     * Get average price
     */
    public function getAveragePrice() {
        $result = $this->db->query("SELECT AVG(price_per_day) as avg_price FROM cars");
        $row = $result->fetch_assoc();
        return $row['avg_price'] ?? 0;
    }

    public function addCar($model, $year, $price, $desc, $image, $status, $quantity, $owner_id) {
        $stmt = $this->db->prepare("INSERT INTO cars (model, year, price_per_day, description, image, status, quantity, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsssii", $model, $year, $price, $desc, $image, $status, $quantity, $owner_id);
        return $stmt->execute();
    }

    public function updateCar($id, $model, $year, $price, $desc, $image, $status, $quantity, $owner_id) {
        if ($owner_id !== null) {
            $stmt = $this->db->prepare("UPDATE cars SET model=?, year=?, price_per_day=?, description=?, image=?, status=?, quantity=?, owner_id=? WHERE id=?");
            $stmt->bind_param("sidsssiii", $model, $year, $price, $desc, $image, $status, $quantity, $owner_id, $id);
        } else {
            $stmt = $this->db->prepare("UPDATE cars SET model=?, year=?, price_per_day=?, description=?, image=?, status=?, quantity=? WHERE id=?");
            $stmt->bind_param("sidsssii", $model, $year, $price, $desc, $image, $status, $quantity, $id);
        }
        return $stmt->execute();
    }

    public function updateCarForOwner($id, $owner_id, $model, $year, $price, $desc, $image, $status, $quantity) {
        $stmt = $this->db->prepare("UPDATE cars SET model=?, year=?, price_per_day=?, description=?, image=?, status=?, quantity=? WHERE id=? AND owner_id=?");
        $stmt->bind_param("sidsssiii", $model, $year, $price, $desc, $image, $status, $quantity, $id, $owner_id);
        return $stmt->execute();
    }

    public function deleteCar($id) {
        $stmt = $this->db->prepare("DELETE FROM cars WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getActiveBookingsCount($car_id, $today) {
        $car_id = (int)$car_id;
        $sql = "SELECT COUNT(*) FROM rentals WHERE car_id = $car_id AND status IN ('pending', 'approved') AND '$today' BETWEEN start_date AND end_date";
        return $this->db->query($sql)->fetch_row()[0];
    }

    public function getNextAvailableDate($car_id, $today) {
        $car_id = (int)$car_id;
        $next_sql = "SELECT MIN(end_date) FROM rentals WHERE car_id = $car_id AND status IN ('pending', 'approved') AND end_date >= '$today'";
        $next_res = $this->db->query($next_sql);
        if ($next_res && $date_row = $next_res->fetch_row()) {
            if ($date_row[0]) {
                return date('M d', strtotime($date_row[0] . ' +1 day'));
            }
        }
        return null;
    }
}
