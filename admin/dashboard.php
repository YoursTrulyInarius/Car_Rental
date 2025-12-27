<?php
require_once '../config.php';

// Admin Access Control
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Fetch Metrics
$total_cars = $mysqli->query("SELECT COUNT(*) FROM cars")->fetch_row()[0];
$pending_rentals = $mysqli->query("SELECT COUNT(*) FROM rentals WHERE status = 'pending'")->fetch_row()[0];
$active_rentals = $mysqli->query("SELECT COUNT(*) FROM rentals WHERE status = 'approved'")->fetch_row()[0];
$total_revenue = $mysqli->query("SELECT SUM(total_price) FROM rentals WHERE status = 'approved'")->fetch_row()[0] ?? 0;

// Fetch Recent Rentals
$recent_rentals = $mysqli->query("SELECT r.*, u.name as user_name, c.model, c.year, c.image 
                                  FROM rentals r 
                                  JOIN users u ON r.user_id = u.id 
                                  JOIN cars c ON r.car_id = c.id 
                                  ORDER BY r.created_at DESC LIMIT 5");
?>

<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-muted mb-0">Overview of your rental business performance.</p>
        </div>
        <a href="cars.php" class="btn btn-primary d-flex align-items-center shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Add New Car
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <!-- Total Cars -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 border-start border-4 border-primary align-items-center">
                <div class="card-body p-4 w-100 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1 tracking-wide">Total Cars</h6>
                        <h2 class="mb-0 fw-bolder text-dark display-6"><?php echo $total_cars; ?></h2>
                    </div>
                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-car-front-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pending Requests -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 border-start border-4 border-warning align-items-center">
                <div class="card-body p-4 w-100 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1 tracking-wide">Pending</h6>
                        <h2 class="mb-0 fw-bolder text-dark display-6"><?php echo $pending_rentals; ?></h2>
                    </div>
                    <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Rentals -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 border-start border-4 border-success align-items-center">
                <div class="card-body p-4 w-100 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1 tracking-wide">Active</h6>
                        <h2 class="mb-0 fw-bolder text-dark display-6"><?php echo $active_rentals; ?></h2>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 border-start border-4 border-info align-items-center">
                <div class="card-body p-4 w-100 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1 tracking-wide">Revenue</h6>
                        <h2 class="mb-0 fw-bolder text-dark display-6">₱<?php echo number_format($total_revenue); ?></h2>
                    </div>
                    <div class="icon-box bg-info bg-opacity-10 text-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom border-light p-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Recent Rentals</h5>
            <a href="rentals.php" class="btn btn-sm btn-light text-muted">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase">
                        <th class="ps-4">Car</th>
                        <th>Customer</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Requested</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if($recent_rentals->num_rows > 0): ?>
                        <?php while($rental = $recent_rentals->fetch_assoc()): ?>
                            <?php
                                    $time_ago = strtotime($rental['created_at']);
                                    $current_time = time();
                                    $time_difference = $current_time - $time_ago;
                                    $seconds = $time_difference;
                                    $minutes      = round($seconds / 60);           
                                    $hours        = round($seconds / 3600);         
                                    $days         = round($seconds / 86400);        
                                    $weeks        = round($seconds / 604800);       
                                    $months       = round($seconds / 2629440);      
                                    $years        = round($seconds / 31553280);     

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
                                                <?php $img = $rental['image'] ? '../uploads/'.$rental['image'] : 'https://via.placeholder.com/60'; ?>
                                                <img src="<?php echo $img; ?>" class="rounded-3 me-3 object-fit-cover shadow-sm" width="50" height="50" alt="Car">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($rental['model']); ?></span>
                                                    <small class="text-muted"><?php echo $rental['year']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    <?php echo strtoupper(substr($rental['user_name'], 0, 1)); ?>
                                                </div>
                                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($rental['user_name']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-dark small">
                                                    <?php echo date('M d', strtotime($rental['start_date'])); ?> - <?php echo date('M d', strtotime($rental['end_date'])); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-primary">₱<?php echo number_format($rental['total_price'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $badge = 'secondary';
                                            if($rental['status'] == 'approved') $badge = 'success';
                                            if($rental['status'] == 'rejected') $badge = 'danger';
                                            if($rental['status'] == 'pending') $badge = 'warning';
                                            ?>
                                            <span class="badge bg-<?php echo $badge; ?> bg-opacity-10 text-<?php echo $badge; ?> px-2 py-1 rounded-pill" style="font-size: 0.75rem;"><?php echo ucfirst($rental['status']); ?></span>
                                        </td>
                                        <td class="text-end pe-4 text-muted small fw-bold">
                                            <?php echo $time_string; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No recent rentals found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// Helper function for "Time ago"
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

require_once '../includes/footer.php'; 
?>
