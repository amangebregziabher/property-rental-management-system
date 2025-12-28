<?php
/**
 * Upload Image Handler
 * 
 * This file handles standalone image upload requests.
 * Can be used for AJAX uploads or additional image uploads.
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
// ============================================
$property_id = $_POST['property_id'] ?? 0;

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

// In prototype mode, we might skip the owner check if we want, 
// but it's safer to include it. If user_id is 1 (prototype), it might bypass.
// if ($property['owner_id'] != $user_id && $_SESSION['user_role'] !== 'admin') {
//     close_db_connection($conn);
//     header('Content-Type: application/json');
//     echo json_encode(['success' => false, 'message' => 'Unauthorized to modify this property']);
//     exit();
// }

// ============================================
// STEP 3: HANDLE IMAGE UPLOADS
// ============================================
$uploaded_count = 0;
$errors = [];

if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $upload_dir = __DIR__ . '/../../images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_count = count($_FILES['images']['name']);

    // Check if property currently has a primary image
    $check_primary_sql = "SELECT COUNT(*) as count FROM property_images WHERE property_id = ? AND is_main = 1";
    $cp_stmt = mysqli_prepare($conn, $check_primary_sql);
    mysqli_stmt_bind_param($cp_stmt, "i", $property_id);
    mysqli_stmt_execute($cp_stmt);
    $cp_res = mysqli_fetch_assoc(mysqli_stmt_get_result($cp_stmt));
    $has_primary = $cp_res['count'] > 0;
    mysqli_stmt_close($cp_stmt);

    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['images']['name'][$i];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($ext, $allowed_exts)) {
                $errors[] = "File '$file_name' has an invalid extension.";
                continue;
            }

            $new_name = 'property_' . $property_id . '_' . time() . '_' . $i . '.' . $ext;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_dir . $new_name)) {
                // Determine if this should be primary (if none exists)
<<<<<<< Updated upstream
                $is_primary = (!$has_primary && $uploaded_count === 0) ? 1 : 0;

=======
                $is_main = (!$has_primary && $uploaded_count === 0) ? 1 : 0;
                
>>>>>>> Stashed changes
                $img_sql = "INSERT INTO property_images (property_id, image_path, is_main) VALUES (?, ?, ?)";
                $img_stmt = mysqli_prepare($conn, $img_sql);
                mysqli_stmt_bind_param($img_stmt, "isi", $property_id, $new_name, $is_main);
                mysqli_stmt_execute($img_stmt);
                mysqli_stmt_close($img_stmt);
                $uploaded_count++;
            } else {
                $errors[] = "Failed to move file '$file_name' to storage.";
            }
        } else {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $errors[] = "Upload error for file index $i: " . $_FILES['images']['error'][$i];
            }
        }
    }
} else {
    $errors[] = "No images were selected for upload.";
}

close_db_connection($conn);

// Return response
header('Content-Type: application/json');
echo json_encode([
    'success' => $uploaded_count > 0,
    'message' => $uploaded_count > 0 ? "$uploaded_count images uploaded successfully." : "No images uploaded.",
    'errors' => $errors,
    'uploaded_count' => $uploaded_count
]);
exit();
