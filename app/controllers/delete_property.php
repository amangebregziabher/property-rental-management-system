<?php
/**
 * Delete Property Backend Handler
 * 
 * Handles property deletion including image cleanup.
 */

session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// ============================================
// STEP 1: RECEIVE AND VALIDATE PROPERTY ID
// ============================================
$property_id = $_GET['id'] ?? $_POST['id'] ?? 0;

if (empty($property_id) || !is_numeric($property_id) || intval($property_id) <= 0) {
    $_SESSION['error_message'] = "Invalid property ID";
    header('Location: ../views/property_list.php');
    exit();
}

$property_id = intval($property_id);

// ============================================
// STEP 2: DATABASE CONNECTION
// ============================================
$conn = get_db_connection();

// ============================================
// STEP 3: VERIFY PROPERTY EXISTS
// ============================================
$check_sql = "SELECT id, owner_id FROM properties WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "i", $property_id);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);
$property = mysqli_fetch_assoc($result);
mysqli_stmt_close($check_stmt);

if (!$property) {
    $_SESSION['error_message'] = "Property not found";
    close_db_connection($conn);
    header('Location: ../views/property_list.php');
    exit();
}

// ============================================
// STEP 4: AUTHORIZATION CHECK
// ============================================
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please login to perform this action";
    close_db_connection($conn);
    header('Location: ../views/login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'tenant';

// Only the owner or an admin can delete the property
if ($user_id != $property['owner_id'] && $user_role !== 'admin') {
    $_SESSION['error_message'] = "You are not authorized to delete this property";
    close_db_connection($conn);
    header('Location: ../views/property_list.php');
    exit();
}

// ============================================
// STEP 4: DELETE PROPERTY IMAGES FROM FILESYSTEM
// ============================================
$img_sql = "SELECT image_path FROM property_images WHERE property_id = ?";
$img_stmt = mysqli_prepare($conn, $img_sql);
mysqli_stmt_bind_param($img_stmt, "i", $property_id);
mysqli_stmt_execute($img_stmt);
$img_result = mysqli_stmt_get_result($img_stmt);

$upload_dir = __DIR__ . '/../../images/';
while ($image = mysqli_fetch_assoc($img_result)) {
    $file_path = $upload_dir . $image['image_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}
mysqli_stmt_close($img_stmt);

// ============================================
// STEP 5: DELETE FROM DATABASE
// ============================================
// Note: property_images has ON DELETE CASCADE, so they will be deleted automatically
$del_sql = "DELETE FROM properties WHERE id = ?";
$del_stmt = mysqli_prepare($conn, $del_sql);
mysqli_stmt_bind_param($del_stmt, "i", $property_id);

if (mysqli_stmt_execute($del_stmt)) {
    $_SESSION['success_message'] = "Property deleted successfully";
} else {
    $_SESSION['error_message'] = "Error deleting property";
}

mysqli_stmt_close($del_stmt);

// ============================================
// STEP 6: CLOSE CONNECTION AND REDIRECT
// ============================================
close_db_connection($conn);

header('Location: ../views/property_list.php');
exit();
?>
