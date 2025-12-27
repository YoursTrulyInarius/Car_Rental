<?php
require_once 'config.php';

// Redirection as requested: If not logged in, go to login page
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Check if Admin tries to access user page, optionally redirect to admin dashboard
if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'){
    header("Location: admin/dashboard.php");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Fetch available cars
$sql = "SELECT * FROM cars WHERE status = 'available' ORDER BY created_at DESC";
$result = $mysqli->query($sql);
?>

<div class="container py-5">
    <div class="row mb-5 text-center">
        <div class="col-lg-8 mx-auto">
            <h1 class="display-5 fw-bold text-primary">Your Premium Dashboard</h1>
            <p class="lead text-muted">Browse our exclusive collection available for rent.</p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php
                    // Check Availability Logic
                    $car_id = $row['id'];
                    $quantity = $row['quantity'];
                    $today = date('Y-m-d');
                    
                    // Count active bookings
                    $check_sql = "SELECT COUNT(*) FROM rentals WHERE car_id = $car_id AND status IN ('pending', 'approved') AND '$today' BETWEEN start_date AND end_date";
                    $active_count = $mysqli->query($check_sql)->fetch_row()[0];
                    
                    $available_qty = $quantity - $active_count;
                    if($available_qty < 0) $available_qty = 0;
                    
                    $is_available = ($available_qty > 0);
                    $btn_class = $is_available ? 'btn-primary' : 'btn-secondary disabled';
                    $btn_text = $is_available ? 'Rent Now' : 'Rent Now'; // Keep button text or change? Usually disable.
                    
                    $badge_class = 'bg-success';
                    $badge_text = "$available_qty Available";

                    if(!$is_available){
                        $btn_text = "Out of Stock";
                        $badge_class = 'bg-danger';
                        $badge_text = "Out of Stock";

                        // Calculate Next Available Date
                        // Find the earliest end_date of ongoing rentals that is >= today
                        $next_sql = "SELECT MIN(end_date) FROM rentals WHERE car_id = $car_id AND status IN ('pending', 'approved') AND end_date >= '$today'";
                        $next_res = $mysqli->query($next_sql);
                        if($next_res && $date_row = $next_res->fetch_row()){
                            if($date_row[0]){
                                $next_date_str = date('M d', strtotime($date_row[0] . ' +1 day'));
                                $badge_text = "Available on $next_date_str";
                                $badge_class = 'bg-warning text-dark';
                                $btn_text = "Return $next_date_str";
                            }
                        }
                    }
                ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-lg rounded-4 overflow-hidden card-hover-effect">
                        <!-- Car Image -->
                        <?php 
                        $img_path = !empty($row['image']) ? 'uploads/' . $row['image'] : 'https://via.placeholder.com/400x250?text=Premium+Fleet'; 
                        ?>
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($img_path); ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="<?php echo htmlspecialchars($row['model']); ?>">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-white text-dark shadow-sm px-3 py-2 rounded-pill fw-bold mb-1 d-block"><?php echo $row['year']; ?></span>
                                <span class="badge <?php echo $badge_class; ?> text-white shadow-sm px-3 py-2 rounded-pill fw-bold d-block"><?php echo $badge_text; ?></span>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold mb-2 text-dark"><?php echo htmlspecialchars($row['model']); ?></h5>
                            <p class="card-text text-muted small flex-grow-1 lh-sm mb-4">
                                <?php echo substr(htmlspecialchars($row['description']), 0, 90) . '...'; ?>
                            </p>
                            
                            <div class="mt-auto pt-3 border-top border-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Daily Rate</small>
                                    <h5 class="text-primary mb-0 fw-bold">₱<?php echo number_format($row['price_per_day'], 2); ?></h5>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary rounded-pill shadow-sm fw-bold py-2" data-bs-toggle="modal" data-bs-target="#carModal<?php echo $row['id']; ?>">
                                        View Details
                                    </button>
                                    <a href="car_details.php?id=<?php echo $row['id']; ?>" class="btn <?php echo $btn_class; ?> rounded-pill shadow-sm fw-bold py-2"><?php echo $btn_text; ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Car Detail Modal -->
                <div class="modal fade" id="carModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header border-bottom-0">
                                <h5 class="modal-title fw-bold">Vehicle Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <img src="<?php echo htmlspecialchars($img_path); ?>" class="w-100" style="height: 400px; object-fit: cover;" alt="<?php echo htmlspecialchars($row['model']); ?>">
                                <div class="p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($row['model']); ?></h2>
                                        <h3 class="text-primary mb-0">₱<?php echo number_format($row['price_per_day'], 2); ?> <span class="fs-6 text-muted fw-normal">/ day</span></h3>
                                    </div>
                                    <div class="mb-4">
                                        <span class="badge bg-light text-dark border me-2"><?php echo $row['year']; ?> Model</span>
                                        <span class="badge <?php echo $badge_class; ?> text-white border border-white"><?php echo $badge_text; ?></span>
                                    </div>
                                    <p class="text-muted lead fs-6"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="row g-3">
                                        <div class="col-4 text-center border-end">
                                            <i class="bi bi-fuel-pump fs-3 text-primary mb-2"></i>
                                            <p class="small text-muted mb-0">Full Tank</p>
                                        </div>
                                        <div class="col-4 text-center border-end">
                                            <i class="bi bi-speedometer2 fs-3 text-primary mb-2"></i>
                                            <p class="small text-muted mb-0">Unlimited Mileage</p>
                                        </div>
                                        <div class="col-4 text-center">
                                            <i class="bi bi-shield-check fs-3 text-primary mb-2"></i>
                                            <p class="small text-muted mb-0">Insured</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 p-4 bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                <a href="car_details.php?id=<?php echo $row['id']; ?>" class="btn <?php echo $btn_class; ?> rounded-pill px-4 fw-bold shadow-sm"><?php echo $is_available ? 'Proceed to Rent' : 'Unavailable'; ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-car-front display-1 text-muted mb-3"></i>
                <h3>No cars available at the moment.</h3>
                <p class="text-muted">Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
