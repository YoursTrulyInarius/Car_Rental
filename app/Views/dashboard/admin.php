<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$allCars = $carModel->getAll();
$inventorySummary = [
    'available' => 0,
    'reserved' => 0,
    'rented' => 0,
    'maintenance' => 0,
];
$carRows = [];

if ($allCars && $allCars->num_rows > 0) {
    while ($car = $allCars->fetch_assoc()) {
        $status = $car['status'] ?? 'available';
        if (!isset($inventorySummary[$status])) {
            $inventorySummary[$status] = 0;
        }
        $inventorySummary[$status]++;
        $carRows[] = $car;
    }
}

$availableCars = $inventorySummary['available'];
$unavailableCars = max((count($carRows) - $availableCars), 0);
$recentFleet = array_slice($carRows, 0, 6);
?>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <span class="admin-brand-mark">CR</span>
            <div>
                <strong>CAR RENTAL</strong>
                <small class="text-white-50">Admin Panel</small>
            </div>
        </div>

        <nav class="admin-nav">
            <a class="<?php echo ($current_page == 'dashboard.php' || $current_page == 'admin') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                <i class="bi bi-speedometer2"></i>
                <span>Overview</span>
            </a>
            <a class="<?php echo ($current_page == 'cars.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/cars.php">
                <i class="bi bi-car-front"></i>
                <span>Cars</span>
            </a>
            <a class="<?php echo ($current_page == 'rentals.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/rentals.php">
                <i class="bi bi-calendar3"></i>
                <span>Rentals</span>
            </a>
        </nav>

        <div class="admin-sidebar-card">
            <span class="d-block text-uppercase small text-white-50 mb-1 fw-bold">Quick Stats</span>
            <h3 class="mb-0 fw-bold text-white"><?php echo count($carRows); ?></h3>
            <small class="text-white-50">Fleet units</small>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="admin-kicker">Operations</p>
                <h1 class="mb-0">Admin Dashboard</h1>
            </div>

            <div class="admin-topbar-actions">
                <a class="btn btn-primary rounded-pill" href="<?php echo BASE_URL; ?>admin/cars.php">
                    <i class="bi bi-plus-circle me-2"></i>Add Car
                </a>
            </div>
        </header>

        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4" role="alert">
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <section class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="admin-stat-card stat-blue">
                    <div>
                        <p>Total Cars</p>
                        <h3><?php echo $total_cars; ?></h3>
                    </div>
                    <span class="admin-icon"><i class="bi bi-car-front-fill"></i></span>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="admin-stat-card stat-green">
                    <div>
                        <p>Available</p>
                        <h3><?php echo $availableCars; ?></h3>
                    </div>
                    <span class="admin-icon"><i class="bi bi-check-circle-fill"></i></span>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="admin-stat-card stat-warning">
                    <div>
                        <p>Unavailable</p>
                        <h3><?php echo $unavailableCars; ?></h3>
                    </div>
                    <span class="admin-icon"><i class="bi bi-slash-circle"></i></span>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="admin-stat-card stat-purple">
                    <div>
                        <p>Pending Rentals</p>
                        <h3><?php echo $pending_rentals; ?></h3>
                    </div>
                    <span class="admin-icon"><i class="bi bi-hourglass-split"></i></span>
                </div>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-panel-label">Fleet</p>
                            <h4>Vehicle availability</h4>
                        </div>
                        <a href="<?php echo BASE_URL; ?>admin/cars.php" class="btn btn-sm btn-light">Manage cars</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Car</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Owner</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentFleet)): ?>
                                    <?php foreach ($recentFleet as $car): ?>
                                        <?php
                                            $status = $car['status'] ?? 'available';
                                            $statusClass = 'success';
                                            if ($status === 'rented' || $status === 'reserved') $statusClass = 'warning';
                                            if ($status === 'maintenance') $statusClass = 'secondary';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="mini-car-thumb">
                                                        <i class="bi bi-car-front-fill"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($car['brand'] ?? 'Vehicle'); ?> <?php echo htmlspecialchars($car['model'] ?? ''); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($car['plate_number'] ?? ''); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
                                            </td>
                                            <td class="fw-semibold text-primary">₱<?php echo number_format((float)($car['price_per_day'] ?? 0), 2); ?></td>
                                            <td>
                                                <?php $ownerName = $car['owner_id'] ? ($carModel->getById($car['owner_id'])['name'] ?? 'Owner') : 'N/A'; ?>
                                                <?php echo htmlspecialchars($ownerName); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No cars found in the fleet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-panel-label">Actions</p>
                            <h4>Quick tools</h4>
                        </div>
                    </div>

                    <div class="quick-action-list">
                        <a href="<?php echo BASE_URL; ?>admin/cars.php" class="quick-action-item">
                            <span class="quick-icon blue"><i class="bi bi-plus-circle"></i></span>
                            <div>
                                <strong>Add new car</strong>
                                <small>Manage fleet inventory</small>
                            </div>
                        </a>

                        <a href="<?php echo BASE_URL; ?>admin/rentals.php" class="quick-action-item">
                            <span class="quick-icon green"><i class="bi bi-card-list"></i></span>
                            <div>
                                <strong>Review rentals</strong>
                                <small>Approve or reject requests</small>
                            </div>
                        </a>

                    </div>
                </div>

                <div class="admin-panel mt-4">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-panel-label">Summary</p>
                            <h4>Availability</h4>
                        </div>
                    </div>

                    <div class="summary-stack">
                        <div class="summary-item">
                            <span>Available</span>
                            <strong><?php echo $availableCars; ?></strong>
                        </div>
                        <div class="summary-item">
                            <span>Reserved</span>
                            <strong><?php echo $inventorySummary['reserved']; ?></strong>
                        </div>
                        <div class="summary-item">
                            <span>Rented</span>
                            <strong><?php echo $inventorySummary['rented']; ?></strong>
                        </div>
                        <div class="summary-item">
                            <span>Maintenance</span>
                            <strong><?php echo $inventorySummary['maintenance']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <p class="admin-panel-label">Renters</p>
                    <h4>Who is renting</h4>
                </div>
                <a href="<?php echo BASE_URL; ?>admin/rentals.php" class="btn btn-sm btn-light">View all</a>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Dates</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($recent_rentals && $recent_rentals->num_rows > 0): ?>
                            <?php while($rental = $recent_rentals->fetch_assoc()): ?>
                                <?php
                                    $badge = 'secondary';
                                    if ($rental['status'] === 'approved') $badge = 'success';
                                    if ($rental['status'] === 'rejected') $badge = 'danger';
                                    if ($rental['status'] === 'pending') $badge = 'warning';
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="customer-avatar"><?php echo strtoupper(substr($rental['user_name'] ?? 'U', 0, 1)); ?></div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($rental['user_name'] ?? 'Customer'); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($rental['status'] ?? 'pending'); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($rental['model'] ?? 'Vehicle'); ?></td>
                                    <td><?php echo date('M d', strtotime($rental['start_date'])); ?> - <?php echo date('M d', strtotime($rental['end_date'])); ?></td>
                                    <td class="fw-semibold text-primary">₱<?php echo number_format((float)($rental['total_price'] ?? 0), 2); ?></td>
                                    <td><span class="status-badge status-<?php echo $badge; ?>"><?php echo ucfirst($rental['status'] ?? 'pending'); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No rental activity yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
