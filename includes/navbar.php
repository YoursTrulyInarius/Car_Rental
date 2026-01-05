<?php
// Determine active page for highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <span class="navbar-brand mb-0 h1" style="cursor: default;">
            <i class="bi bi-car-front-fill me-2"></i>VEGA'S CAR RENTAL
        </span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                
                <!-- Admin Links -->
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'cars.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/cars.php">Fleet Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'rentals.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/rentals.php" id="rentals-link">Rentals</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-danger btn-sm" href="<?php echo BASE_URL; ?>logout.php">Logout (Admin)</a>
                    </li>

                <!-- User Links -->
                <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] == 'user'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>dashboard.php">Browse Cars</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'my_rentals.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>my_rentals.php">My Rentals</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <span class="navbar-text me-3">Hi, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                        <a class="btn btn-outline-primary btn-sm" href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                    </li>

                <!-- Guest Links -->
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-primary me-2" href="<?php echo BASE_URL; ?>login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
