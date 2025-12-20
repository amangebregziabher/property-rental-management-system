<?php
/**
 * Login Handler Controller
 * 
 * This script handles the POST request from the login form and performs
 * server-side validation.
 */

// Start session to store potential error messages (though we're using GET for simplicity in this prototype)
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $errors = [];

    // 1. Validate Email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (!preg_match('/\.com$/i', $email)) {
        $errors[] = "Only emails ending with .com are allowed.";
    }

    // 2. Validate Password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[\W_]/', $password)) {
        $errors[] = "Password must include at least one letter, one number, and one special character.";
    }

    // Check for errors
    if (!empty($errors)) {
        // In a real app, you might use session for errors, 
        // but here we redirect back with the first error message for simplicity.
        $errorMessage = urlencode($errors[0]);
        header("Location: ../views/login.php?error=$errorMessage");
        exit();
    }

    // If validation passes, redirect to property list (simulating successful login)
    header("Location: ../views/property_list.php");
    exit();

} else {
    // If someone tries to access this script directly without POST, redirect home
    header("Location: ../public/index.php");
    exit();
}
