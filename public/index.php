<?php
/**
 * Property Rental Management System
 * Entry Point
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('ROUTES_PATH', BASE_PATH . '/routes');

// In a real app, we would include config and routes here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Rental Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">PRMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../app/views/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../app/views/property_list.php">Properties</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-light py-5 mb-5">
        <div class="container text-center">
            <h1 class="display-4">Welcome to PRMS</h1>
            <p class="lead text-muted">A simple solution for managing your rental properties.</p>
            <hr class="my-4">
            <p>Manage listings, track availability, and more.</p>
            <a class="btn btn-primary btn-lg" href="../app/views/property_list.php" role="button">View Properties</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Manage Properties</h5>
                        <p class="card-text">View and update your property inventory easily.</p>
                        <a href="../app/views/property_list.php" class="btn btn-outline-primary">Go to Properties</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Add New Listing</h5>
                        <p class="card-text">List your properties for potential tenants.</p>
                        <a href="../app/views/add_property.php" class="btn btn-outline-primary">Add Property</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">User Accounts</h5>
                        <p class="card-text">Manage your profile and security settings.</p>
                        <a href="../app/views/login.php" class="btn btn-outline-primary">Login / Sign Up</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <h5>System Information</h5>
                    <p class="mb-0">This is a blueprint/prototype application. Backend logic and authentication are not yet implemented.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Property Rental Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

