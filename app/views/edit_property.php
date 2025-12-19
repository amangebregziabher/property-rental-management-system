<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing - PRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="beautified-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top">
        <div class="container-fluid mx-4">
            <a class="navbar-brand text-gradient fs-3" href="../public/index.php">PRMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="property_list.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_property.php">Add Property</a>
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
                        <li class="breadcrumb-item active" aria-current="page">#PROP-001</li>
                    </ol>
                </nav>
                <h1 class="fw-bold text-gradient">Refine Listing</h1>
                <p class="text-secondary lead">Update details for "Modern Downtown Apartment"</p>
            </div>
        </div>

        <div class="row animate-up" style="animation-delay: 0.1s;">
            <div class="col-lg-10 mx-auto">
                <div class="card glass-panel border-0 p-4 p-md-5">
                    <div class="card-body">
                        <form method="POST" action="../controllers/update_property.php" enctype="multipart/form-data">
                            
                            <input type="hidden" name="property_id" value="1">

                            <div class="row g-4">
                                <!-- Left Column: Core Data -->
                                <div class="col-md-7">
                                    <h5 class="fw-bold border-start border-primary border-4 ps-3 mb-4">Content Refinement</h5>
                                    
                                    <div class="mb-4">
                                        <label for="title" class="form-label">Property Title <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-fonts"></i></span>
                                            <input type="text" class="form-control border-start-0" id="title" name="title" value="Modern Downtown Apartment" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label">Detailed Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="8">Beautiful 2-bedroom apartment in the heart of downtown with stunning city views. Features modern kitchen appliances, floor-to-ceiling windows, and private balcony.</textarea>
                                    </div>

                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Managed Gallery</h6>
                                        <div class="row g-2 mb-3">
                                            <div class="col-4">
                                                <div class="position-relative group">
                                                    <img src="https://via.placeholder.com/300x200?text=Main+Image" class="img-fluid rounded-3 shadow-sm border" alt="Property">
                                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-1 opacity-0 hover-opacity-100 transition"><i class="bi bi-x"></i></button>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="position-relative">
                                                    <img src="https://via.placeholder.com/300x200?text=Bedroom" class="img-fluid rounded-3 shadow-sm border" alt="Property">
                                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-1 opacity-0 transition"><i class="bi bi-x"></i></button>
                                                </div>
                                            </div>
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
                                            <input type="number" class="form-control h-auto" id="price" name="price" step="0.01" min="0" value="1200.00" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="location" class="form-label">Geography <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0 rounded-start-3"><i class="bi bi-geo-alt"></i></span>
                                            <input type="text" class="form-control border-start-0" id="location" name="location" value="Downtown, City Center" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="type" class="form-label">Property Classification <span class="text-danger">*</span></label>
                                        <select class="form-select h-auto" id="type" name="type" required>
                                            <option value="Apartment" selected>🏢 Apartment</option>
                                            <option value="House">🏠 House</option>
                                            <option value="Condo">🏙️ Condo</option>
                                            <option value="Studio">📦 Studio</option>
                                            <option value="Villa">🏰 Villa</option>
                                            <option value="Townhouse">🏡 Townhouse</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Current Availability Status <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-column gap-2 bg-light bg-opacity-50 p-3 rounded-3">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="status" id="statusAvailable" value="Available" checked>
                                                <label class="form-check-label text-success fw-bold" for="statusAvailable">Currently Available</label>
                                            </div>
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="status" id="statusRented" value="Rented">
                                                <label class="form-check-label text-danger fw-bold" for="statusRented">Leased Out</label>
                                            </div>
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="status" id="statusMaintenance" value="Maintenance">
                                                <label class="form-check-label text-warning fw-bold" for="statusMaintenance">Maintenance Mode</label>
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
    <style>
        /* Specific group hover for edit page */
        .group:hover .opacity-0 { opacity: 1 !important; }
        .hover-opacity-100:hover { opacity: 1 !important; }
    </style>
</body>
</html>

<?php
// ============================================
// EDIT PROPERTY FORM
// ============================================
// This form is pre-filled with static/dummy data
// Submits to ../controllers/update_property.php
// 
// Pre-filled values (static for prototype):
// - property_id: 1
// - title: "Modern Downtown Apartment"
// - description: "Beautiful 2-bedroom apartment..."
// - price: 1200.00
// - location: "Downtown, City Center"
// - type: "Apartment"
// - status: "Available"
// 
// TODO for backend implementation:
// - Get property_id from URL parameter (?id=X)
// - Fetch property data from database
// - Pre-fill form with actual database values
// - Validate ownership before allowing edit
// - Update property in database
// - Handle new image uploads
// - Redirect to property list on success
// ============================================
?>
