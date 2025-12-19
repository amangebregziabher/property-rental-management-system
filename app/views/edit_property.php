<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - Property Rental Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../public/index.php">PRMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="property_list.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_property.php">Add Property</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Edit Property</h2>
                <p class="text-muted">Update property information</p>
            </div>
        </div>

        <!-- Edit Property Form -->
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        <form method="POST" action="../controllers/update_property.php" enctype="multipart/form-data">
                            
                            <!-- Hidden Property ID -->
                            <input type="hidden" name="property_id" value="1">

                            <!-- Title Field (Pre-filled with dummy data) -->
                            <div class="mb-3">
                                <label for="title" class="form-label">Property Title *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="title" 
                                    name="title" 
                                    value="Modern Downtown Apartment"
                                    required
                                >
                            </div>

                            <!-- Description Field (Pre-filled) -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea 
                                    class="form-control" 
                                    id="description" 
                                    name="description" 
                                    rows="4"
                                >Beautiful 2-bedroom apartment in the heart of downtown with stunning city views.</textarea>
                            </div>

                            <!-- Price Field (Pre-filled) -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Monthly Rent (USD) *</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="price" 
                                    name="price" 
                                    step="0.01"
                                    min="0"
                                    value="1200.00"
                                    required
                                >
                            </div>

                            <!-- Location Field (Pre-filled) -->
                            <div class="mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="location" 
                                    name="location" 
                                    value="Downtown, City Center"
                                    required
                                >
                            </div>

                            <!-- Property Type Field (Pre-selected) -->
                            <div class="mb-3">
                                <label for="type" class="form-label">Property Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="Apartment" selected>Apartment</option>
                                    <option value="House">House</option>
                                    <option value="Condo">Condo</option>
                                    <option value="Studio">Studio</option>
                                    <option value="Villa">Villa</option>
                                    <option value="Townhouse">Townhouse</option>
                                </select>
                            </div>

                            <!-- Status Field (Pre-selected) -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">-- Select Status --</option>
                                    <option value="Available" selected>Available</option>
                                    <option value="Rented">Rented</option>
                                    <option value="Maintenance">Maintenance</option>
                                </select>
                            </div>

                            <!-- Current Images Display -->
                            <div class="mb-3">
                                <label class="form-label">Current Images</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <p class="text-muted">Image 1</p>
                                                <small>property_1_main.jpg</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <p class="text-muted">Image 2</p>
                                                <small>property_1_bedroom.jpg</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload New Images -->
                            <div class="mb-3">
                                <label for="images" class="form-label">Upload New Images (Optional)</label>
                                <input 
                                    type="file" 
                                    class="form-control" 
                                    id="images" 
                                    name="images[]" 
                                    multiple
                                    accept="image/*"
                                >
                                <small class="form-text text-muted">
                                    Upload new images to add to this property
                                </small>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="property_list.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    Update Property
                                </button>
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
