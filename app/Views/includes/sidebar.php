<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <span class="admin-brand-mark">CR</span>
        <div>
            <strong>CAR RENTAL</strong>
            <small class="text-white-50">Admin Panel</small>
        </div>
    </div>

    <nav class="admin-nav">
        <a class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php">
            <i class="bi bi-speedometer2"></i>
            <span>Overview</span>
        </a>
        <a class="<?php echo ($current_page == 'cars.php' || $current_page == 'owner_dashboard.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/cars.php">
            <i class="bi bi-car-front"></i>
            <span>Fleet Management</span>
        </a>
        <a class="<?php echo ($current_page == 'rentals.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/rentals.php" id="rentals-link">
            <i class="bi bi-calendar3"></i>
            <span>Rentals</span>
        </a>
    </nav>

    <div class="admin-sidebar-footer mt-auto pt-3 border-top border-secondary-subtle">
        <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline-danger w-100 rounded-pill d-flex align-items-center justify-content-center gap-2 py-2 fw-bold">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
