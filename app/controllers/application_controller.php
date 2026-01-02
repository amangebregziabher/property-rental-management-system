<?php
session_start();
require_once __DIR__ . '/../../config/db_connect.php';

// Access control: Only owners and admins can perform these actions
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'owner' && $_SESSION['user_role'] !== 'admin')) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
  exit();
}

$action = $_GET['action'] ?? '';
$application_id = $_GET['id'] ?? 0;

if (!$application_id || !is_numeric($application_id)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid application ID.']);
  exit();
}

$conn = get_db_connection();

// Verify ownership of the property associated with the application
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$verify_sql = "SELECT ra.id, ra.property_id, p.owner_id 
               FROM rental_applications ra 
               JOIN properties p ON ra.property_id = p.id 
               WHERE ra.id = ?";
$verify_stmt = mysqli_prepare($conn, $verify_sql);
mysqli_stmt_bind_param($verify_stmt, "i", $application_id);
mysqli_stmt_execute($verify_stmt);
$verify_result = mysqli_stmt_get_result($verify_stmt);
$application_data = mysqli_fetch_assoc($verify_result);

if (!$application_data || ($user_role !== 'admin' && $application_data['owner_id'] != $user_id)) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'You do not have permission to manage this application.']);
  close_db_connection($conn);
  exit();
}

if ($action === 'approve') {
  $status = 'Approved';
  $property_status_update = "Rented";
} elseif ($action === 'reject') {
  $status = 'Rejected';
  $property_status_update = null;
} elseif ($action === 'reset') {
  $status = 'Pending';
  $property_status_update = "Available";
} else {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid action.']);
  close_db_connection($conn);
  exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
  // 1. Update application status
  $update_app_sql = "UPDATE rental_applications SET status = ? WHERE id = ?";
  $update_app_stmt = mysqli_prepare($conn, $update_app_sql);
  mysqli_stmt_bind_param($update_app_stmt, "si", $status, $application_id);

  if (!mysqli_stmt_execute($update_app_stmt)) {
    throw new Exception("Error updating application status.");
  }

  // 2. Update property status if necessary
  if ($property_status_update) {
    $property_id = $application_data['property_id']; // Need to ensure property_id is in $application_data
    $update_prop_sql = "UPDATE properties SET status = ? WHERE id = ?";
    $update_prop_stmt = mysqli_prepare($conn, $update_prop_sql);
    mysqli_stmt_bind_param($update_prop_stmt, "si", $property_status_update, $property_id);

    if (!mysqli_stmt_execute($update_prop_stmt)) {
      throw new Exception("Error updating property status.");
    }
  }

  mysqli_commit($conn);
  echo json_encode(['success' => true, 'message' => "Application has been $status and property status updated."]);
} catch (Exception $e) {
  mysqli_rollback($conn);
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

close_db_connection($conn);
