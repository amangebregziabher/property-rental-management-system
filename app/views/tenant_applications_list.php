<?php
session_start();

// Check if user is logged in and is an owner or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'owner' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$property_filter = $_GET['property'] ?? '';
$search = trim($_GET['search'] ?? '');

// Fetch applications from database with filters
$conn = get_db_connection();

// First, get all properties owned by this user (for filter dropdown)
$properties_sql = "SELECT id, title FROM properties WHERE owner_id = ? ORDER BY title ASC";
$properties_stmt = mysqli_prepare($conn, $properties_sql);
mysqli_stmt_bind_param($properties_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($properties_stmt);
$properties_result = mysqli_stmt_get_result($properties_stmt);
$user_properties = [];
while ($row = mysqli_fetch_assoc($properties_result)) {
    $user_properties[] = $row;
}

// Build applications query
$sql = "SELECT ra.*, p.title as property_title, p.location as property_location, p.price as property_price
        FROM rental_applications ra
        INNER JOIN properties p ON ra.property_id = p.id
        WHERE p.owner_id = ?";

$params = [$_SESSION['user_id']];
$types = "i";

if (!empty($status_filter)) {
    $sql .= " AND ra.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($property_filter) && is_numeric($property_filter)) {
    $sql .= " AND ra.property_id = ?";
    $params[] = $property_filter;
    $types .= "i";
}

if (!empty($search)) {
    $sql .= " AND (ra.applicant_name LIKE ? OR ra.applicant_email LIKE ? OR ra.applicant_phone LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$sql .= " ORDER BY ra.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$applications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $applications[] = $row;
}

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM rental_applications ra
    INNER JOIN properties p ON ra.property_id = p.id
    WHERE p.owner_id = ?";
$stats_stmt = mysqli_prepare($conn, $stats_sql);
mysqli_stmt_bind_param($stats_stmt, "i", $_SESSION['user_id']);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-color);
            color: white;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Tenant Applications</h1>
                <p class="page-subtitle">Review and manage rental applications for your properties</p>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stats-card total">
                        <div class="stats-icon">
                            <i class="bi bi-file-earmark-text text-white"></i>
                        </div>
                        <h3 class="text-white mb-0"><?php echo $stats['total']; ?></h3>
                        <p class="text-white-50 mb-0">Total Applications</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stats-card pending">
                        <div class="stats-icon">
                            <i class="bi bi-clock-history text-white"></i>
                        </div>
                        <h3 class="text-white mb-0"><?php echo $stats['pending']; ?></h3>
                        <p class="text-white-50 mb-0">Pending Review</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stats-card approved">
                        <div class="stats-icon">
                            <i class="bi bi-check-circle text-white"></i>
                        </div>
                        <h3 class="text-white mb-0"><?php echo $stats['approved']; ?></h3>
                        <p class="text-white-50 mb-0">Approved</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stats-card rejected">
                        <div class="stats-icon">
                            <i class="bi bi-x-circle text-white"></i>
                        </div>
                        <h3 class="text-white mb-0"><?php echo $stats['rejected']; ?></h3>
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
                                placeholder="Name, email, or phone..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white">Property</label>
                        <select name="property" class="form-select">
                            <option value="">All Properties</option>
                            <?php foreach ($user_properties as $prop): ?>
                                <option value="<?php echo $prop['id']; ?>" 
                                    <?php echo $property_filter == $prop['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prop['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="applicant-info">
                                    <div class="applicant-avatar">
                                        <?php echo strtoupper(substr($app['applicant_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h5 class="text-white mb-1"><?php echo htmlspecialchars($app['applicant_name']); ?></h5>
                                        <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                            <?php echo $app['status']; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <i class="bi bi-house-door"></i>
                                    <strong class="text-white"><?php echo htmlspecialchars($app['property_title']); ?></strong>
                                    <span class="text-white-50">- <?php echo htmlspecialchars($app['property_location']); ?></span>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <i class="bi bi-envelope"></i>
                                            <span><?php echo htmlspecialchars($app['applicant_email']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <i class="bi bi-telephone"></i>
                                            <span><?php echo htmlspecialchars($app['applicant_phone'] ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($app['move_in_date']): ?>
                                    <div class="info-row">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>Move-in Date: <?php echo date('M d, Y', strtotime($app['move_in_date'])); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($app['monthly_income']): ?>
                                    <div class="info-row">
                                        <i class="bi bi-cash-stack"></i>
                                        <span>Monthly Income: $<?php echo number_format($app['monthly_income'], 2); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="info-row">
                                    <i class="bi bi-clock"></i>
                                    <span>Applied: <?php echo date('M d, Y \a\t g:i A', strtotime($app['created_at'])); ?></span>
                                </div>
                            </div>

                            <div class="col-lg-4 d-flex align-items-center justify-content-lg-end mt-3 mt-lg-0">
                                <div class="action-buttons">
                                    <button class="btn btn-action btn-view" 
                                        onclick="viewApplication(<?php echo $app['id']; ?>)">
                                        <i class="bi bi-eye me-1"></i> View Details
                                    </button>
                                    <?php if ($app['status'] === 'Pending'): ?>
                                        <button class="btn btn-action btn-approve" 
                                            onclick="updateStatus(<?php echo $app['id']; ?>, 'Approved')">
                                            <i class="bi bi-check-lg me-1"></i> Approve
                                        </button>
                                        <button class="btn btn-action btn-reject" 
                                            onclick="updateStatus(<?php echo $app['id']; ?>, 'Rejected')">
                                            <i class="bi bi-x-lg me-1"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewApplication(id) {
            window.location.href = 'application_details.php?id=' + id;
        }

        function updateStatus(id, status) {
            if (confirm(`Are you sure you want to ${status.toLowerCase()} this application?`)) {
                const formData = new FormData();
                formData.append('application_id', id);
                formData.append('status', status);

                fetch('../controllers/update_application_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>

</html>
