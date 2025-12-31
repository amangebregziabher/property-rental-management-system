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
            p.price as property_price, p.type as property_type, p.bedrooms, p.bathrooms
            FROM rental_applications ra
            INNER JOIN properties p ON ra.property_id = p.id
            WHERE ra.id = ? AND ra.user_id = ?";
} else {
    // Owner/Admin query
    $sql = "SELECT ra.*, p.title as property_title, p.location as property_location, 
            p.price as property_price, p.type as property_type, p.bedrooms, p.bathrooms
            FROM rental_applications ra
            INNER JOIN properties p ON ra.property_id = p.id
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
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-bg: #0f0f23;
            --card-bg: rgba(255, 255, 255, 0.05);
            --border-color: rgba(255, 255, 255, 0.1);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.3) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .main-content {
            position: relative;
            z-index: 1;
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        .detail-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            color: white;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: white;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .status-badge {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .status-approved {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .status-rejected {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
        }

        .applicant-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .applicant-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 20px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-success-gradient {
            background: var(--success-gradient);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }

        .btn-success-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .btn-danger-gradient {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-danger-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
            color: white;
        }

        .document-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .document-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .navbar {
            background: var(--card-bg) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: white;
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .applicant-header {
                flex-direction: column;
                text-align: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Rejection Modal Styling */
        .modal-content.glass-modal {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            color: white;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .rejection-reason-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            padding: 1rem;
        }

        .rejection-reason-input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #ff6b6b;
            color: white;
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 107, 0.25);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../../public/index.php">PRMS</a>
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
            <a href="tenant_applications_list.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Back to Applications
            </a>

            <!-- Applicant Header -->
            <div class="detail-card">
                <div class="applicant-header">
                    <div class="applicant-avatar-large">
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
                            class="status-badge status-<?php echo strtolower($application['status']); ?>">
                            <?php echo $application['status']; ?>
                        </span>
                        <p class="text-white-50 mt-2 mb-0">
                            <i class="bi bi-clock me-2"></i>
                            Applied on <?php echo date('F d, Y \a\t g:i A', strtotime($application['created_at'])); ?>
                        </p>
                    </div>
                    <?php if ($user_role !== 'tenant' && $application['status'] === 'Pending'): ?>
                        <div id="status-actions" class="d-flex gap-2">
                            <button class="btn btn-success-gradient" onclick="updateStatus('Approved')">
                                <i class="bi bi-check-lg me-2"></i>Approve
                            </button>
                            <button class="btn btn-danger-gradient" onclick="updateStatus('Rejected')">
                                <i class="bi bi-x-lg me-2"></i>Reject
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Property Information -->
            <div class="detail-card">
                <h2 class="section-title">
                    <i class="bi bi-house-door me-2"></i>Property Information
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Property Title</span>
                        <span class="info-value"><?php echo htmlspecialchars($application['property_title']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Location</span>
                        <span
                            class="info-value"><?php echo htmlspecialchars($application['property_location']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Property Type</span>
                        <span class="info-value"><?php echo htmlspecialchars($application['property_type']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Monthly Rent</span>
                        <span class="info-value">$<?php echo number_format($application['property_price'], 2); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Bedrooms</span>
                        <span class="info-value"><?php echo $application['bedrooms']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Bathrooms</span>
                        <span class="info-value"><?php echo $application['bathrooms']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="detail-card">
                <h2 class="section-title">
                    <i class="bi bi-person-lines-fill me-2"></i>Contact Information
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($application['applicant_email']); ?>"
                                class="text-white text-decoration-none">
                                <?php echo htmlspecialchars($application['applicant_email']); ?>
                            </a>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value">
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
            <div class="detail-card">
                <h2 class="section-title">
                    <i class="bi bi-file-earmark-text me-2"></i>Application Details
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Number of Occupants</span>
                        <span class="info-value"><?php echo $application['occupants'] ?? 'Not specified'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Desired Move-in Date</span>
                        <span class="info-value">
                            <?php echo $application['move_in_date'] ? date('F d, Y', strtotime($application['move_in_date'])) : 'Not specified'; ?>
                        </span>
                    </div>
                </div>

                <?php if ($application['message']): ?>
                    <div class="info-item mt-4">
                        <span class="info-label">Applicant Message</span>
                        <div class="info-value" style="white-space: pre-wrap;">
                            <?php echo htmlspecialchars($application['message']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Employment Information -->
            <?php if ($application['employer'] || $application['job_title'] || $application['monthly_income'] || $application['employment_status']): ?>
                <div class="detail-card">
                    <h2 class="section-title">
                        <i class="bi bi-briefcase me-2"></i>Employment Information
                    </h2>
                    <div class="info-grid">
                        <?php if ($application['employer']): ?>
                            <div class="info-item">
                                <span class="info-label">Employer</span>
                                <span class="info-value"><?php echo htmlspecialchars($application['employer']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($application['job_title']): ?>
                            <div class="info-item">
                                <span class="info-label">Job Title</span>
                                <span class="info-value"><?php echo htmlspecialchars($application['job_title']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($application['employment_status']): ?>
                            <div class="info-item">
                                <span class="info-label">Employment Status</span>
                                <span
                                    class="info-value"><?php echo htmlspecialchars($application['employment_status']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($application['monthly_income']): ?>
                            <div class="info-item">
                                <span class="info-label">Monthly Income</span>
                                <span class="info-value">$<?php echo number_format($application['monthly_income'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Documents -->
            <?php if ($application['id_document_path'] || $application['income_document_path']): ?>
                <div class="detail-card">
                    <h2 class="section-title">
                        <i class="bi bi-file-earmark-arrow-down me-2"></i>Uploaded Documents
                    </h2>
                    <div class="d-flex flex-wrap gap-3">
                        <?php if ($application['id_document_path']): ?>
                            <a href="../../storage/<?php echo htmlspecialchars($application['id_document_path']); ?>"
                                class="document-link" target="_blank">
                                <i class="bi bi-file-earmark-person"></i>
                                ID Document
                            </a>
                        <?php endif; ?>
                        <?php if ($application['income_document_path']): ?>
                            <a href="../../storage/<?php echo htmlspecialchars($application['income_document_path']); ?>"
                                class="document-link" target="_blank">
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
                        <textarea class="form-control rejection-reason-input" id="rejectionReason" rows="3"
                            placeholder="Explain why the application is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger-gradient" onclick="confirmRejection()">
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
    </script>
</body>

</html>