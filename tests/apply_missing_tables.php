<?php
require_once __DIR__ . '/../config/db_connect.php';
$conn = get_db_connection();

$queries = [
    "CREATE TABLE IF NOT EXISTS application_notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        application_id INT NOT NULL,
        owner_id INT NOT NULL,
        note TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_note_application
            FOREIGN KEY (application_id) 
            REFERENCES rental_applications(id) 
            ON DELETE CASCADE,
        CONSTRAINT fk_note_owner
            FOREIGN KEY (owner_id) 
            REFERENCES users(id) 
            ON DELETE CASCADE,
        INDEX (application_id),
        INDEX (owner_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "CREATE TABLE IF NOT EXISTS application_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        application_id INT NOT NULL,
        old_status ENUM('Pending', 'Approved', 'Rejected'),
        new_status ENUM('Pending', 'Approved', 'Rejected') NOT NULL,
        changed_by INT NOT NULL,
        reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_history_application
            FOREIGN KEY (application_id) 
            REFERENCES rental_applications(id) 
            ON DELETE CASCADE,
        CONSTRAINT fk_history_user
            FOREIGN KEY (changed_by) 
            REFERENCES users(id) 
            ON DELETE CASCADE,
        INDEX (application_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

echo "Applying missing tables...\n";

foreach ($queries as $i => $sql) {
    try {
        if ($conn->query($sql)) {
            echo "Query " . ($i + 1) . " successful.\n";
        }
    } catch (Exception $e) {
        echo "Error in Query " . ($i + 1) . ": " . $e->getMessage() . "\n";
    }
}

close_db_connection($conn);
echo "Done.\n";
