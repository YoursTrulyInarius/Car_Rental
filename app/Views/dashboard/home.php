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
                            <a class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg hover-float" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                                <i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard
                            </a>
                        <?php else: ?>
                            <a class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg hover-float" href="<?php echo BASE_URL; ?>dashboard.php">
                                <i class="bi bi-car-front-fill me-2 text-primary"></i>Browse Collection
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
                    <img src="<?php echo BASE_URL; ?>assets/images/hero-car.png" onerror="this.src='https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=800&q=80'" class="img-fluid rounded-4 shadow-2xl hover-float" alt="Luxury Car" style="max-height: 380px; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Action / Fleet Search Bar Bar -->
<div class="container mb-5">
    <div class="search-bar-card p-4 p-md-5">
        <form action="<?php echo BASE_URL; ?>dashboard.php" method="GET" class="row g-3 align-items-center">
            <div class="col-lg-4 col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">Vehicle Type</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-car-front text-primary"></i></span>
                    <select name="type" class="form-select bg-light border-0 py-2">
                        <option value="">All Categories & Owners</option>
                        <option value="luxury">Luxury & Performance</option>
                        <option value="suv">SUVs & Crossovers</option>
                        <option value="sedan">Executive Sedans</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">Pickup Location</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-geo-alt text-primary"></i></span>
                    <input type="text" class="form-control bg-light border-0 py-2" placeholder="City or Airport" value="Metro Manila">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">Rental Date</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event text-primary"></i></span>
                    <input type="date" class="form-control bg-light border-0 py-2" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="col-lg-2 col-md-6 d-grid">
                <label class="form-label d-none d-lg-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill py-2.5 fw-bold shadow-sm">
                    <i class="bi bi-search me-1"></i> Search Fleet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Stats Counter Section -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-bolder text-primary mb-1">250+</h2>
                <p class="text-muted fw-medium mb-0">Premium Vehicles</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-bolder text-primary mb-1">15k+</h2>
                <p class="text-muted fw-medium mb-0">Happy Customers</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-bolder text-primary mb-1">99.8%</h2>
                <p class="text-muted fw-medium mb-0">Satisfaction Rate</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-bolder text-primary mb-1">24/7</h2>
                <p class="text-muted fw-medium mb-0">Concierge Support</p>
            </div>
        </div>
    </div>
</section>

<!-- Features / Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5" style="max-width: 650px; margin: 0 auto;">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3">Why Choose VEGA'S</span>
            <h2 class="display-6 fw-bold mb-3">Designed for Seamless Driving Experiences</h2>
            <p class="text-muted">We provide an effortless rental experience tailored for business professionals, vacationers, and car enthusiasts.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="icon-gradient">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Verified Vehicles</h4>
                    <p class="text-muted mb-0">Every vehicle undergoes rigorous safety inspections and sanitation protocol before handing over key.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="icon-gradient">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Zero Hidden Fees</h4>
                    <p class="text-muted mb-0">Transparent daily rates with pay-upon-pickup convenience. What you see is exactly what you pay.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="icon-gradient">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4 class="fw-bold mb-3">24/7 Assistance</h4>
                    <p class="text-muted mb-0">Our dedicated team is ready around the clock to support your journey anywhere, anytime.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3">Simple Process</span>
            <h2 class="display-6 fw-bold">How It Works in 3 Easy Steps</h2>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4">
                    <div class="step-number">1</div>
                    <h4 class="fw-bold mb-2">Choose Your Car</h4>
                    <p class="text-muted">Browse our fleet of verified car owners and pick your ideal ride.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <div class="step-number">2</div>
                    <h4 class="fw-bold mb-2">Select Rental Dates</h4>
                    <p class="text-muted">Choose your pick-up & return schedule with instant price calculation.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <div class="step-number">3</div>
                    <h4 class="fw-bold mb-2">Pick Up & Enjoy</h4>
                    <p class="text-muted">Confirm your reservation and pick up your car with total peace of mind.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3">Customer Reviews</span>
            <h2 class="display-6 fw-bold">Trusted by Thousands of Drivers</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card h-100">
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
                    <p class="text-muted mb-4">"The booking process was insanely smooth. Car was delivered spotless and ran like a charm. Best rental service!"</p>
                    <div class="d-flex align-items-center">
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
                <div class="testimonial-card h-100">
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
                    <p class="text-muted mb-4">"Super responsive support and transparent pricing. I appreciate not having hidden fees at checkout."</p>
                    <div class="d-flex align-items-center">
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
                <div class="testimonial-card h-100">
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
                    <p class="text-muted mb-4">"As a car owner on the platform, managing my fleet and receiving rental requests has never been easier."</p>
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle bg-info bg-opacity-10 text-info me-3 fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            D
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">David Miller</h6>
                            <small class="text-muted">Fleet Owner</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<div class="container my-5">
    <div class="cta-banner p-5 text-center text-lg-start">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h2 class="display-6 fw-bold mb-3">Ready to Hit the Road?</h2>
                <p class="lead text-white-50 mb-0">Sign up today and experience seamless premium car rentals at your fingertips.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary hover-float shadow-lg">
                        Create Free Account
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary hover-float shadow-lg">
                        Browse Cars Now
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
