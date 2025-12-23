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
    <title>List Your Property - PRMS Premium</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.85);
            --accent-gradient: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f0f2f5;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        .form-section-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .step-indicator {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #6c757d;
            margin-right: 12px;
        }

        .step-active .step-indicator {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.2);
        }

        .form-label {
            font-weight: 600;
            color: #2b2d42;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .input-group-custom {
            position: relative;
            background: white;
            border-radius: 12px;
            border: 2px solid #edf2f7;
            transition: all 0.2s ease;
        }

        .input-group-custom:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        .input-group-custom i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .input-group-custom .form-control,
        .input-group-custom .form-select {
            border: none;
            background: transparent;
            padding-left: 48px;
            height: 52px;
        }

        .input-group-custom .form-control:focus {
            box-shadow: none;
        }

        .amenity-checkbox {
            display: none;
        }

        .amenity-label {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: white;
            border: 2px solid #edf2f7;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
        }

        .amenity-checkbox:checked + .amenity-label {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
            color: var(--primary);
        }

        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.5);
        }

        .upload-zone:hover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }

        .preview-card {
            position: sticky;
            top: 100px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .preview-card:hover {
            transform: translateY(-10px);
        }

        .property-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            padding: 6px 14px;
            border-radius: 100px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            font-size: 0.75rem;
            font-weight: 700;
            z-index: 10;
        }

        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
    </style>
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
                        <a class="nav-link" href="tenant_view.php">Browse Listings</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="ownerDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end glass-panel border-0 shadow-lg mt-2 p-2">
                            <li><a class="dropdown-item rounded-3 py-2" href="property_list.php"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger rounded-3 py-2" href="../controllers/auth_controller.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-5 animate-up">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold text-white mb-2">Create a <span class="text-primary">Stunning</span> Listing</h1>
                <p class="lead text-white opacity-75">Connect with your future tenants by providing detailed information about your space.</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger border-0 glass-panel shadow-lg mb-5 animate-up p-4" role="alert">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                </div>
                <h5 class="mb-0 fw-bold text-danger">Please fix the following:</h5>
            </div>
            <ul class="mb-0 ms-5 fw-medium">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="../controllers/add_property.php" method="POST" enctype="multipart/form-data">
            <div class="row g-5">
                <!-- Main Form Column -->
                <div class="col-lg-8 animate-up animate-delay-1">
                    <div class="form-section-card p-4 p-md-5">
                        
                        <!-- Section 1: Basics -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <span class="step-indicator bg-primary text-white text-center">1</span>
                                <h4 class="fw-bold mb-0">Basic Information</h4>
                            </div>
                            
                            <div class="mb-4">
                                <label for="title" class="form-label">Property Title</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-house-door"></i>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           placeholder="e.g. Luxurious Penthouse with Ocean View" required
                                           value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="type" class="form-label">Property Type</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-building"></i>
                                        <select class="form-select" id="type" name="type" required>
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
                                </div>
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Monthly Rent (USD)</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-currency-dollar"></i>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                               placeholder="0.00" required
                                               value="<?php echo htmlspecialchars($form_data['price'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Details -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <span class="step-indicator text-center">2</span>
                                <h4 class="fw-bold mb-0">Detailed Description</h4>
                            </div>
                            
                            <div class="mb-4">
                                <label for="location" class="form-label">Location Address</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-geo-alt"></i>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           placeholder="Enter full address or city" required
                                           value="<?php echo htmlspecialchars($form_data['location'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control border-2 rounded-4 p-3" id="description" name="description" 
                                          rows="6" placeholder="Tell us about the property features..."><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Section 3: Media -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <span class="step-indicator text-center">3</span>
                                <h4 class="fw-bold mb-0">Property Media</h4>
                            </div>
                            
                            <div class="upload-zone" onclick="document.getElementById('images').click()">
                                <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3 d-block"></i>
                                <h5 class="fw-bold">Click to upload or drag and drop</h5>
                                <p class="text-muted mb-0">JPG, PNG, GIF up to 5MB each.</p>
                                <input type="file" class="d-none" id="images" name="images[]" multiple accept="image/*">
                            </div>
                            <div id="image-preview-container" class="row g-3 mt-3"></div>
                        </div>

                        <!-- Section 4: Status -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <span class="step-indicator text-center">4</span>
                                <h4 class="fw-bold mb-0">Visibility</h4>
                            </div>
                            
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusAvailable" value="Available" checked>
                                    <label class="form-check-label fw-bold text-success" for="statusAvailable">Available Now</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusDraft" value="Maintenance">
                                    <label class="form-check-label fw-bold text-secondary" for="statusDraft">Maintenance Mode</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5 opacity-10">

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1 py-3 rounded-4 shadow-lg border-0">
                                <i class="bi bi-send-fill me-2"></i> List Property Now
                            </button>
                            <a href="property_list.php" class="btn btn-light btn-lg px-4 py-3 rounded-4 border">Cancel</a>
                        </div>
                    </div>
                </div>

                <!-- Preview Column -->
                <div class="col-lg-4 d-none d-lg-block animate-up animate-delay-2">
                    <div class="preview-card">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-lg">
                            <div class="position-relative">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=600" id="preview-img" class="card-img-top" style="height: 240px; object-fit: cover;" alt="Preview">
                                <span class="property-badge text-primary" id="preview-type">Apartment</span>
                                <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-50 text-white">
                                    <h4 class="fw-bold mb-0" id="preview-price">$0,000<small class="fs-6 opacity-75">/mo</small></h4>
                                </div>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <h5 class="fw-bold mb-2 text-truncate" id="preview-title">Property Title</h5>
                                <div class="d-flex align-items-center gap-1 text-muted small mb-3">
                                    <i class="bi bi-geo-alt-fill text-danger"></i> 
                                    <span id="preview-location">Location not set</span>
                                </div>
                                <div class="d-flex gap-3 border-top pt-3">
                                    <span class="small text-muted"><i class="bi bi-square me-1"></i> Featured</span>
                                    <span class="small text-muted"><i class="bi bi-check-circle me-1"></i> Verified</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-4 glass-panel rounded-4 text-white">
                            <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning"></i> Quick Tips</h6>
                            <ul class="small mb-0 opacity-75 list-unstyled">
                                <li class="mb-2">- Use professional photos</li>
                                <li class="mb-2">- Be honest about the space</li>
                                <li>- Set a fair market price</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Live Preview Script -->
    <script>
        document.getElementById('title').addEventListener('input', (e) => {
            document.getElementById('preview-title').textContent = e.target.value || 'Property Title';
        });
        document.getElementById('price').addEventListener('input', (e) => {
            const val = e.target.value ? parseFloat(e.target.value).toLocaleString() : '0,000';
            document.getElementById('preview-price').innerHTML = `$${val}<small class="fs-6 opacity-75">/mo</small>`;
        });
        document.getElementById('location').addEventListener('input', (e) => {
            document.getElementById('preview-location').textContent = e.target.value || 'Location not set';
        });
        document.getElementById('type').addEventListener('change', (e) => {
            document.getElementById('preview-type').textContent = e.target.value;
        });

        // Image preview logic
        document.getElementById('images').addEventListener('change', function(e) {
            const container = document.getElementById('image-preview-container');
            container.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ex) {
                    document.getElementById('preview-img').src = ex.target.result;
                }
                reader.readAsDataURL(this.files[0]);
                
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(ex) {
                        const div = document.createElement('div');
                        div.className = 'col-3';
                        div.innerHTML = `
                            <div class="position-relative rounded-3 overflow-hidden" style="height: 60px;">
                                <img src="${ex.target.result}" class="w-100 h-100" style="object-fit: cover;">
                            </div>
                        `;
                        container.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
</body>
</html>
