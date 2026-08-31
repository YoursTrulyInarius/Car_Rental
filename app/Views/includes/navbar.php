<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center me-4" href="<?php echo BASE_URL; ?>index.php">
            <i class="bi bi-car-front-fill me-2 text-primary"></i>CAR RENTAL
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-1">

                <!-- Admin Links -->
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'owner'): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold <?php echo ($current_page == 'dashboard.php') ? 'active text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold <?php echo ($current_page == 'cars.php') ? 'active text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>admin/cars.php">Fleet Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold <?php echo ($current_page == 'rentals.php') ? 'active text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>admin/rentals.php" id="rentals-link">Rentals</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="<?php echo BASE_URL; ?>logout.php">Logout (Admin)</a>
                    </li>

                <!-- Customer Links -->
                <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] == 'customer'): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold <?php echo ($current_page == 'dashboard.php') ? 'active text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>dashboard.php">Browse Cars</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold <?php echo ($current_page == 'my_rentals.php') ? 'active text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>my_rentals.php">My Rentals</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <span class="navbar-text me-3 fw-semibold">Hi, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Customer'); ?></span>
                        <a class="btn btn-outline-primary btn-sm rounded-pill px-3" href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                    </li>

                <!-- Guest Links -->
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold nav-home-link" href="<?php echo BASE_URL; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold nav-section-link" href="<?php echo BASE_URL; ?>index.php#available-cars">Available Cars</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold nav-section-link" href="<?php echo BASE_URL; ?>index.php#features">Why Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold nav-section-link" href="<?php echo BASE_URL; ?>index.php#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold nav-section-link" href="<?php echo BASE_URL; ?>index.php#reviews">Reviews</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" href="<?php echo BASE_URL; ?>login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
    (function () {
        function updateGuestNavState() {
            var links = document.querySelectorAll('.nav-section-link, .nav-home-link');
            var hash = window.location.hash || '';
            var pathname = window.location.pathname;
            var isHome = pathname.endsWith('/index.php') || pathname.endsWith('/');

            links.forEach(function (link) {
                link.classList.remove('active', 'text-primary');
            });

            if (!isHome && !hash) {
                return;
            }

            var target = hash ? hash.replace('#', '') : 'home';
            var matchedLink = null;

            if (!hash) {
                matchedLink = document.querySelector('.nav-home-link');
            } else {
                if (hash === '#available-cars') {
                    matchedLink = document.querySelector('a[href$="#available-cars"]');
                } else if (hash === '#features') {
                    matchedLink = document.querySelector('a[href$="#features"]');
                } else if (hash === '#how-it-works') {
                    matchedLink = document.querySelector('a[href$="#how-it-works"]');
                } else if (hash === '#reviews') {
                    matchedLink = document.querySelector('a[href$="#reviews"]');
                }
            }

            if (matchedLink) {
                matchedLink.classList.add('active', 'text-primary');
            } else if (!hash) {
                var homeLink = document.querySelector('.nav-home-link');
                if (homeLink) {
                    homeLink.classList.add('active', 'text-primary');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', updateGuestNavState);
        window.addEventListener('hashchange', updateGuestNavState);
    })();
</script>
