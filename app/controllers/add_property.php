<?php
/**
 * Add Property Backend Handler
 * 
 * This file receives POST data from the add property form
 * and will handle property creation in the database.
 * 
 * NOTE: This is a PROTOTYPE - Backend logic is NOT implemented
 */

// ============================================
// STEP 1: RECEIVE POST DATA
// ============================================
// TODO: Receive form data from POST request
// Expected fields:
// - title (string, required)
// - description (text, optional)
// - price (decimal, required)
// - location (string, required)
// - type (enum, required)
// - status (enum, required)
// - images[] (file array, optional)

// Example (not implemented):
// $title = $_POST['title'] ?? '';
// $description = $_POST['description'] ?? '';
// $price = $_POST['price'] ?? 0;
// $location = $_POST['location'] ?? '';
// $type = $_POST['type'] ?? '';
// $status = $_POST['status'] ?? '';

// ============================================
// STEP 2: VALIDATE INPUT DATA
// ============================================
// TODO: Validate all required fields are present
// - Check if title is not empty
// - Check if price is numeric and positive
// - Check if location is not empty
// - Check if type is valid enum value
// - Check if status is valid enum value

// Example validation (not implemented):
// $errors = [];
// if (empty($title)) {
//     $errors[] = "Title is required";
// }
// if (!is_numeric($price) || $price <= 0) {
//     $errors[] = "Price must be a positive number";
// }
// if (empty($location)) {
//     $errors[] = "Location is required";
// }

// ============================================
// STEP 3: VALIDATE IMAGE UPLOADS
// ============================================
// TODO: Validate uploaded images
// - Check file types (jpg, jpeg, png, gif only)
// - Check file sizes (max 5MB per file)
// - Check for upload errors
// - Validate total number of images

// Example validation (not implemented):
// $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
// $max_file_size = 5 * 1024 * 1024; // 5MB
// 
// if (isset($_FILES['images'])) {
//     foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
//         $file_type = $_FILES['images']['type'][$key];
//         $file_size = $_FILES['images']['size'][$key];
//         
//         if (!in_array($file_type, $allowed_types)) {
//             $errors[] = "Invalid file type for image " . ($key + 1);
//         }
//         if ($file_size > $max_file_size) {
//             $errors[] = "Image " . ($key + 1) . " exceeds 5MB limit";
//         }
//     }
// }

// ============================================
// STEP 4: DATABASE CONNECTION
// ============================================
// TODO: Include database connection
// require_once '../../config/db_connect.php';
// $conn = get_db_connection();

// ============================================
// STEP 5: INSERT PROPERTY INTO DATABASE
// ============================================
// TODO: Insert property data into properties table
// - Get current user ID (from session - not implemented)
// - Prepare SQL INSERT statement
// - Bind parameters to prevent SQL injection
// - Execute query
// - Get inserted property ID

// Example SQL (not implemented):
// $owner_id = 1; // Hardcoded for prototype (should come from session)
// 
// $sql = "INSERT INTO properties (title, description, price, location, type, status, owner_id) 
//         VALUES (?, ?, ?, ?, ?, ?, ?)";
// 
// $stmt = mysqli_prepare($conn, $sql);
// mysqli_stmt_bind_param($stmt, "ssdsssi", $title, $description, $price, $location, $type, $status, $owner_id);
// mysqli_stmt_execute($stmt);
// $property_id = mysqli_insert_id($conn);

// ============================================
// STEP 6: HANDLE IMAGE UPLOADS
// ============================================
// TODO: Process and save uploaded images
// - Create uploads directory if not exists
// - Generate unique filenames
// - Move uploaded files to storage
// - Insert image paths into property_images table

// Example (not implemented):
// $upload_dir = '../../storage/uploads/';
// 
// if (isset($_FILES['images'])) {
//     foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
//         if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
//             $file_name = $_FILES['images']['name'][$key];
//             $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
//             $new_filename = 'property_' . $property_id . '_' . time() . '_' . $key . '.' . $file_ext;
//             $destination = $upload_dir . $new_filename;
//             
//             if (move_uploaded_file($tmp_name, $destination)) {
//                 // Insert into property_images table
//                 $is_primary = ($key === 0) ? 1 : 0;
//                 $img_sql = "INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?, ?, ?)";
//                 $img_stmt = mysqli_prepare($conn, $img_sql);
//                 mysqli_stmt_bind_param($img_stmt, "isi", $property_id, $new_filename, $is_primary);
//                 mysqli_stmt_execute($img_stmt);
//             }
//         }
//     }
// }

// ============================================
// STEP 7: CLOSE DATABASE CONNECTION
// ============================================
// TODO: Close database connection
// close_db_connection($conn);

// ============================================
// STEP 8: REDIRECT OR SHOW SUCCESS MESSAGE
// ============================================
// TODO: Redirect to property list with success message
// header('Location: ../views/property_list.php?success=1');
// exit();

// ============================================
// PLACEHOLDER RESPONSE (PROTOTYPE)
// ============================================
echo "<!DOCTYPE html>";
echo "<html><head><title>Add Property - Response</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container mt-5'>";
echo "<div class='alert alert-info'>";
echo "<h4>Prototype Notice</h4>";
echo "<p><strong>Backend logic is NOT implemented.</strong></p>";
echo "<p>In a full implementation, this page would:</p>";
echo "<ul>";
echo "<li>Validate the submitted form data</li>";
echo "<li>Insert the property into the database</li>";
echo "<li>Upload and save property images</li>";
echo "<li>Redirect to the property list</li>";
echo "</ul>";
echo "<p>Received POST data (if any): " . (empty($_POST) ? "None" : "Present") . "</p>";
echo "</div>";
echo "<a href='../views/property_list.php' class='btn btn-primary'>Back to Property List</a>";
echo "</div>";
echo "</body></html>";
?>
