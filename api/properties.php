<?php
/**
 * API Endpoint for Property Management
 * Handles creating new properties via POST request
 */

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Configure as needed for production
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// Start session to access authentication if available
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

// Check authentication - fallback to ID 1 for prototype if not logged in
$owner_id = $_SESSION['user_id'] ?? 1;

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  // Get ID from query string or JSON body
  $property_id = $_GET['id'] ?? null;

  if (!$property_id) {
    // Try reading from body if not in query parameters
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $property_id = $data['id'] ?? null;
  }

  if (!$property_id) {
    send_response(false, 'Property ID is required', [], [], 400);
  }

  $conn = get_db_connection();

  try {
    // Check property existence and ownership
    $check_sql = "SELECT id, owner_id FROM properties WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $property = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$property) {
      send_response(false, 'Property not found', [], [], 404);
    }

    if ($property['owner_id'] != $owner_id) {
      send_response(false, 'Unauthorized: You do not own this property', [], [], 403);
    }

    // Get associated images to delete files from filesystem
    // (Database records will be deleted via CASCADE, but we need paths first)
    $img_sql = "SELECT image_path FROM property_images WHERE property_id = ?";
    $stmt = mysqli_prepare($conn, $img_sql);
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $res_images = mysqli_stmt_get_result($stmt);

    $images_to_delete = [];
    while ($row = mysqli_fetch_assoc($res_images)) {
      $images_to_delete[] = $row['image_path'];
    }
    mysqli_stmt_close($stmt);

    // Start Transaction
    mysqli_begin_transaction($conn);

    // Delete Property (Cascades to property_images)
    $delete_sql = "DELETE FROM properties WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $property_id);

    if (!mysqli_stmt_execute($stmt)) {
      throw new Exception("Failed to delete property record");
    }
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);
    close_db_connection($conn);

    // Remove files from filesystem
    $deleted_files_count = 0;
    $upload_dir = __DIR__ . '/../images/';

    foreach ($images_to_delete as $img_file) {
      $file_path = $upload_dir . $img_file;
      if (file_exists($file_path)) {
        if (unlink($file_path)) {
          $deleted_files_count++;
        }
      }
    }

    send_response(true, 'Property and associated images deleted successfully', [
      'property_id' => $property_id,
      'deleted_images_count' => $deleted_files_count
    ]);

  } catch (Exception $e) {
    if (isset($conn)) {
      mysqli_rollback($conn);
      close_db_connection($conn);
    }
    send_response(false, 'Error deleting property: ' . $e->getMessage(), [], [], 500);
  }
}

// Only allow POST methods (for the remaining logic)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  send_response(false, 'Invalid request method. Only POST and DELETE are allowed.', [], [], 405);
}

// Initialize variables
$errors = [];
$input = [];

// Check if content type is JSON
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($content_type, 'application/json') !== false) {
  // Read JSON input
  $json = file_get_contents('php://input');
  $input = json_decode($json, true) ?? [];
} else {
  // Read Form Data
  $input = $_POST;
}

// Map 'type' to 'property_type' if needed
if (isset($input['type']) && !isset($input['property_type'])) {
  $input['property_type'] = $input['type'];
}

// --- Validation ---

$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$price = $input['price'] ?? '';
$location = trim($input['location'] ?? '');
$property_type = $input['property_type'] ?? '';
$status = $input['status'] ?? 'Available';

// Validate Title
if (empty($title)) {
  $errors[] = "Property title is required";
} elseif (strlen($title) > 255) {
  $errors[] = "Property title must be less than 255 characters";
}

// Validate Price
if (empty($price)) {
  $errors[] = "Monthly rent is required";
} elseif (!is_numeric($price) || floatval($price) <= 0) {
  $errors[] = "Monthly rent must be a positive number";
}

// Validate Location
if (empty($location)) {
  $errors[] = "Location is required";
}

// Validate Property Type
$valid_types = ['Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse'];
if (empty($property_type) || !in_array($property_type, $valid_types)) {
  $errors[] = "Invalid property type selected. Valid types: " . implode(', ', $valid_types);
}

// Validate Status
$valid_statuses = ['Available', 'Rented', 'Maintenance'];
if (!in_array($status, $valid_statuses)) {
  $errors[] = "Invalid property status selected";
}

