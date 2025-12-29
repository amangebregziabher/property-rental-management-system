<?php
require_once __DIR__ . '/config/db_connect.php';
$conn = get_db_connection();

$sql = "CREATE TABLE IF NOT EXISTS rental_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT,
    applicant_name VARCHAR(100) NOT NULL,
    applicant_email VARCHAR(100) NOT NULL,
    applicant_phone VARCHAR(20) NOT NULL,
    message TEXT,
    occupants INT DEFAULT 1,
    move_in_date DATE NOT NULL,
    employer VARCHAR(100),
    job_title VARCHAR(100),
    monthly_income DECIMAL(10, 2),
    employment_status VARCHAR(50),
    id_document_path VARCHAR(255),
    income_document_path VARCHAR(255),
    status VARCHAR(20) DEFAULT 'Pending', -- Pending, Approved, Rejected
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'rental_applications' created successfully.\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

close_db_connection($conn);
?>
