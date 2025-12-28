<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Get property ID from URL
$property_id = $_GET['id'] ?? 0;

if (empty($property_id) || !is_numeric($property_id)) {
    header('Location: tenant_view.php');
    exit();
}

$conn = get_db_connection();

// Fetch property details
$sql = "SELECT p.*, u.name as owner_name FROM properties p 
        LEFT JOIN users u ON p.owner_id = u.id 
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

// Access Control: If property is not 'Available', only owner or admin can view it
if ($property['status'] !== 'Available') {
    $user_id = $_SESSION['user_id'] ?? 0;
    $user_role = $_SESSION['user_role'] ?? 'tenant';

    if ($user_id != $property['owner_id'] && $user_role !== 'admin') {
        close_db_connection($conn);
        $_SESSION['error_message'] = "This property is no longer available for viewing.";
        header('Location: tenant_view.php');
        exit();
    }
}

// Fetch all property images
$img_sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_main DESC, id ASC";
$img_stmt = mysqli_prepare($conn, $img_sql);
mysqli_stmt_bind_param($img_stmt, "i", $property_id);
mysqli_stmt_execute($img_stmt);
$images_result = mysqli_stmt_get_result($img_stmt);
$images = [];
while ($img = mysqli_fetch_assoc($images_result)) {
    $images[] = $img;
}

