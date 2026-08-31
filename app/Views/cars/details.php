<?php
global $mysqli;
$mysqli = $mysqli ?? ($GLOBALS['mysqli'] ?? null);

if (!$mysqli) {
    http_response_code(500);
    echo '<div class="alert alert-danger mb-0 rounded-3">Database connection is unavailable.</div>';
    exit;
}

if (!empty($_GET['modal']) && $_GET['modal'] === '1') {
    $car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $carModel = new \App\Models\Car($mysqli);
    $car = $carModel->getById($car_id);

    if (!$car) {
        http_response_code(404);
        echo '<div class="alert alert-danger mb-0 rounded-3">Vehicle not found.</div>';
        exit;
    }

    $car_imgs = getCarImagesList($car['image']);
    $car_id = (int)$car['id'];
    $total_units = (int)($car['quantity'] ?? 1);
    $today = date('Y-m-d');
    $active_count = $carModel->getActiveBookingsCount($car_id, $today);
    $avail_stock = max(0, $total_units - $active_count);
    $is_fully_rented = ($avail_stock <= 0 || ($car['status'] ?? 'available') === 'rented');

    echo '<div class="modal-content border-0 rounded-4">';
    echo '<div class="modal-header border-0 pb-2">';
    echo '<h5 class="modal-title fw-bold">' . htmlspecialchars($car['model']) . '</h5>';
    echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
    echo '</div>';
    echo '<div class="modal-body p-0">';
    echo '<div class="overflow-hidden rounded-4">';
    if (!empty($car_imgs)) {
        echo '<img src="' . BASE_URL . 'uploads/' . htmlspecialchars($car_imgs[0]) . '" class="img-fluid w-100" style="height: 220px; object-fit: cover;" alt="' . htmlspecialchars($car['model']) . '">';
    } else {
        echo '<img src="https://via.placeholder.com/800x420?text=Premium+Fleet" class="img-fluid w-100" style="height: 220px; object-fit: cover;" alt="Car">';
    }
    echo '</div>';
    echo '<div class="p-4">';
    echo '<div class="d-flex align-items-center gap-2 flex-wrap mb-3">';
    echo '<span class="badge bg-primary px-3 py-2">' . htmlspecialchars($car['year']) . ' Model</span>';
    if (!empty($car['type'])) {
        echo '<span class="badge bg-light text-dark border px-3 py-2 text-capitalize">' . htmlspecialchars(ucfirst($car['type'])) . '</span>';
    }
    if ($is_fully_rented) {
        echo '<span class="badge bg-danger px-3 py-2">Fully Rented</span>';
    } else {
        echo '<span class="badge bg-success px-3 py-2">' . $avail_stock . ' Available</span>';
    }
    echo '</div>';
    echo '<div class="d-flex align-items-end mb-3">';
    echo '<h3 class="text-primary mb-0 fw-bold me-2">₱' . number_format((float)($car['price_per_day'] ?? 0), 2) . '</h3>';
    echo '<span class="text-muted mb-1">/ day</span>';
    echo '</div>';
    echo '<p class="text-muted mb-3">' . nl2br(htmlspecialchars($car['description'])) . '</p>';
    echo '<div class="row g-2 text-sm text-muted">';
    echo '<div class="col-6"><div class="bg-light rounded-3 p-2"><strong class="d-block text-dark">Brand</strong>' . htmlspecialchars($car['brand'] ?? 'N/A') . '</div></div>';
    echo '<div class="col-6"><div class="bg-light rounded-3 p-2"><strong class="d-block text-dark">Owner</strong>' . htmlspecialchars($car['owner_name'] ?? 'System Administrator') . '</div></div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    exit;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <a href="<?php echo BASE_URL; ?>dashboard.php" class="text-decoration-none text-muted mb-4 d-inline-block"><i class="bi bi-arrow-left me-2"></i>Back to Fleet</a>
    
    <div class="row g-5">
        <div class="col-lg-7">
            <?php 
            $car_imgs = getCarImagesList($car['image']);
            ?>
            <?php if (!empty($car_imgs)): ?>
                <div id="detailsCarCarousel" class="carousel slide shadow rounded-4 overflow-hidden mb-3" data-bs-ride="carousel" data-bs-interval="2500">
                    <?php if(count($car_imgs) > 1): ?>
                        <div class="carousel-indicators">
                            <?php foreach($car_imgs as $idx => $img): ?>
                                <button type="button" data-bs-target="#detailsCarCarousel" data-bs-slide-to="<?php echo $idx; ?>" class="<?php echo ($idx === 0) ? 'active' : ''; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="carousel-inner">
                        <?php foreach($car_imgs as $idx => $img): ?>
                            <div class="carousel-item <?php echo ($idx === 0) ? 'active' : ''; ?>">
                                <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($img); ?>" class="d-block w-100" style="height: 420px; object-fit: cover;" alt="<?php echo htmlspecialchars($car['model']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if(count($car_imgs) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#detailsCarCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#detailsCarCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <img src="https://via.placeholder.com/800x420?text=Premium+Fleet" class="w-100 rounded-4 shadow" style="height: 420px; object-fit: cover;" alt="Car">
            <?php endif; ?>
            
            <?php 
                $car_id = (int)$car['id'];
                $total_units = (int)($car['quantity'] ?? 1);
                $today = date('Y-m-d');
                $carModelObj = new \App\Models\Car($mysqli);
                $active_count = $carModelObj->getActiveBookingsCount($car_id, $today);
                $avail_stock = max(0, $total_units - $active_count);
                $is_fully_rented = ($avail_stock <= 0 || ($car['status'] ?? 'available') === 'rented');
            ?>

            <div class="mt-4">
                <h2 class="fw-bold"><?php echo htmlspecialchars($car['model']); ?></h2>
                <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-primary px-3 py-2"><?php echo $car['year']; ?> Model</span>
                    <?php if(!empty($car['type'])): ?>
                        <span class="badge bg-light text-dark border px-3 py-2 text-capitalize">
                            <i class="bi bi-gear-wide-connected me-1 text-primary"></i>
                            <?php echo htmlspecialchars(ucfirst($car['type'])); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($is_fully_rented): ?>
                        <span class="badge bg-danger px-3 py-2">
                            <i class="bi bi-x-circle-fill me-1"></i>Fully Rented
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i><?php echo $avail_stock; ?> Unit(s) Available
                        </span>
                    <?php endif; ?>
                </div>
                <p class="mt-3 text-muted lead"><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow border-0 p-4 h-100 bg-white rounded-4">
                <div class="border-bottom pb-3 mb-4">
                    <h3 class="fw-bold mb-1">Rental Confirmation</h3>
                    <p class="text-muted small mb-0">Complete the details below to secure your ride.</p>
                </div>

                <?php if ($is_fully_rented): ?>
                    <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <strong>Fully Rented:</strong> All <?php echo $total_units; ?> unit(s) of this vehicle are currently rented out. Please check back later.
                    </div>
                <?php endif; ?>

                <div class="d-flex align-items-end mb-4">
                    <h2 class="text-primary mb-0 fw-bold display-5 me-2">₱<?php echo number_format($car['price_per_day'], 2); ?></h2>
                    <span class="text-muted mb-2">/ day</span>
                </div>

                <form action="<?php echo BASE_URL; ?>rent_process.php" method="POST" id="rentalForm">
                    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                    <input type="hidden" name="price" id="pricePerDay" value="<?php echo $car['price_per_day']; ?>">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label text-uppercase small text-muted fw-bold">Pick-up Date</label>
                            <input type="date" name="start_date" id="startDate" class="form-control form-control-lg bg-light border-0" required min="<?php echo date('Y-m-d'); ?>" <?php echo $is_fully_rented ? 'disabled' : ''; ?>>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-uppercase small text-muted fw-bold">Return Date</label>
                            <input type="date" name="end_date" id="endDate" class="form-control form-control-lg bg-light border-0" required min="<?php echo date('Y-m-d'); ?>" <?php echo $is_fully_rented ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                    <!-- Computed Total Display -->
                    <div class="d-flex justify-content-between align-items-center mb-4 p-4 bg-primary bg-opacity-10 rounded-3 text-primary" id="totalBox" style="display:none !important;">
                        <div>
                            <span class="d-block small text-uppercase fw-bold opacity-75">Total Estimate</span>
                            <span class="fs-2 fw-bold" id="totalPrice">₱0.00</span>
                        </div>
                        <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="mb-4">
                        <div class="p-3 border border-2 rounded-3 bg-white shadow-sm">
                            <div class="form-check mb-0">
                                <input class="form-check-input border-secondary" type="checkbox" value="" id="termsCheck" required style="transform: scale(1.1); cursor: pointer;" <?php echo $is_fully_rented ? 'disabled' : ''; ?>>
                                <label class="form-check-label small text-dark lh-sm ms-2" for="termsCheck" style="cursor: pointer;">
                                    I verify that I possess a valid driver's license and agree to the 
                                    <a href="#" class="text-primary text-decoration-none fw-bold">Terms & Conditions</a> 
                                    and <a href="#" class="text-primary text-decoration-none fw-bold">Rental Agreement</a>.
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn <?php echo $is_fully_rented ? 'btn-secondary disabled' : 'btn-primary'; ?> btn-lg w-100 py-3 fw-bold shadow-hover hover-float" <?php echo $is_fully_rented ? 'disabled' : ''; ?>>
                        <?php echo $is_fully_rented ? 'Fully Rented — Unavailable' : 'Confirm Rental Request'; ?>
                    </button>
                    
                    <p class="text-center text-muted small mt-3 mb-0">
                        <i class="bi bi-shield-lock-fill me-1 text-success"></i> 
                        Zero payment now. Pay upon pickup.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const startDate = document.getElementById('startDate');
const endDate = document.getElementById('endDate');
const pricePerDay = parseFloat(document.getElementById('pricePerDay').value);
const totalBox = document.getElementById('totalBox');
const totalPrice = document.getElementById('totalPrice');

function calculateTotal() {
    if(startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const diffTime = end - start; 
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
        
        if (diffDays > 0) { 
            const total = diffDays * pricePerDay;
            totalPrice.innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            totalBox.style.display = 'flex';
            totalBox.style.setProperty('display', 'flex', 'important');
        } else if (diffDays === 0) {
             const total = 1 * pricePerDay;
             totalPrice.innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
             totalBox.style.display = 'flex';
             totalBox.style.setProperty('display', 'flex', 'important');
        } else {
            totalBox.style.display = 'none';
            totalBox.style.setProperty('display', 'none', 'important');
        }
    }
}

startDate.addEventListener('change', calculateTotal);
endDate.addEventListener('change', calculateTotal);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
