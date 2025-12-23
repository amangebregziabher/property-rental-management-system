<?php
session_start();
$errors = $_SESSION['form_errors'] ?? [];
$success = $_SESSION['success_message'] ?? '';
unset($_SESSION['form_errors']);
unset($_SESSION['success_message']);

$redirect_to = $_GET['redirect_to'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - PRMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="col-12 col-md-8 col-lg-5">
            <!-- Logo/Home Link -->
            <div class="text-center mb-5 animate-up">
                <a href="../../public/index.php" class="text-decoration-none d-inline-block">
                    <h1 class="display-4 fw-bold text-gradient mb-0">PRMS</h1>
                    <p class="text-secondary small text-uppercase ls-2">Property Rental System</p>
                </a>
            </div>

            <div class="card glass-panel border-0 shadow-lg rounded-4 overflow-hidden animate-up" style="animation-delay: 0.1s;">
                <div class="card-body p-4 p-md-5">
                    <h2 class="fw-bold mb-4">Welcome Back</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger border-0 small mb-4">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 small mb-4">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form action="../controllers/auth_controller.php?action=login" method="POST">
                        <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirect_to); ?>">
                        
                        <div class="mb-4">
                            <label for="email" class="form-label small fw-bold text-secondary">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="name@example.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label small fw-bold text-secondary">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label small" for="remember">Remember me</label>
                            </div>
                            <a href="#" class="small text-decoration-none">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold mb-4">
                            Sign In <i class="bi bi-arrow-right-short fs-4"></i>
                        </button>

                        <div class="text-center">
                            <p class="small text-muted mb-0">Don't have an account? <a href="register.php" class="fw-bold text-decoration-none">Create an account</a></p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer Links -->
            <div class="text-center mt-5 opacity-50 animate-up" style="animation-delay: 0.2s;">
                <p class="small mb-0">&copy; 2024 PRMS. All rights reserved.</p>
                <div class="mt-2">
                    <a href="../../public/index.php" class="text-dark small mx-2">Home</a>
                    <a href="tenant_view.php" class="text-dark small mx-2">Browse Homes</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body.auth-page { font-family: 'Outfit', sans-serif; }
    </style>
</body>
</html>
