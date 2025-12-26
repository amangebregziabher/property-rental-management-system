<?php
/**
 * Verification Script: Update Data Reflection
 * 
 * This script verifies that updates to a property are correctly 
 * reflected in the database.
 */

require_once __DIR__ . '/../config/db_connect.php';

echo "<h2>Verifying Update Data Reflection</h2>";

$conn = get_db_connection();

// 1. Get a random property to test
$sql = "SELECT * FROM properties LIMIT 1";
$result = mysqli_query($conn, $sql);
$property = mysqli_fetch_assoc($result);

if (!$property) {
    echo "<p style='color:red;'>Error: No properties found in database to test.</p>";
    exit();
}

$id = $property['id'];
$original_title = $property['title'];
$test_title = "Verification Test - " . date('Y-m-d H:i:s');

echo "<p>Testing with Property ID: <strong>$id</strong></p>";
echo "<ul>";
echo "<li>Original Title: $original_title</li>";
echo "<li>Target Title: $test_title</li>";
echo "</ul>";

// 2. Perform Update (manually via SQL for verification)
$update_sql = "UPDATE properties SET title = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($stmt, "si", $test_title, $id);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo "<p style='color:green;'>Update executed successfully.</p>";
} else {
    echo "<p style='color:red;'>Update failed: " . mysqli_error($conn) . "</p>";
}
mysqli_stmt_close($stmt);

// 3. Verify Reflection
$verify_sql = "SELECT title FROM properties WHERE id = ?";
$v_stmt = mysqli_prepare($conn, $verify_sql);
mysqli_stmt_bind_param($v_stmt, "i", $id);
mysqli_stmt_execute($v_stmt);
$v_res = mysqli_stmt_get_result($v_stmt);
$updated_property = mysqli_fetch_assoc($v_res);
mysqli_stmt_close($v_stmt);

if ($updated_property['title'] === $test_title) {
    echo "<h3 style='color:green;'>SUCCESS: Data reflection verified!</h3>";
    echo "<p>The new title is correctly stored in the database.</p>";
} else {
    echo "<h3 style='color:red;'>FAILURE: Data reflection failed.</h3>";
    echo "<p>Expected: $test_title</p>";
    echo "<p>Actual: " . $updated_property['title'] . "</p>";
}

// 4. Revert change (optional, but good for test scripts)
$revert_sql = "UPDATE properties SET title = ? WHERE id = ?";
$r_stmt = mysqli_prepare($conn, $revert_sql);
mysqli_stmt_bind_param($r_stmt, "si", $original_title, $id);
mysqli_stmt_execute($r_stmt);
mysqli_stmt_close($r_stmt);
echo "<p>Note: Title has been reverted to original for cleanup.</p>";

close_db_connection($conn);
?>
