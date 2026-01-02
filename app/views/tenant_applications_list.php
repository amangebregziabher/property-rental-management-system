<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_role = strtolower($_SESSION['user_role'] ?? 'tenant');

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../helpers/notification_helper.php';

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$property_filter = $_GET['property'] ?? '';
$search = trim($_GET['search'] ?? '');

// Fetch applications from database with filters
$conn = get_db_connection();

// First, get all properties owned by this user (for filter dropdown) - ONLY FOR OWNERS
$user_properties = [];
if ($user_role === 'owner') {
    $properties_sql = "SELECT id, title FROM properties WHERE owner_id = ? ORDER BY title ASC";
    $properties_stmt = mysqli_prepare($conn, $properties_sql);
    mysqli_stmt_bind_param($properties_stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($properties_stmt);
    $properties_result = mysqli_stmt_get_result($properties_stmt);
    while ($row = mysqli_fetch_assoc($properties_result)) {
        $user_properties[] = $row;
    }
}

// Pagination Configuration
$per_page = 6;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

// Build applications query with filters
$sql = "SELECT ra.*, p.title as property_title, p.location as property_location, p.price as property_price, pi.image_path
        FROM rental_applications ra
        INNER JOIN properties p ON ra.property_id = p.id 
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1";

$count_sql = "SELECT COUNT(*) as total_count
              FROM rental_applications ra
              INNER JOIN properties p ON ra.property_id = p.id";

$params = [];
$types = "";
$where_clauses = [];

if ($user_role === 'tenant') {
    $where_clauses[] = "ra.user_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= "i";
} elseif ($user_role === 'owner') {
    $where_clauses[] = "p.owner_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= "i";
}

if (!empty($status_filter)) {
    $where_clauses[] = "ra.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($property_filter) && is_numeric($property_filter)) {
    $where_clauses[] = "ra.property_id = ?";
    $params[] = $property_filter;
    $types .= "i";
}

if (!empty($search)) {
    $where_clauses[] = "(ra.applicant_name LIKE ? OR ra.applicant_email LIKE ? OR ra.applicant_phone LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : " WHERE 1=1 ";
$sql .= $where_sql;
$count_sql .= $where_sql;

// Get total count for pagination
$count_stmt = mysqli_prepare($conn, $count_sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)['total_count'];
$total_pages = ceil($total_records / $per_page);

// Add sorting and pagination to main query
$sql .= " ORDER BY ra.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$applications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $applications[] = $row;
}

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN ra.status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN ra.status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN ra.status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM rental_applications ra
    INNER JOIN properties p ON ra.property_id = p.id ";

if ($user_role === 'tenant') {
    $stats_sql .= " WHERE ra.user_id = ? ";
} elseif ($user_role === 'owner') {
    $stats_sql .= " WHERE p.owner_id = ? ";
}

$stats_stmt = mysqli_prepare($conn, $stats_sql);
if ($user_role === 'tenant' || $user_role === 'owner') {
    mysqli_stmt_bind_param($stats_stmt, "i", $_SESSION['user_id']);
}
mysqli_stmt_execute($stats_stmt);
$stats_result = mysqli_stmt_get_result($stats_stmt);
$stats = mysqli_fetch_assoc($stats_result);

close_db_connection($conn);

// Get pending applications count for notification badge
$pending_count = 0;
if ($user_role === 'owner' || $user_role === 'admin') {
    // Re-open/use connection since stats logic might have closed it or we need fresh for simple helper
    $conn = get_db_connection();
    $pending_count = get_pending_applications_count($conn, $_SESSION['user_id']);
    close_db_connection($conn);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Applications - PRMS</title>
    <meta name="description" content="Manage and review tenant rental applications for your properties">
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
                    <?php if ($user_role === 'owner' || $user_role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="property_list.php">My Properties</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="tenant_view.php">Find Home</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="tenant_applications_list.php">
                            Applications
                            <?php if ($pending_count > 0): ?>
                                <span class="badge bg-danger rounded-circle notification-badge"><?php echo $pending_count; ?></span>
                            <?php endif; ?>
                        </a>
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
            <!-- Page Header -->
            <div class="mb-5 text-center text-md-start">
                <?php if ($user_role === 'tenant'): ?>
                    <h1 class="display-5 fw-bold text-white mb-2">My Applications</h1>
                    <p class="lead text-white-50">Track the status of your rental applications</p>
                <?php else: ?>
                    <h1 class="display-5 fw-bold text-white mb-2">Tenant Applications</h1>
                    <p class="lead text-white-50">Review and manage rental applications for your properties</p>
                <?php endif; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="glass-panel p-4 h-100 border-start border-4 border-info">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3 p-3 bg-white bg-opacity-10 fs-2">
                            <i class="bi bi-file-earmark-text text-white"></i>
                        </div>
                        <h3 id="stat-total" class="text-white mb-0"><?php echo $stats['total']; ?></h3>
                        <p class="text-white-50 mb-0">Total Applications</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="glass-panel p-4 h-100 border-start border-4 border-warning">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3 p-3 bg-white bg-opacity-10 fs-2">
                            <i class="bi bi-clock-history text-white"></i>
                        </div>
                        <h3 id="stat-pending" class="text-white mb-0"><?php echo $stats['pending']; ?></h3>
                        <p class="text-white-50 mb-0">Pending Review</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="glass-panel p-4 h-100 border-start border-4 border-success">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3 p-3 bg-white bg-opacity-10 fs-2">
                            <i class="bi bi-check-circle text-white"></i>
                        </div>
                        <h3 id="stat-approved" class="text-white mb-0"><?php echo $stats['approved']; ?></h3>
                        <p class="text-white-50 mb-0">Approved</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="glass-panel p-4 h-100 border-start border-4 border-danger">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3 p-3 bg-white bg-opacity-10 fs-2">
                            <i class="bi bi-x-circle text-white"></i>
                        </div>
                        <h3 id="stat-rejected" class="text-white mb-0"><?php echo $stats['rejected']; ?></h3>
                        <p class="text-white-50 mb-0">Rejected</p>
                    </div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="glass-panel p-4 mb-4">
                <form action="tenant_applications_list.php" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-white">Search Applicant</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                placeholder="Name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>
                                Approved</option>
                            <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>
                                Rejected</option>
                        </select>
                    </div>
                    <?php if ($user_role !== 'tenant'): ?>
                        <div class="col-md-3">
                            <label class="form-label text-white">Property</label>
                            <select name="property" class="form-select">
                                <option value="">All Properties</option>
                                <?php foreach ($user_properties as $prop): ?>
                                    <option value="<?php echo $prop['id']; ?>" <?php echo $property_filter == $prop['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prop['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-gradient w-100">
                            <i class="bi bi-funnel me-2"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Applications List -->
            <?php if (empty($applications)): ?>
                <div class="glass-panel text-center p-5 text-white-50">
                    <i class="bi bi-inbox d-block display-1 mb-3 opacity-25"></i>
                    <h3 class="text-white mb-2">No Applications Found</h3>
                    <p class="text-white-50">There are no applications matching your criteria.</p>
                    <a href="tenant_applications_list.php" class="btn btn-primary rounded-pill mt-3 px-4">View All Applications</a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div class="glass-panel p-4 mb-4 position-relative">
                        <div class="row g-0 align-items-center">
                            <!-- Image Section -->
                            <div class="col-md-3 col-lg-2">
                                <div class="position-relative h-100" style="min-height: 160px;">
                                    <?php if (!empty($app['image_path'])): ?>
                                        <img src="../../images/<?php echo htmlspecialchars($app['image_path']); ?>"
                                            class="img-fluid rounded-start h-100 w-100 object-fit-cover position-absolute top-0 start-0"
                                            alt="<?php echo htmlspecialchars($app['property_title']); ?>"
                                            style="border-radius: 20px;">
                                    <?php else: ?>
                                        <div class="h-100 w-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 rounded-4"
                                            style="border-radius: 20px;">
                                            <i class="bi bi-house-door fs-1 text-white opacity-50"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Content Section -->
                            <div class="col-md-9 col-lg-10 ps-md-4 py-3 py-md-0">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <h4 class="text-white fw-bold mb-0">
                                                <?php echo htmlspecialchars($app['property_title']); ?>
                                            </h4>
                                            <span id="status-badge-<?php echo $app['id']; ?>"
                                                class="badge rounded-pill fs-6 px-3 py-2 <?php echo $app['status'] === 'Pending' ? 'bg-warning text-dark' : ($app['status'] === 'Approved' ? 'bg-success' : 'bg-danger'); ?>">
                                                <?php echo $app['status']; ?>
                                            </span>
                                        </div>

                                        <div class="d-flex flex-wrap gap-3 text-white-50 mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                                <?php echo htmlspecialchars($app['property_location']); ?>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-cash-stack text-success"></i>
                                                $<?php echo number_format($app['property_price'], 2); ?>/mo
                                            </div>
                                        </div>

                                        <!-- Submission Date (Always visible) -->
                                        <div class="d-flex align-items-center gap-2 text-white-50 small mb-2">
                                            <i class="bi bi-clock"></i>
                                            Applied on <?php echo date('M d, Y \a\t g:i A', strtotime($app['created_at'])); ?>
                                        </div>

                                        <!-- Tenant vs Owner Specific Info -->
                                        <?php if ($user_role !== 'tenant'): ?>
                                            <div class="p-3 rounded-3 bg-white bg-opacity-10">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="applicant-avatar-small rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                                        style="width: 40px; height: 40px;">
                                                        <?php echo strtoupper(substr($app['applicant_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="text-white fw-bold">
                                                            <?php echo htmlspecialchars($app['applicant_name']); ?>
                                                        </div>
                                                        <div class="small text-white-50">
                                                            <i class="bi bi-envelope me-1"></i>
                                                            <?php echo htmlspecialchars($app['applicant_email']); ?>
                                                            <span class="mx-2">•</span>
                                                            <i class="bi bi-telephone me-1"></i>
                                                            <?php echo htmlspecialchars($app['applicant_phone'] ?? 'N/A'); ?>
                                                        </div>
                                                        <div class="small text-white-50 mt-1">
                                                            <i class="bi bi-calendar3 me-1"></i>
                                                            Applied: <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                                                            <i class="bi bi-clock ms-2 me-1"></i>
                                                            <?php echo date('g:i A', strtotime($app['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Actions Section -->
                                    <div
                                        class="col-lg-4 d-flex flex-column justify-content-center align-items-lg-end mt-3 mt-lg-0 gap-2">
                                        <?php if ($user_role !== 'tenant'): ?>
                                            <button class="btn btn-info w-100 mb-2 rounded-pill text-white"
                                                onclick="viewApplication(<?php echo $app['id']; ?>)">
                                                <i class="bi bi-eye me-2"></i>View Full Details
                                            </button>

                                            <div id="actions-container-<?php echo $app['id']; ?>" class="w-100">
                                                <?php if ($app['status'] === 'Pending'): ?>
                                                    <div class="d-flex gap-2 w-100">
                                                        <button class="btn btn-success flex-grow-1 rounded-pill"
                                                            onclick="updateStatus(<?php echo $app['id']; ?>, 'Approved')">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                        </button>
                                                        <button class="btn btn-danger flex-grow-1 rounded-pill"
                                                            onclick="updateStatus(<?php echo $app['id']; ?>, 'Rejected')">
                                                            <i class="bi bi-x-lg"></i> Reject
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <!-- Tenant View Status Only -->
                                            <?php if ($app['status'] === 'Approved'): ?>
                                                <div class="text-end">
                                                    <div class="text-success fw-bold mb-1"><i
                                                            class="bi bi-check-circle-fill me-2"></i>Congratulations!</div>
                                                    <div class="small text-white-50">Owner will contact you shortly.</div>
                                                </div>
                                            <?php elseif ($app['status'] === 'Rejected'): ?>
                                                <div class="text-end">
                                                    <div class="text-danger fw-bold mb-1"><i
                                                            class="bi bi-x-circle-fill me-2"></i>Application Declined</div>
                                                    <div class="small text-white-50">Best of luck with your search.</div>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-end">
                                                    <div class="text-warning fw-bold mb-1"><i
                                                            class="bi bi-hourglass-split me-2"></i>Under Review</div>
                                                    <div class="small text-white-50">We'll notify you of updates.</div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination UI -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Application pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php 
                            $query_params = $_GET;
                            unset($query_params['page']);
                            $base_query = http_build_query($query_params);
                            $base_url = "tenant_applications_list.php?" . ($base_query ? $base_query . "&" : "");
                            ?>

                            <!-- Previous Page -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link bg-dark bg-opacity-25 border-secondary text-white rounded-start-pill px-3" 
                                   href="<?php echo $base_url . 'page=' . ($page - 1); ?>" tabindex="-1">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link <?php echo $page == $i ? 'bg-primary border-primary' : 'bg-dark bg-opacity-25 border-secondary text-white'; ?>" 
                                       href="<?php echo $base_url . 'page=' . $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link bg-dark bg-opacity-25 border-secondary text-white rounded-end-pill px-3" 
                                   href="<?php echo $base_url . 'page=' . ($page + 1); ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
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
        let currentStatusUpdateId = null;

        document.addEventListener('DOMContentLoaded', function () {
            rejectionModal = new bootstrap.Modal(document.getElementById('rejectionModal'));
        });

        function viewApplication(id) {
            window.location.href = 'application_details.php?id=' + id;
        }

        function updateStatus(id, status) {
            if (status === 'Rejected') {
                currentStatusUpdateId = id;
                rejectionModal.show();
                return;
            }

            if (confirm(`Are you sure you want to ${status.toLowerCase()} this application?`)) {
                submitStatusChange(id, status);
            }
        }

        function confirmRejection() {
            if (!currentStatusUpdateId) return;
            const reason = document.getElementById('rejectionReason').value;
            submitStatusChange(currentStatusUpdateId, 'Rejected', reason);
        }

        function submitStatusChange(id, status, reason = '') {
            const formData = new FormData();
            formData.append('application_id', id);
            formData.append('status', status);
            formData.append('reason', reason);

            fetch('../controllers/update_application_status.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI in real-time
                        if (rejectionModal) rejectionModal.hide();

                        // 1. Update Badge
                        const badge = document.getElementById(`status-badge-${id}`);
                        if (badge) {
                            badge.textContent = status;
                            badge.className = `status-badge status-${status.toLowerCase()}`;
                        }

                        // 2. Update Actions Container (Remove buttons and maybe show a message)
                        const actionsContainer = document.getElementById(`actions-container-${id}`);
                        if (actionsContainer) {
                            actionsContainer.style.transition = 'all 0.5s ease';
                            actionsContainer.style.opacity = '0';
                            setTimeout(() => {
                                actionsContainer.innerHTML = `
                                <div class="text-end animate__animated animate__fadeIn">
                                    <div class="text-${status === 'Approved' ? 'success' : 'danger'} fw-bold mb-1">
                                        <i class="bi bi-${status === 'Approved' ? 'check-circle' : 'x-circle'}-fill me-2"></i>
                                        Application ${status}
                                    </div>
                                    <div class="small text-white-50">Status updated just now</div>
                                </div>
                            `;
                                actionsContainer.style.opacity = '1';
                            }, 500);
                        }

                        // 3. Update Stats Cards
                        const pendingStat = document.getElementById('stat-pending');
                        const targetStat = document.getElementById(`stat-${status.toLowerCase()}`);

                        if (pendingStat) {
                            pendingStat.textContent = Math.max(0, parseInt(pendingStat.textContent) - 1);
                        }
                        if (targetStat) {
                            targetStat.textContent = parseInt(targetStat.textContent) + 1;
                        }

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