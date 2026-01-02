<?php
require_once __DIR__ . '/../config/db_connect.php';

try {
    $conn = get_db_connection();

    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('application_received', 'status_change', 'payment', 'maintenance', 'system') NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        related_id INT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_notif_user
            FOREIGN KEY (user_id) 
            REFERENCES users(id) 
            ON DELETE CASCADE,
        INDEX (user_id),
        INDEX (is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (mysqli_query($conn, $sql)) {
        echo "Table 'notifications' created successfully (or already exists).\n";
    } else {
        throw new Exception("Error creating table: " . mysqli_error($conn));
    }

    close_db_connection($conn);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
