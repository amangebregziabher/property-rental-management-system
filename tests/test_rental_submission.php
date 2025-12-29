<?php
/**
 * Test Script for Rental Application Submission
 * Tests database constraints and insertion logic.
 */

require_once __DIR__ . '/../config/db_connect.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "========================================\n";
echo "TESTING RENTAL APPLICATION SUBMISSION\n";
echo "========================================\n\n";

$conn = get_db_connection();

// 1. Setup Test Data (User & Property)
echo "[SETUP] Creating test user...\n";
try {
    $conn->query("INSERT INTO users (id, name, email, password, role) VALUES (999, 'Test Applicant', 'test_app@example.com', 'hashed', 'tenant')");
} catch (Exception $e) {
    echo "USER SETUP ERROR: " . $e->getMessage() . "\n";
}

echo "[SETUP] Creating test category...\n";
try {
    $conn->query("INSERT INTO categories (id, name, description) VALUES (999, 'Test Cat', 'Desc')");
} catch (Exception $e) {
    echo "CATEGORY SETUP ERROR: " . $e->getMessage() . "\n";
}

echo "[SETUP] Creating test property...\n";
try {
    $conn->query("INSERT INTO properties (id, owner_id, category_id, title, description, price, location, type, status) VALUES (999, 999, 999, 'Test Property', 'Desc', 1000, 'Test Location', 'Apartment', 'Available')");
} catch (Exception $e) {
    echo "PROPERTY SETUP ERROR: " . $e->getMessage() . "\n";
}

$property_id = 999;
$user_id = 999;

function attempt_submission($conn, $data, $test_name)
{
    echo "TEST: $test_name... ";

    $sql = "INSERT INTO rental_applications (
            property_id, user_id, applicant_name, applicant_email, applicant_phone, 
            message, occupants, move_in_date, employer, job_title, 
            monthly_income, employment_status, id_document_path, income_document_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "iissssisssdsss",
            $data['property_id'],
            $data['user_id'],
            $data['applicant_name'],
            $data['applicant_email'],
            $data['applicant_phone'],
            $data['message'],
            $data['occupants'],
            $data['move_in_date'],
            $data['employer'],
            $data['job_title'],
            $data['monthly_income'],
            $data['employment_status'],
            $data['id_document_path'],
            $data['income_document_path']
        );
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($conn);
        echo "PASSED (Inserted ID: $id)\n";
        return $id;
    } catch (Exception $e) {
        echo "FAILED (Expected if invalid): " . $e->getMessage() . "\n";
        return false;
    }
}

// 2. Test Case: Valid Submission
$valid_data = [
    'property_id' => $property_id,
    'user_id' => $user_id,
    'applicant_name' => 'John Doe',
    'applicant_email' => 'john@test.com',
    'applicant_phone' => '1234567890',
    'message' => 'Interested',
    'occupants' => 2,
    'move_in_date' => '2025-01-01',
    'employer' => 'Tech Corp',
    'job_title' => 'Dev',
    'monthly_income' => 5000.00,
    'employment_status' => 'Full-time',
    'id_document_path' => 'path/to/id.jpg',
    'income_document_path' => 'path/to/income.jpg'
];
$valid_id = attempt_submission($conn, $valid_data, "Valid Submission");

// 3. Test Case: Invalid (Missing Name - Note: DB might allows empty string, but let's check NULL if we passed it, or constraint)
// The schema defines applicant_name as NOT NULL.
// PHP mysqli_bind_param doesn't easily allow passing literal NULL unless variable is null.
$invalid_data_name = $valid_data;
$invalid_data_name['applicant_name'] = null;

// Catching the error when binding or executing
echo "TEST: Missing Name (NULL)... ";
try {
    // We must manually prepare/bind to force NULL for test
    $stmt = $conn->prepare("INSERT INTO rental_applications (property_id, applicant_name, applicant_email) VALUES (?, ?, ?)");
    $null = null;
    $stmt->bind_param("iss", $property_id, $null, $valid_data['applicant_email']);
    $stmt->execute();
    echo "FAILED (Should not insert NULL name)\n";
} catch (Exception $e) {
    echo "PASSED (Refused NULL Name: " . $e->getMessage() . ")\n";
}

// 4. Test Case: Verify inserted data
if ($valid_id) {
    echo "[VERIFY] Checking inserted record... ";
    $res = $conn->query("SELECT * FROM rental_applications WHERE id = $valid_id");
    $row = $res->fetch_assoc();
    if ($row['applicant_name'] === 'John Doe' && $row['monthly_income'] == 5000.00) {
        echo "MATCHED!\n";
    } else {
        echo "MISMATCH!\n";
    }
}

// 5. Cleanup
echo "[CLEANUP] Removing test data...\n";
$conn->query("DELETE FROM rental_applications WHERE user_id = 999");
$conn->query("DELETE FROM properties WHERE id = 999");
$conn->query("DELETE FROM categories WHERE id = 999");
$conn->query("DELETE FROM users WHERE id = 999");

close_db_connection($conn);
echo "\nTest Complete.\n";
?>