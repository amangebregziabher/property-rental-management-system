<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Get property ID from URL
$property_id = $_GET['property_id'] ?? 0;

if (empty($property_id) || !is_numeric($property_id)) {
    header('Location: tenant_view.php');
    exit();
}

$conn = get_db_connection();

// Fetch property details to show what they are applying for
$sql = "SELECT p.*, 
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_main DESC, id ASC LIMIT 1) as main_image
        FROM properties p 
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $property_id);
mysqli_stmt_execute($stmt);
$property = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$property) {
    close_db_connection($conn);
    header('Location: tenant_view.php');
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

close_db_connection($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Application - <?php echo htmlspecialchars($property['title']); ?> - PRMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .application-form .form-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0.5rem;
        }

        .application-form .form-control,
        .application-form .form-select {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.8rem 1rem;
            border-radius: 12px;
        }

        .application-form .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
        }

        .upload-area {
            border: 2px dashed rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.05);
        }

        .upload-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .step-indicator {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .step-indicator.active {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(67, 97, 238, 0.4);
        }

        .property-summary {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.1));
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .property-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
        }
    </style>
</head>

<body class="tenant-portal">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top">
        <div class="container">
            <a class="navbar-brand text-gradient fs-3 fw-bold" href="../../public/index.php">PRMS</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="tenant_view.php">Find Home</a>
                    </li>
                    <?php if ($is_logged_in): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="tenant_applications_list.php">My Applications</a>
                    </li>
                     <li class="nav-item ms-lg-3">
                        <span class="nav-link text-white fw-bold">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                    </li>
                    <li class="nav-item ms-lg-2">
                         <a class="nav-link text-danger" href="../controllers/auth_controller.php?action=logout">Logout</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item ms-lg-3">
                         <a href="login.php" class="btn btn-outline-light btn-sm px-4">Login</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Property Summary Header -->
                <div class="property-summary mb-5 d-flex align-items-center gap-4 animate-up">
                    <?php if ($property['main_image']): ?>
                        <img src="../../images/<?php echo htmlspecialchars($property['main_image']); ?>"
                            class="property-thumb" alt="Property">
                    <?php else: ?>
                        <div class="property-thumb bg-dark d-flex align-items-center justify-content-center">
                            <i class="bi bi-house text-muted fs-3"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($property['title']); ?></h4>
                        <p class="text-secondary mb-0 small"><i class="bi bi-geo-alt me-1"></i>
                            <?php echo htmlspecialchars($property['location']); ?></p>
                    </div>
                    <div class="ms-auto text-end">
                        <div class="h4 fw-bold text-primary mb-0">
                            $<?php echo number_format($property['price'], 0); ?>/mo</div>
                        <span class="badge bg-success-subtle text-success small">Available Now</span>
                    </div>
                </div>

                <div class="card glass-panel border-0 shadow-lg animate-up" style="animation-delay: 0.1s;">
                    <div class="card-body p-4 p-md-5 application-form">
                        <div class="mb-5">
                            <h2 class="fw-bold h3 mb-2">Rental Application</h2>
                            <p class="text-secondary">Please provide accurate information for your application. This
                                information will be reviewed by the property owner.</p>
                        </div>

                        <?php if (isset($_SESSION['form_errors'])): ?>
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-4">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($_SESSION['form_errors'] as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php unset($_SESSION['form_errors']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-4">
                                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                            </div>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>

                        <form action="../controllers/submit_application.php" method="POST"
                            enctype="multipart/form-data">
                            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">

                            <!-- Step 1: Personal Information -->
                            <div class="section-title d-flex align-items-center mb-4">
                                <div class="step-indicator active">1</div>
                                <h5 class="fw-bold mb-0">Personal Information</h5>
                            </div>

                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="applicant_name" class="form-control" placeholder="John Doe"
                                        value="<?php echo htmlspecialchars($user_name); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="applicant_email" class="form-control"
                                        placeholder="john@example.com"
                                        value="<?php echo htmlspecialchars($user_email); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="applicant_phone" class="form-control"
                                        placeholder="+1 (555) 000-0000" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Number of Occupants</label>
                                    <input type="number" name="occupants" class="form-control" min="1" value="1"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Desired Move-in</label>
                                    <input type="date" name="move_in_date" class="form-control" required>
                                </div>
                            </div>

                            <!-- Step 2: Employment & Income -->
                            <div class="section-title d-flex align-items-center mb-4">
                                <div class="step-indicator active">2</div>
                                <h5 class="fw-bold mb-0">Employment & Financials</h5>
                            </div>

                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <label class="form-label">Current Employer</label>
                                    <input type="text" name="employer" class="form-control" placeholder="Company Name"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Job Title</label>
                                    <input type="text" name="job_title" class="form-control"
                                        placeholder="Software Engineer" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Monthly Gross Income (USD)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="monthly_income" class="form-control"
                                            placeholder="0.00" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Employment Status</label>
                                    <select name="employment_status" class="form-select" required>
                                        <option value="Full-time">Full-time</option>
                                        <option value="Part-time">Part-time</option>
                                        <option value="Self-employed">Self-employed</option>
                                        <option value="Retired">Retired</option>
                                        <option value="Student">Student</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Step 3: Document Upload -->
                            <div class="section-title d-flex align-items-center mb-4">
                                <div class="step-indicator active">3</div>
                                <h5 class="fw-bold mb-0">Required Documents</h5>
                            </div>

                            <p class="text-secondary small mb-4">Please upload scanned copies of your ID/Passport and
                                recent pay stubs for verification. Max file size: 5MB per file.</p>

                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <div class="upload-area" onclick="document.getElementById('id_doc').click()">
                                        <i class="bi bi-file-earmark-person upload-icon"></i>
                                        <h6 class="fw-bold mb-1">Government ID / Passport</h6>
                                        <p class="text-muted small mb-0" id="id_doc_name">Click to browse files</p>
                                        <input type="file" name="id_document" id="id_doc" class="d-none"
                                            accept=".pdf,.jpg,.png" onchange="updateFileName(this, 'id_doc_name')"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="upload-area" onclick="document.getElementById('income_doc').click()">
                                        <i class="bi bi-file-earmark-medical upload-icon"></i>
                                        <h6 class="fw-bold mb-1">Proof of Income (Pay Stubs)</h6>
                                        <p class="text-muted small mb-0" id="income_doc_name">Click to browse files</p>
                                        <input type="file" name="income_document" id="income_doc" class="d-none"
                                            accept=".pdf,.jpg,.png" onchange="updateFileName(this, 'income_doc_name')"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Message -->
                            <div class="mb-5">
                                <label class="form-label">Message to Owner (Optional)</label>
                                <textarea name="message" class="form-control" rows="4"
                                    placeholder="Mention why you're a great fit for this property..."></textarea>
                            </div>

                            <!-- Submission -->
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary py-3 fw-bold text-uppercase fs-6">
                                    Submit Application <i class="bi bi-send ms-2"></i>
                                </button>
                                <a href="property_details.php?id=<?php echo $property_id; ?>"
                                    class="btn btn-outline-secondary py-2 border-0 opacity-75">Cancel and Return</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-5 glass-panel border-0 border-top mt-5">
        <div class="container text-center">
            <p class="text-secondary mb-0 small">© 2024 PRMS - Professional Property Management Solutions</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateFileName(input, targetId) {
            const fileName = input.files[0] ? input.files[0].name : 'Click to browse files';
            document.getElementById(targetId).textContent = fileName;
            document.getElementById(targetId).parentElement.style.borderColor = '#4361ee';
        }
    </script>
</body>

</html>