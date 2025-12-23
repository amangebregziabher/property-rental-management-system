<?php
require_once 'config/db_connect.php';
$conn = get_db_connection();
$result = mysqli_query($conn, "DESCRIBE property_images");
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
close_db_connection($conn);
?>
