<?php
/**
 * Property Rental Management System
 * Entry Point
 */
session_start();

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRMS - Find Your Perfect Rental</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body class="landing-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top">
        <div class="container">
            <a class="navbar-brand text-gradient fs-3 fw-bold" href="index.php">PRMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../app/views/tenant_view.php">Find a Home</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../app/views/my_applications.php">My Applications</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown"
                                role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end glass-panel border-0 shadow-sm mt-2">
                                <?php if ($_SESSION['user_role'] === 'owner' || $_SESSION['user_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="../app/views/property_list.php">Owner Dashboard</a></li>
                                    <li><a class="dropdown-item" href="../app/views/manage_applications.php">Manage
                                            Applications</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item text-danger"
                                        href="../app/controllers/auth_controller.php?action=logout">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a href="../app/views/login.php" class="btn btn-outline-primary btn-sm px-4">Owner Portal</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section position-relative overflow-hidden d-flex align-items-center">
        <div class="container position-relative z-1 py-5">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start animate-up">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3 fw-bold">#1
                        Selection of Premium Rentals</span>
                    <h1 class="display-3 fw-bold mb-4">Your Journey to a <span class="text-gradient">Better Home</span>
                        Starts Here</h1>
                    <p class="lead text-secondary mb-5 op-75">Connect with property owners and discover exceptional
                        living spaces tailored to your lifestyle. Simple, transparent, and beautiful.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <a href="../app/views/tenant_view.php"
                            class="btn btn-primary btn-lg px-5 py-3 rounded-4 shadow-lg fw-bold">
                            Find a Home <i class="bi bi-arrow-right-short fs-4"></i>
                        </a>
                        <a href="../app/views/login.php"
                            class="btn btn-white btn-lg px-5 py-3 rounded-4 shadow-sm fw-bold border">
                            List Your Property
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0 d-none d-lg-block animate-up" style="animation-delay: 0.2s;">
                    <div class="hero-image-container position-relative">
                        <div
                            class="glass-card shadow-lg p-3 rounded-4 position-absolute top-0 start-0 translate-middle mt-5 ms-5 z-2 animate-bounce">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-success text-white rounded-circle p-1"><i class="bi bi-check2 small"></i>
                                </div>
                                <span class="small fw-bold">Verified Listings</span>
                            </div>
                        </div>
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=800"
                            class="img-fluid rounded-5 shadow-2xl" alt="Modern House">
                        <div
                            class="glass-card shadow-lg p-3 rounded-4 position-absolute bottom-0 end-0 translate-middle-x mb-n4 z-2 animate-up">
                            <div class="d-flex align-items-center gap-3">
                                <span class="display-6 fw-bold text-primary">500+</span>
                                <span class="small text-muted fw-bold">Available<br>Properties</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-blur-hero"></div>
    </section>

    <!-- Content Breakdown -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 glass-panel p-4 rounded-4 hover-up shadow-sm">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center mb-4"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-search fs-3"></i>
                        </div>
                        <h4 class="fw-bold">Smart Search</h4>
                        <p class="text-secondary">Filter by location, price, and property type to find exactly what
                            you're looking for.</p>
                        <a href="../app/views/tenant_view.php"
                            class="btn btn-link text-primary p-0 fw-bold text-decoration-none">Browse Now <i
                                class="bi bi-chevron-right small"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 glass-panel p-4 rounded-4 hover-up shadow-sm">
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center mb-4"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>
                        <h4 class="fw-bold">Secured Listings</h4>
                        <p class="text-secondary">All properties are carefully reviewed to ensure a safe and reliable
                            rental experience.</p>
                        <a href="../app/views/login.php"
                            class="btn btn-link text-success p-0 fw-bold text-decoration-none">Manage Account <i
                                class="bi bi-chevron-right small"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 glass-panel p-4 rounded-4 hover-up shadow-sm">
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-4 d-flex align-items-center justify-content-center mb-4"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                        <h4 class="fw-bold">Real-time Stats</h4>
                        <p class="text-secondary">Get instant updates on property availability and new market entries.
                        </p>
                        <a href="../app/views/property_list.php"
                            class="btn btn-link text-info p-0 fw-bold text-decoration-none">View Inventory <i
                                class="bi bi-chevron-right small"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start mb-4 mb-md-0">
                    <a class="navbar-brand fs-3 fw-bold text-white" href="#">PRMS</a>
                    <p class="text-secondary small mt-2">Modern solutions for the property market.</p>
                </div>
                <div class="col-md-4 text-center mb-4 mb-md-0">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="text-white opacity-50"><i class="bi bi-facebook fs-4"></i></a>
                        <a href="#" class="text-white opacity-50"><i class="bi bi-twitter-x fs-4"></i></a>
                        <a href="#" class="text-white opacity-50"><i class="bi bi-instagram fs-4"></i></a>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <p class="mb-0 small opacity-50">&copy; 2024 PRMS. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>