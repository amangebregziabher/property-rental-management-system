<?php
session_start();
$errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - PRMS</title>
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
        <div class="col-12 col-md-8 col-lg-6">
            <!-- Logo/Home Link -->
            <div class="text-center mb-5 animate-up">
                <a href="../../public/index.php" class="text-decoration-none d-inline-block">
                    <h1 class="display-4 fw-bold text-gradient mb-0">PRMS</h1>
                    <p class="text-secondary small text-uppercase ls-2">Join our Community</p>
                </a>
            </div>

            <div class="card glass-panel border-0 shadow-lg rounded-4 overflow-hidden animate-up" style="animation-delay: 0.1s;">
                <div class="row g-0">
                    <div class="col-lg-12">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="fw-bold mb-4">Register Account</h2>
                            
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger border-0 small mb-4">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form action="../controllers/auth_controller.php?action=register" method="POST">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-12">
                                        <label for="name" class="form-label small fw-bold text-secondary">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control border-start-0" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" placeholder="John Doe" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="email" class="form-label small fw-bold text-secondary">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control border-start-0" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" placeholder="name@example.com" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="password" class="form-label small fw-bold text-secondary">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-shield-lock"></i></span>
                                            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Min. 6 characters" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-secondary">Who are you?</label>
                                        <div class="d-flex gap-3">
                                            <div class="flex-fill">
                                                <input type="radio" class="btn-check" name="role" id="roleTenant" value="tenant" checked>
                                                <label class="btn btn-outline-primary w-100 py-3 rounded-3" for="roleTenant">
                                                    <i class="bi bi-house-heart d-block fs-3 mb-1"></i>
                                                    I'm a Tenant
                                                </label>
                                            </div>
                                            <div class="flex-fill">
                                                <input type="radio" class="btn-check" name="role" id="roleOwner" value="owner">
                                                <label class="btn btn-outline-primary w-100 py-3 rounded-3" for="roleOwner">
                                                    <i class="bi bi-building-check d-block fs-3 mb-1"></i>
                                                    I'm an Owner
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check small">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label text-muted" for="terms">
                                            I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>.
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold mb-4">
                                    Create Account <i class="bi bi-person-plus small ms-2"></i>
                                </button>

                                <div class="text-center">
                                    <p class="small text-muted mb-0">Already have an account? <a href="login.php" class="fw-bold text-decoration-none">Sign in here</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Links -->
            <div class="text-center mt-5 opacity-50 animate-up" style="animation-delay: 0.2s;">
                <p class="small mb-0">&copy; 2024 PRMS. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body.auth-page { font-family: 'Outfit', sans-serif; }
        .btn-check:checked + .btn-outline-primary {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
    </style>
</body>
</html>
