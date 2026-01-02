<?php

/**
 * Get the count of pending rental applications for properties owned by a specific owner.
 *
 * @param mysqli $conn The database connection
 * @param int $owner_id The ID of the owner
 * @return int The count of pending applications
 */
function get_pending_applications_count($conn, $owner_id) {
    if (!$conn || !$owner_id) {
        return 0;
    }

    $sql = "SELECT COUNT(ra.id) as pending_count 
            FROM rental_applications ra
            INNER JOIN properties p ON ra.property_id = p.id
            WHERE p.owner_id = ? AND ra.status = 'Pending'";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $owner_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['pending_count'] ?? 0);
    }

    return 0;
}
