<?php
/**
 * API Endpoint to Retrieve Owner Applications
 * Fetches all rental applications for properties owned by the logged-in owner.
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

// Check if user is owner or admin
$user_role = strtolower($_SESSION['user_role'] ?? '');
if ($user_role !== 'owner' && $user_role !== 'admin') {
    send_response(false, 'Access denied. Only property owners can access this endpoint.', [], [], 403);
}

$user_id = $_SESSION['user_id'];

// Get filter parameters
$property_filter = filter_input(INPUT_GET, 'property_id', FILTER_VALIDATE_INT);
$status_filter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);

// Database Connection
$conn = null;

try {
    $conn = get_db_connection();

    // Query to fetch applications with property and applicant details
    $sql = "SELECT 
                ra.id AS application_id,
                ra.applicant_name,
                ra.applicant_email,
                ra.applicant_phone,
                ra.status AS application_status,
                ra.created_at AS application_date,
                ra.message,
                ra.occupants,
                ra.move_in_date,
                ra.employer,
                ra.job_title,
                ra.monthly_income,
                ra.employment_status,
                ra.id_document_path,
                ra.income_document_path,
                p.id AS property_id,
                p.title AS property_title,
                p.location AS property_location,
                p.price AS property_price,
                p.type AS property_type,
                p.bedrooms,
                p.bathrooms,
                pi.image_path AS property_image
            FROM rental_applications ra
            INNER JOIN properties p ON ra.property_id = p.id
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
            WHERE p.owner_id = ?";

    $params = [$user_id];
    $types = "i";

    // Apply filters
    if ($property_filter) {
        $sql .= " AND ra.property_id = ?";
        $params[] = $property_filter;
        $types .= "i";
    }

    if ($status_filter && in_array($status_filter, ['Pending', 'Approved', 'Rejected'])) {
        $sql .= " AND ra.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    $sql .= " ORDER BY ra.created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception("Database prepare error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $applications = [];

    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate income to rent ratio
        $income_ratio = null;
        if ($row['monthly_income'] && $row['property_price'] > 0) {
            $income_ratio = round(($row['monthly_income'] / $row['property_price']), 2);
        }

        // Check document upload status
        $documents_uploaded = [
            'id_document' => !empty($row['id_document_path']),
            'income_document' => !empty($row['income_document_path'])
        ];

        $row['income_to_rent_ratio'] = $income_ratio;
        $row['documents_status'] = $documents_uploaded;
        
        $applications[] = $row;
    }

    mysqli_stmt_close($stmt);

    // Get statistics
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN ra.status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN ra.status = 'Approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN ra.status = 'Rejected' THEN 1 ELSE 0 END) as rejected
        FROM rental_applications ra
        INNER JOIN properties p ON ra.property_id = p.id
        WHERE p.owner_id = ?";

    $stats_stmt = mysqli_prepare($conn, $stats_sql);
    mysqli_stmt_bind_param($stats_stmt, "i", $user_id);
    mysqli_stmt_execute($stats_stmt);
    $stats_result = mysqli_stmt_get_result($stats_stmt);
    $stats = mysqli_fetch_assoc($stats_result);
    mysqli_stmt_close($stats_stmt);

    close_db_connection($conn);

    // Check if no applications were found
    if (empty($applications)) {
        send_response(true, 'No applications found for your properties.', [
            'applications' => $applications,
            'statistics' => $stats
        ]);
    }

    send_response(true, 'Applications retrieved successfully', [
        'applications' => $applications,
        'statistics' => $stats
    ]);

} catch (mysqli_sql_exception $e) {
    if ($conn) {
        close_db_connection($conn);
    }
    // Database-specific errors - log for debugging
    error_log("Database error in get_owner_applications.php: " . $e->getMessage());
    send_response(false, 'Unable to retrieve applications due to a database error. Please try again later.', [], [], 500);
} catch (Exception $e) {
    if ($conn) {
        close_db_connection($conn);
    }
    // General errors - log for debugging
    error_log("Error in get_owner_applications.php: " . $e->getMessage());
    send_response(false, 'An unexpected error occurred while fetching applications. Please try again later.', [], [], 500);
}
?>
