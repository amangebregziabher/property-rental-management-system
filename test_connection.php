<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db_connect.php';

echo "Attempting connection...\n";
$db_config = require 'config/database.php';
echo "Config: Host=" . $db_config['host'] . ", Port=" . $db_config['port'] . ", Database=" . $db_config['database'] . "\n";

try {
    $conn = get_db_connection();
    echo "SUCCESS: Connected successfully!\n";

    $result = mysqli_query($conn, "SHOW TABLES");
    echo "Tables found:\n";
    while ($row = mysqli_fetch_row($result)) {
        echo " - " . $row[0] . "\n";
    }

    close_db_connection($conn);
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
?>
