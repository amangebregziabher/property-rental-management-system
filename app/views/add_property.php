<?php
session_start();

// Get any error messages
$errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Clear session data
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Property - PRMS</title>
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
                        <a class="nav-link" href="property_list.php">Properties</a>
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
            <div class="col-md-12 text-center text-md-start">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="property_list.php" class="text-decoration-none">Inventory</a></li>
                        <li class="breadcrumb-item active" aria-current="page">New Listing</li>
                    </ol>
                </nav>
                <h1 class="fw-bold text-gradient">Create New Listing</h1>
                <p class="text-secondary lead">Fill in the details to list your property on the market</p>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
        <div class="row mb-4 animate-up">
            <div class="col-lg-10 mx-auto">
                <div class="alert alert-danger border-0 shadow-sm glass-panel" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <h5 class="mb-0 fw-bold text-danger">Validation Issues Detected</h5>
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
                        <form method="POST" action="../controllers/add_property.php" enctype="multipart/form-data">
                            
                            <div class="row g-4">
                                <!-- Left Column: Core Data -->
                                <div class="col-md-7">
                                    <h5 class="fw-bold border-start border-primary border-4 ps-3 mb-4 text-primary">Property Information</h5>
                                    
                                    <!-- Title Field -->
                                    <div class="mb-4">
                                        <label for="title" class="form-label">Property Title <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-fonts"></i></span>
                                            <input 
                                                type="text" 
                                                class="form-control border-start-0" 
                                                id="title" 
                                                name="title" 
                                                placeholder="e.g., Modern Downtown Apartment"
                                                value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <!-- Description Field -->
                                    <div class="mb-4">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea 
                                            class="form-control" 
                                            id="description" 
                                            name="description" 
                                            rows="8"
                                            placeholder="Describe the property features, amenities, local neighborhood highlights, etc."
                                        ><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                                    </div>

                                </div>

                                <!-- Right Column: Market Data -->
                                <div class="col-md-5">
                                    <h5 class="fw-bold border-start border-secondary border-4 ps-3 mb-4 text-secondary">Listing Details</h5>
                                    
                                    <!-- Price Field -->
                                    <div class="mb-4">
                                        <label for="price" class="form-label">Monthly Rent (USD) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-currency-dollar text-primary"></i></span>
                                            <input 
                                                type="number" 
                                                class="form-control h-auto border-start-0" 
                                                id="price" 
                                                name="price" 
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                value="<?php echo htmlspecialchars($form_data['price'] ?? ''); ?>"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <!-- Location Field -->
                                    <div class="mb-4">
                                        <label for="location" class="form-label">Location / Geography <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-geo-alt text-primary"></i></span>
                                            <input 
                                                type="text" 
                                                class="form-control border-start-0" 
                                                id="location" 
                                                name="location" 
                                                placeholder="e.g., Downtown, City Center"
                                                value="<?php echo htmlspecialchars($form_data['location'] ?? ''); ?>"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <!-- Property Type Field -->
                                    <div class="mb-4">
                                        <label for="type" class="form-label">Property Classification <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-house-door text-primary"></i></span>
                                            <select class="form-select border-start-0 h-auto" id="type" name="type" required>
                                                <option value="">-- Select Type --</option>
                                                <option value="Apartment" <?php echo (($form_data['type'] ?? '') === 'Apartment') ? 'selected' : ''; ?>>🏢 Apartment</option>
                                                <option value="House" <?php echo (($form_data['type'] ?? '') === 'House') ? 'selected' : ''; ?>>🏠 House</option>
                                                <option value="Condo" <?php echo (($form_data['type'] ?? '') === 'Condo') ? 'selected' : ''; ?>>🏙️ Condo</option>
                                                <option value="Studio" <?php echo (($form_data['type'] ?? '') === 'Studio') ? 'selected' : ''; ?>>📦 Studio</option>
                                                <option value="Villa" <?php echo (($form_data['type'] ?? '') === 'Villa') ? 'selected' : ''; ?>>🏰 Villa</option>
                                                <option value="Townhouse" <?php echo (($form_data['type'] ?? '') === 'Townhouse') ? 'selected' : ''; ?>>🏡 Townhouse</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Status Field -->
                                    <div class="mb-4">
                                        <label class="form-label">Inaugural Status <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-column gap-2 bg-light bg-opacity-50 p-3 rounded-4 shadow-sm">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input mt-1" type="radio" name="status" id="statusAvailable" value="Available" <?php echo (($form_data['status'] ?? 'Available') === 'Available') ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-success fw-bold flex-grow-1" for="statusAvailable">
                                                    Currently Available
                                                    <span class="d-block small text-muted fw-normal mt-1 italic">Listing will be visible to all potential tenants immediately.</span>
                                                </label>
                                            </div>
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input mt-1" type="radio" name="status" id="statusRented" value="Rented" <?php echo (($form_data['status'] ?? '') === 'Rented') ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-danger fw-bold flex-grow-1" for="statusRented">
                                                    Pre-Leased / Rented
                                                    <span class="d-block small text-muted fw-normal mt-1 italic">Mark as unavailable for immediate rental.</span>
                                                </label>
                                            </div>
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input mt-1" type="radio" name="status" id="statusMaintenance" value="Maintenance" <?php echo (($form_data['status'] ?? '') === 'Maintenance') ? 'checked' : ''; ?>>
                                                <label class="form-check-label text-warning fw-bold flex-grow-1" for="statusMaintenance">
                                                    Under Maintenance
                                                    <span class="d-block small text-muted fw-normal mt-1 italic">Routine repairs or upgrades are currently in progress.</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="mt-5 d-flex gap-3">
                                        <button type="submit" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                                            Publish Listing <i class="bi bi-cloud-arrow-up fs-5"></i>
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
</body>
</html>
