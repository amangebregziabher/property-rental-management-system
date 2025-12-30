<?php
/**
 * Controller to Add Internal Notes to Applications
 * Allows property owners to add private notes for tracking application review
 */

session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Helper function to send JSON response
function send_json_response($success, $message, $data = [])
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    send_json_response(false, 'Unauthorized. Please log in.');
}

// Check if user is owner or admin
$user_role = strtolower($_SESSION['user_role'] ?? '');
if ($user_role !== 'owner' && $user_role !== 'admin') {
    send_json_response(false, 'Access denied. Only property owners can add notes.');
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Invalid request method. POST required.');
}

// Get and validate input
$application_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
$note = trim(filter_input(INPUT_POST, 'note', FILTER_SANITIZE_STRING));

if (!$application_id) {
    send_json_response(false, 'Invalid application ID.');
}

if (empty($note)) {
    send_json_response(false, 'Note cannot be empty.');
}

$conn = null;

try {
    $conn = get_db_connection();

    // Verify that the owner has access to this application
    $verify_sql = "SELECT ra.id 
                   FROM rental_applications ra
                   INNER JOIN properties p ON ra.property_id = p.id
                   WHERE ra.id = ? AND p.owner_id = ?";
    
    $verify_stmt = mysqli_prepare($conn, $verify_sql);
    mysqli_stmt_bind_param($verify_stmt, "ii", $application_id, $_SESSION['user_id']);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);

    if (mysqli_num_rows($verify_result) === 0) {
        mysqli_stmt_close($verify_stmt);
        close_db_connection($conn);
        send_json_response(false, 'Application not found or access denied.');
    }

    mysqli_stmt_close($verify_stmt);

    // Insert the note
    $insert_sql = "INSERT INTO application_notes (application_id, owner_id, note) 
                   VALUES (?, ?, ?)";
    
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "iis", $application_id, $_SESSION['user_id'], $note);
    
    if (!mysqli_stmt_execute($insert_stmt)) {
        throw new Exception("Failed to add note: " . mysqli_error($conn));
    }

    $note_id = mysqli_insert_id($conn);
    mysqli_stmt_close($insert_stmt);
    close_db_connection($conn);

    send_json_response(true, 'Note added successfully.', [
        'note_id' => $note_id,
        'created_at' => date('Y-m-d H:i:s')
    ]);

} catch (mysqli_sql_exception $e) {
    if ($conn) {
        close_db_connection($conn);
    }
    error_log("Database error in add_application_note.php: " . $e->getMessage());
    send_json_response(false, 'Database error occurred. Please try again.');
} catch (Exception $e) {
    if ($conn) {
        close_db_connection($conn);
    }
    error_log("Error in add_application_note.php: " . $e->getMessage());
    send_json_response(false, 'An error occurred. Please try again.');
}
?>
