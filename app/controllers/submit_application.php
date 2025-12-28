<?php
/**
 * Submit Application Backend Handler
 */

session_start();
require_once __DIR__ . '/../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/tenant_view.php');
    exit();
}

$errors = [];
$property_id = $_POST['property_id'] ?? 0;
$applicant_name = trim($_POST['applicant_name'] ?? '');
$applicant_email = trim($_POST['applicant_email'] ?? '');
$applicant_phone = trim($_POST['applicant_phone'] ?? '');
$occupants = $_POST['occupants'] ?? 1;
$move_in_date = $_POST['move_in_date'] ?? '';
$employer = trim($_POST['employer'] ?? '');
$job_title = trim($_POST['job_title'] ?? '');
$monthly_income = $_POST['monthly_income'] ?? 0;
$employment_status = $_POST['employment_status'] ?? '';
$message = trim($_POST['message'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

// Validation
if (empty($applicant_name))
    $errors[] = "Applicant name is required";
if (empty($applicant_email))
    $errors[] = "Email is required";
if (empty($move_in_date))
    $errors[] = "Move-in date is required";

// Handle File Uploads
$upload_dir = __DIR__ . '/../../storage/applications/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$id_document_path = '';
$income_document_path = '';

$allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];

// ID Document
if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['id_document'];
    if (in_array($file['type'], $allowed_types) && $file['size'] <= 5 * 1024 * 1024) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'id_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $id_document_path = 'storage/applications/' . $filename;
        }
    } else {
        $errors[] = "Invalid ID document file or too large.";
    }
}

// Income Document
if (isset($_FILES['income_document']) && $_FILES['income_document']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['income_document'];
    if (in_array($file['type'], $allowed_types) && $file['size'] <= 5 * 1024 * 1024) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'income_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $income_document_path = 'storage/applications/' . $filename;
        }
    } else {
        $errors[] = "Invalid income document file or too large.";
    }
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    header("Location: ../views/submit_application.php?property_id=$property_id");
    exit();
}

$conn = get_db_connection();
$sql = "INSERT INTO rental_applications (
            property_id, user_id, applicant_name, applicant_email, applicant_phone, 
            message, occupants, move_in_date, employer, job_title, 
            monthly_income, employment_status, id_document_path, income_document_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param(
        $stmt,
        "iissssisssdsss",
        $property_id,
        $user_id,
        $applicant_name,
        $applicant_email,
        $applicant_phone,
        $message,
        $occupants,
        $move_in_date,
        $employer,
        $job_title,
        $monthly_income,
        $employment_status,
        $id_document_path,
        $income_document_path
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Your application has been submitted successfully!";
        header("Location: ../views/tenant_applications_list.php");
    } else {
        $_SESSION['form_errors'] = ["Error submitting application. Please try again."];
        header("Location: ../views/submit_application.php?property_id=$property_id");
    }
    mysqli_stmt_close($stmt);
}

close_db_connection($conn);
?>