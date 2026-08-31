<?php
$isAjaxRequest = (!empty($_GET['ajax']) && $_GET['ajax'] === '1');
if (!$isAjaxRequest) {
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/navbar.php';
}

if ($isAjaxRequest) {
    echo '<div id="fleetResults">';
    if ($cars && $cars->num_rows > 0) {
        echo '<div class="fleet-grid">';
        while($row = $cars->fetch_assoc()) {
            $car_id = $row['id'];
            $car_imgs = getCarImagesList($row['image']);
            $available_qty = max(0, (int)($row['quantity'] ?? 0));
            echo '<article class="fleet-card">';
            echo '<div class="fleet-image-wrap">';
            if (!empty($car_imgs)) {
                echo '<img src="' . BASE_URL . 'uploads/' . htmlspecialchars($car_imgs[0]) . '" alt="' . htmlspecialchars($row['model']) . '">';
            } else {
                echo '<img src="https://via.placeholder.com/800x500?text=Premium+Fleet" alt="' . htmlspecialchars($row['model']) . '">';
            }
            echo '</div>';
            echo '<div class="fleet-card-body">';
            echo '<div class="fleet-card-topline">';
            echo '<h3>' . htmlspecialchars($row['model']) . '</h3>';
            echo '<span class="fleet-type">' . htmlspecialchars($row['type'] ?? 'SUV') . '</span>';
            echo '</div>';
            echo '<p class="fleet-owner">' . htmlspecialchars($row['owner_name'] ?? 'System Administrator') . '</p>';
            echo '<div class="fleet-price-row"><div><small>Daily Rate</small><strong>₱' . number_format((float)($row['price_per_day'] ?? 0), 2) . '</strong></div><span class="fleet-availability">' . $available_qty . ' Available</span></div>';
            echo '<button type="button" class="fleet-details-btn fleet-details-modal-btn" data-car-id="' . $car_id . '">View Details</button>';
            echo '</div></article>';
        }
        echo '</div>';
    } else {
        echo '<div class="text-center py-5"><i class="bi bi-car-front display-1 text-muted mb-3"></i><h4 class="fw-bold">No cars available in this category</h4><p class="text-muted mb-0">Try another type to see more options.</p></div>';
    }
    echo '</div>';
    exit;
}
?>

<!-- Hero Section -->
<section class="hero-wrapper">
    <div class="hero-bg-shapes">
        <div class="shape-circle shape-1"></div>
        <div class="shape-circle shape-2"></div>
    </div>
    <div class="container position-relative z-1">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="hero-badge mb-4 animate-fade-up">
                    <i class="bi bi-stars text-warning fs-6"></i>
                    <span>Premium Vehicle Mobility Platform</span>
                </div>
                <h1 class="display-3 fw-bolder mb-4 text-white tracking-tight animate-fade-up delay-100">
                    Drive Your Dream <br class="d-none d-lg-block">With Complete Freedom.
                </h1>
                <p class="lead text-white-50 mb-5 fs-5 animate-fade-up delay-200" style="max-width: 600px;">
                    Unlock instant access to luxury sedans, sports cars, and spacious family SUVs. Verified hosts, transparent daily rates, and zero hidden fees.
                </p>
                <div class="hero-cta-row animate-fade-up delay-300">
                    <div class="hero-metric-pill">
                        <span class="pulse-dot"></span>
                        <span>24/7 concierge support</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block animate-fade-up delay-200 text-center">
                <div class="hero-visual position-relative d-inline-block">
                    <img src="https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=800&q=80" class="img-fluid rounded-4 shadow-2xl hero-car-image" alt="Luxury Car" style="max-height: 380px; width: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</section>

