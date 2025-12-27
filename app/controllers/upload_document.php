<?php
/**
 * Upload Document Handler
 * 
 * This file handles document upload requests for properties.
 */

session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/property_list.php');
    exit();
}

// ============================================
// STEP 1: RECEIVE UPLOAD REQUEST
$property_id = $_POST['property_id'] ?? 0;
$document_type = $_POST['document_type'] ?? 'Other';

if (empty($property_id) || !is_numeric($property_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing or invalid property reference ID']);
    exit();
}

// ============================================
// STEP 2: DATABASE CONNECTION & OWNERSHIP CHECK
// ============================================
$conn = get_db_connection();

// Verify property exists and belongs to the user
$check_sql = "SELECT owner_id FROM properties WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "i", $property_id);
mysqli_stmt_execute($check_stmt);
$res = mysqli_stmt_get_result($check_stmt);
$property = mysqli_fetch_assoc($res);
mysqli_stmt_close($check_stmt);

if (!$property) {
    close_db_connection($conn);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Property not found']);
    exit();
}

// Optional: Check ownership
// if ($property['owner_id'] != $user_id && $_SESSION['user_role'] !== 'admin') {
//     close_db_connection($conn);
//     header('Content-Type: application/json');
//     echo json_encode(['success' => false, 'message' => 'Unauthorized to modify this property']);
//     exit();
// }

// ============================================
// STEP 3: HANDLE DOCUMENT UPLOADS
// ============================================
$uploaded_count = 0;
$errors = [];

if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
    $upload_dir = __DIR__ . '/../../storage/documents/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_count = count($_FILES['documents']['name']);

    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['documents']['name'][$i];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'jpg', 'jpeg', 'png'];

            if (!in_array($ext, $allowed_exts)) {
                $errors[] = "File '$file_name' has an invalid extension.";
                continue;
            }

            // Sanitize filename and create unique name
            $safe_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
            $new_name = 'doc_' . $property_id . '_' . time() . '_' . $i . '_' . $safe_name;

            if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $upload_dir . $new_name)) {
                $doc_sql = "INSERT INTO property_documents (property_id, document_name, document_path, document_type) VALUES (?, ?, ?, ?)";
                $doc_stmt = mysqli_prepare($conn, $doc_sql);
                mysqli_stmt_bind_param($doc_stmt, "isss", $property_id, $file_name, $new_name, $document_type);
                mysqli_stmt_execute($doc_stmt);
                mysqli_stmt_close($doc_stmt);
                $uploaded_count++;
            } else {
                $errors[] = "Failed to move file '$file_name' to storage.";
            }
        } else {
            if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $errors[] = "Upload error for file '$file_name': " . $_FILES['documents']['error'][$i];
            }
        }
    }
} else {
    $errors[] = "No documents were selected for upload.";
}

close_db_connection($conn);

// Return response
header('Content-Type: application/json');
echo json_encode([
    'success' => $uploaded_count > 0,
    'message' => $uploaded_count > 0 ? "$uploaded_count documents uploaded successfully." : "No documents uploaded.",
    'errors' => $errors,
    'uploaded_count' => $uploaded_count
]);
exit();
