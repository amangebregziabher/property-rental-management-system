<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - Property Rental Management System</title>
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
                        <a class="nav-link active" href="add_property.php">Add Property</a>
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
                <h2>Add New Property</h2>
                <p class="text-muted">Fill in the details to list a new property</p>
            </div>
        </div>

        <!-- Add Property Form -->
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        <form method="POST" action="../controllers/add_property.php" enctype="multipart/form-data">
                            
                            <!-- Title Field -->
                            <div class="mb-3">
                                <label for="title" class="form-label">Property Title *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="title" 
                                    name="title" 
                                    placeholder="e.g., Modern Downtown Apartment"
                                    required
                                >
                            </div>

                            <!-- Description Field -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea 
                                    class="form-control" 
                                    id="description" 
                                    name="description" 
                                    rows="4"
                                    placeholder="Describe the property features, amenities, etc."
                                ></textarea>
                            </div>

                            <!-- Price Field -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Monthly Rent (USD) *</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="price" 
                                    name="price" 
                                    step="0.01"
                                    min="0"
                                    placeholder="e.g., 1200.00"
                                    required
                                >
                            </div>

                            <!-- Location Field -->
                            <div class="mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="location" 
                                    name="location" 
                                    placeholder="e.g., Downtown, City Center"
                                    required
                                >
                            </div>

                            <!-- Property Type Field -->
                            <div class="mb-3">
                                <label for="type" class="form-label">Property Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="Apartment">Apartment</option>
                                    <option value="House">House</option>
                                    <option value="Condo">Condo</option>
                                    <option value="Studio">Studio</option>
                                    <option value="Villa">Villa</option>
                                    <option value="Townhouse">Townhouse</option>
                                </select>
                            </div>

                            <!-- Status Field -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">-- Select Status --</option>
                                    <option value="Available" selected>Available</option>
                                    <option value="Rented">Rented</option>
                                    <option value="Maintenance">Maintenance</option>
                                </select>
                            </div>

                            <!-- Image Upload Field -->
                            <div class="mb-3">
                                <label for="images" class="form-label">Property Images</label>
                                <input 
                                    type="file" 
                                    class="form-control" 
                                    id="images" 
                                    name="images[]" 
                                    multiple
                                    accept="image/*"
                                >
                                <small class="form-text text-muted">
                                    You can select multiple images. Accepted formats: JPG, PNG, GIF
                                </small>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="property_list.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    Add Property
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
// ADD PROPERTY FORM
// ============================================
// This form submits to ../controllers/add_property.php
// 
// Form fields:
// - title (text, required)
// - description (textarea, optional)
// - price (number, required)
// - location (text, required)
// - type (select, required)
// - status (select, required)
// - images[] (file upload, multiple, optional)
// 
// TODO for backend implementation:
// - Validate all required fields
// - Validate price is numeric and positive
// - Validate image file types
// - Insert property into database
// - Handle image uploads
// - Redirect to property list on success
// ============================================
?>
