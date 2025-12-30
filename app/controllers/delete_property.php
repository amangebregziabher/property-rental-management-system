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

// Helper function for sending JSON response
function send_json_response($success, $message, $status_code = 200) {
    header('Content-Type: application/json');
    http_response_code($status_code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// Check if it's an AJAX request
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if (empty($property_id) || !is_numeric($property_id) || intval($property_id) <= 0) {
    $error = "Invalid property ID";
    if ($is_ajax) {
        send_json_response(false, $error, 400);
    }
    $_SESSION['error_message'] = $error;
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
    $error = "Property not found";
    if ($is_ajax) {
        send_json_response(false, $error, 404);
    }
    $_SESSION['error_message'] = $error;
    close_db_connection($conn);
    header('Location: ../views/property_list.php');
    exit();
}

// ============================================
// STEP 4: AUTHORIZATION CHECK
// ============================================
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $error = "Please login to perform this action";
    if ($is_ajax) {
        send_json_response(false, $error, 401);
    }
    $_SESSION['error_message'] = $error;
    close_db_connection($conn);
    header('Location: ../views/login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'tenant';

// Only the owner or an admin can delete the property
if ($user_id != $property['owner_id'] && $user_role !== 'admin') {
    $error = "You are not authorized to delete this property";
    if ($is_ajax) {
        send_json_response(false, $error, 403);
    }
    $_SESSION['error_message'] = $error;
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
    $msg = "Property deleted successfully";
    if ($is_ajax) {
        close_db_connection($conn);
        send_json_response(true, $msg);
    }
    $_SESSION['success_message'] = $msg;
} else {
    $error = "Error deleting property";
    if ($is_ajax) {
        close_db_connection($conn);
        send_json_response(false, $error, 500);
    }
    $_SESSION['error_message'] = $error;
}

mysqli_stmt_close($del_stmt);

// ============================================
// STEP 6: CLOSE CONNECTION AND REDIRECT
// ============================================
close_db_connection($conn);

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../views/admin_dashboard.php');
} else {
    header('Location: ../views/property_list.php');
}
exit();
?>