<section id="available-cars" class="fleet-section bg-light">
    <div class="container">
        <div class="fleet-header mb-4">
            <h2 class="fleet-title">Browse Available Cars</h2>
            <div class="fleet-toolbar">
                <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="fleet-filter-form" id="fleetFilterForm">
                    <label class="fleet-filter-label">Vehicle Category</label>
                    <div class="fleet-filter-control">
                        <i class="bi bi-car-front"></i>
                        <select name="type" id="fleetTypeFilter">
                            <option value="">All Categories & Owners</option>
                            <option value="sedan" <?php echo (($_GET['type'] ?? '') === 'sedan') ? 'selected' : ''; ?>>Sedan</option>
                            <option value="suv" <?php echo (($_GET['type'] ?? '') === 'suv') ? 'selected' : ''; ?>>SUV</option>
                            <option value="sports" <?php echo (($_GET['type'] ?? '') === 'sports') ? 'selected' : ''; ?>>Sports</option>
                            <option value="van" <?php echo (($_GET['type'] ?? '') === 'van') ? 'selected' : ''; ?>>Van</option>
                            <option value="truck" <?php echo (($_GET['type'] ?? '') === 'truck') ? 'selected' : ''; ?>>Truck</option>
                            <option value="luxury" <?php echo (($_GET['type'] ?? '') === 'luxury') ? 'selected' : ''; ?>>Luxury</option>
                            <option value="other" <?php echo (($_GET['type'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div id="fleetResults">
            <?php if($cars && $cars->num_rows > 0): ?>
                <div class="fleet-grid">
                    <?php while($row = $cars->fetch_assoc()): ?>
                        <?php
                            $car_id = $row['id'];
                            $car_imgs = getCarImagesList($row['image']);
                            $available_qty = max(0, (int)($row['quantity'] ?? 0));
                        ?>
                        <article class="fleet-card">
                            <div class="fleet-image-wrap">
                                <?php if(!empty($car_imgs)): ?>
                                    <img src="<?php echo BASE_URL; ?>uploads/<?php echo htmlspecialchars($car_imgs[0]); ?>" alt="<?php echo htmlspecialchars($row['model']); ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/800x500?text=Premium+Fleet" alt="<?php echo htmlspecialchars($row['model']); ?>">
                                <?php endif; ?>
                            </div>

                            <div class="fleet-card-body">
                                <div class="fleet-card-topline">
                                    <h3><?php echo htmlspecialchars($row['model']); ?></h3>
                                    <span class="fleet-type"><?php echo htmlspecialchars($row['type'] ?? 'SUV'); ?></span>
                                </div>

                                <p class="fleet-owner"><?php echo htmlspecialchars($row['owner_name'] ?? 'System Administrator'); ?></p>

                                <div class="fleet-price-row">
                                    <div>
                                        <small>Daily Rate</small>
                                        <strong>₱<?php echo number_format((float)($row['price_per_day'] ?? 0), 2); ?></strong>
                                    </div>
                                    <span class="fleet-availability"><?php echo $available_qty; ?> Available</span>
                                </div>

                                <button type="button" class="fleet-details-btn fleet-details-modal-btn" data-car-id="<?php echo $car_id; ?>">View Details</button>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-car-front display-1 text-muted mb-3"></i>
                    <h4 class="fw-bold">No cars available in this category</h4>
                    <p class="text-muted mb-0">Try another type to see more options.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="carDetailsModal" tabindex="-1" aria-labelledby="carDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div id="carDetailsModalBody" class="modal-body p-0">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <div>Loading vehicle details...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Boxed Stats Section -->
<section class="section-spacing">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-lg-3">
                <div class="stat-box text-center">
                    <div class="icon-square bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-car-front-fill fs-4"></i>
                    </div>
                    <h2 class="display-6 fw-bolder text-dark mb-1">250+</h2>
                    <p class="text-muted fw-medium mb-0 small text-uppercase tracking-wide">Vehicles Available</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box text-center">
                    <div class="icon-square bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <h2 class="display-6 fw-bolder text-dark mb-1">15,000+</h2>
                    <p class="text-muted fw-medium mb-0 small text-uppercase tracking-wide">Happy Customers</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box text-center">
                    <div class="icon-square bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-star-fill fs-4"></i>
                    </div>
                    <h2 class="display-6 fw-bolder text-dark mb-1">99.8%</h2>
                    <p class="text-muted fw-medium mb-0 small text-uppercase tracking-wide">Satisfaction Rate</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box text-center">
                    <div class="icon-square bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-headset fs-4"></i>
                    </div>
                    <h2 class="display-6 fw-bolder text-dark mb-1">24/7</h2>
                    <p class="text-muted fw-medium mb-0 small text-uppercase tracking-wide">Customer Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid (Box Layout) -->
<section id="features" class="section-spacing bg-light">
    <div class="container">
        <div class="text-center mb-5" style="max-width: 650px; margin: 0 auto;">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3">Why Choose Us</span>
            <h2 class="display-6 fw-bold mb-3">Premium Rental Solutions</h2>
            <p class="text-muted fs-6">Designed with safety, transparency, and top-tier customer care at every mile.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="box-card h-100">
                    <div class="icon-gradient">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Insured & Verified Vehicles</h4>
                    <p class="text-muted mb-0 lh-relaxed">Every car undergoes multi-point technical inspections and sanitization protocols prior to customer pickup.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box-card h-100">
                    <div class="icon-gradient">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Zero Hidden Charges</h4>
                    <p class="text-muted mb-0 lh-relaxed">Upfront pricing guarantee. Pay upon pickup with complete confidence and flexible cancellation options.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box-card h-100">
                    <div class="icon-gradient">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h4 class="fw-bold mb-3">24/7 Roadside Concierge</h4>
                    <p class="text-muted mb-0 lh-relaxed">Our support team is available 24/7 to provide instant road assistance, guidance, and emergency response.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Boxed How It Works Steps -->
<section id="how-it-works" class="section-spacing">
    <div class="container">
        <div class="text-center mb-5" style="max-width: 600px; margin: 0 auto;">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3">Seamless Experience</span>
            <h2 class="display-6 fw-bold mb-3">3 Steps To Your Vehicle</h2>
            <p class="text-muted fs-6">Reserve your ride in under two minutes with our intuitive booking engine.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="step-card h-100">
                    <span class="step-badge">STEP 01</span>
                    <h4 class="fw-bold mb-3">Select Your Vehicle</h4>
                    <p class="text-muted mb-0">Browse through curated owner fleets and select your desired model based on specs and rates.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card h-100">
                    <span class="step-badge">STEP 02</span>
                    <h4 class="fw-bold mb-3">Set Rental Dates</h4>
                    <p class="text-muted mb-0">Input your pick-up and return schedule to generate an instant, transparent estimate.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card h-100">
                    <span class="step-badge">STEP 03</span>
                    <h4 class="fw-bold mb-3">Confirm & Drive</h4>
                    <p class="text-muted mb-0">Submit your booking request. Pick up your vehicle at the designated office and enjoy your trip.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Boxed Testimonials Grid -->
<section id="reviews" class="section-spacing bg-light">
    <div class="container">
        <div class="text-center mb-5" style="max-width: 600px; margin: 0 auto;">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3">Client Feedback</span>
            <h2 class="display-6 fw-bold mb-3">What Our Drivers Say</h2>
            <p class="text-muted fs-6">Real stories from travelers, business professionals, and fleet managers.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="text-warning me-2">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <span class="small fw-bold text-muted">5.0 Rating</span>
                        </div>
                        <p class="text-muted mb-4 fs-6 lh-relaxed">"The booking experience was flawless. Vehicle arrived spotless and ready to drive. Highly recommended service!"</p>
                    </div>
                    <div class="d-flex align-items-center pt-3 border-top border-light">
                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-3 fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            M
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Marcus Vance</h6>
                            <small class="text-muted">Verified Customer</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="text-warning me-2">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <span class="small fw-bold text-muted">5.0 Rating</span>
                        </div>
                        <p class="text-muted mb-4 fs-6 lh-relaxed">"Extremely responsive support and zero surprise charges. I appreciate the transparent pricing structure."</p>
                    </div>
                    <div class="d-flex align-items-center pt-3 border-top border-light">
                        <div class="avatar-circle bg-success bg-opacity-10 text-success me-3 fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            S
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Sophia Reyes</h6>
                            <small class="text-muted">Business Traveler</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="text-warning me-2">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <span class="small fw-bold text-muted">5.0 Rating</span>
                        </div>
                        <p class="text-muted mb-4 fs-6 lh-relaxed">"Managing our owner fleet and tracking rental requests is smooth and effective. Great platform design."</p>
                    </div>
                    <div class="d-flex align-items-center pt-3 border-top border-light">
                        <div class="avatar-circle bg-info bg-opacity-10 text-info me-3 fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            D
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">David Miller</h6>
                            <small class="text-muted">Car Owner / Admin</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Boxed Call to Action Banner -->
<div class="container my-5 py-3">
    <div class="cta-banner p-5 text-center text-lg-start">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h2 class="display-6 fw-bold mb-3">Ready to Experience Premier Mobility?</h2>
                <p class="lead text-white-50 mb-0">Join thousands of satisfied drivers today. Register in minutes and access our fleet.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary hover-float shadow-lg">
                        Create Free Account
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary hover-float shadow-lg">
                        Explore Collection
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterSelect = document.getElementById('fleetTypeFilter');
        const fleetForm = document.getElementById('fleetFilterForm');
        const fleetResults = document.getElementById('fleetResults');

        if (filterSelect && fleetForm && fleetResults) {
            filterSelect.addEventListener('change', function () {
                const selectedType = this.value;
                const url = new URL('<?php echo BASE_URL; ?>index.php', window.location.origin);
                url.searchParams.set('ajax', '1');

                if (selectedType) {
                    url.searchParams.set('type', selectedType);
                } else {
                    url.searchParams.delete('type');
                }

                fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(function (html) {
                    const match = html.match(/<div id="fleetResults">([\s\S]*?)<\/div>\s*$/);
                    if (match && match[1]) {
                        fleetResults.innerHTML = match[1];
                    } else {
                        fleetResults.innerHTML = html;
                    }
                    const currentHash = window.location.hash;
                    if (currentHash) {
                        history.replaceState(null, '', window.location.pathname + window.location.search + currentHash);
                    }
                })
                .catch(function () {
                    fleetForm.submit();
                });
            });
        }

        const carDetailsModal = new bootstrap.Modal(document.getElementById('carDetailsModal'));
        const carDetailsModalBody = document.getElementById('carDetailsModalBody');

        document.addEventListener('click', async function (event) {
            const trigger = event.target.closest('.fleet-details-modal-btn');
            if (!trigger) return;

            event.preventDefault();
            const carId = trigger.dataset.carId;
            carDetailsModalBody.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border text-primary mb-3" role="status"></div><div>Loading vehicle details...</div></div>';
            carDetailsModal.show();

            try {
                const response = await fetch('<?php echo BASE_URL; ?>car_details.php?modal=1&id=' + encodeURIComponent(carId));
                const html = await response.text();
                carDetailsModalBody.innerHTML = html;
            } catch (error) {
                carDetailsModalBody.innerHTML = '<div class="alert alert-danger m-4 mb-0">Unable to load vehicle details right now.</div>';
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
