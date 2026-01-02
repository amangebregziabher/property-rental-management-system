<?php
/**
 * Add Property Backend Handler
 * 
 * handles property creation including form validation,
 * image uploads, and database insertion.
 */

session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Initialize response variables
$errors = [];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/add_property.php');
    exit();
}

// ============================================
// STEP 1: RECEIVE AND SANITIZE POST DATA
// ============================================
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = $_POST['price'] ?? '';
$location = trim($_POST['location'] ?? '');
$type = $_POST['type'] ?? '';
$status = $_POST['status'] ?? 'Available';

// ============================================
// STEP 2: VALIDATE INPUT DATA
// ============================================
if (empty($title)) {
    $errors[] = "Property title is required";
} elseif (strlen($title) > 255) {
    $errors[] = "Property title must be less than 255 characters";
}

if (empty($price)) {
    $errors[] = "Monthly rent is required";
} elseif (!is_numeric($price) || floatval($price) <= 0) {
    $errors[] = "Monthly rent must be a positive number";
}

if (empty($location)) {
    $errors[] = "Location is required";
}

$valid_types = ['Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse'];
if (empty($type) || !in_array($type, $valid_types)) {
    $errors[] = "Invalid property type selected";
}

$valid_statuses = ['Available', 'Rented', 'Maintenance'];
if (empty($status) || !in_array($status, $valid_statuses)) {
    $errors[] = "Invalid property status selected";
}

// ============================================
// STEP 3: VALIDATE IMAGE UPLOADS
// ============================================
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$max_file_size = 5 * 1024 * 1024; // 5MB
$uploaded_images = [];

if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $file_count = count($_FILES['images']['name']);

    for ($i = 0; $i < $file_count; $i++) {
        // Skip if no file uploaded
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        // Check for upload errors
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading image " . ($i + 1);
            continue;
        }

        $file_name = $_FILES['images']['name'][$i];
        $file_size = $_FILES['images']['size'][$i];
        $file_tmp = $_FILES['images']['tmp_name'][$i];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_extensions)) {
            $errors[] = "Image " . ($i + 1) . " has an invalid file type. Allowed: JPG, PNG, GIF";
            continue;
        }

        // Validate file size
        if ($file_size > $max_file_size) {
            $errors[] = "Image " . ($i + 1) . " exceeds the 5MB size limit";
            continue;
        }

        // Store valid file info for later upload
        $uploaded_images[] = [
            'tmp_name' => $file_tmp,
            'extension' => $file_ext,
            'original_name' => $file_name
        ];
    }
}

// ============================================
// STEP 4: IF ERRORS, REDIRECT BACK
// ============================================
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = [
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'location' => $location,
        'type' => $type,
        'status' => $status
    ];
    header('Location: ../views/add_property.php');
    exit();
}

// ============================================
// STEP 5: DATABASE CONNECTION
// ============================================
$conn = get_db_connection();

// ============================================
// STEP 6: INSERT PROPERTY INTO DATABASE
// ============================================
// For prototype, use owner_id from session
$owner_id = $_SESSION['user_id'];

// Lookup category_id from type name
$cat_sql = "SELECT id FROM categories WHERE name = ? LIMIT 1";
$cat_stmt = mysqli_prepare($conn, $cat_sql);
mysqli_stmt_bind_param($cat_stmt, "s", $type);
mysqli_stmt_execute($cat_stmt);
$cat_result = mysqli_stmt_get_result($cat_stmt);
$category_row = mysqli_fetch_assoc($cat_result);
$category_id = $category_row ? $category_row['id'] : null;

if (!$category_id) {
    // Fallback or error if category not found? 
    // For now, let's assume we must find it or default to something? 
    // Or better, error out.
     $_SESSION['form_errors'] = ["Invalid category selected"];
    header('Location: ../views/add_property.php');
    exit();
}
mysqli_stmt_close($cat_stmt);

$sql = "INSERT INTO properties (owner_id, title, description, price, location, category_id, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    $_SESSION['form_errors'] = ["Database error: Could not prepare statement"];
    header('Location: ../views/add_property.php');
    exit();
}

mysqli_stmt_bind_param($stmt, "issdsis", $owner_id, $title, $description, $price, $location, $category_id, $status);

if (!mysqli_stmt_execute($stmt)) {
    $_SESSION['form_errors'] = ["Database error: Could not insert property"];
    mysqli_stmt_close($stmt);
    close_db_connection($conn);
    header('Location: ../views/add_property.php');
    exit();
}

$property_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// ============================================
// STEP 7: HANDLE IMAGE UPLOADS
// ============================================
$upload_dir = __DIR__ . '/../../images/';

// Create upload directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$image_upload_success = true;
foreach ($uploaded_images as $index => $image) {
    // Generate unique filename
    $new_filename = 'property_' . $property_id . '_' . time() . '_' . $index . '.' . $image['extension'];
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($image['tmp_name'], $destination)) {
        // Insert image record into database
        $is_main = ($index === 0) ? 1 : 0;
        $img_sql = "INSERT INTO property_images (property_id, image_path, is_main) VALUES (?, ?, ?)";
        $img_stmt = mysqli_prepare($conn, $img_sql);

        if ($img_stmt) {
            mysqli_stmt_bind_param($img_stmt, "isi", $property_id, $new_filename, $is_main);
            mysqli_stmt_execute($img_stmt);
            mysqli_stmt_close($img_stmt);
        }
    } else {
        $image_upload_success = false;
    }
}

// ============================================
// STEP 8: CLOSE CONNECTION AND REDIRECT
// ============================================
close_db_connection($conn);

// Set success message
if ($image_upload_success) {
    $_SESSION['success_message'] = "Property added successfully!";
} else {
    $_SESSION['success_message'] = "Property added, but some images could not be uploaded.";
}

header('Location: ../views/property_list.php');
exit();
