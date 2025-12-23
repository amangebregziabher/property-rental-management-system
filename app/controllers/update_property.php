<?php
/**
 * Update Property Backend Handler
 * 
 * Handles property updates including form validation
 * and database updates.
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
$property_id = intval($_POST['property_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = $_POST['price'] ?? '';
$location = trim($_POST['location'] ?? '');
$type = $_POST['type'] ?? '';
$status = $_POST['status'] ?? '';

// ============================================
// STEP 2: VALIDATE INPUT DATA
// ============================================
if ($property_id <= 0) {
    $errors[] = "Invalid property ID";
}

if (empty($title)) {
    $errors[] = "Property title is required";
} elseif (strlen($title) > 255) {
    $errors[] = "Property title must be less than 255 characters";
}

if (empty($price)) {
    $errors[] = "Monthly rent is required";
} elseif (!is_numeric($price) || floatval($price) <= 0) {
    $errors[] = "Monthly rent must be a positive number";
}

if (empty($location)) {
    $errors[] = "Location is required";
}

$valid_types = ['Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse'];
if (!in_array($type, $valid_types)) {
    $errors[] = "Invalid property type";
}

$valid_statuses = ['Available', 'Rented', 'Maintenance'];
if (!in_array($status, $valid_statuses)) {
    $errors[] = "Invalid property status";
}

// ============================================
// STEP 3: HANDLE VALIDATION ERRORS
// ============================================
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    header('Location: ../views/edit_property.php?id=' . $property_id);
    exit();
}

$conn = get_db_connection();

// ============================================
// STEP 4: VERIFY OWNERSHIP (Mock for Prototype)
// ============================================
// For prototype, assume owner_id = 1
$owner_id = 1;

$check_sql = "SELECT owner_id FROM properties WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "i", $property_id);
mysqli_stmt_execute($check_stmt);
$check_res = mysqli_stmt_get_result($check_stmt);
$property_data = mysqli_fetch_assoc($check_res);

if (!$property_data || $property_data['owner_id'] != $owner_id) {
    $_SESSION['error_message'] = "Unauthorized access or property not found";
    close_db_connection($conn);
    header('Location: ../views/property_list.php');
    exit();
}

// ============================================
// STEP 5: UPDATE PROPERTY TABLE
// ============================================
$update_sql = "UPDATE properties SET title = ?, description = ?, price = ?, location = ?, type = ?, status = ? WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "ssdsssi", $title, $description, $price, $location, $type, $status, $property_id);

if (!mysqli_stmt_execute($update_stmt)) {
    $_SESSION['form_errors'] = ["Database error: Could not update property details"];
    close_db_connection($conn);
    header('Location: ../views/edit_property.php?id=' . $property_id);
    exit();
}

// ============================================
// STEP 6: FINAL REDIRECT
// ============================================
close_db_connection($conn);
$_SESSION['success_message'] = "Property updated successfully!";
header('Location: ../views/property_list.php');
exit();
?>
