<?php
session_start();

// Check if user is logged in and is an owner or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'owner' && $_SESSION['user_role'] !== 'admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$application_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
$new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

// Validate inputs
if (!$application_id || !$new_status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Validate status value
$allowed_statuses = ['Pending', 'Approved', 'Rejected'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit();
}

$conn = get_db_connection();

try {
    // First, verify that the application belongs to a property owned by this user
    $verify_sql = "SELECT ra.id 
                   FROM rental_applications ra
                   INNER JOIN properties p ON ra.property_id = p.id
                   WHERE ra.id = ? AND p.owner_id = ?";
    
    $verify_stmt = mysqli_prepare($conn, $verify_sql);
    mysqli_stmt_bind_param($verify_stmt, "ii", $application_id, $_SESSION['user_id']);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    
    if (mysqli_num_rows($verify_result) === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to update this application']);
        close_db_connection($conn);
        exit();
    }
    
    // Update the application status
    $update_sql = "UPDATE rental_applications SET status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "si", $new_status, $application_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        // If approved, you might want to update the property status or create a booking
        // This is a placeholder for future enhancement
        if ($new_status === 'Approved') {
            // TODO: Implement booking creation or property status update
        }
        
        echo json_encode([
            'success' => true, 
            'message' => "Application has been {$new_status} successfully",
            'status' => $new_status
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update application status']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
} finally {
    close_db_connection($conn);
}
?>
