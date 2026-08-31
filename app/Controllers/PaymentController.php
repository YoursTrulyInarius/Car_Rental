<?php
namespace App\Controllers;

use App\Models\Payment;
use App\Models\Rental;

class PaymentController {
    private $db;
    private $paymentModel;
    private $rentalModel;

    public function __construct($mysqli) {
        $this->db = $mysqli;
        $this->paymentModel = new Payment($mysqli);
        $this->rentalModel = new Rental($mysqli);
    }

    /**
     * Process payment for rental
     */
    public function processPayment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $rental_id = $_POST['rental_id'] ?? 0;
            $payment_method = $_POST['payment_method'] ?? Payment::METHOD_CASH;
            $amount = $_POST['amount'] ?? 0;
            $transaction_id = $_POST['transaction_id'] ?? '';
            $reference_number = $_POST['reference_number'] ?? '';

            // Validate rental exists
            $rental = $this->rentalModel->getRentalById($rental_id);
            if (!$rental) {
                header("Location: " . BASE_URL . "dashboard.php?error=rental_not_found");
                exit;
            }

            // Check payment amount matches rental total
            if ($amount != $rental['total_price']) {
                header("Location: " . BASE_URL . "dashboard.php?error=amount_mismatch");
                exit;
            }

            // Create payment record
            if ($this->paymentModel->create($rental_id, $_SESSION['user_id'], $amount, $payment_method, $transaction_id, $reference_number)) {
                header("Location: " . BASE_URL . "dashboard.php?msg=payment_processed");
                exit;
            } else {
                header("Location: " . BASE_URL . "dashboard.php?error=payment_failed");
                exit;
            }
        } else {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }
    }

    /**
     * Get payment details
     */
    public function getPaymentDetails($payment_id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        $payment = $this->paymentModel->getById($payment_id);
        
        if (!$payment || ($payment['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'owner')) {
            header("Location: " . BASE_URL . "dashboard.php?error=unauthorized");
            exit;
        }

        return $payment;
    }

    /**
     * Get user's payment history
     */
    public function getPaymentHistory() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }

        return $this->paymentModel->getByUserId($_SESSION['user_id']);
    }

    /**
     * Admin: Confirm payment
     */
    public function confirmPayment() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            die("Unauthorized");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $payment_id = $_POST['payment_id'] ?? 0;
            $payment_date = $_POST['payment_date'] ?? date('Y-m-d H:i:s');

            if ($this->paymentModel->updateStatus($payment_id, Payment::STATUS_COMPLETED, $payment_date)) {
                $_SESSION['swal_success'] = "Payment confirmed successfully.";
                header("Location: " . BASE_URL . "admin/rentals.php");
            } else {
                $_SESSION['swal_error'] = "Failed to confirm payment.";
                header("Location: " . BASE_URL . "admin/rentals.php");
            }
            exit;
        }
    }

    /**
     * Admin: Cancel payment
     */
    public function cancelPayment() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            die("Unauthorized");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $payment_id = $_POST['payment_id'] ?? 0;

            if ($this->paymentModel->updateStatus($payment_id, Payment::STATUS_CANCELLED)) {
                $_SESSION['swal_success'] = "Payment cancelled.";
            } else {
                $_SESSION['swal_error'] = "Failed to cancel payment.";
            }
            
            header("Location: " . BASE_URL . "admin/rentals.php");
            exit;
        }
    }

    /**
     * Admin: Refund payment
     */
    public function refundPayment() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            die("Unauthorized");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $payment_id = $_POST['payment_id'] ?? 0;

            if ($this->paymentModel->updateStatus($payment_id, Payment::STATUS_REFUNDED)) {
                $_SESSION['swal_success'] = "Payment refunded successfully.";
            } else {
                $_SESSION['swal_error'] = "Failed to refund payment.";
            }
            
            header("Location: " . BASE_URL . "admin/rentals.php");
            exit;
        }
    }

    /**
     * Get payment statistics
     */
    public function getStatistics() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            return null;
        }

        return $this->paymentModel->getStatistics();
    }
}
