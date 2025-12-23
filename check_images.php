<?php
require_once 'config/db_connect.php';
$conn = get_db_connection();
echo "--- Property Images Records ---\n";
$result = mysqli_query($conn, "SELECT * FROM property_images");
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        print_r($row);
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
close_db_connection($conn);
?>
