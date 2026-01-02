<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_role = $_SESSION['user_role'] ?? 'tenant';

// Get application ID
$application_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$application_id) {
    header('Location: tenant_applications_list.php');
    exit();
}

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

$conn = get_db_connection();


if ($user_role === 'tenant') {
    $sql = "SELECT ra.*, p.title as property_title, p.location as property_location, 
            p.price as property_price, c.name as property_type, p.bedrooms, p.bathrooms
            FROM rental_applications ra
            INNER JOIN properties p ON ra.property_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE ra.id = ? AND ra.user_id = ?";
} else {
    // Owner/Admin query
    $sql = "SELECT ra.*, p.title as property_title, p.location as property_location, 
            p.price as property_price, c.name as property_type, p.bedrooms, p.bathrooms
            FROM rental_applications ra
            INNER JOIN properties p ON ra.property_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE ra.id = ? " . ($user_role !== 'admin' ? "AND p.owner_id = ?" : "");
}

$stmt = mysqli_prepare($conn, $sql);

if ($user_role === 'admin') {
    mysqli_stmt_bind_param($stmt, "i", $application_id);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $application_id, $_SESSION['user_id']);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    close_db_connection($conn);
    header('Location: tenant_applications_list.php');
    exit();
}

$application = mysqli_fetch_assoc($result);
close_db_connection($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - <?php echo htmlspecialchars($application['applicant_name']); ?> - PRMS</title>
    <meta name="description" content="View detailed information about tenant rental application">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?php echo time(); ?>">





</head>

<body class="beautified-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top">
        <div class="container-fluid mx-4">
            <a class="navbar-brand text-gradient fs-3 fw-bold" href="../../public/index.php">PRMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="property_list.php">My Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="tenant_applications_list.php">Applications</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown"
                            role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5"></i>
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item text-danger"
                                    href="../controllers/auth_controller.php?action=logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <a href="tenant_applications_list.php" class="text-white-50 text-decoration-none mb-4 d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to Applications
            </a>

            <!-- Applicant Header -->
            <div class="glass-panel p-4 mb-4">
                <div class="d-flex align-items-center gap-4 mb-4 flex-wrap text-center text-md-start">
                    <div class="bg-primary bg-gradient rounded-4 d-flex align-items-center justify-content-center text-white fw-bold display-4 shadow" style="width: 100px; height: 100px;">
                        <?php echo strtoupper(substr($application['applicant_name'], 0, 1)); ?>
                    </div>
                    <div class="flex-grow-1">
                        <?php if ($user_role === 'tenant'): ?>
                            <h1 class="text-white mb-2">My Application for
                                <?php echo htmlspecialchars($application['property_title']); ?></h1>
                        <?php else: ?>
                            <h1 class="text-white mb-2"><?php echo htmlspecialchars($application['applicant_name']); ?></h1>
                        <?php endif; ?>
                        <span id="application-status-badge"
                            class="badge rounded-pill fs-6 px-3 py-2 <?php echo $application['status'] === 'Pending' ? 'bg-warning text-dark' : ($application['status'] === 'Approved' ? 'bg-success' : 'bg-danger'); ?>">
                            <?php echo $application['status']; ?>
                        </span>
                        <p class="text-white-50 mt-2 mb-0">
                            <i class="bi bi-clock me-2"></i>
                            Applied on <?php echo date('F d, Y \a\t g:i A', strtotime($application['created_at'])); ?>
                        </p>
                    </div>
                    <?php if ($user_role !== 'tenant' && $application['status'] === 'Pending'): ?>
                        <div id="status-actions" class="d-flex gap-2">
                            <button class="btn btn-success rounded-pill px-4" onclick="updateStatus('Approved')">
                                <i class="bi bi-check-lg me-2"></i>Approve
                            </button>
                            <button class="btn btn-danger rounded-pill px-4" onclick="updateStatus('Rejected')">
                                <i class="bi bi-x-lg me-2"></i>Reject
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Property Information -->
            <div class="glass-panel p-4 mb-4">
                <h2 class="fs-4 fw-bold border-bottom border-white border-opacity-10 pb-3 mb-4 text-white">
                    <i class="bi bi-house-door me-2"></i>Property Information
                </h2>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Property Title</span>
                        <span class="d-block fs-5 text-white fw-medium"><?php echo htmlspecialchars($application['property_title']); ?></span>
                    </div>
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Location</span>
                        <span
                            class="d-block fs-5 text-white fw-medium"><?php echo htmlspecialchars($application['property_location']); ?></span>
                    </div>
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Property Type</span>
                        <span class="d-block fs-5 text-white fw-medium"><?php echo htmlspecialchars($application['property_type']); ?></span>
                    </div>
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Monthly Rent</span>
                        <span class="d-block fs-5 text-white fw-medium">$<?php echo number_format($application['property_price'], 2); ?></span>
                    </div>
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Bedrooms</span>
                        <span class="d-block fs-5 text-white fw-medium"><?php echo $application['bedrooms']; ?></span>
                    </div>
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Bathrooms</span>
                        <span class="d-block fs-5 text-white fw-medium"><?php echo $application['bathrooms']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="glass-panel p-4 mb-4">
                <h2 class="fs-4 fw-bold border-bottom border-white border-opacity-10 pb-3 mb-4 text-white">
                    <i class="bi bi-person-lines-fill me-2"></i>Contact Information
                </h2>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Email Address</span>
                        <span class="d-block fs-5 text-white fw-medium">
                            <a href="mailto:<?php echo htmlspecialchars($application['applicant_email']); ?>"
                                class="text-white text-decoration-none">
                                <?php echo htmlspecialchars($application['applicant_email']); ?>
                            </a>
                        </span>
                    </div>
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Phone Number</span>
                        <span class="d-block fs-5 text-white fw-medium">
                            <?php if ($application['applicant_phone']): ?>
                                <a href="tel:<?php echo htmlspecialchars($application['applicant_phone']); ?>"
                                    class="text-white text-decoration-none">
                                    <?php echo htmlspecialchars($application['applicant_phone']); ?>
                                </a>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Application Details -->
            <div class="glass-panel p-4 mb-4">
                <h2 class="fs-4 fw-bold border-bottom border-white border-opacity-10 pb-3 mb-4 text-white">
                    <i class="bi bi-file-earmark-text me-2"></i>Application Details
                </h2>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Number of Occupants</span>
                        <span class="d-block fs-5 text-white fw-medium"><?php echo $application['occupants'] ?? 'Not specified'; ?></span>
                    </div>
                    <div class="col">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Desired Move-in Date</span>
                        <span class="d-block fs-5 text-white fw-medium">
                            <?php echo $application['move_in_date'] ? date('F d, Y', strtotime($application['move_in_date'])) : 'Not specified'; ?>
                        </span>
                    </div>
                </div>

                <?php if ($application['message']): ?>
                    <div class="info-item mt-4">
                        <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Applicant Message</span>
                        <div class="d-block fs-5 text-white fw-medium" style="white-space: pre-wrap;">
                            <?php echo htmlspecialchars($application['message']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Employment Information -->
            <?php if ($application['employer'] || $application['job_title'] || $application['monthly_income'] || $application['employment_status']): ?>
                <div class="glass-panel p-4 mb-4">
                    <h2 class="fs-4 fw-bold border-bottom border-white border-opacity-10 pb-3 mb-4 text-white">
                        <i class="bi bi-briefcase me-2"></i>Employment Information
                    </h2>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php if ($application['employer']): ?>
                            <div class="col">
                                <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Employer</span>
                                <span class="d-block fs-5 text-white fw-medium"><?php echo htmlspecialchars($application['employer']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($application['job_title']): ?>
                            <div class="col">
                                <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Job Title</span>
                                <span class="d-block fs-5 text-white fw-medium"><?php echo htmlspecialchars($application['job_title']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($application['employment_status']): ?>
                            <div class="col">
                                <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Employment Status</span>
                                <span
                                    class="d-block fs-5 text-white fw-medium"><?php echo htmlspecialchars($application['employment_status']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($application['monthly_income']): ?>
                            <div class="col">
                                <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Monthly Income</span>
                                <span class="d-block fs-5 text-white fw-medium">$<?php echo number_format($application['monthly_income'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Documents -->
            <?php if ($application['id_document_path'] || $application['income_document_path']): ?>
                <div class="glass-panel p-4 mb-4">
                    <h2 class="fs-4 fw-bold border-bottom border-white border-opacity-10 pb-3 mb-4 text-white">
                        <i class="bi bi-file-earmark-arrow-down me-2"></i>Uploaded Documents
                    </h2>
                    <div class="d-flex flex-wrap gap-3">
                        <?php if ($application['id_document_path']): ?>
                            <a href="#" class="btn btn-outline-light d-inline-flex align-items-center gap-2 py-3 px-4 rounded-3 text-decoration-none" 
                               onclick="viewDocument('../../<?php echo htmlspecialchars($application['id_document_path']); ?>', 'ID Document'); return false;">
                                <i class="bi bi-file-earmark-person"></i>
                                ID Document
                            </a>
                        <?php endif; ?>
                        <?php if ($application['income_document_path']): ?>
                            <a href="#" class="btn btn-outline-light d-inline-flex align-items-center gap-2 py-3 px-4 rounded-3 text-decoration-none"
                               onclick="viewDocument('../../<?php echo htmlspecialchars($application['income_document_path']); ?>', 'Income Proof'); return false;">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                                Income Proof
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rejection Confirmation Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectionModalLabel">Confirm Rejection</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this application? This action cannot be undone.</p>
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Reason for Rejection (Optional)</label>
                        <textarea class="form-control" id="rejectionReason" rows="3"
                            placeholder="Explain why the application is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" onclick="confirmRejection()">
                        Confirm Rejection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let rejectionModal = null;

        document.addEventListener('DOMContentLoaded', function () {
            rejectionModal = new bootstrap.Modal(document.getElementById('rejectionModal'));
        });

        function updateStatus(status) {
            if (status === 'Rejected') {
                rejectionModal.show();
                return;
            }

            if (confirm(`Are you sure you want to ${status.toLowerCase()} this application?`)) {
                submitStatusChange(status);
            }
        }

        function confirmRejection() {
            const reason = document.getElementById('rejectionReason').value;
            submitStatusChange('Rejected', reason);
        }

        function submitStatusChange(status, reason = '') {
            const formData = new FormData();
            formData.append('application_id', <?php echo $application_id; ?>);
            formData.append('status', status);
            formData.append('reason', reason);

            fetch('../controllers/update_application_status.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Real-time UI Update
                        if (rejectionModal) rejectionModal.hide();

                        // Update Badge
                        const badge = document.getElementById('application-status-badge');
                        if (badge) {
                            badge.textContent = status;
                            badge.className = 'status-badge status-' + status.toLowerCase();
                        }

                        // Remove Actions
                        const actions = document.getElementById('status-actions');
                        if (actions) {
                            actions.style.transition = 'all 0.5s ease';
                            actions.style.opacity = '0';
                            setTimeout(() => actions.remove(), 500);
                        }

                        // Show success message (using a simple alert for now, but UI is updated)
                        console.log(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                    console.error('Error:', error);
                });
        }

        function viewDocument(path, title) {
            const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
            const container = document.getElementById('documentPreviewContainer');
            document.getElementById('documentPreviewTitle').textContent = title;
            
            // Determine file type
            const isImage = path.match(/\.(jpeg|jpg|gif|png)$/) != null;
            
            if (isImage) {
                container.innerHTML = `<img src="${path}" class="img-fluid rounded shadow-sm" style="max-height: 80vh;">`;
            } else {
                // Assume PDF or other browser-renderable format
                container.innerHTML = `<iframe src="${path}" class="w-100 rounded shadow-sm" style="height: 80vh; border: none;"></iframe>`;
            }
            
            modal.show();
        }
    </script>
    
    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content glass-modal border-0">
                <div class="modal-header border-bottom border-white border-opacity-10">
                    <h5 class="modal-title" id="documentPreviewTitle">Document Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center" id="documentPreviewContainer">
                    <!-- Content injected via JS -->
                </div>
            </div>
        </div>
    </div>
</body>

</html>