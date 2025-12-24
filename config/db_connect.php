<?php
/**
 * Database Connection Helper
 * Procedural PHP connection using mysqli
 */

// Load database configuration
$db_config = require __DIR__ . '/database.php';

// Create connection function
function get_db_connection()
{
    global $db_config;

    $conn = mysqli_connect(
        $db_config['host'],
        $db_config['username'],
        $db_config['password'],
        $db_config['database'],
        $db_config['port']
    );

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Set charset
    mysqli_set_charset($conn, $db_config['charset']);

    return $conn;
}

// Function to close database connection
function close_db_connection($conn)
{
    if ($conn) {
        mysqli_close($conn);
    }
}
?>