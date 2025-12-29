<?php
/**
 * Tenant Controller
 * 
 * Handles methods related to tenant operations, including profile management.
 */

session_start();

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../helpers/Validator.php';

// Action dispatcher
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'update_profile':
        updateProfile();
        break;
    default:
        // Redirect to tenant view or appropriate page
        header('Location: ../views/tenant_view.php');
        exit();
}

/**
 * Handle tenant profile update
 */
function updateProfile() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../views/tenant_view.php');
        exit();
    }

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        header('Location: ../views/login.php');
        exit();
    }

    // Initialize Validator with POST data
    $validator = new Validator($_POST);

    // Define validation rules
    $validator->validate('employer_name', 'required', 'Employer Name is required.');
    $validator->validate('job_title', 'required', 'Job Title is required.');
    $validator->validate('monthly_income', 'required');
    $validator->validate('monthly_income', 'numeric', 'Monthly Income must be a number.');
    $validator->validate('monthly_income', 'min:0', 'Monthly Income cannot be negative.');
    $validator->validate('emergency_contact_name', 'required', 'Emergency Contact Name is required.');
    $validator->validate('emergency_contact_phone', 'required', 'Emergency Contact Phone is required.');
    $validator->validate('emergency_contact_phone', 'phone', 'Please enter a valid phone number.');

    if (!$validator->isValid()) {
        $_SESSION['form_errors'] = $validator->getErrors();
        $_SESSION['form_data'] = $_POST;
        header('Location: ../views/tenant_view.php');
        exit();
    }

    // Sanitize input
    $employer_name = trim($_POST['employer_name']);
    $job_title = trim($_POST['job_title']);
    $monthly_income = $_POST['monthly_income'];
    $emergency_name = trim($_POST['emergency_contact_name']);
    $emergency_phone = trim($_POST['emergency_contact_phone']);

    $conn = get_db_connection();

    // Check if profile exists
    $check_sql = "SELECT id FROM tenant_profiles WHERE user_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $user_id);
    mysqli_stmt_execute($check_stmt);
    $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));
    mysqli_stmt_close($check_stmt);

    if ($exists) {
        // Update existing profile
        $sql = "UPDATE tenant_profiles SET 
                employer_name = ?, 
                job_title = ?, 
                monthly_income = ?, 
                emergency_contact_name = ?, 
                emergency_contact_phone = ? 
                WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssdssi", $employer_name, $job_title, $monthly_income, $emergency_name, $emergency_phone, $user_id);
    } else {
        // Create new profile
        $sql = "INSERT INTO tenant_profiles 
                (user_id, employer_name, job_title, monthly_income, emergency_contact_name, emergency_contact_phone) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issdss", $user_id, $employer_name, $job_title, $monthly_income, $emergency_name, $emergency_phone);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Profile updated successfully!";
    } else {
        $_SESSION['form_errors'] = ["Database error: " . mysqli_error($conn)];
    }

    mysqli_stmt_close($stmt);
    close_db_connection($conn);
    
    header('Location: ../views/tenant_view.php');
    exit();
}
?>
