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
            <div class="col-12 col-md-10 col-lg-7">
                <!-- Logo/Home Link -->
                <div class="text-center mb-5 animate-up">
                    <a href="../../public/index.php" class="text-decoration-none d-inline-block">
                        <h1 class="display-3 fw-bold text-white mb-0">PRMS</h1>
                        <p class="text-white opacity-50 small text-uppercase ls-2">Become Part of the Elite Community</p>
                    </a>
                </div>

                <div class="card auth-card border-0 rounded-4 overflow-hidden animate-up" style="animation-delay: 0.1s;">
                    <div class="row g-0">
                        <div class="col-lg-12">
                            <div class="card-body p-4 p-md-5">
                                <div class="text-center mb-5">
                                    <h2 class="fw-bold text-white mb-2">Create Account</h2>
                                    <p class="text-white opacity-50 small">Fill in your details to get started with PRMS</p>
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

                                <form action="../controllers/auth_controller.php?action=register" method="POST">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label small fw-bold">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-person text-white opacity-50"></i></span>
                                                <input type="text" class="form-control border-start-0 py-3" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" placeholder="John Doe" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="email" class="form-label small fw-bold">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-envelope text-white opacity-50"></i></span>
                                                <input type="email" class="form-control border-start-0 py-3" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" placeholder="name@example.com" required>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="password" class="form-label small fw-bold">Create Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-shield-lock text-white opacity-50"></i></span>
                                                <input type="password" class="form-control border-start-0 py-3" id="password" name="password" placeholder="At least 6 characters" required>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-2">
                                            <label class="form-label small fw-bold d-block text-center mb-4">What's your primary goal?</label>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <input type="radio" class="btn-check" name="role" id="roleTenant" value="tenant" checked>
                                                    <label class="role-card btn h-100 p-4 border-0 rounded-4 w-100" for="roleTenant">
                                                        <div class="role-icon mb-3">
                                                            <i class="bi bi-house-heart fs-1"></i>
                                                        </div>
                                                        <div class="fw-bold fs-5 mb-1">Find a Home</div>
                                                        <div class="small opacity-50">I'm looking for the perfect rental property.</div>
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="radio" class="btn-check" name="role" id="roleOwner" value="owner">
                                                    <label class="role-card btn h-100 p-4 border-0 rounded-4 w-100" for="roleOwner">
                                                        <div class="role-icon mb-3">
                                                            <i class="bi bi-building-check fs-1"></i>
                                                        </div>
                                                        <div class="fw-bold fs-5 mb-1">List Property</div>
                                                        <div class="small opacity-50">I want to manage and rent out my properties.</div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-check small text-center">
                                                <input class="form-check-input bg-transparent border-white border-opacity-20 d-inline-block float-none me-2" type="checkbox" id="terms" required>
                                                <label class="form-check-label text-white opacity-75" for="terms">
                                                    I agree to the <a href="#" class="text-white text-decoration-none border-bottom">Terms</a> & <a href="#" class="text-white text-decoration-none border-bottom">Privacy Policy</a>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-lg fw-bold mb-4">
                                                Create Account <i class="bi bi-person-plus fs-5 ms-2"></i>
                                            </button>
                                            
                                            <div class="text-center">
                                                <p class="small text-white opacity-50 mb-0">Member already? <a href="login.php" class="fw-bold text-white text-decoration-none border-bottom">Sign in to your dashboard</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Links -->
                <div class="text-center mt-5 opacity-25 animate-up" style="animation-delay: 0.2s;">
                    <p class="small text-white mb-0">&copy; 2024 PRMS. Redefining property management.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .ls-2 { letter-spacing: 2px; }
        .role-card {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            color: white !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: none !important;
            letter-spacing: normal !important;
        }
        .role-card:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.2) !important;
        }
        .btn-check:checked + .role-card {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.2), rgba(181, 23, 158, 0.2)) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.3) !important;
        }
        .btn-check:checked + .role-card .role-icon i {
            color: var(--primary-light);
            text-shadow: 0 0 15px rgba(76, 201, 240, 0.5);
        }
        .role-icon i { transition: all 0.3s ease; opacity: 0.8; }
        .border-bottom { border-bottom: 2px solid rgba(255,255,255,0.1) !important; transition: all 0.3s ease; }
        .border-bottom:hover { border-bottom-color: var(--primary) !important; color: white !important; }
    </style>
</body>
</html>
