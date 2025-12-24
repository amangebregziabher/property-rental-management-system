<?php
session_start();

// Access control: Only owners and admins can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'owner' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Get property ID from URL
$property_id = $_GET['id'] ?? 0;

if (empty($property_id) || !is_numeric($property_id)) {
    $_SESSION['error_message'] = "Invalid property record requested";
    header('Location: property_list.php');
    exit();
}

$conn = get_db_connection();

// Fetch property record
$sql = "SELECT * FROM properties WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $property_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$property = mysqli_fetch_assoc($result);

if (!$property) {
    $_SESSION['error_message'] = "Property not found";
    close_db_connection($conn);
    header('Location: property_list.php');
    exit();
}

// Fetch property images
$img_sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC";
$img_stmt = mysqli_prepare($conn, $img_sql);
mysqli_stmt_bind_param($img_stmt, "i", $property_id);
mysqli_stmt_execute($img_stmt);
$images_result = mysqli_stmt_get_result($img_stmt);
$property_images = [];
while ($img = mysqli_fetch_assoc($images_result)) {
    $property_images[] = $img;
}

close_db_connection($conn);

// Get any form validation errors from session
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing - PRMS</title>
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
        <!-- Page Header -->
        <div class="row mb-5 animate-up">
            <div class="col-md-12 text-center text-md-start">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="property_list.php" class="text-decoration-none">Inventory</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#PROP-<?php echo str_pad($property['id'], 3, '0', STR_PAD_LEFT); ?></li>
                    </ol>
                </nav>
                <h1 class="fw-bold text-gradient">Refine Listing</h1>
                <p class="text-secondary lead">Update details for "<?php echo htmlspecialchars($property['title']); ?>"</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="row mb-4 animate-up">
            <div class="col-lg-10 mx-auto">
                <div class="alert alert-danger border-0 shadow-sm glass-panel" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <h5 class="mb-0 fw-bold text-danger">Validation Issues</h5>
                    </div>
                    <ul class="mb-0 ms-4">
                        <?php foreach ($errors as $error): ?>
                            <li class="fw-medium"><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row animate-up" style="animation-delay: 0.1s;">
            <div class="col-lg-10 mx-auto">
                <div class="card glass-panel border-0 p-4 p-md-5">
                    <div class="card-body">
                        <form method="POST" action="../controllers/update_property.php" enctype="multipart/form-data">
                            
                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">

                            <div class="row g-4">
                                <!-- Left Column: Core Data -->
                                <div class="col-md-7">
                                    <h5 class="fw-bold border-start border-primary border-4 ps-3 mb-4">Content Refinement</h5>
                                    
                                    <div class="mb-4">
                                        <label for="title" class="form-label">Property Title <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-fonts"></i></span>
                                            <input type="text" class="form-control border-start-0" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label">Detailed Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="8"><?php echo htmlspecialchars($property['description']); ?></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Managed Gallery</h6>
                                        <div class="row g-2 mb-3" id="property-gallery">
                                            <?php foreach ($property_images as $index => $img): 
                                                $path = $img['image_path'];
                                                if (strpos($path, 'uploads/') === 0) {
                                                    $path = str_replace('uploads/', '', $path);
                                                }
                                            ?>
                                            <div class="col-4 image-container" id="image-<?php echo $img['id']; ?>">
                                                <div class="position-relative group overflow-hidden rounded-3 shadow-sm">
                                                    <img src="../../images/<?php echo htmlspecialchars($path); ?>" class="img-fluid border" alt="Property">
                                                    <div class="image-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 opacity-0 transition d-flex align-items-center justify-content-center">
                                                        <button type="button" class="btn btn-danger btn-sm rounded-circle p-2" onclick="removeImage(<?php echo $img['id']; ?>)">
                                                            <i class="bi bi-trash fs-5"></i>
                                                        </button>
                                                    </div>
                                                    <?php if ($img['is_primary']): ?>
                                                        <span class="badge bg-primary position-absolute top-0 start-0 m-1">Main</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            <div id="deleted-images"></div>
                                        </div>
                                        
                                        <label class="form-label small text-secondary">Append New Visuals</label>
                                        <input type="file" class="form-control mb-2" id="images" name="images[]" multiple accept="image/*">
                                        <small class="text-secondary op-75">Adding images will not delete existing ones unless explicitly removed.</small>
                                    </div>
                                </div>

                                <!-- Right Column: Variable Data -->
                                <div class="col-md-5">
                                    <h5 class="fw-bold border-start border-secondary border-4 ps-3 mb-4">Market & Logic</h5>
                                    
                                    <div class="mb-4">
                                        <label for="price" class="form-label">Monthly Rent (USD) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control h-auto" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($property['price']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="location" class="form-label">Geography <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-geo-alt"></i></span>
                                            <input type="text" class="form-control border-start-0" id="location" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="type" class="form-label">Property Classification <span class="text-danger">*</span></label>
                                        <select class="form-select h-auto" id="type" name="type" required>
                                            <option value="Apartment" <?php echo $property['type'] === 'Apartment' ? 'selected' : ''; ?>>🏢 Apartment</option>
                                            <option value="House" <?php echo $property['type'] === 'House' ? 'selected' : ''; ?>>🏠 House</option>
                                            <option value="Condo" <?php echo $property['type'] === 'Condo' ? 'selected' : ''; ?>>🏙️ Condo</option>
                                            <option value="Studio" <?php echo $property['type'] === 'Studio' ? 'selected' : ''; ?>>📦 Studio</option>
                                            <option value="Villa" <?php echo $property['type'] === 'Villa' ? 'selected' : ''; ?>>🏰 Villa</option>
                                            <option value="Townhouse" <?php echo $property['type'] === 'Townhouse' ? 'selected' : ''; ?>>🏡 Townhouse</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Current Availability Status <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-column gap-2 bg-dark bg-opacity-25 p-3 rounded-4 shadow-sm border border-white border-opacity-10">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="status" id="statusAvailable" value="Available" <?php echo $property['status'] === 'Available' ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-success fw-bold p-1" for="statusAvailable">Currently Available</label>
                                            </div>
                                            <div class="form-check custom-radio border-top border-white border-opacity-10 pt-2">
                                                <input class="form-check-input" type="radio" name="status" id="statusRented" value="Rented" <?php echo $property['status'] === 'Rented' ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-danger fw-bold p-1" for="statusRented">Leased Out</label>
                                            </div>
                                            <div class="form-check custom-radio border-top border-white border-opacity-10 pt-2">
                                                <input class="form-check-input" type="radio" name="status" id="statusMaintenance" value="Maintenance" <?php echo $property['status'] === 'Maintenance' ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-warning fw-bold p-1" for="statusMaintenance">Maintenance Mode</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 d-flex gap-3">
                                        <button type="submit" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                                            Update Details <i class="bi bi-check2-circle fs-5"></i>
                                        </button>
                                        <a href="property_list.php" class="btn btn-outline-secondary px-4 d-flex align-items-center">Cancel</a>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeImage(imageId) {
            if (confirm('Queue this image for removal?')) {
                const imgElement = document.getElementById('image-' + imageId);
                imgElement.style.opacity = '0.3';
                imgElement.classList.add('grayscale');
                
                // Add hidden input to track deleted images
                const deletedContainer = document.getElementById('deleted-images');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_images[]';
                input.value = imageId;
                deletedContainer.appendChild(input);
            }
        }
    </script>
    <style>
        .group:hover .image-overlay { opacity: 1 !important; }
        .transition { transition: all 0.3s ease; }
        .grayscale { filter: grayscale(1); }
        .breadcrumb-item + .breadcrumb-item::before { color: #adb5bd; }
    </style>
</body>
</html>
