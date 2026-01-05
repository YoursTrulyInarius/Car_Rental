<footer class="mt-auto">
    <div class="container">
        <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> VEGAS CAR RENTAL. All rights reserved.</p>
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
            input.type = "password"
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        }
    }

    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    // Real-time Notification for Admins
    let lastPendingCount = null;

    function checkNewRentals() {
        fetch('<?php echo BASE_URL; ?>admin/check_pending.php')
            .then(response => response.json())
            .then(data => {
                if (data.pending_count !== undefined) {
                    const rentalsLink = document.getElementById('rentals-link');
                    
                    // Update Navbar Badge
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

                    // Popup Notification only on INCREASE
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

    // Check every 3 seconds for faster response
    setInterval(checkNewRentals, 3000);
    checkNewRentals(); // Initial check
    <?php endif; ?>

    // Success/Error Popups from Session
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
