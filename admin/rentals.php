<?php
require_once '../config.php';
// Include PHPMailer Manually
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Admin Check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

// Helper function to send email
function sendStatusEmail($toEmail, $userName, $carModel, $status, $dates, $totalPrice){
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'limvic2019@gmail.com'; 
        $mail->Password   = 'svetplaptkssyrba';    
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Bypassing SSL for local servers
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('limvic2019@gmail.com', 'JEBWINE Rental');
        $mail->addAddress($toEmail, $userName);

        $mail->isHTML(true);
        $subjectStatus = strtoupper($status);
        $mail->Subject = "Rental Update: Your Request is $subjectStatus";
        
        // Colors
        $color = ($status == 'approved') ? '#198754' : '#dc3545'; // Green or Red
        $statusText = ucfirst($status);

        // HTML Template
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
            <div style='text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee;'>
                <h1 style='color: #0d6efd; margin: 0;'>JEBWINE'S</h1>
                <p style='color: #6c757d; margin: 5px 0 0;'>PREMIUM CAR RENTAL</p>
            </div>
            
            <div style='padding: 30px 0;'>
                <h2 style='color: #333;'>Hello, $userName</h2>
                <p style='font-size: 16px; color: #555;'>Your rental request for the <strong>$carModel</strong> has been <strong style='color: $color;'>$subjectStatus</strong>.</p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; color: #6c757d;'>Vehicle</td>
                            <td style='padding: 8px 0; font-weight: bold; text-align: right;'>$carModel</td>
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
                &copy; " . date('Y') . " JEBWINE'S Car Rental. All rights reserved.
            </div>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle Status Change
if(isset($_POST['action']) && isset($_POST['rental_id'])){
    $rental_id = $_POST['rental_id'];
    $new_status = $_POST['action']; // approved OR rejected

    // Get rental details for email
    $query = "SELECT r.*, u.email, u.name as user_name, c.model as car_model 
              FROM rentals r 
              JOIN users u ON r.user_id = u.id 
              JOIN cars c ON r.car_id = c.id 
              WHERE r.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $rental_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rental = $result->fetch_assoc();

    if($rental){
        // Update DB
        $update = $mysqli->prepare("UPDATE rentals SET status = ? WHERE id = ?");
        $update->bind_param("si", $new_status, $rental_id);
        
        if($update->execute()){
            // Format Data
            $dates = date('M d', strtotime($rental['start_date'])) . ' - ' . date('M d', strtotime($rental['end_date']));
            $totalPrice = '₱' . number_format($rental['total_price'], 2);

            // Send Email
            sendStatusEmail($rental['email'], $rental['user_name'], $rental['car_model'], $new_status, $dates, $totalPrice);

            $_SESSION['swal_success'] = "Rental request has been " . $new_status . " successfully.";
        }
    }
    header("Location: rentals.php");
    exit;
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Fetch Rentals
$sql = "SELECT r.*, u.name as user_name, c.model as car_model 
        FROM rentals r 
        JOIN users u ON r.user_id = u.id 
        JOIN cars c ON r.car_id = c.id 
        ORDER BY r.created_at DESC";
$rentals = $mysqli->query($sql);
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Rental Requests</h2>
            <p class="text-muted mb-0">Manage incoming booking requests from customers.</p>
        </div>
    </div>

    <div class="card shadow border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase text-muted small fw-bold tracking-wide">Customer</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Vehicle</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Duration</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Total</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Requested</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Status</th>
                            <th class="text-end pe-4 py-3 text-uppercase text-muted small fw-bold tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $rentals->fetch_assoc()): ?>
                            <?php
                            // Calculate Time Ago
                            $time_ago = strtotime($row['created_at']);
                            $current_time = time();
                            $time_difference = $current_time - $time_ago;
                            $seconds = $time_difference;
                            $minutes      = round($seconds / 60);           // value 60 is seconds  
                            $hours        = round($seconds / 3600);         // value 3600 is 60 minutes * 60 sec  
                            $days         = round($seconds / 86400);        // value 86400 is 24 hours * 60 minutes * 60 sec
                            $weeks        = round($seconds / 604800);       // value 604800 is 7 days * 24 hours * 60 minutes * 60 sec
                            $months       = round($seconds / 2629440);      // value 2629440 is ((365+365+365+365+366)/5/12) days * 24 hours * 60 minutes * 60 sec
                            $years        = round($seconds / 31553280);     // value 31553280 is (365+365+365+365+366)/5 days * 24 hours * 60 minutes * 60 sec

                            if($seconds <= 60) {
                                $time_string = "Just now";
                            } else if($minutes <=60) {
                                $time_string = ($minutes==1) ? "one minute ago" : "$minutes mins ago";
                            } else if($hours <=24) {
                                $time_string = ($hours==1) ? "an hour ago" : "$hours hours ago";
                            } else if($days <= 7) {
                                $time_string = ($days==1) ? "yesterday" : "$days days ago";
                            } else if($weeks <= 4.3) {
                                $time_string = ($weeks==1) ? "a week ago" : "$weeks weeks ago";
                            } else if($months <=12) {
                                $time_string = ($months==1) ? "a month ago" : "$months months ago";
                            } else {
                                $time_string = ($years==1) ? "one year ago" : "$years years ago";
                            }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-3 fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($row['user_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($row['user_name']); ?></h6>
                                            <small class="text-muted">Customer</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['car_model']); ?></span>
                                </td>
                                <td>
                                    <span class="d-block text-dark small fw-bold">
                                        <?php echo date('M d, Y', strtotime($row['start_date'])); ?> 
                                        <i class="bi bi-arrow-right mx-1 text-muted"></i> 
                                        <?php echo date('M d, Y', strtotime($row['end_date'])); ?>
                                    </span>
                                    <small class="text-muted"><?php 
                                        $diff = strtotime($row['end_date']) - strtotime($row['start_date']);
                                        echo ceil($diff / (60 * 60 * 24)) . ' Days'; 
                                    ?></small>
                                </td>
                                <td class="fw-bold text-primary">₱<?php echo number_format($row['total_price'], 2); ?></td>
                                <td class="text-muted small"><?php echo $time_string; ?></td>
                                <td>
                                    <?php 
                                    $statusClass = 'bg-secondary text-secondary';
                                    $statusIcon = 'bi-circle';
                                    
                                    if($row['status'] == 'approved') {
                                        $statusClass = 'bg-success text-success';
                                        $statusIcon = 'bi-check-circle-fill';
                                    }
                                    if($row['status'] == 'rejected') {
                                        $statusClass = 'bg-danger text-danger';
                                        $statusIcon = 'bi-x-circle-fill';
                                    }
                                    if($row['status'] == 'pending') {
                                        $statusClass = 'bg-warning text-warning';
                                        $statusIcon = 'bi-clock-fill';
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?> bg-opacity-10 py-2 px-3 rounded-pill">
                                        <i class="bi <?php echo $statusIcon; ?> me-1"></i>
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if($row['status'] == 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="rental_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="approved" class="btn btn-sm btn-success rounded-pill px-3 fw-bold me-1 hover-float shadow-sm">
                                                Approve
                                            </button>
                                            <button type="submit" name="action" value="rejected" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold hover-float shadow-sm">
                                                Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light border rounded-pill px-3 disabled text-muted">
                                            Completed
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