close_db_connection($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - PRMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="tenant-portal">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top">
        <div class="container">
            <a class="navbar-brand text-gradient fs-3 fw-bold" href="../../public/index.php">PRMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="tenant_view.php">Find Home</a>
                    </li>
                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] === 'tenant')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="tenant_applications_list.php">My Applications</a>
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
                                    <li><a class="dropdown-item" href="property_list.php">Owner Dashboard</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item text-danger"
                                        href="../controllers/auth_controller.php?action=logout">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a href="login.php" class="btn btn-outline-primary btn-sm px-4">Owner Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="tenant_view.php" class="text-decoration-none">Properties</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['title']); ?></li>
            </ol>
        </nav>

        <div class="row g-5 animate-up">
            <!-- Left Column: Gallery -->
            <div class="col-lg-7">
                <div class="gallery-container shadow-lg rounded-4 overflow-hidden mb-4">
                    <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php if (empty($images)): ?>
                                <div class="carousel-item active">
                                    <div
                                        class="detail-hero-placeholder bg-light d-flex align-items-center justify-content-center">
                                        <i class="bi bi-image text-muted display-1"></i>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($images as $index => $img): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="../../images/<?php echo htmlspecialchars($img['image_path']); ?>"
                                            class="d-block w-100 detail-hero-img" alt="Property Image">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel"
                                data-bs-slide="prev">
                                <span class="carousel-control-prev-icon shadow-sm" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel"
                                data-bs-slide="next">
                                <span class="carousel-control-next-icon shadow-sm" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="property-info glass-panel p-4 p-md-5 rounded-4 border-0">
                    <h2 class="fw-bold mb-4">Property Description</h2>
                    <p class="lead text-secondary" style="white-space: pre-wrap;">
                        <?php echo htmlspecialchars($property['description'] ?: 'No description provided for this listing.'); ?>
                    </p>
                </div>
            </div>

            <!-- Right Column: Details & Booking -->
            <div class="col-lg-5">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card glass-panel border-0 rounded-4 shadow-lg mb-4">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <span
                                        class="badge bg-primary px-3 py-2 rounded-3 mb-2"><?php echo htmlspecialchars($property['type']); ?></span>
                                    <h1 class="h2 fw-bold mb-0"><?php echo htmlspecialchars($property['title']); ?></h1>
                                </div>
                                <div class="text-end">
                                    <div class="h3 fw-bold text-primary mb-0">
                                        $<?php echo number_format($property['price'], 0); ?></div>
                                    <div class="small text-muted">per month</div>
                                </div>
                            </div>

                            <div
                                class="location-box d-flex align-items-center gap-3 bg-light bg-opacity-50 p-3 rounded-4 mb-5 border border-white border-opacity-50">
                                <div class="icon-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 45px; height: 45px;">
                                    <i class="bi bi-geo-alt-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="small text-muted text-uppercase fw-bold ls-1">Location</div>
                                    <div class="fw-bold text-dark">
                                        <?php echo htmlspecialchars($property['location']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mb-5">
                                <div class="col-6">
                                    <div class="small text-muted text-uppercase fw-bold ls-1 mb-1">Status</div>
                                    <div class="text-success fw-bold d-flex align-items-center gap-2">
                                        <div class="pulse-green"></div> Available
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted text-uppercase fw-bold ls-1 mb-1">Listed On</div>
                                    <div class="fw-bold">
                                        <?php echo date('M d, Y', strtotime($property['created_at'])); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="contact-card p-4 rounded-4 bg-primary bg-gradient text-white mb-5 shadow-sm">
                                <h5 class="fw-bold mb-3">Interested in this property?</h5>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php if ($_SESSION['user_id'] != $property['owner_id']): ?>
                                        <p class="small opacity-75 mb-4">Interested in renting? Submit your application now or
                                            schedule a tour.</p>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-white fw-bold py-3" data-bs-toggle="modal"
                                                data-bs-target="#applyModal">
                                                <i class="bi bi-pencil-square me-2"></i> Apply for Rental
                                            </button>
                                            <button class="btn btn-outline-white fw-bold py-3">
                                                <i class="bi bi-calendar-check me-2"></i> Schedule a Tour
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <p class="small opacity-75 mb-4">You are the owner of this property.</p>
                                        <div class="d-grid gap-2">
                                            <a href="edit_property.php?id=<?php echo $property_id; ?>"
                                                class="btn btn-white fw-bold py-3">
                                                <i class="bi bi-pencil me-2"></i> Edit Property Details
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="small opacity-75 mb-4">Please sign in to your account to schedule a tour or
                                        send an inquiry to the owner.</p>
                                    <div class="d-grid gap-2">
                                        <a href="login.php?redirect_to=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                            class="btn btn-white fw-bold py-3">
                                            <i class="bi bi-box-arrow-in-right me-2"></i> Sign in to Rent
                                    <p class="small opacity-75 mb-4">Apply now to start your leasing process or schedule a
                                        tour to see the property in person.</p>
                                    <div class="d-grid gap-2">
                                        <a href="submit_application.php?property_id=<?php echo $property_id; ?>"
                                            class="btn btn-white fw-bold py-3">
                                            <i class="bi bi-file-earmark-text me-2"></i> Apply to Rent
                                        </a>
                                        <button class="btn btn-outline-white fw-bold py-3"><i
                                                class="bi bi-calendar-check me-2"></i> Schedule a Tour</button>
                                    </div>
                                <?php else: ?>
                                    <p class="small opacity-75 mb-4">Please sign in to your account to apply for this
                                        property or schedule a tour.</p>
                                    <div class="d-grid gap-2">
                                        <a href="login.php?redirect_to=<?php echo urlencode('submit_application.php?property_id=' . $property_id); ?>"
                                            class="btn btn-white fw-bold py-3">
                                            <i class="bi bi-box-arrow-in-right me-2"></i> Sign in to Apply
                                        </a>
                                        <a href="register.php" class="btn btn-outline-white fw-bold py-3">Create Free
                                            Account</a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="text-center">
                                <p class="small text-muted mb-0">Managed by <span
                                        class="fw-bold text-dark"><?php echo htmlspecialchars($property['owner_name']); ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-5 glass-panel border-0 border-top mt-5">
        <div class="container text-center">
            <p class="text-muted mb-0">© 2024 PRMS - Your trusted partner in finding the perfect home.</p>
        </div>
    </footer>

    <!-- Application Modal -->
    <div class="modal fade" id="applyModal" tabindex="-1" aria-labelledby="applyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="applyModalLabel">Rental Application</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="applicationForm">
                    <div class="modal-body p-4">
                        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">

                        <div class="mb-3">
                            <label class="form-label small text-uppercase fw-bold opacity-75">Full Name</label>
                            <input type="text" name="applicant_name"
                                class="form-control bg-dark text-white border-secondary py-2"
                                value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-uppercase fw-bold opacity-75">Email Address</label>
                            <input type="email" name="applicant_email"
                                class="form-control bg-dark text-white border-secondary py-2"
                                value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-uppercase fw-bold opacity-75">Phone Number</label>
                            <input type="tel" name="applicant_phone"
                                class="form-control bg-dark text-white border-secondary py-2"
                                placeholder="+1 (555) 000-0000" required>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small text-uppercase fw-bold opacity-75">Additional Message
                                (Optional)</label>
                            <textarea name="message" class="form-control bg-dark text-white border-secondary py-2"
                                rows="3" placeholder="Tell the owner a bit about yourself..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-link text-white text-decoration-none"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-pill" id="submitAppBtn">
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="statusToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('applicationForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const btn = document.getElementById('submitAppBtn');
            const originalBtnText = btn.innerHTML;
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => data[key] = value);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

            try {
                const response = await fetch('../../api/submit_application.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                const toast = new bootstrap.Toast(document.getElementById('statusToast'));
                const toastEl = document.getElementById('statusToast');
                const toastMsg = document.getElementById('toastMessage');

                if (result.success) {
                    toastEl.classList.remove('bg-danger');
                    toastEl.classList.add('bg-success');
                    toastMsg.innerText = result.message || 'Application submitted successfully!';
                    this.reset();
                    bootstrap.Modal.getInstance(document.getElementById('applyModal')).hide();
                } else {
                    toastEl.classList.remove('bg-success');
                    toastEl.classList.add('bg-danger');
                    const errorMsgs = result.errors ? (Array.isArray(result.errors) ? result.errors : Object.values(result.errors)) : [];
                    toastMsg.innerText = errorMsgs.length > 0 ? errorMsgs.join('\n') : (result.message || 'Error submitting application.');
                }
                toast.show();
            } catch (error) {
                console.error('Error:', error);
                const toast = new bootstrap.Toast(document.getElementById('statusToast'));
                const toastEl = document.getElementById('statusToast');
                const toastMsg = document.getElementById('toastMessage');
                toastEl.classList.remove('bg-success');
                toastEl.classList.add('bg-danger');
                toastMsg.innerText = 'An unexpected error occurred. Please try again.';
                toast.show();
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalBtnText;
            }
        });
    </script>
    <style>
        .detail-hero-img {
            height: 500px;
            object-fit: cover;
        }

        .detail-hero-placeholder {
            height: 500px;
        }

        .pulse-green {
            width: 10px;
            height: 10px;
            background: #198754;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
            }
        }

        .btn-white {
            background-color: #fff;
            color: var(--bs-primary);
            border: none;
        }

        .btn-white:hover {
            background-color: #f8f9fa;
            color: var(--bs-primary-dark);
        }

        .btn-outline-white {
            background-color: transparent;
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: #fff;
        }

        .btn-outline-white:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #fff;
            color: #fff;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-content.glass-panel {
            background: rgba(30, 30, 30, 0.9);
        }
    </style>
</body>

</html>