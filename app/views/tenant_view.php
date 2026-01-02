<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../helpers/notification_helper.php';

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$type_filter = $_GET['type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Fetch properties from database with filters
$conn = get_db_connection();

$sql = "SELECT p.*, c.name as type,
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_main DESC, id ASC LIMIT 1) as main_image
        FROM properties p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'Available'";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.location LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if (!empty($type_filter)) {
    $sql .= " AND c.name = ?";
    $params[] = $type_filter;
    $types .= "s";
}

if (!empty($min_price) && is_numeric($min_price)) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if (!empty($max_price) && is_numeric($max_price)) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$properties = [];
while ($row = mysqli_fetch_assoc($result)) {
    $properties[] = $row;
}

// Get pending applications count for notification badge
$pending_count = 0;
if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] === 'owner' || $_SESSION['user_role'] === 'admin')) {
    // We already have a connection $conn
    $pending_count = get_pending_applications_count($conn, $_SESSION['user_id']);
}

close_db_connection($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Perfect Home - PRMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?php echo time(); ?>">
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
                        <a class="nav-link active" href="tenant_view.php">Find Home</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="tenant_applications_list.php">
                            My Applications
                            <?php if ($_SESSION['user_role'] === 'tenant' && isset($pending_count) && $pending_count > 0): ?>
                                <!-- Tenants don't usually need owner notifications, but the user asked for "the owner" -->
                            <?php endif; ?>
                            <?php if (($_SESSION['user_role'] === 'owner' || $_SESSION['user_role'] === 'admin') && $pending_count > 0): ?>
                                <span class="badge bg-danger rounded-circle notification-badge"><?php echo $pending_count; ?></span>
                            <?php endif; ?>
                        </a>
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
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">My Profile</a></li>
                                <?php if ($_SESSION['user_role'] === 'owner' || $_SESSION['user_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="property_list.php">Owner Dashboard</a></li>
                                    <li><a class="dropdown-item" href="manage_applications.php">Manage Applications</a></li>
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

    <!-- Search Section -->
    <header class="page-header py-5 bg-gradient-primary text-white position-relative overflow-hidden mb-5">
        <div class="container position-relative z-1">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3">Discover Your Next Chapter</h1>
                    <p class="lead opacity-75">Browse premium listings and find the space that's just right for you.</p>
                </div>
                <div class="col-lg-6">
                    <div class="glass-panel p-4 rounded-4 shadow-lg">
                        <form action="tenant_view.php" method="GET" class="row g-3">
                            <div class="col-12 text-center text-lg-start mb-4">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="d-inline-block bg-white bg-opacity-10 p-2 rounded-4 backdrop-blur">
                                        <a href="tenant_applications_list.php" class="btn btn-outline-light d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-text"></i> View My Applications
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control border-0 py-3"
                                        placeholder="Search by location or property title..."
                                        value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select name="type" class="form-select border-0 py-2">
                                    <option value="">All Types</option>
                                    <?php
                                    $all_types = ['Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse'];
                                    foreach ($all_types as $t) {
                                        $selected = ($type_filter === $t) ? 'selected' : '';
                                        echo "<option value=\"$t\" $selected>$t</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-blur"></div>
    </header>

    <div class="container mb-5">
        <div class="row g-4">
            <?php if (empty($properties)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-house-exclamation display-1 text-muted opacity-25 mb-3 d-block"></i>
                    <h3 class="text-secondary">No properties found matching your criteria.</h3>
                    <a href="tenant_view.php" class="btn btn-link">View all available properties</a>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 glass-panel property-card hover-up overflow-hidden shadow-sm">
                            <div class="position-relative">
                                <?php if ($property['main_image']): ?>
                                    <img src="../../images/<?php echo htmlspecialchars($property['main_image']); ?>"
                                        class="card-img-top property-thumb"
                                        alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <?php else: ?>
                                    <div
                                        class="card-img-top property-thumb bg-light d-flex align-items-center justify-content-center">
                                        <i class="bi bi-image text-muted display-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div
                                    class="property-badge position-absolute top-0 end-0 m-3 px-3 py-1 bg-primary text-white rounded-pill shadow-sm fw-bold">
                                    $<?php echo number_format($property['price'], 0); ?>/mo
                                </div>
                                <div
                                    class="type-badge position-absolute bottom-0 start-0 m-3 px-3 py-1 bg-white bg-opacity-75 text-dark rounded-3 shadow-sm small fw-bold">
                                    <?php echo htmlspecialchars($property['type']); ?>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($property['title']); ?></h5>
                                <p class="card-text text-muted small d-flex align-items-center gap-2 mb-4">
                                    <i class="bi bi-geo-alt-fill text-primary"></i>
                                    <?php echo htmlspecialchars($property['location']); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <span class="text-secondary small">Available Now</span>
                                    <a href="property_details.php?id=<?php echo $property['id']; ?>"
                                        class="btn btn-outline-primary rounded-pill px-4">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-5 glass-panel border-0 border-top mt-5">
        <div class="container text-center">
            <p class="text-muted mb-0">© 2024 PRMS - Your trusted partner in finding the perfect home.</p>
        </div>
    </footer>

    <!-- Profile Update Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content glass-panel border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Update Tenant Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($_SESSION['form_errors'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['form_errors'] as $field => $errors): ?>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                            <?php unset($_SESSION['form_errors']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                            <?php unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="../controllers/tenant_controller.php?action=update_profile" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Employer Name</label>
                            <input type="text" name="employer_name" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['form_data']['employer_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['form_data']['job_title'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monthly Income ($)</label>
                            <input type="number" name="monthly_income" class="form-control" required min="0" step="0.01" value="<?php echo htmlspecialchars($_SESSION['form_data']['monthly_income'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['form_data']['emergency_contact_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Emergency Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['form_data']['emergency_contact_phone'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Profile</button>
                    </form>
                    <?php unset($_SESSION['form_data']); ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-open modal if there are errors or success messages
        <?php if (isset($_SESSION['form_errors']) || isset($_SESSION['success_message'])): ?>
            var myModal = new bootstrap.Modal(document.getElementById('profileModal'));
            myModal.show();
        <?php endif; ?>
    </script>
</body>

</html>