<?php
/**
 * Add Property Backend Handler
 * 
 * handles property creation including form validation
 * and database insertion.
 */

session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Initialize response variables
$errors = [];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/add_property.php');
    exit();
}

// ============================================
// STEP 1: RECEIVE AND SANITIZE POST DATA
// ============================================
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = $_POST['price'] ?? '';
$location = trim($_POST['location'] ?? '');
$type = $_POST['type'] ?? '';
$status = $_POST['status'] ?? 'Available';

// ============================================
// STEP 2: VALIDATE INPUT DATA
// ============================================
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
if (empty($type) || !in_array($type, $valid_types)) {
    $errors[] = "Invalid property type selected";
}

$valid_statuses = ['Available', 'Rented', 'Maintenance'];
if (empty($status) || !in_array($status, $valid_statuses)) {
    $errors[] = "Invalid property status selected";
}

// ============================================
// STEP 3: IF ERRORS, REDIRECT BACK
// ============================================
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = [
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'location' => $location,
        'type' => $type,
        'status' => $status
    ];
    header('Location: ../views/add_property.php');
    exit();
}

// ============================================
// STEP 4: DATABASE CONNECTION
// ============================================
$conn = get_db_connection();

// ============================================
// STEP 5: INSERT PROPERTY INTO DATABASE
// ============================================
// For prototype, use owner_id = 1
$owner_id = 1;

$sql = "INSERT INTO properties (owner_id, title, description, price, location, type, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    $_SESSION['form_errors'] = ["Database error: Could not prepare statement"];
    header('Location: ../views/add_property.php');
    exit();
}

mysqli_stmt_bind_param($stmt, "issdsss", $owner_id, $title, $description, $price, $location, $type, $status);

if (!mysqli_stmt_execute($stmt)) {
    $_SESSION['form_errors'] = ["Database error: Could not insert property"];
    mysqli_stmt_close($stmt);
    close_db_connection($conn);
    header('Location: ../views/add_property.php');
    exit();
}

mysqli_stmt_close($stmt);

// ============================================
// STEP 6: CLOSE CONNECTION AND REDIRECT
// ============================================
close_db_connection($conn);

$_SESSION['success_message'] = "Property added successfully!";

header('Location: ../views/property_list.php');
exit();
