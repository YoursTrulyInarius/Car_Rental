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

// Fetch Owners (Admins are now the owners)
$owners = $mysqli->query("SELECT id, name, email FROM users WHERE role = 'admin'");

// Handle Add New Admin (Owner)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = 'admin';

    $stmt = $mysqli->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    
    if($stmt->execute()){
        header("Location: dashboard.php?success=1");
        exit;
    } else {
        $error_msg = "Error: " . $mysqli->error;
    }
}
?>

<div class="container py-5">
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>New owner (admin) account created successfully!
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-muted mb-0">Overview of your rental business performance.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary d-flex align-items-center rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#ownerModal">
                <i class="bi bi-person-plus me-2"></i>Add New Owner
            </button>
        </div>
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

    <!-- Owners Grid -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Car Owners</h4>
        </div>
        <div class="row g-4">
            <?php if($owners->num_rows > 0): ?>
                <?php while($owner = $owners->fetch_assoc()): ?>
                    <?php 
                        $owner_id = $owner['id'];
                        $car_count = $mysqli->query("SELECT SUM(quantity) FROM cars WHERE owner_id = $owner_id")->fetch_row()[0] ?? 0;
                    ?>
                    <div class="col-md-3">
                        <a href="owner_dashboard.php?id=<?php echo $owner_id; ?>" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift transition-all">
                                <div class="card-body p-4 text-center">
                                    <div class="avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                                        <i class="bi bi-person-badge fs-2"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($owner['name']); ?></h5>
                                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($owner['email']); ?></p>
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                            <i class="bi bi-car-front-fill me-1 text-primary"></i>
                                            <?php echo $car_count; ?> Cars
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-light border-0 shadow-sm rounded-4 p-4 text-center">
                        <i class="bi bi-people-fill display-4 text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-0">No car owners registered yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- Add Owner Modal -->
<div class="modal fade" id="ownerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Add New Car Owner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="add_admin" value="1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Initial Password</label>
                        <input type="password" name="password" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Owner</button>
                </div>
            </form>
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
