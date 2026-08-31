<?php
namespace App\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Rental {
    private $db;

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    public function createRental($user_id, $car_id, $start_date, $end_date, $total_price) {
        $stmt = $this->db->prepare("INSERT INTO rentals (user_id, car_id, start_date, end_date, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iissd", $user_id, $car_id, $start_date, $end_date, $total_price);
        return $stmt->execute();
    }

    public function getUserRentals($user_id) {
        $stmt = $this->db->prepare("SELECT r.*, c.model, c.image FROM rentals r JOIN cars c ON r.car_id = c.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAllRentals() {
        $sql = "SELECT r.*, u.name as user_name, c.model as car_model 
                FROM rentals r 
                JOIN users u ON r.user_id = u.id 
                JOIN cars c ON r.car_id = c.id 
                ORDER BY r.created_at DESC";
        return $this->db->query($sql);
    }

    public function getRecentRentals($limit = 5) {
        $limit = (int)$limit;
        $sql = "SELECT r.*, u.name as user_name, c.model, c.year, c.image 
                FROM rentals r 
                JOIN users u ON r.user_id = u.id 
                JOIN cars c ON r.car_id = c.id 
                WHERE r.status IN ('pending', 'approved', 'rejected', 'completed')
                ORDER BY r.created_at DESC LIMIT $limit";
        return $this->db->query($sql);
    }

    public function getRentalDetails($rental_id) {
        $query = "SELECT r.*, u.email, u.name as user_name, c.model as car_model 
                  FROM rentals r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN cars c ON r.car_id = c.id 
                  WHERE r.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $rental_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateStatus($rental_id, $status) {
        $stmt = $this->db->prepare("UPDATE rentals SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $rental_id);
        return $stmt->execute();
    }

    public function getPendingCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM rentals WHERE status = 'pending'");
        return $result ? $result->fetch_assoc()['count'] : 0;
    }

    public function getActiveCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM rentals WHERE status IN ('pending', 'approved')");
        return $result ? $result->fetch_assoc()['count'] : 0;
    }

    public function getTotalRevenue() {
        $result = $this->db->query("SELECT SUM(total_price) as total FROM rentals WHERE status = 'approved'");
        $row = $result ? $result->fetch_assoc() : null;
        return ($row && $row['total']) ? $row['total'] : 0;
    }

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
            
            $color = ($status == 'approved') ? '#198754' : '#dc3545';
            $statusText = ucfirst($status);

            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <div style='text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee;'>
                    <h1 style='color: #0d6efd; margin: 0;'>" . htmlspecialchars($fromName) . "</h1>
                    <p style='color: #6c757d; margin: 5px 0 0;'>PREMIUM CAR RENTAL</p>
                </div>
                
                <div style='padding: 30px 0;'>
                    <h2 style='color: #333;'>Hello, " . htmlspecialchars($userName) . "</h2>
                    <p style='font-size: 16px; color: #555;'>Your rental request for the <strong>" . htmlspecialchars($carModel) . "</strong> has been <strong style='color: $color;'>$subjectStatus</strong>.</p>
                    
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
