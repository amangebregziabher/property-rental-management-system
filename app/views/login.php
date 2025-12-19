<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Property Rental Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        /* Custom hover effect for login button */
        .btn-login {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
            background-color: #0056b3;
            border-color: #004a9b;
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Login</h3>

                        <?php
                        // Display error message if present in query string
                        if (isset($_GET['error'])) {
                            echo '<div class="alert alert-danger text-center"><small>' . htmlspecialchars($_GET['error']) . '</small></div>';
                        }
                        ?>

                        <!-- Prototype notice -->
                        <div class="alert alert-warning text-center">
                            <small>Prototype: Auth not implemented.</small>
                        </div>

                        <form method="POST" action="../controllers/login_handler.php" class="needs-validation"
                            id="loginForm" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.com$" placeholder="example@domain.com"
                                    required>
                                <div class="invalid-feedback" id="email-feedback">
                                    Please provide a valid email address ending with .com.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    pattern="(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).{6,}" required>
                                <div class="invalid-feedback">
                                    Password must be at least 6 characters and include a letter, a number, and a special
                                    character.
                                </div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-login">Login</button>
                                <a href="property_list.php" class="btn btn-link">Skip to Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="../public/index.php" class="text-muted">Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            'use strict'

            const form = document.getElementById('loginForm')
            const emailInput = document.getElementById('email')
            const passwordInput = document.getElementById('password')
            const emailFeedback = document.getElementById('email-feedback')

            // Real-time validation for email
            emailInput.addEventListener('input', () => {
                const value = emailInput.value;
                if (value.length > 0) {
                    if (!value.includes('.com')) {
                        emailInput.setCustomValidity('Invalid');
                        emailFeedback.textContent = 'Please put .com before proceeding (Login or Password).';
                        emailInput.classList.add('is-invalid');
                    } else if (!emailInput.validity.valid) {
                        emailInput.setCustomValidity('');
                        emailFeedback.textContent = 'Please provide a valid email address ending with .com.';
                        emailInput.classList.add('is-invalid');
                    } else {
                        emailInput.setCustomValidity('');
                        emailInput.classList.remove('is-invalid');
                        emailInput.classList.add('is-valid');
                    }
                } else {
                    emailInput.setCustomValidity('');
                    emailInput.classList.remove('is-invalid', 'is-valid');
                }
            });

            // Focus transfer logic
            emailInput.addEventListener('keydown', (e) => {
                if (e.key === 'Tab' || e.key === 'Enter') {
                    if (!emailInput.value.includes('.com')) {
                        e.preventDefault();
                        emailInput.setCustomValidity('Invalid');
                        emailFeedback.textContent = 'You must put .com before moving to password.';
                        emailInput.classList.add('is-invalid');
                        emailInput.reportValidity();
                    } else if (emailInput.validity.valid) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            passwordInput.focus();
                        }
                    }
                }
            });

            form.addEventListener('submit', event => {
                if (!form.checkValidity() || !emailInput.value.includes('.com')) {
                    event.preventDefault()
                    event.stopPropagation()

                    if (!emailInput.value.includes('.com')) {
                        emailFeedback.textContent = 'Please put .com before login.';
                        emailInput.classList.add('is-invalid');
                    }
                }

                form.classList.add('was-validated')
            }, false)
        })()
    </script>
</body>

</html>
<?php
// AUTHENTICATION NOT IMPLEMENTED
?>