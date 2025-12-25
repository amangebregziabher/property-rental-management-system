<?php
/**
 * API Endpoint for Property Update
 * Handles updating existing properties via POST request (JSON or Form Data)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once __DIR__ . '/../config/db_connect.php';

function send_response($success, $message, $data = [], $errors = [], $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data, 'errors' => $errors]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method. Please use POST.', [], [], 405);
}

// Receive input
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($content_type, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}

$property_id = $input['property_id'] ?? 0;
if (empty($property_id)) {
    send_response(false, 'Property ID is required', [], [], 400);
}

// Validation
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$price = $input['price'] ?? '';
$location = trim($input['location'] ?? '');
$type = $input['type'] ?? '';
$status = $input['status'] ?? '';

$errors = [];
if (empty($title)) $errors[] = "Title is required";
if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required";
if (empty($location)) $errors[] = "Location is required";

if (!empty($errors)) {
    send_response(false, 'Validation failed', [], $errors, 400);
}

$conn = get_db_connection();

// Check existence
$check_sql = "SELECT id FROM properties WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "i", $property_id);
mysqli_stmt_execute($check_stmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt))) {
    close_db_connection($conn);
    send_response(false, 'Property not found', [], [], 404);
}
mysqli_stmt_close($check_stmt);

// Update
$sql = "UPDATE properties SET title = ?, description = ?, price = ?, location = ?, type = ?, status = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssdsssi", $title, $description, $price, $location, $type, $status, $property_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    close_db_connection($conn);
    send_response(true, 'Property updated successfully', ['property_id' => $property_id]);
} else {
    $db_error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    close_db_connection($conn);
    send_response(false, 'Database update failed', [], [$db_error], 500);
}
?>
