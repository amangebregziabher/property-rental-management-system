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
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?php echo time(); ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <!-- Animated Background Shapes -->
    <div class="auth-bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="container py-5">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <!-- Logo/Home Link -->
                <div class="text-center mb-5 animate-up">
                    <a href="../../public/index.php" class="text-decoration-none d-inline-block">
                        <h1 class="display-3 fw-bold text-white mb-0">PRMS</h1>
                        <p class="text-white opacity-50 small text-uppercase ls-2">The Gold Standard in Property Management</p>
                    </a>
                </div>

                <div class="card auth-card border-0 rounded-4 overflow-hidden animate-up" style="animation-delay: 0.1s;">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-white mb-2">Welcome Back</h2>
                            <p class="text-white opacity-50 small">Enter your credentials to access your dashboard</p>
                        </div>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small mb-4">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success small mb-4">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form action="../controllers/auth_controller.php?action=login" method="POST">
                            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirect_to); ?>">
                            
                            <div class="mb-4">
                                <label for="email" class="form-label small fw-bold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-envelope text-white opacity-50"></i></span>
                                    <input type="email" class="form-control border-start-0 py-3" id="email" name="email" placeholder="name@example.com" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small fw-bold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-shield-lock text-white opacity-50"></i></span>
                                    <input type="password" class="form-control border-start-0 py-3" id="password" name="password" placeholder="••••••••" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input bg-transparent border-white border-opacity-20" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label small text-white opacity-75" for="remember">Keep me signed in</label>
                                </div>
                                <a href="#" class="small text-decoration-none text-primary-light fw-bold">Forgot password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-lg fw-bold mb-4">
                                Sign In <i class="bi bi-arrow-right-short fs-4"></i>
                            </button>

                            <div class="text-center">
                                <p class="small text-white opacity-50 mb-0">No account? <a href="register.php" class="fw-bold text-white text-decoration-none border-bottom">Start your journey today</a></p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer Links -->
                <div class="text-center mt-5 animate-up" style="animation-delay: 0.2s;">
                    <p class="small text-white opacity-25 mb-0">&copy; 2024 PRMS. Crafting modern solutions.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .ls-2 { letter-spacing: 2px; }
        .text-primary-light { color: #4cc9f0; }
        .border-bottom { border-bottom: 2px solid rgba(255,255,255,0.1) !important; transition: all 0.3s ease; }
        .border-bottom:hover { border-bottom-color: var(--primary) !important; color: white !important; }
    </style>
</body>

</html>
