<?php
namespace App\Models;

class Car {
    private $db;

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    public function getAll($owner_id_filter = null) {
        $query = "SELECT c.*, u.name as owner_name FROM cars c LEFT JOIN users u ON c.owner_id = u.id";
        if ($owner_id_filter) {
            $owner_id_filter = (int)$owner_id_filter;
            $query .= " WHERE c.owner_id = $owner_id_filter";
        }
        $query .= " ORDER BY c.created_at DESC";
        return $this->db->query($query);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT c.*, u.name as owner_name FROM cars c LEFT JOIN users u ON c.owner_id = u.id WHERE c.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAvailableByOwner($owner_id) {
        $owner_id = (int)$owner_id;
        return $this->db->query("SELECT * FROM cars WHERE status = 'available' AND owner_id = $owner_id ORDER BY created_at DESC");
    }

    public function getByOwner($owner_id) {
        $owner_id = (int)$owner_id;
        return $this->db->query("SELECT * FROM cars WHERE owner_id = $owner_id ORDER BY created_at DESC");
    }

    public function getTotalCarsCount() {
        return $this->db->query("SELECT COUNT(*) FROM cars")->fetch_row()[0];
    }

    public function getStockCountByOwner($owner_id) {
        $owner_id = (int)$owner_id;
        return $this->db->query("SELECT SUM(quantity) FROM cars WHERE owner_id = $owner_id")->fetch_row()[0] ?? 0;
    }

    public function getAvailableStockCountByOwner($owner_id) {
        $owner_id = (int)$owner_id;
        return $this->db->query("SELECT SUM(quantity) FROM cars WHERE owner_id = $owner_id AND status = 'available'")->fetch_row()[0] ?? 0;
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
