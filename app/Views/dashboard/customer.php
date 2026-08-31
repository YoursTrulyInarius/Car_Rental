<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-primary text-white py-5 mt-5">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill mb-3 fw-semibold">Premium Fleet</span>
                <h1 class="display-5 fw-bold mb-3">Available Cars</h1>
                <p class="lead text-white-50 mb-0">
                    Browse our verified vehicles and choose the right ride for your next trip.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="bg-white rounded-4 p-3 shadow-sm">
                    <form method="GET" action="<?php echo BASE_URL; ?>dashboard.php" class="d-flex flex-column gap-3">
                        <div>
                            <label for="carTypeFilter" class="form-label small text-uppercase text-muted fw-bold mb-2">Filter by type</label>
                            <select id="carTypeFilter" name="type" class="form-select rounded-pill border-0 shadow-sm" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <?php foreach (['sedan','suv','truck','van','sports','luxury','other'] as $typeOption): ?>
                                    <option value="<?php echo htmlspecialchars($typeOption); ?>" <?php echo ($selected_type === $typeOption) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($typeOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (!empty($selected_type)): ?>
                            <div class="d-flex justify-content-end">
                                <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill">Clear</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0">Fleet Overview</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                <?php echo ($cars_result && $cars_result->num_rows > 0) ? $cars_result->num_rows : 0; ?> vehicles
            </span>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php if($cars_result && $cars_result->num_rows > 0): ?>
                <?php while($row = $cars_result->fetch_assoc()): ?>
                <?php
                    $car_id = $row['id'];
                    $quantity = $row['quantity'];
                    $today = date('Y-m-d');
                    
                    $active_count = $carModel->getActiveBookingsCount($car_id, $today);
                    
                    $available_qty = $quantity - $active_count;
                    if($available_qty < 0) $available_qty = 0;
                    
                    $is_available = ($available_qty > 0 && ($row['status'] ?? 'available') === 'available');
                    $btn_class = $is_available ? 'btn-primary' : 'btn-secondary disabled';
                    $btn_text = $is_available ? 'Rent Now' : 'Fully Rented';
                    
                    $badge_class = 'bg-success';
                    $badge_text = "$available_qty Available";

                    if(!$is_available){
                        $badge_class = 'bg-danger';
                        $badge_text = "Fully Rented";

                        $next_date_str = $carModel->getNextAvailableDate($car_id, $today);
                        if($next_date_str){
                            $badge_text = "Available on $next_date_str";
                            $badge_class = 'bg-warning text-dark';
                            $btn_text = "Return $next_date_str";
                        }
                    }
                ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-lg rounded-4 overflow-hidden card-hover-effect">
                        <?php 
                        $car_imgs = getCarImagesList($row['image']); 
                        $card_carousel_id = "cardCarousel_" . $row['id'];
                        ?>
                        <div class="position-relative">
                            <?php if(!empty($car_imgs)): ?>
                                <div id="<?php echo $card_carousel_id; ?>" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
                                    <div class="carousel-inner">
                                        <?php foreach($car_imgs as $idx => $img): ?>
                                            <div class="carousel-item <?php echo ($idx === 0) ? 'active' : ''; ?>">
                                                <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($img); ?>" class="d-block w-100" style="height: 250px; object-fit: cover;" alt="<?php echo htmlspecialchars($row['model']); ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x250?text=Premium+Fleet" class="card-img-top" style="height: 250px; object-fit: cover;" alt="Car">
                            <?php endif; ?>

                            <div class="position-absolute top-0 end-0 m-3 z-3">
                                <span class="badge bg-white text-dark shadow-sm px-3 py-2 rounded-pill fw-bold mb-1 d-block"><?php echo $row['year']; ?></span>
                                <span class="badge <?php echo $badge_class; ?> text-white shadow-sm px-3 py-2 rounded-pill fw-bold d-block"><?php echo $badge_text; ?></span>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h5 class="card-title fw-bold mb-0 text-dark"><?php echo htmlspecialchars($row['model']); ?></h5>
                                <?php if(!empty($row['type'])): ?>
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1 text-capitalize small"><?php echo htmlspecialchars($row['type']); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="card-text text-muted small flex-grow-1 lh-sm mb-2">
                                <?php echo substr(htmlspecialchars($row['description']), 0, 90) . '...'; ?>
                            </p>
                            <?php if(!empty($row['owner_name'])): ?>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-person-badge me-1"></i><?php echo htmlspecialchars($row['owner_name']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="mt-auto pt-3 border-top border-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Daily Rate</small>
                                    <h5 class="text-primary mb-0 fw-bold">₱<?php echo number_format($row['price_per_day'], 2); ?></h5>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo BASE_URL; ?>car_details.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary rounded-pill shadow-sm fw-bold py-2">
                                        View Details
                                    </a>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <a href="<?php echo BASE_URL; ?>car_details.php?id=<?php echo $row['id']; ?>" class="btn <?php echo $btn_class; ?> rounded-pill shadow-sm fw-bold py-2"><?php echo $btn_text; ?></a>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary rounded-pill shadow-sm fw-bold py-2">Login to Rent</a>
                                    <?php endif; ?>
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
                                <?php $modal_carousel_id = "modalCarousel_" . $row['id']; ?>
                                <?php if(!empty($car_imgs)): ?>
                                    <div id="<?php echo $modal_carousel_id; ?>" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
                                        <?php if(count($car_imgs) > 1): ?>
                                            <div class="carousel-indicators">
                                                <?php foreach($car_imgs as $idx => $img): ?>
                                                    <button type="button" data-bs-target="#<?php echo $modal_carousel_id; ?>" data-bs-slide-to="<?php echo $idx; ?>" class="<?php echo ($idx === 0) ? 'active' : ''; ?>"></button>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="carousel-inner">
                                            <?php foreach($car_imgs as $idx => $img): ?>
                                                <div class="carousel-item <?php echo ($idx === 0) ? 'active' : ''; ?>">
                                                    <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($img); ?>" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="<?php echo htmlspecialchars($row['model']); ?>">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if(count($car_imgs) > 1): ?>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $modal_carousel_id; ?>" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $modal_carousel_id; ?>" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/600x400?text=Premium+Fleet" class="w-100" style="height: 400px; object-fit: cover;" alt="Car">
                                <?php endif; ?>
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
                                <a href="<?php echo BASE_URL; ?>car_details.php?id=<?php echo $row['id']; ?>" class="btn <?php echo $btn_class; ?> rounded-pill px-4 fw-bold shadow-sm"><?php echo $is_available ? 'Proceed to Rent' : 'Unavailable'; ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-car-front display-1 text-muted mb-3"></i>
                <h3>No cars available right now.</h3>
                <p class="text-muted">Please check back soon for new arrivals.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
