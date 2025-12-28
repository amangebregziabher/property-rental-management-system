<?php
require_once __DIR__ . '/config/db_connect.php';
$conn = get_db_connection();

$table_name = 'rental_applications';
$result = mysqli_query($conn, "SHOW TABLES LIKE '$table_name'");
if (mysqli_num_rows($result) > 0) {
    echo "Table '$table_name' EXISTS.\n";
    $desc = mysqli_query($conn, "DESCRIBE $table_name");
    while ($row = mysqli_fetch_assoc($desc)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Table '$table_name' DOES NOT EXIST.\n";
}
?>
