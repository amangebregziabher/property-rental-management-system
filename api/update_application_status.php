<?php
/**
 * API Endpoint to Update Rental Application Status
 * Allows property owners to approve or reject applications.
 * Logs status changes in application_status_history.
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

// Check Authentication
if (!isset($_SESSION['user_id'])) {
    send_response(false, 'Unauthorized. Please log in.', [], [], 401);
}

// Check if user is owner or admin
$user_role = strtolower($_SESSION['user_role'] ?? '');
if ($user_role !== 'owner' && $user_role !== 'admin') {
    send_response(false, 'Access denied. Only property owners can update application status.', [], [], 403);
}

// Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method. POST required.', [], [], 405);
}

// Get and Validate Input
$application_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
$new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
$reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING) ?? '';

if (!$application_id) {
    send_response(false, 'Invalid application ID.', [], [], 400);
}

if (!in_array($new_status, ['Approved', 'Rejected'])) {
    send_response(false, 'Invalid status. Must be "Approved" or "Rejected".', [], [], 400);
}

$conn = null;

try {
    $conn = get_db_connection();

    // 1. Verify that the owner has access to this application and get the old status
    $verify_sql = "SELECT ra.id, ra.status, ra.property_id 
                   FROM rental_applications ra
                   INNER JOIN properties p ON ra.property_id = p.id
                   WHERE ra.id = ? AND p.owner_id = ?";
    
    $verify_stmt = mysqli_prepare($conn, $verify_sql);
    mysqli_stmt_bind_param($verify_stmt, "ii", $application_id, $_SESSION['user_id']);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);

    if (mysqli_num_rows($verify_result) === 0) {
        mysqli_stmt_close($verify_stmt);
        send_response(false, 'Application not found or access denied.', [], [], 404);
    }

    $application_data = mysqli_fetch_assoc($verify_result);
    $old_status = $application_data['status'];
    mysqli_stmt_close($verify_stmt);

    // If status is the same, no need to update
    if ($old_status === $new_status) {
        send_response(true, "Application is already $new_status.", [
            'application_id' => $application_id,
            'status' => $new_status
        ]);
    }

    // Start Transaction
    mysqli_begin_transaction($conn);

    // 2. Update Application Status
    $update_sql = "UPDATE rental_applications SET status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "si", $new_status, $application_id);
    
    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception("Failed to update application status: " . mysqli_error($conn));
    }
    mysqli_stmt_close($update_stmt);

    // 3. Log Status History
    $history_sql = "INSERT INTO application_status_history (application_id, old_status, new_status, changed_by, reason) 
                    VALUES (?, ?, ?, ?, ?)";
    $history_stmt = mysqli_prepare($conn, $history_sql);
    mysqli_stmt_bind_param($history_stmt, "issis", $application_id, $old_status, $new_status, $_SESSION['user_id'], $reason);
    
    if (!mysqli_stmt_execute($history_stmt)) {
        throw new Exception("Failed to log status history: " . mysqli_error($conn));
    }
    mysqli_stmt_close($history_stmt);

    // Commit Transaction
    mysqli_commit($conn);
    close_db_connection($conn);

    send_response(true, "Application $new_status successfully.", [
        'application_id' => $application_id,
        'old_status' => $old_status,
        'new_status' => $new_status
    ]);

} catch (mysqli_sql_exception $e) {
    if ($conn) {
        mysqli_rollback($conn);
        close_db_connection($conn);
    }
    error_log("Database error in update_application_status.php: " . $e->getMessage());
    send_response(false, 'Database error occurred. Please try again later.', [], [], 500);
} catch (Exception $e) {
    if ($conn) {
        mysqli_rollback($conn);
        close_db_connection($conn);
    }
    error_log("Error in update_application_status.php: " . $e->getMessage());
    send_response(false, $e->getMessage(), [], [], 500);
}
?>
