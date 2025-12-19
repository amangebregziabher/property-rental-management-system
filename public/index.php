<?php
/**
 * Property Rental Management System
 * Entry Point
 * 
 * This is the main entry point for the application.
 * All requests are routed through this file.
 */

// Define application paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('ROUTES_PATH', BASE_PATH . '/routes');

// Load configuration
$config = require CONFIG_PATH . '/database.php';

// Load routes
require ROUTES_PATH . '/web.php';
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
    <!-- Hero Section -->
    <div class="bg-dark text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold">Property Rental Management System</h1>
                    <p class="lead">Manage your rental properties with ease</p>
                    <p class="text-muted">A comprehensive solution for property owners and tenants</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../app/views/login.php" class="btn btn-primary btn-lg">Login</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">Welcome to PRMS</h2>
                <div class="alert alert-info">
                    <h5>Prototype System</h5>
                    <p>This is a blueprint-level prototype demonstrating the structure and UI of a Property Rental Management System.</p>
                    <p class="mb-0"><strong>Note:</strong> Authentication and backend logic are not implemented in this version.</p>
                </div>
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-house-door text-primary" viewBox="0 0 16 16">
                                <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/>
                            </svg>
                        </div>
                        <h5 class="card-title">Property Management</h5>
                        <p class="card-text">Add, edit, and manage your rental properties efficiently.</p>
                        <a href="../app/views/property_list.php" class="btn btn-outline-primary">View Properties</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-plus-circle text-success" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                            </svg>
                        </div>
                        <h5 class="card-title">Add Property</h5>
                        <p class="card-text">List new properties with detailed information and images.</p>
                        <a href="../app/views/add_property.php" class="btn btn-outline-success">Add New</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-person-circle text-info" viewBox="0 0 16 16">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                            </svg>
                        </div>
                        <h5 class="card-title">User Account</h5>
                        <p class="card-text">Login to access your dashboard and manage properties.</p>
                        <a href="../app/views/login.php" class="btn btn-outline-info">Login</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">System Features</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Implemented (UI Only):</h6>
                                <ul>
                                    <li>Login page interface</li>
                                    <li>Property listing with table view</li>
                                    <li>Add property form</li>
                                    <li>Edit property form</li>
                                    <li>Multiple image upload support</li>
                                    <li>Responsive Bootstrap design</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Planned for Future:</h6>
                                <ul>
                                    <li>User authentication & sessions</li>
                                    <li>Database CRUD operations</li>
                                    <li>Image upload processing</li>
                                    <li>User role management</li>
                                    <li>Tenant management</li>
                                    <li>Payment tracking</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Property Rental Management System. All rights reserved.</p>
            <p class="text-muted mb-0">Prototype Version - For Educational Purposes</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
