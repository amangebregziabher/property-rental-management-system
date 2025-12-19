<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property List - Property Rental Management System</title>
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
                        <a class="nav-link active" href="property_list.php">Properties</a>
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
            <div class="col-md-8">
                <h2>Property Listings</h2>
                <p class="text-muted">Manage all your rental properties</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="add_property.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Property
                </a>
            </div>
        </div>

        <!-- Properties Table -->
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Sample/Static Data Row 1 -->
                            <tr>
                                <td>1</td>
                                <td>Modern Downtown Apartment</td>
                                <td>$1,200.00</td>
                                <td>Downtown, City Center</td>
                                <td><span class="badge bg-info">Apartment</span></td>
                                <td><span class="badge bg-success">Available</span></td>
                                <td>
                                    <a href="edit_property.php?id=1" class="btn btn-sm btn-warning">Edit</a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(1)">Delete</button>
                                </td>
                            </tr>

                            <!-- Sample/Static Data Row 2 -->
                            <tr>
                                <td>2</td>
                                <td>Cozy Suburban House</td>
                                <td>$1,800.00</td>
                                <td>Maple Street, Suburbs</td>
                                <td><span class="badge bg-info">House</span></td>
                                <td><span class="badge bg-success">Available</span></td>
                                <td>
                                    <a href="edit_property.php?id=2" class="btn btn-sm btn-warning">Edit</a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(2)">Delete</button>
                                </td>
                            </tr>

                            <!-- Sample/Static Data Row 3 -->
                            <tr>
                                <td>3</td>
                                <td>Luxury Beachfront Condo</td>
                                <td>$2,500.00</td>
                                <td>Ocean Drive, Beach Area</td>
                                <td><span class="badge bg-info">Condo</span></td>
                                <td><span class="badge bg-danger">Rented</span></td>
                                <td>
                                    <a href="edit_property.php?id=3" class="btn btn-sm btn-warning">Edit</a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(3)">Delete</button>
                                </td>
                            </tr>

                            <!-- Sample/Static Data Row 4 -->
                            <tr>
                                <td>4</td>
                                <td>Student-Friendly Studio</td>
                                <td>$650.00</td>
                                <td>University District</td>
                                <td><span class="badge bg-info">Studio</span></td>
                                <td><span class="badge bg-success">Available</span></td>
                                <td>
                                    <a href="edit_property.php?id=4" class="btn btn-sm btn-warning">Edit</a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(4)">Delete</button>
                                </td>
                            </tr>

                            <!-- Sample/Static Data Row 5 -->
                            <tr>
                                <td>5</td>
                                <td>Executive Villa</td>
                                <td>$4,500.00</td>
                                <td>Hillside Estates</td>
                                <td><span class="badge bg-info">Villa</span></td>
                                <td><span class="badge bg-warning">Maintenance</span></td>
                                <td>
                                    <a href="edit_property.php?id=5" class="btn btn-sm btn-warning">Edit</a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(5)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple delete confirmation (no actual deletion)
        function confirmDelete(propertyId) {
            if (confirm('Are you sure you want to delete this property?')) {
                alert('Delete functionality not implemented (Prototype).\nProperty ID: ' + propertyId);
                // In real implementation, would redirect to:
                // window.location.href = '../controllers/delete_property.php?id=' + propertyId;
            }
        }
    </script>
</body>
</html>

<?php
// ============================================
// PROPERTY LIST - STATIC DATA DISPLAY
// ============================================
// This page displays static/dummy property data
// 
// TODO for future implementation:
// - Connect to database using db_connect.php
// - Fetch properties from database
// - Display dynamic data in table
// - Implement pagination
// - Add search/filter functionality
// - Check user permissions for edit/delete
// ============================================
?>