// Return errors if any
if (!empty($errors)) {
  send_response(false, 'Validation failed', [], $errors, 400);
}

// --- Image Validation (Only for multipart/form-data) ---
$uploaded_images = [];
$max_file_size = 5 * 1024 * 1024; // 5MB
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
  $file_count = count($_FILES['images']['name']);

  for ($i = 0; $i < $file_count; $i++) {
    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE)
      continue;

    if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
      $errors[] = "Error uploading image " . ($i + 1);
      continue;
    }

    $file_name = $_FILES['images']['name'][$i];
    $file_size = $_FILES['images']['size'][$i];
    $file_tmp = $_FILES['images']['tmp_name'][$i];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_mime = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);

    if (!in_array($file_mime, $allowed_types) || !in_array($file_ext, $allowed_extensions)) {
      $errors[] = "Image " . ($i + 1) . " has an invalid file type.";
      continue;
    }

    if ($file_size > $max_file_size) {
      $errors[] = "Image " . ($i + 1) . " exceeds the 5MB size limit";
      continue;
    }

    $uploaded_images[] = [
      'tmp_name' => $file_tmp,
      'extension' => $file_ext,
      'original_name' => $file_name
    ];
  }
}

if (!empty($errors)) {
  send_response(false, 'Image validation failed', [], $errors, 400);
}

// --- Database Insertion ---

$conn = get_db_connection();

try {
  // Start Transaction
  mysqli_begin_transaction($conn);

  // Insert Property
  // Actual DB uses 'type' and 'property_images' uses 'is_primary'
  $sql = "INSERT INTO properties (owner_id, title, description, price, location, type, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);

  if (!$stmt) {
    throw new Exception("Database prepare error: " . mysqli_error($conn));
  }

  mysqli_stmt_bind_param($stmt, "issdsss", $owner_id, $title, $description, $price, $location, $property_type, $status);

  if (!mysqli_stmt_execute($stmt)) {
    throw new Exception("Database execute error: " . mysqli_stmt_error($stmt));
  }

  $property_id = mysqli_insert_id($conn);
  mysqli_stmt_close($stmt);

  // Handle Image Uploads
  $upload_dir = __DIR__ . '/../images/';
  if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
      throw new Exception("Failed to create image upload directory");
    }
  }

  $images_saved = 0;
  $saved_files = []; // Track files to delete on rollback if needed

  foreach ($uploaded_images as $index => $image) {
    $new_filename = 'property_' . $property_id . '_' . time() . '_' . $index . '.' . $image['extension'];
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($image['tmp_name'], $destination)) {
      $saved_files[] = $destination; // Keep track

      $is_primary = ($index === 0) ? 1 : 0;
      $img_sql = "INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?, ?, ?)";
      $img_stmt = mysqli_prepare($conn, $img_sql);

      if ($img_stmt) {
        mysqli_stmt_bind_param($img_stmt, "isi", $property_id, $new_filename, $is_primary);
        if (!mysqli_stmt_execute($img_stmt)) {
          mysqli_stmt_close($img_stmt);
          throw new Exception("Failed to save image record to database");
        }
        mysqli_stmt_close($img_stmt);
        $images_saved++;
      } else {
        throw new Exception("Database prepare error for images: " . mysqli_error($conn));
      }
    } else {
      // If file move fails, we consider this a critical error for integrity or just skip?
      // User asked for "proper linking and transactional integrity".
      // It's cleaner to fail the whole request if an image upload fails so user knows to retry.
      throw new Exception("Failed to upload image file: " . $image['original_name']);
    }
  }

  // Commit Transaction
  mysqli_commit($conn);
  close_db_connection($conn);

  send_response(true, 'Property created successfully', [
    'property_id' => $property_id,
    'images_saved' => $images_saved
  ]);

} catch (Exception $e) {
  if (isset($conn)) {
    mysqli_rollback($conn);
    close_db_connection($conn);
  }

  // Optional: Cleanup files that were moved before the error occurred
  if (isset($saved_files)) {
    foreach ($saved_files as $file) {
      if (file_exists($file))
        unlink($file);
    }
  }

  send_response(false, 'Database error: ' . $e->getMessage(), [], [], 500);
}
