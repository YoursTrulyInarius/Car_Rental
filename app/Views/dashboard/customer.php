<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row mb-5 text-center">
        <div class="col-lg-8 mx-auto">
            <h1 class="display-5 fw-bold text-primary"><?php echo $owner_name ? "Available Fleet from " . htmlspecialchars($owner_name) : "Your Premium Dashboard"; ?></h1>
            <p class="lead text-muted"><?php echo $owner_name ? "Browse cars managed by this owner." : "Browse our exclusive collection of car owners."; ?></p>
            <?php if($owner_id): ?>
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-outline-secondary rounded-pill mt-3 shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Back to Owners
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php if(!$owner_id): ?>
            <!-- Owners Grid -->
            <?php if($owners && $owners->num_rows > 0): ?>
                <?php while($owner = $owners->fetch_assoc()): ?>
                    <?php 
                        $oid = $owner['id'];
                        $car_count = $carModel->getAvailableStockCountByOwner($oid);
                    ?>
                    <div class="col">
                        <a href="<?php echo BASE_URL; ?>dashboard.php?owner_id=<?php echo $oid; ?>" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-lg rounded-4 overflow-hidden card-hover-effect">
                                <div class="card-body p-4 text-center">
                                    <div class="avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                                        <i class="bi bi-person-workspace fs-1"></i>
                                    </div>
                                    <h3 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($owner['name']); ?></h3>
                                    <p class="text-muted mb-4"><?php echo htmlspecialchars($owner['email']); ?></p>
                                    <div class="d-inline-block bg-light rounded-pill px-4 py-2 border">
                                        <span class="fw-bold text-primary"><?php echo $car_count; ?></span>
                                        <span class="text-muted small">Available Cars</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-primary border-0 text-center py-3">
                                    <span class="text-white fw-bold">View Collection <i class="bi bi-chevron-right ms-2"></i></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-people display-1 text-muted mb-3"></i>
                    <h3>No car owners found.</h3>
                    <p class="text-muted">Check back later for new fleet additions.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Cars Grid -->
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
                                    <a href="<?php echo BASE_URL; ?>car_details.php?id=<?php echo $row['id']; ?>" class="btn <?php echo $btn_class; ?> rounded-pill shadow-sm fw-bold py-2"><?php echo $btn_text; ?></a>
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
                <h3>No cars available for this owner.</h3>
                <p class="text-muted">Please check other owners.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
