<?php
require_once __DIR__ . '/../config/db_connect.php';
$conn = get_db_connection();
echo "Tables in prms_db:\n";
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) {
    echo "- " . $row[0] . "\n";
}
close_db_connection($conn);
