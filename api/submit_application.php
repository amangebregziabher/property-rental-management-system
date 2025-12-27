<?php
/**
 * API Endpoint for Submitting Rental Applications
 * Receives application data, validates it, and stores it in the database.
 */

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// Start Session
session_start();

// Include database connection
require_once __DIR__ . '/../config/db_connect.php';

// Helper function to send JSON response
function send_response($success, $message, $data = [], $errors = [], $code = 200)
{
  http_response_code($code);
  echo json_encode([
    'success' => $success,
    'message' => $message,
    'data' => $data,
    'errors' => $errors
  ]);
  exit();
}

// Only allow POST methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  send_response(false, 'Method not allowed. Only POST is allowed.', [], [], 405);
}

// Get input data
$input = [];
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, 'application/json') !== false) {
  // Read JSON input
  $json = file_get_contents('php://input');
  $input = json_decode($json, true) ?? [];
} else {
  // Read Form Data
  $input = $_POST;
}

// --- Validation ---

$errors = [];

// 1. Property ID
$property_id = $input['property_id'] ?? null;
if (empty($property_id) || !is_numeric($property_id)) {
  $errors[] = "Valid Property ID is required.";
}

// 2. Applicant Details
$applicant_name = trim($input['applicant_name'] ?? '');
if (empty($applicant_name)) {
  $errors[] = "Applicant name is required.";
} elseif (strlen($applicant_name) > 255) {
  $errors[] = "Applicant name is too long.";
}

$applicant_email = trim($input['applicant_email'] ?? '');
if (empty($applicant_email)) {
  $errors[] = "Applicant email is required.";
} elseif (!filter_var($applicant_email, FILTER_VALIDATE_EMAIL)) {
  $errors[] = "Invalid email format.";
}

$applicant_phone = trim($input['applicant_phone'] ?? '');
// Optional phone validation could go here

$message = trim($input['message'] ?? '');

// Return validation errors
if (!empty($errors)) {
  send_response(false, 'Validation failed', [], $errors, 400);
}

// --- Database Operations ---

$conn = null;

try {
  // Enable mysqli error reporting for try-catch
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  $conn = get_db_connection();

  // Check if property exists
  $check_sql = "SELECT id FROM properties WHERE id = ?";
  $check_stmt = mysqli_prepare($conn, $check_sql);
  mysqli_stmt_bind_param($check_stmt, "i", $property_id);
  mysqli_stmt_execute($check_stmt);
  mysqli_stmt_store_result($check_stmt);

  if (mysqli_stmt_num_rows($check_stmt) === 0) {
    mysqli_stmt_close($check_stmt);
    send_response(false, 'Property not found', [], ['property_id' => 'Invalid Property ID'], 404);
  }
  mysqli_stmt_close($check_stmt);

  // Prepare User ID (if logged in)
  $user_id = $_SESSION['user_id'] ?? null; // Nullable in DB

  // Insert Application
  $sql = "INSERT INTO rental_applications (property_id, user_id, applicant_name, applicant_email, applicant_phone, message, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
  $stmt = mysqli_prepare($conn, $sql);

  if (!$stmt) {
    throw new Exception("Database prepare error: " . mysqli_error($conn));
  }

  // types: i (property_id), i (user_id - allowed null?), s (name), s (email), s (phone), s (message)
  // IMPORTANT: user_id is nullable. bind_param doesn't handle null directly well with 'i'.
  // We should treat user_id as a variable that might be null.
  // However, mysqli_stmt_bind_param expects variables passed by reference.

  // Workaround for nullable integer in bind_param:
  // If $user_id is null, we can't just pass generic 'i'. 
  // But since we are constructing the query, let's see. 
  // Actually, bind_param doesn't support "i" for null easily unless we manage it carefully or use "s" which usually works for ints too, or explicitly handle nulls.

  // Simplest way is strict variable types.
  // If $user_id is null, we can pass null if the type is correct?
  // Let's use 's' for simple parameters or handle better.
  // Actually, 'i' works with null in modern PHP/mysqli if variable is null.

  mysqli_stmt_bind_param($stmt, "iissss", $property_id, $user_id, $applicant_name, $applicant_email, $applicant_phone, $message);

  if (mysqli_stmt_execute($stmt)) {
    $application_id = mysqli_insert_id($conn);
    send_response(true, 'Application submitted successfully', ['application_id' => $application_id]);
  } else {
    throw new Exception("Database execute error: " . mysqli_stmt_error($stmt));
  }

  mysqli_stmt_close($stmt);
  close_db_connection($conn);

} catch (mysqli_sql_exception $e) {
  if ($conn)
    close_db_connection($conn);
  // Log error securely in real app
  send_response(false, 'Database error occurred', [], ['details' => $e->getMessage()], 500);
} catch (Exception $e) {
  if ($conn)
    close_db_connection($conn);
  send_response(false, 'An unexpected error occurred', [], ['details' => $e->getMessage()], 500);
}
?>