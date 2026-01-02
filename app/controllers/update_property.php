<?php
/**
 * Update Property Backend Handler
 * 
 * Handles property updates including data validation, 
 * image management (add/delete), and database updates.
 */

session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Initialize response variables
$errors = [];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/property_list.php');
    exit();
}

// ============================================
// STEP 1: RECEIVE AND SANITIZE POST DATA
// ============================================
$property_id = $_POST['property_id'] ?? 0;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = $_POST['price'] ?? '';
$location = trim($_POST['location'] ?? '');
$type = $_POST['type'] ?? '';
$status = $_POST['status'] ?? '';
$deleted_images = $_POST['deleted_images'] ?? []; // Array of image IDs to delete

// ============================================
// STEP 2: VALIDATION
// ============================================
if (empty($property_id) || !is_numeric($property_id)) {
    $errors[] = "Critical error: Missing property reference ID";
}

if (empty($title)) {
    $errors[] = "Property title is required";
}

if (empty($price) || !is_numeric($price)) {
    $errors[] = "Valid monthly rent price is required";
}

if (empty($location)) {
    $errors[] = "Property location is required";
}

// IF ERRORS, REDIRECT BACK
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    header("Location: ../views/edit_property.php?id=$property_id");
    exit();
}

// ============================================
// STEP 3: DATABASE CONNECTION
// ============================================
$conn = get_db_connection();

// ============================================
// STEP 4: VERIFY OWNERSHIP
// ============================================
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'tenant';

if ($user_role === 'admin') {
    $check_sql = "SELECT id FROM properties WHERE id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $property_id);
} else {
    $check_sql = "SELECT id FROM properties WHERE id = ? AND owner_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $property_id, $user_id);
}

mysqli_stmt_execute($check_stmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt))) {
    $_SESSION['error_message'] = "Property not found or access denied";
    close_db_connection($conn);
    header('Location: ../views/property_list.php');
    exit();
}
mysqli_stmt_close($check_stmt);

// ============================================
// STEP 5: UPDATE CORE PROPERTY DATA
// ============================================

// Lookup category_id from type name
$cat_sql = "SELECT id FROM categories WHERE name = ? LIMIT 1";
$cat_stmt = mysqli_prepare($conn, $cat_sql);
mysqli_stmt_bind_param($cat_stmt, "s", $type);
mysqli_stmt_execute($cat_stmt);
$cat_result = mysqli_stmt_get_result($cat_stmt);
$category_row = mysqli_fetch_assoc($cat_result);
$category_id = $category_row ? $category_row['id'] : null;

if (!$category_id) {
     $_SESSION['error_message'] = "Invalid category selected";
     header("Location: ../views/edit_property.php?id=$property_id");
     exit();
}
mysqli_stmt_close($cat_stmt);

$sql = "UPDATE properties SET title = ?, description = ?, price = ?, location = ?, category_id = ?, status = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssdsisi", $title, $description, $price, $location, $category_id, $status, $property_id);

if (!mysqli_stmt_execute($stmt)) {
    $_SESSION['error_message'] = "Error updating property details";
    close_db_connection($conn);
    header("Location: ../views/edit_property.php?id=$property_id");
    exit();
}
mysqli_stmt_close($stmt);

// ============================================
// STEP 6: HANDLE IMAGE DELETIONS
// ============================================
if (!empty($deleted_images)) {
    $upload_dir = __DIR__ . '/../../images/';

    foreach ($deleted_images as $img_id) {
        $img_id = intval($img_id);

        // Fetch path before deleting from DB
        $path_sql = "SELECT image_path FROM property_images WHERE id = ? AND property_id = ?";
        $path_stmt = mysqli_prepare($conn, $path_sql);
        mysqli_stmt_bind_param($path_stmt, "ii", $img_id, $property_id);
        mysqli_stmt_execute($path_stmt);
        $res = mysqli_stmt_get_result($path_stmt);
        $img_data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($path_stmt);

        if ($img_data) {
            // Remove from filesystem
            $full_path = $upload_dir . $img_data['image_path'];
            if (file_exists($full_path)) {
                unlink($full_path);
            }
            // Remove from DB
            $del_img_sql = "DELETE FROM property_images WHERE id = ?";
            $del_img_stmt = mysqli_prepare($conn, $del_img_sql);
            mysqli_stmt_bind_param($del_img_stmt, "i", $img_id);
            mysqli_stmt_execute($del_img_stmt);
            mysqli_stmt_close($del_img_stmt);
        }
    }
}

// ============================================
// STEP 7: HANDLE NEW IMAGE UPLOADS
// ============================================
if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $upload_dir = __DIR__ . '/../../images/';
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
            $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            $new_name = 'property_' . $property_id . '_' . time() . '_' . $i . '.' . $ext;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_dir . $new_name)) {
                // Determine if this should be primary (if none exists)
                $is_main = (!$has_primary && $i === 0) ? 1 : 0;
                $img_sql = "INSERT INTO property_images (property_id, image_path, is_main) VALUES (?, ?, ?)";
                $img_stmt = mysqli_prepare($conn, $img_sql);
                mysqli_stmt_bind_param($img_stmt, "isi", $property_id, $new_name, $is_main);
                mysqli_stmt_execute($img_stmt);
                mysqli_stmt_close($img_stmt);
            }
        }
    }
}

// Fallback: If no primary image exists after deletions/additions, set the first available one as primary
$check_primary_again = "SELECT id FROM property_images WHERE property_id = ? AND is_main = 1";
$cpa_stmt = mysqli_prepare($conn, $check_primary_again);
mysqli_stmt_bind_param($cpa_stmt, "i", $property_id);
mysqli_stmt_execute($cpa_stmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($cpa_stmt))) {
    // Set first image as primary
    $set_p_sql = "UPDATE property_images SET is_main = 1 WHERE property_id = ? LIMIT 1";
    $set_p_stmt = mysqli_prepare($conn, $set_p_sql);
    mysqli_stmt_bind_param($set_p_stmt, "i", $property_id);
    mysqli_stmt_execute($set_p_stmt);
    mysqli_stmt_close($set_p_stmt);
}
mysqli_stmt_close($cpa_stmt);

// ============================================
// STEP 8: REDIRECT
// ============================================
close_db_connection($conn);
$_SESSION['success_message'] = "Property updated successfully";
header("Location: ../views/property_list.php");
exit();
?>