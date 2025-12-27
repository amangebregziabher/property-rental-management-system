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

// Fetch existing documents
$doc_sql = "SELECT * FROM property_documents WHERE property_id = ? ORDER BY created_at DESC";
$doc_stmt = mysqli_prepare($conn, $doc_sql);
mysqli_stmt_bind_param($doc_stmt, "i", $property_id);
mysqli_stmt_execute($doc_stmt);
$docs_result = mysqli_stmt_get_result($doc_stmt);
$documents = [];
while ($doc = mysqli_fetch_assoc($docs_result)) {
    $documents[] = $doc;
}

close_db_connection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Documents - PRMS</title>
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

    <div class="container mt-5 pb-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="d-flex align-items-center justify-content-between mb-4 animate-up">
                    <div>
                        <h2 class="fw-bold mb-1">Document Management</h2>
                        <p class="text-secondary mb-0">Property: <strong><?php echo htmlspecialchars($property['title']); ?></strong></p>
                    </div>
                    <a href="property_list.php" class="btn btn-outline-light rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i> Dashboard
                    </a>
                </div>

                <div class="row g-4">
                    <!-- Upload Form -->
                    <div class="col-md-5 animate-up" style="animation-delay: 0.1s;">
                        <div class="card glass-panel border-0 shadow-lg p-4 h-100">
                            <h4 class="fw-bold mb-4">Add Document</h4>
                            <form id="uploadDocForm" action="../controllers/upload_document.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Document Type</label>
                                    <select name="document_type" class="form-select bg-dark text-white border-secondary">
                                        <option value="Lease Agreement">Lease Agreement</option>
                                        <option value="ID Copy">ID Copy</option>
                                        <option value="Title Deed">Title Deed</option>
                                        <option value="Insurance Policy">Insurance Policy</option>
                                        <option value="Maintenance Receipt">Maintenance Receipt</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="upload-zone border-dashed rounded-4 p-4 text-center mb-4" id="dropZone" onclick="document.getElementById('fileInput').click()">
                                    <i class="bi bi-file-earmark-arrow-up display-4 text-primary opacity-50 mb-3"></i>
                                    <h6>Click or Drag Document</h6>
                                    <p class="text-secondary small">PDF, DOCX, JPG allowed</p>
                                    <input type="file" name="documents[]" id="fileInput" multiple class="d-none">
                                    <button type="button" class="btn btn-sm btn-primary px-4">
                                        Select Files
                                    </button>
                                </div>

                                <div id="fileList" class="mb-4 small text-secondary"></div>

                                <button type="submit" class="btn btn-success w-100 py-3 fw-bold">
                                    Upload Documents <i class="bi bi-upload ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Documents -->
                    <div class="col-md-7 animate-up" style="animation-delay: 0.2s;">
                        <div class="card glass-panel border-0 shadow-lg p-4 h-100">
                            <h4 class="fw-bold mb-4">Repository</h4>
                            <div class="table-responsive">
                                <table class="table table-hover table-dark align-middle">
                                    <thead>
                                        <tr class="small text-uppercase text-secondary">
                                            <th>Name / Type</th>
                                            <th>Date</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="documentsTableBody">
                                        <?php if (empty($documents)): ?>
                                            <tr id="noDocumentsRow">
                                                <td colspan="3" class="text-center py-5 text-secondary">
                                                    No documents uploaded yet.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($documents as $doc): ?>
                                                <tr id="doc-<?php echo $doc['id']; ?>">
                                                    <td>
                                                        <div class="fw-bold text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($doc['document_name']); ?></div>
                                                        <span class="badge bg-secondary-subtle text-white small"><?php echo htmlspecialchars($doc['document_type']); ?></span>
                                                    </td>
                                                    <td class="small text-secondary">
                                                        <?php echo date('M d, Y', strtotime($doc['created_at'])); ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group">
                                                            <a href="../../storage/documents/<?php echo htmlspecialchars($doc['document_path']); ?>" target="_blank" class="btn btn-sm btn-light rounded-circle p-2" title="View">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <button type="button" onclick="deleteDocument(<?php echo $doc['id']; ?>)" class="btn btn-sm btn-outline-danger rounded-circle p-2 ms-1" title="Delete">
                                                                <i class="bi bi-trash"></i>
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
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const dropZone = document.getElementById('dropZone');

        fileInput.addEventListener('change', updateFileList);

        function updateFileList() {
            fileList.innerHTML = '';
            const files = Array.from(fileInput.files);
            if (files.length > 0) {
                fileList.innerHTML = '<strong>Selected files:</strong><ul class="mt-1 mb-0">';
                files.forEach(file => {
                    fileList.innerHTML += `<li>${file.name} (${(file.size / 1024).toFixed(1)} KB)</li>`;
                });
                fileList.innerHTML += '</ul>';
            }
        }

        // Drag and drop handling
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
            updateFileList();
        });

        document.getElementById('uploadDocForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

            try {
                const response = await fetch(e.target.action, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Upload failed: ' + (result.message || 'Unknown error'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Upload Documents <i class="bi bi-upload ms-2"></i>';
                }
            } catch (err) {
                alert('An error occurred during upload.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Upload Documents <i class="bi bi-upload ms-2"></i>';
            }
        });

        async function deleteDocument(docId) {
            if (!confirm('Are you sure you want to delete this document?')) return;

            try {
                const response = await fetch('../controllers/delete_document.php?id=' + docId);
                const result = await response.json();
                
                if (result.success) {
                    const row = document.getElementById('doc-' + docId);
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        if (document.querySelectorAll('#documentsTableBody tr').length === 0) {
                            document.getElementById('documentsTableBody').innerHTML = `
                                <tr id="noDocumentsRow">
                                    <td colspan="3" class="text-center py-5 text-secondary">
                                        No documents uploaded yet.
                                    </td>
                                </tr>`;
                        }
                    }, 300);
                } else {
                    alert('Delete failed: ' + result.message);
                }
            } catch (err) {
                alert('An error occurred during deletion.');
            }
        }
    </script>

    <style>
        .border-dashed { border: 2px dashed rgba(255,255,255,0.2) !important; transition: all 0.3s; cursor: pointer; }
        .border-dashed:hover { border-color: #0d6efd !important; background: rgba(13, 110, 253, 0.05); }
        .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.4); }
        tr { transition: opacity 0.3s ease; }
    </style>
</body>
</html>
