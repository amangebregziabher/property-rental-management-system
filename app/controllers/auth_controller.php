<?php
/**
 * Authentication Controller
 * 
 * Handles login, registration, and logout logic.
 */

session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Action dispatcher
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegistration();
        break;
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        header('Location: ../views/login.php');
        exit();
}

/**
 * Handle user registration
 */
function handleRegistration()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../views/register.php');
        exit();
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'tenant';

    $errors = [];

    if (empty($name))
        $errors[] = "Name is required";
    if (empty($email))
        $errors[] = "Email is required";
    if (empty($password))
        $errors[] = "Password is required";
    if (strlen($password) < 6)
        $errors[] = "Password must be at least 6 characters";

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: ../views/register.php');
        exit();
    }

    $conn = get_db_connection();

    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt))) {
        $_SESSION['form_errors'] = ["Email is already registered"];
        close_db_connection($conn);
        header('Location: ../views/register.php');
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Registration successful! You can now login.";
        header('Location: ../views/login.php');
    } else {
        $_SESSION['form_errors'] = ["Error during registration"];
        header('Location: ../views/register.php');
    }

    mysqli_stmt_close($stmt);
    close_db_connection($conn);
    exit();
}

/**
 * Handle user login
 */
function handleLogin()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../views/login.php');
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect_to'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['form_errors'] = ["Email and password are required"];
        header('Location: ../views/login.php' . ($redirect ? "?redirect_to=" . urlencode($redirect) : ""));
        exit();
    }

    $conn = get_db_connection();
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    close_db_connection($conn);

    if ($user && password_verify($password, $user['password'])) {
        // Login success
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        if (!empty($redirect)) {
            header("Location: $redirect");
        } else {
            // Default redirects based on role
            if ($user['role'] === 'admin') {
                header('Location: ../views/admin_dashboard.php');
            } elseif ($user['role'] === 'owner') {
                header('Location: ../views/property_list.php');
            } else {
                header('Location: ../views/tenant_view.php');
            }
        }
    } else {
        $_SESSION['form_errors'] = ["Invalid email or password"];
        header('Location: ../views/login.php' . ($redirect ? "?redirect_to=" . urlencode($redirect) : ""));
    }
    exit();
}

/**
 * Handle user logout
 */
function handleLogout()
{
    session_destroy();
    header('Location: ../../public/index.php');
    exit();
}
