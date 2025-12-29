<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$type_filter = $_GET['type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Fetch properties from database with filters
$conn = get_db_connection();

$sql = "SELECT p.*, 
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
        FROM properties p 
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
    $sql .= " AND p.type = ?";
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
                            <a class="nav-link" href="my_applications.php">My Applications</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>