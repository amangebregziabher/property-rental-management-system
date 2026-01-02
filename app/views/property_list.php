<?php
session_start();

// Access control: Only owners and admins can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'owner' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../helpers/notification_helper.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'tenant';

// Fetch properties from database
$conn = get_db_connection();

// Get pending applications count for notification badge
$pending_count = 0;
if ($user_role === 'owner' || $user_role === 'admin') {
    $pending_count = get_pending_applications_count($conn, $user_id);
}

// SQL differs based on role: Admin sees all, Owner sees only their own
$sql = "SELECT p.*, u.name as owner_name, c.name as type,
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_main DESC, id ASC LIMIT 1) as main_image
        FROM properties p 
        LEFT JOIN users u ON p.owner_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id";

if ($user_role !== 'admin') {
    $sql .= " WHERE p.owner_id = ? ";
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($user_role !== 'admin') {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$properties = [];
while ($row = mysqli_fetch_assoc($result)) {
    $properties[] = $row;
}

// Stats for dashboard
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available,
    SUM(CASE WHEN status = 'Rented' THEN 1 ELSE 0 END) as rented,
    SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance
    FROM properties ";

if ($user_role !== 'admin') {
    $stats_sql .= " WHERE owner_id = ? ";
}

$stats_stmt = mysqli_prepare($conn, $stats_sql);
if ($user_role !== 'admin') {
    mysqli_stmt_bind_param($stats_stmt, "i", $user_id);
}
mysqli_stmt_execute($stats_stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stats_stmt));

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
            <a class="navbar-brand text-gradient fs-3 fw-bold" href="../../public/index.php">PRMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="tenant_view.php">Find Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_applications.php">Applications</a>
                        <a class="nav-link" href="tenant_applications_list.php">Applications</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 active" href="#"
                            id="ownerDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5"></i>
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end glass-panel border-0 shadow-sm mt-2">
                            <li><span class="dropdown-item-text small text-muted">Role:
                                    <?php echo ucfirst($_SESSION['user_role']); ?></span></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger"
                                    href="../controllers/auth_controller.php?action=logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <header class="page-header py-5 bg-gradient-primary text-white position-relative overflow-hidden mb-5">
        <div class="container position-relative z-1">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3 animate-up">Inventory Management</h1>
                    <p class="lead opacity-75 animate-up" style="animation-delay: 0.1s;">Manage your listings, track
                        availability, and oversee your rental portfolio.</p>
                </div>
                <div class="col-lg-5 text-lg-end animate-up" style="animation-delay: 0.2s;">
                    <a href="manage_applications.php"
                        class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg me-2">
                        <i class="bi bi-file-earmark-person me-2"></i> Applications
                    </a>
                    <a href="add_property.php" class="btn btn-light btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg">
                        <i class="bi bi-plus-lg me-2 text-primary"></i> Post New Listing
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-blur"></div>
    </header>

    <div class="container pb-5">
        <!-- Stats Row -->
        <div class="row g-4 mb-5 animate-up" style="animation-delay: 0.3s;">
            <div class="col-md-3">
                <div
                    class="glass-panel p-4 rounded-4 text-center border-0 shadow-sm h-100 d-flex flex-column justify-content-center">
                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Total Assets</div>
                    <div class="display-5 fw-bold"><?php echo $stats['total']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="glass-panel p-4 rounded-4 text-center border-0 shadow-sm h-100 d-flex flex-column justify-content-center">
                    <div class="text-success small fw-bold text-uppercase mb-1">Available</div>
                    <div class="display-5 fw-bold"><?php echo $stats['available'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="glass-panel p-4 rounded-4 text-center border-0 shadow-sm h-100 d-flex flex-column justify-content-center">
                    <div class="text-danger small fw-bold text-uppercase mb-1">Leased Out</div>
                    <div class="display-5 fw-bold"><?php echo $stats['rented'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="glass-panel p-4 rounded-4 text-center border-0 shadow-sm h-100 d-flex flex-column justify-content-center">
                    <div class="text-warning small fw-bold text-uppercase mb-1">In Maintenance</div>
                    <div class="display-5 fw-bold"><?php echo $stats['maintenance'] ?? 0; ?></div>
                </div>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show glass-panel border-0 border-start border-4 border-success mb-4 animate-up text-white"
                role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i> <strong>Success!</strong>
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show glass-panel border-0 border-start border-4 border-danger mb-4 animate-up text-white"
                role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <strong>Error!</strong>
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Inventory Table -->
        <div class="row animate-up" style="animation-delay: 0.1s;">
            <div class="col-12">
                <div class="card bg-transparent border-0 overflow-visible">
                    <div class="table-responsive">
                        <table class="table premium-table align-middle mb-0">
                            <thead>
                                <tr class="text-white small text-uppercase fw-bold">
                                    <th class="ps-4 py-3">Ref ID</th>
                                    <th class="py-3">Preview</th>
                                    <th class="py-3">Listing Title</th>
                                    <th class="py-3">Monthly Rent</th>
                                    <th class="py-3">Geography</th>
                                    <th class="py-3">Status</th>
                                    <th class="text-end pe-4 py-3">System Actions</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (empty($properties)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="bi bi-building-dash fs-1 text-muted opacity-50 mb-3 d-block"></i>
                                                <p class="text-secondary lead">No properties listed yet.</p>
                                                <a href="add_property.php" class="btn btn-sm btn-outline-primary">Post your
                                                    first listing</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($properties as $property): ?>
                                        <tr class="transition">
                                            <td class="ps-4">
                                                <span class="ref-id-text">#PR-<?php echo str_pad($property['id'], 3, '0', STR_PAD_LEFT); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($property['main_image']):
                                                    $image_path = $property['main_image'];
                                                    if (strpos($image_path, 'uploads/') === 0) {
                                                        $image_path = str_replace('uploads/', '', $image_path);
                                                    }
                                                    ?>
                                                    <div class="rounded-3 shadow-sm border overflow-hidden"
                                                        style="width: 70px; height: 50px;">
                                                        <img src="../../images/<?php echo htmlspecialchars($image_path); ?>"
                                                            alt="Property" class="w-100 h-100" style="object-fit: cover;">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center border"
                                                        style="width: 70px; height: 50px;">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-white">
                                                    <?php echo htmlspecialchars($property['title']); ?>
                                                </div>
                                                <div class="small text-white-50 italic">
                                                    <?php echo htmlspecialchars($property['type']); ?>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-primary">
                                                $<?php echo number_format($property['price'], 2); ?></td>
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
                                                <span
                                                    class="badge <?php echo $status_class; ?> d-inline-flex align-items-center gap-1">
                                                    <i class="bi <?php echo $icon; ?>"></i>
                                                    <?php echo htmlspecialchars($property['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                                    <a href="edit_property.php?id=<?php echo $property['id']; ?>"
                                                        class="btn btn-sm btn-light border-end" title="Edit Listing">
                                                        <i class="bi bi-pencil-square text-primary"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-light"
                                                        onclick="confirmDelete(<?php echo $property['id']; ?>)"
                                                        title="Delete Listing">
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
                    <p class="lead">Are you sure you want to remove this property? This action will permanently delete
                        all associated data and media.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Keep Listing</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4">Delete Permanently</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="deleteToast" class="toast glass-panel border-0 shadow-lg" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="toast-header bg-transparent border-0 py-3">
                <i id="toastIcon" class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                <strong class="me-auto text-white">Notification</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
            <div id="toastBody" class="toast-body text-white-50 pb-3">
                Action completed successfully.
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let propertyToDelete = null;
        let deleteModal = null;
        let deleteToast = null;

        document.addEventListener('DOMContentLoaded', function () {
            deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteToast = new bootstrap.Toast(document.getElementById('deleteToast'));

            document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
                if (propertyToDelete) {
                    performDelete(propertyToDelete);
                }
            });
        });

        function confirmDelete(id) {
            propertyToDelete = id;
            deleteModal.show();
        }

        async function performDelete(id) {
            try {
                // Find the row and add deleting state
                const row = document.querySelector(`tr:has(button[onclick="confirmDelete(${id})"])`);
                if (row) row.classList.add('opacity-50', 'pointer-events-none');

                const response = await fetch(`../controllers/delete_property.php?id=${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                deleteModal.hide();

                if (data.success) {
                    showToast(true, data.message);
                    if (row) {
                        row.classList.add('slide-out-left');
                        setTimeout(() => {
                            row.remove();
                            updateQuickStats(); // Hypothetical stats update
                            checkEmptyInventory();
                        }, 500);
                    }
                } else {
                    if (row) row.classList.remove('opacity-50', 'pointer-events-none');
                    showToast(false, data.message);
                }
            } catch (error) {
                deleteModal.hide();
                showToast(false, "Network error occurred.");
            }
        }

        function showToast(success, message) {
            const icon = document.getElementById('toastIcon');
            const body = document.getElementById('toastBody');

            icon.className = success ? 'bi bi-check-circle-fill me-2 fs-5 text-success' : 'bi bi-exclamation-triangle-fill me-2 fs-5 text-danger';
            body.textContent = message;
            deleteToast.show();
        }

        function updateQuickStats() {
            // Update the "Total Assets" counter
            const totalCounter = document.querySelector('.display-5.fw-bold');
            if (totalCounter) {
                let current = parseInt(totalCounter.textContent);
                totalCounter.textContent = Math.max(0, current - 1);
            }
            // Ideally we'd update other stats too, but this requires more complex logic or a fresh summary fetch
        }

        function checkEmptyInventory() {
            const tbody = document.querySelector('tbody');
            if (tbody && tbody.querySelectorAll('tr').length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="opacity-50 mb-3">
                                <i class="bi bi-house-door display-1"></i>
                            </div>
                            <h4 class="text-white">Your inventory is empty.</h4>
                            <p class="text-white-50">Start by listing your first property.</p>
                            <a href="add_property.php" class="btn btn-sm btn-outline-primary mt-2 px-4 rounded-pill">Post New Listing</a>
                        </td>
                    </tr>`;
            }
        }
    </script>
    <style>
        .slide-out-left {
            animation: slideOutLeft 0.5s cubic-bezier(0.55, 0.085, 0.68, 0.53) both;
        }

        @keyframes slideOutLeft {
            0% {
                transform: translateX(0);
                opacity: 1;
            }

            100% {
                transform: translateX(-100%);
                opacity: 0;
            }
        }

        tr {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
    </style>
</body>

</html>