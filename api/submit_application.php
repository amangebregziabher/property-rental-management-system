<?php
/**
 * API Endpoint for Submitting Rental Applications
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  send_response(false, 'Invalid request method.', [], [], 405);
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
  send_response(false, 'Authentication required.', [], ['auth' => 'Please sign in to submit an application.'], 401);
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$property_id = $input['property_id'] ?? 0;
$applicant_name = trim($input['applicant_name'] ?? '');
$applicant_email = trim($input['applicant_email'] ?? '');
$applicant_phone = trim($input['applicant_phone'] ?? '');
$message = trim($input['message'] ?? '');

$errors = [];
if (empty($property_id))
  $errors['property_id'] = "Property ID is required.";
if (empty($applicant_name))
  $errors['applicant_name'] = "Full name is required.";
if (empty($applicant_email))
  $errors['applicant_email'] = "Email address is required.";
if (empty($applicant_phone))
  $errors['applicant_phone'] = "Phone number is required.";

if (!empty($errors)) {
  send_response(false, 'Validation failed.', [], $errors, 400);
}

$conn = get_db_connection();

try {
  // Check if property exists
  $prop_check = mysqli_prepare($conn, "SELECT id FROM properties WHERE id = ?");
  mysqli_stmt_bind_param($prop_check, "i", $property_id);
  mysqli_stmt_execute($prop_check);
  if (!mysqli_fetch_assoc(mysqli_stmt_get_result($prop_check))) {
    throw new Exception("Property not found.");
  }
  mysqli_stmt_close($prop_check);

  // Insert application
  $sql = "INSERT INTO rental_applications (property_id, user_id, applicant_name, applicant_email, applicant_phone, message) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "iissss", $property_id, $user_id, $applicant_name, $applicant_email, $applicant_phone, $message);

  if (mysqli_stmt_execute($stmt)) {
    $application_id = mysqli_insert_id($conn);
    send_response(true, 'Application submitted successfully!', ['application_id' => $application_id]);
  } else {
    throw new Exception("Error saving application: " . mysqli_stmt_error($stmt));
  }

} catch (Exception $e) {
  send_response(false, $e->getMessage(), [], [], 500);
} finally {
  close_db_connection($conn);
}
