<?php
require_once __DIR__ . '/../config/db_connect.php';

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = get_db_connection();
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$sql = "
ALTER TABLE rental_applications 
ADD COLUMN IF NOT EXISTS occupants INT DEFAULT 1,
ADD COLUMN IF NOT EXISTS move_in_date DATE,
ADD COLUMN IF NOT EXISTS employer VARCHAR(255),
ADD COLUMN IF NOT EXISTS job_title VARCHAR(255),
ADD COLUMN IF NOT EXISTS monthly_income DECIMAL(10, 2),
ADD COLUMN IF NOT EXISTS employment_status VARCHAR(50),
ADD COLUMN IF NOT EXISTS id_document_path VARCHAR(255),
ADD COLUMN IF NOT EXISTS income_document_path VARCHAR(255);
";

try {
    if (mysqli_multi_query($conn, $sql)) {
        echo "Table 'rental_applications' updated successfully.\n";
    } else {
        echo "Error updating table: " . mysqli_error($conn) . "\n";
    }
} catch (Exception $e) {
    echo "Error executing SQL: " . $e->getMessage() . "\n";
}

close_db_connection($conn);
?>