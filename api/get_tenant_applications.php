<?php
/**
 * API Endpoint to Retrieve Tenant Applications
 * Fetches all rental applications submitted by the logged-in tenant.
 */

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

$user_id = $_SESSION['user_id'];

// Database Connection
$conn = null;

try {
    $conn = get_db_connection();

    // Query to fetch applications with property details
    // We LEFT JOIN with properties to get the property title and location even if the property was deleted (though ideally it shouldn't be hard deleted)
    $sql = "SELECT 
                ra.id AS application_id,
                ra.status AS application_status,
                ra.created_at AS application_date,
                ra.message AS application_message,
                ra.applicant_name,
                ra.applicant_email,
                ra.applicant_phone,
                p.id AS property_id,
                p.title AS property_title,
                p.location AS property_location,
                p.price AS property_price,
                (SELECT MAX(changed_at) FROM application_status_history ash WHERE ash.application_id = ra.id) as status_updated_at
            FROM rental_applications ra
            LEFT JOIN properties p ON ra.property_id = p.id
            WHERE ra.user_id = ?
            ORDER BY ra.created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception("Database prepare error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $applications = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $applications[] = $row;
    }

    mysqli_stmt_close($stmt);
    close_db_connection($conn);

    // Check if no applications were found
    if (empty($applications)) {
        send_response(true, 'No applications found. You haven\'t submitted any rental applications yet.', $applications);
    }

    send_response(true, 'Applications retrieved successfully', $applications);

} catch (mysqli_sql_exception $e) {
    if ($conn) {
        close_db_connection($conn);
    }
    // Database-specific errors - log for debugging
    error_log("Database error in get_tenant_applications.php: " . $e->getMessage());
    send_response(false, 'Unable to retrieve applications due to a database error. Please try again later.', [], [], 500);
} catch (Exception $e) {
    if ($conn) {
        close_db_connection($conn);
    }
    // General errors - log for debugging
    error_log("Error in get_tenant_applications.php: " . $e->getMessage());
    send_response(false, 'An unexpected error occurred while fetching your applications. Please try again later.', [], [], 500);
}
?>