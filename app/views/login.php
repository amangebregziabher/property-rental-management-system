<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Property Rental Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Property Rental System</h3>
                        <p class="mb-0">Login to your account</p>
                    </div>
                    <div class="card-body">
                        <!-- Login Form -->
                        <form method="POST" action="#">
                            <!-- Email Field -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email" 
                                    placeholder="Enter your email"
                                    required
                                >
                            </div>

                            <!-- Password Field -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter your password"
                                    required
                                >
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <!-- Login Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Login
                                </button>
                            </div>
                        </form>

                        <!-- Authentication Not Implemented Notice -->
                        <div class="alert alert-warning mt-3" role="alert">
                            <strong>Note:</strong> This is a prototype. Authentication is not implemented.
                        </div>

                        <!-- Additional Links -->
                        <div class="text-center mt-3">
                            <a href="property_list.php" class="text-decoration-none">
                                Skip to Property List →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// ============================================
// AUTHENTICATION NOT IMPLEMENTED
// ============================================
// This is a prototype/blueprint system
// No actual authentication logic is present
// 
// TODO for future implementation:
// - Validate email and password fields
// - Check credentials against database
// - Create session for logged-in user
// - Redirect to dashboard/property list
// - Handle login errors
// ============================================
?>
