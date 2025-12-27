<?php
require_once 'config.php';

// Public Landing Page - No Access Control needed for guests
// But if logged in, maybe show "Go to Dashboard" button instead of "Login/Register"

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="position-relative overflow-hidden p-3 p-md-5 m-md-3 text-center bg-light rounded-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); color: white;">
    <div class="col-md-8 p-lg-5 mx-auto my-5">
        <h1 class="display-3 fw-bold mb-4">Drive Your Dream Today</h1>
        <p class="lead fw-normal mb-5 text-white-50">Experience the ultimate freedom with our premium fleet of vehicles. Affordable rates, luxury choices, and seamless booking.</p>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <h3 class="fw-light mb-4">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
            <?php if($_SESSION['role'] == 'admin'): ?>
                <a class="btn btn-light btn-lg px-5 fw-bold shadow-sm hover-float" href="admin/dashboard.php">Go to Admin Dashboard</a>
            <?php else: ?>
                <a class="btn btn-light btn-lg px-5 fw-bold shadow-sm hover-float" href="dashboard.php">Go to Dashboard</a>
            <?php endif; ?>
        <?php else: ?>
            <div class="d-flex gap-3 justify-content-center lead fw-normal">
                <a class="btn btn-light btn-lg px-4 fw-bold shadow-sm hover-float" href="register.php">Get Started</a>
                <a class="btn btn-outline-light btn-lg px-4 fw-bold hover-float" href="login.php">Sign In</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="product-device shadow-sm d-none d-md-block"></div>
    <div class="product-device product-device-2 shadow-sm d-none d-md-block"></div>
</div>

<div class="container py-5">
    <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
        <div class="col d-flex align-items-start">
            <div class="icon-square bg-primary bg-opacity-10 text-primary flex-shrink-0 me-3 rounded-circle p-3">
                <i class="bi bi-shield-check fs-4"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-2">Secure Booking</h4>
                <p>Paragraph of text beneath the heading to explain the heading.</p>
            </div>
        </div>
        <div class="col d-flex align-items-start">
            <div class="icon-square bg-primary bg-opacity-10 text-primary flex-shrink-0 me-3 rounded-circle p-3">
                <i class="bi bi-currency-dollar fs-4"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-2">Best Prices</h4>
                <p>Paragraph of text beneath the heading to explain the heading.</p>
            </div>
        </div>
        <div class="col d-flex align-items-start">
            <div class="icon-square bg-primary bg-opacity-10 text-primary flex-shrink-0 me-3 rounded-circle p-3">
                <i class="bi bi-emoji-smile fs-4"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-2">24/7 Support</h4>
                <p>Paragraph of text beneath the heading to explain the heading.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
