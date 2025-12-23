<?php
session_start();

// Access control: Only owners and admins can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'owner' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Fetch properties from database
$conn = get_db_connection();
$sql = "SELECT p.*, u.name as owner_name,
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
        FROM properties p 
        LEFT JOIN users u ON p.owner_id = u.id 
        ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $sql);
$properties = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $properties[] = $row;
    }
}

close_db_connection($conn);

// Success/Error messages from session
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Inventory - PRMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="beautified-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top">
        <div class="container-fluid mx-4">
            <a class="navbar-brand text-gradient fs-3" href="../../public/index.php">PRMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="tenant_view.php">Browse Listing</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 active" href="#" id="ownerDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end glass-panel border-0 shadow-sm mt-2">
                            <li><a class="dropdown-item" href="property_list.php">Owner Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../controllers/auth_controller.php?action=logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Dashboard Header -->
        <div class="row mb-5 animate-up">
            <div class="col-md-6 text-center text-md-start">
                <h1 class="fw-bold text-gradient">Property Inventory</h1>
                <p class="text-secondary lead">Manage and monitor all rental listings from one place.</p>
            </div>
            <div class="col-md-6 text-center text-md-end d-flex align-items-center justify-content-md-end gap-3 mt-4 mt-md-0">
                <div class="bg-white bg-opacity-50 p-2 px-3 rounded-4 shadow-sm border border-white d-none d-lg-block">
                    <span class="small text-muted text-uppercase fw-bold ls-1">Total Listings:</span>
                    <span class="fs-4 fw-bold text-primary ms-2"><?php echo count($properties); ?></span>
                </div>
                <a href="add_property.php" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill fs-5"></i> Add New Property
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show glass-panel border-0 mb-4 animate-up" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show glass-panel border-0 mb-4 animate-up" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Inventory Table -->
        <div class="row animate-up" style="animation-delay: 0.1s;">
            <div class="col-12">
                <div class="card glass-panel border-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light bg-transparent">
                                <tr class="text-secondary small text-uppercase fw-bold">
                                    <th class="border-0 ps-4">Ref ID</th>
                                    <th class="border-0">Preview</th>
                                    <th class="border-0">Listing Title</th>
                                    <th class="border-0">Monthly Rent</th>
                                    <th class="border-0">Geography</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 text-end pe-4">System Actions</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (empty($properties)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bi bi-building-dash fs-1 text-muted opacity-50 mb-3 d-block"></i>
                                            <p class="text-secondary lead">No properties listed yet.</p>
                                            <a href="add_property.php" class="btn btn-sm btn-outline-primary">Post your first listing</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($properties as $property): ?>
                                    <tr class="transition">
                                        <td class="ps-4 fw-bold text-secondary">#PROP-<?php echo str_pad($property['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <?php if ($property['main_image']): 
                                                $image_path = $property['main_image'];
                                                if (strpos($image_path, 'uploads/') === 0) {
                                                    $image_path = str_replace('uploads/', '', $image_path);
                                                }
                                            ?>
                                                <div class="rounded-3 shadow-sm border overflow-hidden" style="width: 70px; height: 50px;">
                                                    <img src="../../images/<?php echo htmlspecialchars($image_path); ?>" 
                                                         alt="Property" 
                                                         class="w-100 h-100" 
                                                         style="object-fit: cover;">
                                                </div>
                                            <?php else: ?>
                                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center border" style="width: 70px; height: 50px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($property['title']); ?></div>
                                            <div class="small text-muted italic"><?php echo htmlspecialchars($property['type']); ?></div>
                                        </td>
                                        <td class="fw-bold text-primary">$<?php echo number_format($property['price'], 2); ?></td>
                                        <td>
                                            <span class="small d-flex align-items-center gap-1">
                                                <i class="bi bi-geo-alt text-secondary"></i>
                                                <?php echo htmlspecialchars($property['location']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = 'bg-success';
                                            $icon = 'bi-check-circle';
                                            if ($property['status'] === 'Rented') {
                                                $status_class = 'bg-danger';
                                                $icon = 'bi-lock';
                                            }
                                            if ($property['status'] === 'Maintenance') {
                                                $status_class = 'bg-warning';
                                                $icon = 'bi-tools';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?> d-inline-flex align-items-center gap-1">
                                                <i class="bi <?php echo $icon; ?>"></i>
                                                <?php echo htmlspecialchars($property['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                                <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-light border-end" title="Edit Listing">
                                                    <i class="bi bi-pencil-square text-primary"></i>
                                                </a>
                                                <button class="btn btn-sm btn-light" onclick="confirmDelete(<?php echo $property['id']; ?>)" title="Delete Listing">
                                                    <i class="bi bi-trash3 text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Confirm Erasure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <i class="bi bi-exclamation-octagon text-danger display-1 mb-3 opacity-50"></i>
                    <p class="lead">Are you sure you want to remove this property? This action will permanently delete all associated data and media.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Keep Listing</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger px-4">Delete Permanently</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDeleteBtn').href = '../controllers/delete_property.php?id=' + id;
            deleteModal.show();
        }
    </script>
</body>
</html>
