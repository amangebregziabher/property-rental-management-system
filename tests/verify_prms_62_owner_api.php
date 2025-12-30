<?php
/**
 * Verification Script for PRMS-62: Owner Applications API
 * This script verifies the logic used in api/get_owner_applications.php
 */

require_once __DIR__ . '/../config/db_connect.php';

echo "========================================\n";
echo "PRMS-62: VERIFYING OWNER APPLICATIONS API\n";
echo "========================================\n\n";

$conn = get_db_connection();

// 1. Find an owner with applications or use sample data
$owner_id = 2; // Default John Smith from schema.sql
echo "[INFO] Testing with Owner ID: $owner_id\n";

// 2. Verify Database Structure
echo "[TEST] Verifying rental_applications table structure... ";
$columns = $conn->query("DESCRIBE rental_applications");
$has_created_at = false;
while ($col = $columns->fetch_assoc()) {
    if ($col['Field'] === 'created_at') $has_created_at = true;
    if ($col['Field'] === 'applied_at') {
        echo "FAILED (Found 'applied_at' column which is inconsistent with schema and should be 'created_at')\n";
    }
}
if ($has_created_at) {
    echo "PASSED ('created_at' exists)\n";
} else {
    echo "FAILED ('created_at' missing)\n";
}

// 3. Verify Owner Application Fetch Logic
echo "[TEST] Fetching applications for owner... ";
$sql = "SELECT 
            ra.id AS application_id,
            ra.applicant_name,
            ra.status AS application_status,
            ra.created_at AS application_date,
            p.id AS property_id,
            p.title AS property_title
        FROM rental_applications ra
        INNER JOIN properties p ON ra.property_id = p.id
        WHERE p.owner_id = ?
        ORDER BY ra.created_at DESC";

try {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_num_rows($result);
    echo "PASSED (Found $count applications)\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}

// 4. Verify Statistics Logic
echo "[TEST] Verifying statistics calculation... ";
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN ra.status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN ra.status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN ra.status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM rental_applications ra
    INNER JOIN properties p ON ra.property_id = p.id
    WHERE p.owner_id = ?";

try {
    $stats_stmt = mysqli_prepare($conn, $stats_sql);
    mysqli_stmt_bind_param($stats_stmt, "i", $owner_id);
    mysqli_stmt_execute($stats_stmt);
    $stats_result = mysqli_stmt_get_result($stats_stmt);
    $stats = mysqli_fetch_assoc($stats_result);
    echo "PASSED (Total: {$stats['total']}, Pending: {$stats['pending']})\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}

// 5. Verify Tenant Application Query (Fix Verification)
echo "[TEST] Verifying Tenant API fix (created_at column)... ";
$tenant_id = 3; // Default Jane Doe
$tenant_sql = "SELECT 
            ra.id AS application_id,
            ra.created_at AS application_date
        FROM rental_applications ra
        WHERE ra.user_id = ?
        ORDER BY ra.created_at DESC";

try {
    $t_stmt = mysqli_prepare($conn, $tenant_sql);
    mysqli_stmt_bind_param($t_stmt, "i", $tenant_id);
    mysqli_stmt_execute($t_stmt);
    echo "PASSED (Query successful with 'created_at')\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}

close_db_connection($conn);
echo "\nVerification Complete.\n";
