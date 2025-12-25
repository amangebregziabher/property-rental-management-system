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
$sql = "SELECT title FROM properties WHERE id = ?";
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

close_db_connection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Images - PRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="beautified-page">
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top">
        <div class="container-fluid mx-4">
            <a class="navbar-brand text-gradient fs-3 fw-bold" href="../../public/index.php">PRMS</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card glass-panel border-0 shadow-lg p-4">
                    <div class="card-body">
                        <h2 class="fw-bold mb-4">Upload Images</h2>
                        <p class="text-secondary mb-4">Adding images for: <strong><?php echo htmlspecialchars($property['title']); ?></strong></p>
                        
                        <form id="uploadForm" action="../controllers/upload_image.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                            
                            <div class="upload-zone border-dashed rounded-4 p-5 text-center mb-4" id="dropZone">
                                <i class="bi bi-cloud-arrow-up display-1 text-primary opacity-50 mb-3"></i>
                                <h5>Drag and drop images here</h5>
                                <p class="text-secondary small">or click to browse from your device</p>
                                <input type="file" name="images[]" id="fileInput" multiple accept="image/*" class="d-none">
                                <button type="button" class="btn btn-primary px-4" onclick="document.getElementById('fileInput').click()">
                                    Select Files
                                </button>
                            </div>

                            <div id="previewContainer" class="row g-2 mb-4"></div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-success w-100 py-3 fw-bold">
                                    Start Upload <i class="bi bi-upload ms-2"></i>
                                </button>
                                <a href="edit_property.php?id=<?php echo $property_id; ?>" class="btn btn-outline-secondary px-4 d-flex align-items-center">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('previewContainer');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-primary');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-primary');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary');
            fileInput.files = e.dataTransfer.files;
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', () => {
            handleFiles(fileInput.files);
        });

        function handleFiles(files) {
            previewContainer.innerHTML = '';
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const col = document.createElement('div');
                        col.className = 'col-3';
                        col.innerHTML = `
                            <div class="position-relative rounded overflow-hidden shadow-sm" style="padding-top: 100%">
                                <img src="${e.target.result}" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover">
                            </div>
                        `;
                        previewContainer.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>

    <style>
        .border-dashed { border: 2px dashed #ccc; transition: border-color 0.3s; }
        .border-primary { border-color: #0d6efd !important; background: rgba(13, 110, 253, 0.05); }
        .object-fit-cover { object-fit: cover; }
    </style>
</body>
</html>
