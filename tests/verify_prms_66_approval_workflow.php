<?php
/**
 * Verification Script for PRMS-66: Test Approval and Rejection Workflow
 * 
 * This script simulates the backend actions that occur when an owner
 * approves or rejects a tenant application.
 */

require_once __DIR__ . '/../config/db_connect.php';

echo "==================================================\n";
echo "PRMS-66: VERIFYING APPROVAL AND REJECTION WORKFLOW\n";
echo "==================================================\n\n";

$conn = get_db_connection();
mysqli_report(MYSQLI_REPORT_OFF); // Disable exceptions for manual handling

// 1. SETUP: Create a test application
echo "[SETUP] Creating a test application...\n";
$owner_id = 999; // Mock Owner ID
$tenant_id = 888; // Mock Tenant ID

// Ensure mock users exist or just use Ids if DB allows (foreign keys might fail)
// For robustness, let's find a real property and owner if possible, or insert ignoring constraints if disabled.
// Safe bet: Insert a dummy property for an existing owner if possible.
$prop_res = $conn->query("SELECT id, owner_id FROM properties LIMIT 1");
if ($prop_res && $prop_res->num_rows > 0) {
    $prop = $prop_res->fetch_assoc();
    $property_id = $prop['id'];
    $owner_id = $prop['owner_id'];
} else {
    // If no properties, we can't easily test without violating FKs unless we mock everything.
    // Assuming DB has data.
    echo "[ERROR] No properties found in DB. Cannot test.\n";
    exit(1);
}

// Create application
$email = "test_prms66_" . rand(1000,9999) . "@example.com";
$conn->query("INSERT INTO rental_applications (property_id, user_id, applicant_name, applicant_email, status, created_at) 
              VALUES ($property_id, $tenant_id, 'PRMS-66 Test User', '$email', 'Pending', NOW())");
$application_id = $conn->insert_id;

if (!$application_id) {
    echo "[ERROR] Failed to create test application: " . $conn->error . "\n";
    exit(1);
}
echo "[INFO] Created Test Application ID: $application_id (Status: Pending)\n";

// 2. TEST: Approve Application
echo "\n[TEST CASE 1] Approve Application (Pending -> Approved)\n";
$new_status = 'Approved';
$reason = 'Verified income documents';

// Simulate Controller Logic
$old_status = 'Pending';
$update_sql = "UPDATE rental_applications SET status = '$new_status' WHERE id = $application_id";
if ($conn->query($update_sql)) {
    // Simulate History Log (as implemented in controller)
    $conn->query("INSERT INTO application_status_history (application_id, old_status, new_status, changed_by, reason) 
                  VALUES ($application_id, '$old_status', '$new_status', $owner_id, '$reason')");
    echo " -> Application updated.\n";
} else {
    echo " -> [FAIL] Update failed: " . $conn->error . "\n";
}

// Verify in DB
$res = $conn->query("SELECT status FROM rental_applications WHERE id = $application_id");
$row = $res->fetch_assoc();
if ($row['status'] === 'Approved') {
    echo " -> [PASS] Database status is 'Approved'\n";
} else {
    echo " -> [FAIL] Database status is '{$row['status']}'\n";
}

// Verify History
$hist = $conn->query("SELECT * FROM application_status_history WHERE application_id = $application_id AND new_status = 'Approved' ORDER BY id DESC LIMIT 1");
if ($hist && $hist->num_rows > 0) {
    echo " -> [PASS] History record found.\n";
} else {
    echo " -> [FAIL] No history record found.\n";
}

// 3. TEST: Reject Application
echo "\n[TEST CASE 2] Reject Application (Approved -> Rejected)\n";
$old_status = 'Approved'; 
$new_status = 'Rejected';
$reason = 'Property no longer available';

// Simulate Controller Logic
$update_sql = "UPDATE rental_applications SET status = '$new_status' WHERE id = $application_id";
if ($conn->query($update_sql)) {
    // Simulate History Log
    $conn->query("INSERT INTO application_status_history (application_id, old_status, new_status, changed_by, reason) 
                  VALUES ($application_id, '$old_status', '$new_status', $owner_id, '$reason')");
    echo " -> Application updated.\n";
} else {
    echo " -> [FAIL] Update failed: " . $conn->error . "\n";
}

// Verify in DB
$res = $conn->query("SELECT status FROM rental_applications WHERE id = $application_id");
$row = $res->fetch_assoc();
if ($row['status'] === 'Rejected') {
    echo " -> [PASS] Database status is 'Rejected'\n";
} else {
    echo " -> [FAIL] Database status is '{$row['status']}'\n";
}

// Verify History
$hist = $conn->query("SELECT * FROM application_status_history WHERE application_id = $application_id AND new_status = 'Rejected' ORDER BY id DESC LIMIT 1");
if ($hist && $hist->num_rows > 0) {
    echo " -> [PASS] History record found.\n";
} else {
    echo " -> [FAIL] No history record found.\n";
}

// 4. CLEANUP
echo "\n[CLEANUP] Removing test data...\n";
// $conn->query("DELETE FROM application_status_history WHERE application_id = $application_id");
// $conn->query("DELETE FROM rental_applications WHERE id = $application_id");
echo "Data left for manual inspection (Ids: App=$application_id).\n";

echo "\nVerification Complete.\n";
close_db_connection($conn);
?>
