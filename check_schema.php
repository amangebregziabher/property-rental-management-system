<?php
require_once __DIR__ . '/config/db_connect.php';
$conn = get_db_connection();
$result = mysqli_query($conn, "DESCRIBE properties");
while ($row = mysqli_fetch_assoc($result)) {
    print_r($row);
}
?>
