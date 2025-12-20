<?php
/**
 * Get Property by ID API
 * 
 * Returns property details and associated images as JSON.
 * Usage: get_property.php?id={property_id}
 */

header('Content-Type: application/json');

require_once '../../config/db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Property ID is required and must be numeric'
    ]);
    exit();
}

$property_id = (int) $_GET['id'];
$conn = get_db_connection();

try {
    // 1. Fetch property details with owner info
    $sql = "SELECT p.*, u.name as owner_name, u.email as owner_email 
            FROM properties p 
            JOIN users u ON p.owner_id = u.id 
            WHERE p.id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $property = mysqli_fetch_assoc($result);

    if (!$property) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Property not found'
        ]);
        exit();
    }

    // 2. Fetch associated images
    $img_sql = "SELECT id, image_path, is_main FROM property_images WHERE property_id = ?";
    $img_stmt = mysqli_prepare($conn, $img_sql);
    mysqli_stmt_bind_param($img_stmt, "i", $property_id);
    mysqli_stmt_execute($img_stmt);
    $img_result = mysqli_stmt_get_result($img_stmt);

    $images = [];
    while ($row = mysqli_fetch_assoc($img_result)) {
        $images[] = $row;
    }

    // Combine data
    $property['images'] = $images;

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $property
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An internal server error occurred: ' . $e->getMessage()
    ]);
} finally {
    close_db_connection($conn);
}
