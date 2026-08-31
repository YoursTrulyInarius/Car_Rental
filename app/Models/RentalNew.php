<?php
namespace App\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Rental {
    private $db;
    
    // Define rental statuses
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    /**
     * Create rental transaction
     */
    public function createRental($user_id, $car_id, $start_date, $end_date, $total_price, $notes = '') {
        // Validate dates
        if ($start_date >= $end_date) {
            return false;
        }
        
        // Check vehicle availability
        $carModel = new Car($this->db);
        if (!$carModel->isAvailableForPeriod($car_id, $start_date, $end_date)) {
            return false;
        }
        
        $status = self::STATUS_PENDING;
        $stmt = $this->db->prepare("INSERT INTO rentals (user_id, car_id, start_date, end_date, total_price, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdss", $user_id, $car_id, $start_date, $end_date, $total_price, $status, $notes);
        return $stmt->execute();
    }

    /**
     * Get rental by ID
     */
    public function getRentalById($id) {
        $stmt = $this->db->prepare("SELECT r.*, u.email, u.name as user_name, u.phone, c.model as car_model, c.brand, c.plate_number, c.price_per_day
                                   FROM rentals r 
                                   LEFT JOIN users u ON r.user_id = u.id 
                                   LEFT JOIN cars c ON r.car_id = c.id 
                                   WHERE r.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get rental details
     */
    public function getRentalDetails($rental_id) {
        return $this->getRentalById($rental_id);
    }

    /**
     * Get user rentals
     */
    public function getUserRentals($user_id, $status = null) {
        $user_id = (int)$user_id;
        
        $query = "SELECT r.*, c.model, c.brand, c.image, c.price_per_day 
                  FROM rentals r 
                  JOIN cars c ON r.car_id = c.id 
                  WHERE r.user_id = ?";
        
        if ($status && in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            $query .= " AND r.status = '$status'";
        }
        
        $query .= " ORDER BY r.start_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get all rentals
     */
    public function getAllRentals($status = null, $limit = null, $offset = 0) {
        $query = "SELECT r.*, u.name as user_name, u.email, u.phone, c.model as car_model, c.brand, c.plate_number
                  FROM rentals r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN cars c ON r.car_id = c.id";
        
        if ($status && in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            $query .= " WHERE r.status = '$status'";
        }
        
        $query .= " ORDER BY r.created_at DESC";
        
        if ($limit) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $query .= " LIMIT $offset, $limit";
        }
        
        return $this->db->query($query);
    }

    /**
     * Get recent rentals
     */
    public function getRecentRentals($limit = 5) {
        $limit = (int)$limit;
        $query = "SELECT r.*, u.name as user_name, c.model, c.year, c.image 
                  FROM rentals r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN cars c ON r.car_id = c.id 
                  WHERE r.status = '" . self::STATUS_COMPLETED . "'
                  ORDER BY r.created_at DESC 
                  LIMIT $limit";
        return $this->db->query($query);
    }

    /**
     * Get rentals by car ID
     */
    public function getByCarId($car_id, $status = null) {
        $car_id = (int)$car_id;
        
        $query = "SELECT r.*, u.name as user_name, u.email, u.phone
                  FROM rentals r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  WHERE r.car_id = ?";
        
        if ($status) {
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
     * Update rental status
     */
    public function updateStatus($rental_id, $status) {
        $validStatuses = [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_COMPLETED, self::STATUS_CANCELLED];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE rentals SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $rental_id);
        return $stmt->execute();
    }

    /**
     * Update rental information
     */
    public function update($id, $start_date, $end_date, $total_price, $notes = '') {
        $stmt = $this->db->prepare("UPDATE rentals SET start_date = ?, end_date = ?, total_price = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $start_date, $end_date, $total_price, $notes, $id);
        return $stmt->execute();
    }

    /**
     * Complete rental with return date
     */
    public function completeRental($id, $actual_return_date = null) {
        if (!$actual_return_date) {
            $actual_return_date = date('Y-m-d');
        }
        
        $status = self::STATUS_COMPLETED;
        $stmt = $this->db->prepare("UPDATE rentals SET status = ?, actual_return_date = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $actual_return_date, $id);
        return $stmt->execute();
    }

    /**
     * Get pending rentals count
     */
    public function getPendingCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM rentals WHERE status = '" . self::STATUS_PENDING . "'");
        return $result->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get approved rentals count
     */
    public function getApprovedCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM rentals WHERE status = '" . self::STATUS_APPROVED . "'");
        return $result->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get active count (approved + not completed)
     */
    public function getActiveCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM rentals WHERE status = '" . self::STATUS_APPROVED . "' AND end_date >= CURDATE()");
        return $result->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get total revenue from completed rentals
     */
    public function getTotalRevenue($status = null) {
        if ($status) {
            $result = $this->db->query("SELECT SUM(total_price) as total FROM rentals WHERE status = '$status'");
        } else {
            $result = $this->db->query("SELECT SUM(total_price) as total FROM rentals");
        }
        
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Get rentals by date range
     */
    public function getByDateRange($start_date, $end_date, $status = null) {
        $query = "SELECT r.*, u.name as user_name, u.email, c.model as car_model
                  FROM rentals r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN cars c ON r.car_id = c.id 
                  WHERE r.start_date >= ? AND r.start_date <= ?";
        
        if ($status) {
            $query .= " AND r.status = ?";
        }
        
        $query .= " ORDER BY r.start_date ASC";
        
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
     * Get rental statistics
     */
    public function getStatistics() {
        $stats = [
            'total_rentals' => 0,
            'pending_rentals' => 0,
            'approved_rentals' => 0,
            'completed_rentals' => 0,
            'cancelled_rentals' => 0,
            'total_revenue' => 0,
            'average_rental_price' => 0
        ];
        
        $result = $this->db->query("SELECT 
                                   COUNT(*) as total_rentals,
                                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_rentals,
                                   SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_rentals,
                                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_rentals,
                                   SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_rentals,
                                   SUM(total_price) as total_revenue,
                                   AVG(total_price) as average_rental_price
                                   FROM rentals");
        
        if ($row = $result->fetch_assoc()) {
            $stats['total_rentals'] = (int)$row['total_rentals'];
            $stats['pending_rentals'] = (int)($row['pending_rentals'] ?? 0);
            $stats['approved_rentals'] = (int)($row['approved_rentals'] ?? 0);
            $stats['completed_rentals'] = (int)($row['completed_rentals'] ?? 0);
            $stats['cancelled_rentals'] = (int)($row['cancelled_rentals'] ?? 0);
            $stats['total_revenue'] = (float)($row['total_revenue'] ?? 0);
            $stats['average_rental_price'] = (float)($row['average_rental_price'] ?? 0);
        }
        
        return $stats;
    }

    /**
     * Delete rental
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM rentals WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Send rental status email notification
     */
    public function sendStatusEmail($toEmail, $userName, $carModel, $status, $dates, $totalPrice) {
        require_once __DIR__ . '/../../admin/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../../admin/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../admin/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        try {
            // Configured Server settings from config.php
            $mail->isSMTP();
            $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('SMTP_USERNAME') ? SMTP_USERNAME : ''; 
            $mail->Password   = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : ''; 
            $mail->SMTPSecure = (defined('SMTP_ENCRYPTION') && SMTP_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;

            // Bypassing SSL for local servers
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@carrental.com';
            $fromName  = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Car Rental';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($toEmail, $userName);

            $mail->isHTML(true);
            $subjectStatus = strtoupper($status);
            $mail->Subject = "Rental Update: Your Request is $subjectStatus";
            
            $color = ($status == 'approved' || $status == 'completed') ? '#198754' : '#dc3545';
            $statusText = ucfirst($status);

            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <div style='text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee;'>
                    <h1 style='color: #0d6efd; margin: 0;'>" . htmlspecialchars($fromName) . "</h1>
                    <p style='color: #6c757d; margin: 5px 0 0;'>PREMIUM CAR RENTAL</p>
                </div>
                
                <div style='padding: 30px 0;'>
                    <h2 style='color: #333;'>Hello, " . htmlspecialchars($userName) . "</h2>
                    <p style='font-size: 16px; color: #555;'>Your rental request for the <strong>" . htmlspecialchars($carModel) . "</strong> has been <strong style='color: $color;'>$statusText</strong>.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; color: #6c757d;'>Vehicle</td>
                                <td style='padding: 8px 0; font-weight: bold; text-align: right;'>" . htmlspecialchars($carModel) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #6c757d;'>Rental Dates</td>
                                <td style='padding: 8px 0; font-weight: bold; text-align: right;'>$dates</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #6c757d;'>Total Price</td>
                                <td style='padding: 8px 0; font-weight: bold; text-align: right; color: #0d6efd;'>$totalPrice</td>
                            </tr>
                            <tr style='border-top: 1px solid #ddd;'>
                                <td style='padding: 15px 0 0; color: #6c757d;'>Status</td>
                                <td style='padding: 15px 0 0; font-weight: bold; text-align: right; color: $color;'>$statusText</td>
                            </tr>
                        </table>
                    </div>

                    <p style='margin-top: 30px; font-size: 14px; color: #888;'>
                        " . ($status == 'approved' ? "Please visit our office to complete the payment and pick up your vehicle." : "If you have any questions, please contact our support.") . "
                    </p>
                </div>

                <div style='text-align: center; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;'>
                    &copy; " . date('Y') . " " . htmlspecialchars($fromName) . ". All rights reserved.
                </div>
            </div>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
