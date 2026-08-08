<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
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
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start animate-fade-up delay-300">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] == 'admin'): ?>
                            <a class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg hover-float text-primary" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                            </a>
                        <?php else: ?>
                            <a class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg hover-float text-primary" href="<?php echo BASE_URL; ?>dashboard.php">
                                <i class="bi bi-car-front-fill me-2"></i>Browse Collection
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg hover-float text-primary" href="<?php echo BASE_URL; ?>register.php">
                            Get Started Now <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold hover-float" href="<?php echo BASE_URL; ?>login.php">
                            Sign In
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block animate-fade-up delay-200 text-center">
                <div class="position-relative d-inline-block">
                    <img src="https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=800&q=80" class="img-fluid rounded-4 shadow-2xl hover-float" alt="Luxury Car" style="max-height: 380px; width: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Boxed Search Bar Container -->
<div class="container mb-5">
    <div class="search-bar-card p-4 p-md-5">
        <form action="<?php echo BASE_URL; ?>dashboard.php" method="GET" class="row g-4 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted mb-2">Vehicle Category</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-car-front text-primary"></i></span>
                    <select name="type" class="form-select bg-light border-0 py-2.5">
                        <option value="">All Categories & Owners</option>
                        <option value="luxury">Luxury & Performance</option>
                        <option value="suv">SUVs & Crossovers</option>
                        <option value="sedan">Executive Sedans</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted mb-2">Pickup Location</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-geo-alt text-primary"></i></span>
                    <input type="text" class="form-control bg-light border-0 py-2.5" placeholder="City or Airport" value="Metro Manila">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted mb-2">Rental Start Date</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event text-primary"></i></span>
                    <input type="date" class="form-control bg-light border-0 py-2.5" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="col-lg-2 col-md-6 d-grid">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill py-2.5 fw-bold shadow-sm">
                    <i class="bi bi-search me-1"></i> Search Fleet
                </button>
            </div>
        </form>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
