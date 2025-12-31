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

        .stats-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .stats-card:hover::before {
            opacity: 1;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-card.total .stats-icon {
            background: var(--info-gradient);
        }

        .stats-card.pending .stats-icon {
            background: var(--warning-gradient);
        }

        .stats-card.approved .stats-icon {
            background: var(--success-gradient);
        }

        .stats-card.rejected .stats-icon {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        }

        .filter-panel {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .application-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .application-card:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
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

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-color);
            color: white;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            color: white;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-select option {
            background: #1a1a2e;
            color: white;
        }

        .page-header {
            margin-bottom: 3rem;
        }

        .page-title {
            color: white;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        .applicant-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .applicant-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
        }

        .info-row i {
            color: rgba(255, 255, 255, 0.6);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-view {
            background: var(--info-gradient);
            color: white;
        }

        .btn-approve {
            background: var(--success-gradient);
            color: white;
        }

        .btn-reject {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .empty-state i {
            font-size: 5rem;
            opacity: 0.3;
            margin-bottom: 1rem;
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

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .stats-card {
                margin-bottom: 1rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
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
            <!-- Page Header -->
            <div class="page-header">
                <?php if ($user_role === 'tenant'): ?>
                    <h1 class="page-title">My Applications</h1>
                    <p class="page-subtitle">Track the status of your rental applications</p>
                <?php else: ?>
                    <h1 class="page-title">Tenant Applications</h1>
                    <p class="page-subtitle">Review and manage rental applications for your properties</p>
                <?php endif; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stats-card total">
                        <div class="stats-icon">
                            <i class="bi bi-file-earmark-text text-white"></i>
                        </div>
                        <h3 id="stat-total" class="text-white mb-0"><?php echo $stats['total']; ?></h3>
                        <p class="text-white-50 mb-0">Total Applications</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stats-card pending">
                        <div class="stats-icon">
                            <i class="bi bi-clock-history text-white"></i>
                        </div>
                        <h3 id="stat-pending" class="text-white mb-0"><?php echo $stats['pending']; ?></h3>
                        <p class="text-white-50 mb-0">Pending Review</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stats-card approved">
                        <div class="stats-icon">
                            <i class="bi bi-check-circle text-white"></i>
                        </div>
                        <h3 id="stat-approved" class="text-white mb-0"><?php echo $stats['approved']; ?></h3>
                        <p class="text-white-50 mb-0">Approved</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stats-card rejected">
                        <div class="stats-icon">
                            <i class="bi bi-x-circle text-white"></i>
                        </div>
                        <h3 id="stat-rejected" class="text-white mb-0"><?php echo $stats['rejected']; ?></h3>
                        <p class="text-white-50 mb-0">Rejected</p>
                    </div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel">
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
                <div class="empty-state">
                    <i class="bi bi-inbox d-block"></i>
                    <h3 class="text-white mb-2">No Applications Found</h3>
                    <p class="text-white-50">There are no applications matching your criteria.</p>
                    <a href="tenant_applications_list.php" class="btn btn-gradient mt-3">View All Applications</a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <div class="row g-0 align-items-center">
                            <!-- Image Section -->
                            <div class="col-md-3 col-lg-2">
                                <div class="position-relative h-100" style="min-height: 160px;">
                                    <?php if (!empty($app['image_path'])): ?>
                                        <img src="../../storage/<?php echo htmlspecialchars($app['image_path']); ?>"
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
                                                class="status-badge status-<?php echo strtolower($app['status']); ?>">
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
                                                            <?php echo htmlspecialchars($app['applicant_name']); ?></div>
                                                        <div class="small text-white-50">
                                                            <i class="bi bi-envelope me-1"></i>
                                                            <?php echo htmlspecialchars($app['applicant_email']); ?>
                                                            <span class="mx-2">•</span>
                                                            <i class="bi bi-telephone me-1"></i>
                                                            <?php echo htmlspecialchars($app['applicant_phone'] ?? 'N/A'); ?>
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
                                            <button class="btn btn-view w-100 mb-2"
                                                onclick="viewApplication(<?php echo $app['id']; ?>)">
                                                <i class="bi bi-eye me-2"></i>View Full Details
                                            </button>

                                            <div id="actions-container-<?php echo $app['id']; ?>" class="w-100">
                                                <?php if ($app['status'] === 'Pending'): ?>
                                                    <div class="d-flex gap-2 w-100">
                                                        <button class="btn btn-approve flex-grow-1"
                                                            onclick="updateStatus(<?php echo $app['id']; ?>, 'Approved')">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                        </button>
                                                        <button class="btn btn-reject flex-grow-1"
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