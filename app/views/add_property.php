<?php
session_start();

// Access control: Only owners and admins can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'owner' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get form data and errors from session (if any)
$form_data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];

// Clear session data after retrieving
unset($_SESSION['form_data']);
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - PRMS</title>
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
        <!-- Page Header -->
        <div class="row mb-5 animate-up">
            <div class="col-md-12 text-center text-md-start">
                <h1 class="fw-bold text-gradient">List New Property</h1>
                <p class="text-secondary lead">Fill in the details to showcase your property to potential tenants.</p>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($errors)): ?>
        <div class="row mb-4 animate-up">
            <div class="col-lg-10 mx-auto">
                <div class="alert alert-danger border-0 shadow-sm glass-panel" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <h5 class="mb-0 fw-bold text-danger">Validation Errors</h5>
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
                        <form action="../controllers/add_property.php" method="POST" enctype="multipart/form-data">
                            <div class="row g-4">
                                <!-- Left Column: Core Data -->
                                <div class="col-md-7">
                                    <h5 class="fw-bold border-start border-primary border-4 ps-3 mb-4">Core Information</h5>
                                    
                                    <div class="mb-4">
                                        <label for="title" class="form-label font-heading">Property Title <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-fonts"></i></span>
                                            <input 
                                                type="text" 
                                                class="form-control border-start-0" 
                                                id="title" 
                                                name="title" 
                                                value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>"
                                                placeholder="e.g. Modern Apartment in Downtown"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label font-heading">Property Description</label>
                                        <textarea 
                                            class="form-control" 
                                            id="description" 
                                            name="description" 
                                            rows="8" 
                                            placeholder="Describe the property features, amenities, and surroundings..."
                                        ><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- Image Upload Field -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Gallery Management</h6>
                                        <div class="mb-3">
                                            <label for="images" class="form-label small text-secondary">Upload Property Photos</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-images"></i></span>
                                                <input 
                                                    type="file" 
                                                    class="form-control border-start-0" 
                                                    id="images" 
                                                    name="images[]" 
                                                    multiple
                                                    accept="image/*"
                                                >
                                            </div>
                                            <small class="form-text text-muted mt-2 d-block">
                                                <i class="bi bi-info-circle me-1"></i> You can select multiple images. Formats: JPG, PNG, GIF (Max 5MB each)
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Market Data -->
                                <div class="col-md-5">
                                    <h5 class="fw-bold border-start border-secondary border-4 ps-3 mb-4">Market Details</h5>
                                    
                                    <div class="mb-4">
                                        <label for="price" class="form-label font-heading">Monthly Rent (USD) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3">$</span>
                                            <input 
                                                type="number" 
                                                class="form-control border-start-0 h-auto" 
                                                id="price" 
                                                name="price" 
                                                step="0.01" 
                                                min="0"
                                                value="<?php echo htmlspecialchars($form_data['price'] ?? ''); ?>"
                                                placeholder="0.00"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="location" class="form-label font-heading">Location <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-geo-alt"></i></span>
                                            <input 
                                                type="text" 
                                                class="form-control border-start-0" 
                                                id="location" 
                                                name="location" 
                                                value="<?php echo htmlspecialchars($form_data['location'] ?? ''); ?>"
                                                placeholder="City, Area or Full Address"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="type" class="form-label font-heading">Property Type <span class="text-danger">*</span></label>
                                        <select class="form-select border-3 h-auto" id="type" name="type" required>
                                            <option value="" disabled <?php echo empty($form_data['type']) ? 'selected' : ''; ?>>Select Type</option>
                                            <?php
                                            $types = ['Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse'];
                                            foreach ($types as $t) {
                                                $selected = ($form_data['type'] ?? '') === $t ? 'selected' : '';
                                                echo "<option value=\"$t\" $selected>$t</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label for="status" class="form-label font-heading">Listing Status <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-column gap-2 bg-light bg-opacity-50 p-3 rounded-4 shadow-sm border border-light">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="status" id="statusAvailable" value="Available" <?php echo ($form_data['status'] ?? 'Available') === 'Available' ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-success fw-bold p-1" for="statusAvailable">Available Now</label>
                                            </div>
                                            <div class="form-check custom-radio border-top border-light-subtle pt-2">
                                                <input class="form-check-input" type="radio" name="status" id="statusRented" value="Rented" <?php echo ($form_data['status'] ?? '') === 'Rented' ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-danger fw-bold p-1" for="statusRented">Already Rented</label>
                                            </div>
                                            <div class="form-check custom-radio border-top border-light-subtle pt-2">
                                                <input class="form-check-input" type="radio" name="status" id="statusMaintenance" value="Maintenance" <?php echo ($form_data['status'] ?? '') === 'Maintenance' ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-warning fw-bold p-1" for="statusMaintenance">Under Maintenance</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 d-flex gap-3">
                                        <button type="submit" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                                            Publish Property <i class="bi bi-cloud-arrow-up fs-5"></i>
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
