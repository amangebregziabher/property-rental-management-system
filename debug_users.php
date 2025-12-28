<?php
require_once __DIR__ . '/config/db_connect.php';
$conn = get_db_connection();

echo "Checking users table...\n";
$result = mysqli_query($conn, "SELECT id, name, email, role, password FROM users");
while ($row = mysqli_fetch_assoc($result)) {
    echo "ID: " . $row['id'] . " | " . $row['name'] . " | " . $row['email'] . " | " . $row['role'] . "\n";
    if ($row['role'] === 'Admin' || $row['role'] === 'admin' || $row['email'] === 'john.smith@prms.com') {
        // Reset password to 'password'
        $new_hash = password_hash('password', PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = '$new_hash' WHERE id = " . $row['id'];
        if (mysqli_query($conn, $update_sql)) {
            echo ">> Password for " . $row['email'] . " reset to 'password'\n";
        } else {
            echo ">> Failed to reset password for " . $row['email'] . "\n";
        }
    }
}
?>
