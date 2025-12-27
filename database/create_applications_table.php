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
CREATE TABLE IF NOT EXISTS rental_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT NULL,
    applicant_name VARCHAR(255) NOT NULL,
    applicant_email VARCHAR(255) NOT NULL,
    applicant_phone VARCHAR(50),
    message TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_app_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_app_user
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE SET NULL,
    INDEX (property_id),
    INDEX (user_id),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
  if (mysqli_query($conn, $sql)) {
    echo "Table 'rental_applications' created successfully (or already exists).\n";
  } else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
  }
} catch (Exception $e) {
  echo "Error executing SQL: " . $e->getMessage() . "\n";
}

close_db_connection($conn);
?>