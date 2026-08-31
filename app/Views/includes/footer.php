<footer class="site-footer mt-auto">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-4">
                <a href="<?php echo BASE_URL; ?>index.php" class="site-footer-brand">
                    <i class="bi bi-car-front-fill"></i>
                    CAR RENTAL
                </a>
            </div>
            <div class="col-lg-4">
                <div class="site-footer-links">
                    <a href="<?php echo BASE_URL; ?>dashboard.php">Browse Cars</a>
                    <a href="<?php echo BASE_URL; ?>my_rentals.php">My Rentals</a>
                    <a href="<?php echo BASE_URL; ?>login.php">Sign In</a>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="site-footer-meta">
                    <span><i class="bi bi-envelope me-1"></i>support@carrental.com</span>
                </div>
            </div>
        </div>
        <div class="site-footer-bottom">&copy; <?php echo date('Y'); ?> CAR RENTAL. All rights reserved.</div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        }
    }

    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
    // Real-time Notification for Admins
    let lastPendingCount = null;

    function checkNewRentals() {
        fetch('<?php echo BASE_URL; ?>admin/check_pending.php')
            .then(response => response.json())
            .then(data => {
                if (data.pending_count !== undefined) {
                    const rentalsLink = document.getElementById('rentals-link');
                    
                    if (rentalsLink) {
                        let badge = document.getElementById('pending-badge');
                        if (data.pending_count > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.id = 'pending-badge';
                                badge.className = 'badge rounded-pill bg-danger ms-1 animate-fade-up';
                                rentalsLink.appendChild(badge);
                            }
                            badge.innerText = data.pending_count;
                        } else if (badge) {
                            badge.remove();
                        }
                    }

                    if (lastPendingCount !== null && data.pending_count > lastPendingCount) {
                        Swal.fire({
                            title: 'NEW RENTAL REQUEST!',
                            text: 'A user is trying to rent a car right now.',
                            icon: 'warning',
                            iconColor: '#dc3545',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: true,
                            confirmButtonText: 'View',
                            confirmButtonColor: '#dc3545',
                            timer: 8000,
                            timerProgressBar: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?php echo BASE_URL; ?>admin/rentals.php';
                            }
                        });
                    }
                    lastPendingCount = data.pending_count;
                }
            })
            .catch(error => console.error('Error polling pending requests:', error));
    }

    setInterval(checkNewRentals, 3000);
    checkNewRentals();
    <?php endif; ?>

    <?php if(isset($_SESSION['swal_success'])): ?>
    Swal.fire({
        title: 'Success!',
        text: '<?php echo $_SESSION['swal_success']; ?>',
        icon: 'success',
        confirmButtonColor: '#0d6efd'
    });
    <?php unset($_SESSION['swal_success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['swal_error'])): ?>
    Swal.fire({
        title: 'Error!',
        text: '<?php echo $_SESSION['swal_error']; ?>',
        icon: 'error',
        confirmButtonColor: '#dc3545'
    });
    <?php unset($_SESSION['swal_error']); ?>
    <?php endif; ?>
</script>

</body>
</html>
