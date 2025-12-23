<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Get success/error messages if any
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Fetch properties from database
$conn = get_db_connection();
$sql = "SELECT p.*, u.name as owner_name
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Inventory - PRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
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
                        <a class="nav-link active" href="property_list.php">Properties</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="login.php" class="btn btn-outline-primary btn-sm px-4">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Page Header -->
        <div class="row mb-5 animate-up">
            <div class="col-md-8 text-center text-md-start">
                <h1 class="fw-bold text-gradient">Property Inventory</h1>
                <p class="text-secondary lead">Manage and track your active rental listings</p>
            </div>
            <div class="col-md-4 text-center text-md-end d-flex align-items-center justify-content-md-end">
                <a href="add_property.php" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle fs-5"></i> Add New Property
                </a>
            </div>
        </div>

        <!-- Feedback Messages -->
        <?php if (!empty($success_message)): ?>
        <div class="row mb-4 animate-up">
            <div class="col-12">
                <div class="alert alert-success border-0 shadow-sm glass-panel d-flex align-items-center gap-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                    <div>
                        <strong class="text-success">Success!</strong> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="row mb-4 animate-up">
            <div class="col-12">
                <div class="alert alert-danger border-0 shadow-sm glass-panel d-flex align-items-center gap-3" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-4 text-danger"></i>
                    <div>
                        <strong class="text-danger">Error!</strong> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Properties Table -->
        <div class="card glass-panel border-0 animate-up" style="animation-delay: 0.1s;">
            <div class="card-body p-4">
                <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-houses text-secondary opacity-25" style="font-size: 5rem;"></i>
                    </div>
                    <h4 class="text-secondary fw-bold">Inventory is Empty</h4>
                    <p class="text-muted">You haven't added any properties yet.</p>
                    <a href="add_property.php" class="btn btn-primary mt-3">List Your First Property</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light bg-transparent">
                            <tr class="text-secondary small text-uppercase fw-bold">
                                <th class="border-0 ps-4">Ref ID</th>
                                <th class="border-0">Listing Title</th>
                                <th class="border-0">Monthly Rent</th>
                                <th class="border-0">Geography</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                            <tr class="transition">
                                <td class="ps-4 fw-bold text-secondary">#PROP-<?php echo str_pad($property['id'], 3, '0', STR_PAD_LEFT); ?></td>
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
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(propertyId) {
            if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
                window.location.href = '../controllers/delete_property.php?id=' + propertyId;
            }
        }
    </script>
</body>
</html>
