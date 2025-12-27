<?php
require_once __DIR__ . '/../config/db_connect.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = get_db_connection();

    $sql = "CREATE TABLE IF NOT EXISTS rental_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        user_id INT NULL,
        applicant_name VARCHAR(255) NOT NULL,
        applicant_email VARCHAR(255) NOT NULL,
        applicant_phone VARCHAR(50),
        message TEXT,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        occupants INT DEFAULT 1,
        move_in_date DATE,
        employer VARCHAR(255),
        job_title VARCHAR(255),
        monthly_income DECIMAL(10, 2),
        employment_status VARCHAR(50),
        id_document_path VARCHAR(255),
        income_document_path VARCHAR(255),
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (mysqli_query($conn, $sql)) {
        echo "Table 'rental_applications' created successfully.\n";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "\n";
    }

    close_db_connection($conn);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>