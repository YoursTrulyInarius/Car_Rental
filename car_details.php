<?php
require_once 'config.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $mysqli->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if(!$car){
    header("Location: index.php");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container py-5">
    <a href="dashboard.php" class="text-decoration-none text-muted mb-4 d-inline-block"><i class="bi bi-arrow-left me-2"></i>Back to Fleet</a>
    
    <div class="row g-5">
        <div class="col-lg-7">
            <?php 
            $img_path = !empty($car['image']) ? 'uploads/' . $car['image'] : 'https://via.placeholder.com/800x400?text=Premium+Car'; 
            ?>
            <img src="<?php echo htmlspecialchars($img_path); ?>" class="car-details-img shadow" alt="<?php echo htmlspecialchars($car['model']); ?>">
            
            <div class="mt-4">
                <h2 class="fw-bold"><?php echo htmlspecialchars($car['model']); ?></h2>
                <span class="badge bg-primary px-3 py-2"><?php echo $car['year']; ?> Model</span>
                <p class="mt-3 text-muted lead"><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow border-0 p-4 h-100 bg-white rounded-4">
                <div class="border-bottom pb-3 mb-4">
                    <h3 class="fw-bold mb-1">Rental Confirmation</h3>
                    <p class="text-muted small mb-0">Complete the details below to secure your ride.</p>
                </div>

                <div class="d-flex align-items-end mb-4">
                    <h2 class="text-primary mb-0 fw-bold display-5 me-2">₱<?php echo number_format($car['price_per_day'], 2); ?></h2>
                    <span class="text-muted mb-2">/ day</span>
                </div>

                <form action="rent_process.php" method="POST" id="rentalForm">
                    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                    <input type="hidden" name="price" id="pricePerDay" value="<?php echo $car['price_per_day']; ?>">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label text-uppercase small text-muted fw-bold">Pick-up Date</label>
                            <input type="date" name="start_date" id="startDate" class="form-control form-control-lg bg-light border-0" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-uppercase small text-muted fw-bold">Return Date</label>
                            <input type="date" name="end_date" id="endDate" class="form-control form-control-lg bg-light border-0" required min="<?php echo date('Y-m-d'); ?>">
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
                                <input class="form-check-input border-secondary" type="checkbox" value="" id="termsCheck" required style="transform: scale(1.1); cursor: pointer;">
                                <label class="form-check-label small text-dark lh-sm ms-2" for="termsCheck" style="cursor: pointer;">
                                    I verify that I possess a valid driver's license and agree to the 
                                    <a href="#" class="text-primary text-decoration-none fw-bold">Terms & Conditions</a> 
                                    and <a href="#" class="text-primary text-decoration-none fw-bold">Rental Agreement</a>.
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-hover hover-float">
                        Confirm Rental Request
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

<?php require_once 'includes/footer.php'; ?>
