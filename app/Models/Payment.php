<?php
namespace App\Models;

class Payment {
    private $db;
    
    // Define payment methods
    const METHOD_CASH = 'cash';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_DEBIT_CARD = 'debit_card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_ONLINE = 'online';
    
    // Define payment statuses
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    /**
     * Create payment record
     */
    public function create($rental_id, $user_id, $amount, $payment_method = self::METHOD_CASH, $transaction_id = '', $reference_number = '') {
        $payment_status = self::STATUS_PENDING;
        $payment_date = null;
        
        $stmt = $this->db->prepare("INSERT INTO payments (rental_id, user_id, amount, payment_method, payment_status, transaction_id, reference_number, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsssss", $rental_id, $user_id, $amount, $payment_method, $payment_status, $transaction_id, $reference_number, $payment_date);
        return $stmt->execute();
    }

    /**
     * Get payment by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT p.*, r.car_id, r.user_id as renter_id, u.name as user_name, c.model as car_model 
                                   FROM payments p 
                                   LEFT JOIN rentals r ON p.rental_id = r.id 
                                   LEFT JOIN users u ON p.user_id = u.id 
                                   LEFT JOIN cars c ON r.car_id = c.id 
                                   WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get payment by rental ID
     */
    public function getByRentalId($rental_id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE rental_id = ?");
        $stmt->bind_param("i", $rental_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all payments
     */
    public function getAll($status = null, $limit = null, $offset = 0) {
        $query = "SELECT p.*, r.car_id, r.user_id as renter_id, u.name as user_name, c.model as car_model 
                  FROM payments p 
                  LEFT JOIN rentals r ON p.rental_id = r.id 
                  LEFT JOIN users u ON p.user_id = u.id 
                  LEFT JOIN cars c ON r.car_id = c.id";
        
        if ($status) {
            $query .= " WHERE p.payment_status = '$status'";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        if ($limit) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $query .= " LIMIT $offset, $limit";
        }
        
        return $this->db->query($query);
    }

    /**
     * Get payments for user
     */
    public function getByUserId($user_id, $status = null) {
        $user_id = (int)$user_id;
        
        $query = "SELECT p.*, r.car_id, c.model as car_model, r.start_date, r.end_date 
                  FROM payments p 
                  LEFT JOIN rentals r ON p.rental_id = r.id 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE p.user_id = ?";
        
        if ($status) {
            $query .= " AND p.payment_status = ?";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
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
     * Update payment status
     */
    public function updateStatus($id, $status, $payment_date = null) {
        $validStatuses = [self::STATUS_PENDING, self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_REFUNDED];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        if (!$payment_date && $status === self::STATUS_COMPLETED) {
            $payment_date = date('Y-m-d H:i:s');
        }
        
        if ($payment_date) {
            $stmt = $this->db->prepare("UPDATE payments SET payment_status = ?, payment_date = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $payment_date, $id);
        } else {
            $stmt = $this->db->prepare("UPDATE payments SET payment_status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
        }
        
        return $stmt->execute();
    }

    /**
     * Update payment information
     */
    public function update($id, $amount, $payment_method, $transaction_id = '', $reference_number = '', $notes = '') {
        $stmt = $this->db->prepare("UPDATE payments SET amount = ?, payment_method = ?, transaction_id = ?, reference_number = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("dssssi", $amount, $payment_method, $transaction_id, $reference_number, $notes, $id);
        return $stmt->execute();
    }

    /**
     * Get total payment amount
     */
    public function getTotalAmount($status = null) {
        if ($status) {
            $result = $this->db->query("SELECT SUM(amount) as total FROM payments WHERE payment_status = '$status'");
        } else {
            $result = $this->db->query("SELECT SUM(amount) as total FROM payments");
        }
        
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Get payment count by status
     */
    public function getCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM payments WHERE payment_status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get total payment count
     */
    public function getTotalCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM payments");
        return $result->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Delete payment
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Get payments within date range
     */
    public function getByDateRange($start_date, $end_date, $status = null) {
        $query = "SELECT p.*, r.car_id, u.name as user_name, c.model as car_model 
                  FROM payments p 
                  LEFT JOIN rentals r ON p.rental_id = r.id 
                  LEFT JOIN users u ON p.user_id = u.id 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE p.payment_date >= ? AND p.payment_date <= ?";
        
        if ($status) {
            $query .= " AND p.payment_status = ?";
        }
        
        $query .= " ORDER BY p.payment_date DESC";
        
        $stmt = $this->db->prepare($query);
        
        if ($status) {
            $stmt->bind_param("sss", $start_date, $end_date, $status);
        } else {
            $stmt->bind_param("ss", $start_date, $end_date);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get payment statistics
     */
    public function getStatistics() {
        $stats = [
            'total_payments' => 0,
            'total_amount' => 0,
            'pending_amount' => 0,
            'completed_amount' => 0,
            'cancelled_amount' => 0,
            'refunded_amount' => 0,
            'payment_methods' => []
        ];
        
        // Get totals
        $result = $this->db->query("SELECT 
                                   COUNT(*) as total_payments,
                                   SUM(amount) as total_amount,
                                   SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                                   SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                                   SUM(CASE WHEN payment_status = 'cancelled' THEN amount ELSE 0 END) as cancelled_amount,
                                   SUM(CASE WHEN payment_status = 'refunded' THEN amount ELSE 0 END) as refunded_amount
                                   FROM payments");
        
        if ($row = $result->fetch_assoc()) {
            $stats['total_payments'] = (int)$row['total_payments'];
            $stats['total_amount'] = (float)($row['total_amount'] ?? 0);
            $stats['pending_amount'] = (float)($row['pending_amount'] ?? 0);
            $stats['completed_amount'] = (float)($row['completed_amount'] ?? 0);
            $stats['cancelled_amount'] = (float)($row['cancelled_amount'] ?? 0);
            $stats['refunded_amount'] = (float)($row['refunded_amount'] ?? 0);
        }
        
        // Get payment methods breakdown
        $result = $this->db->query("SELECT payment_method, COUNT(*) as count, SUM(amount) as total 
                                   FROM payments 
                                   GROUP BY payment_method
                                   ORDER BY total DESC");
        
        while ($row = $result->fetch_assoc()) {
            $stats['payment_methods'][] = $row;
        }
        
        return $stats;
    }
}
