<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP password
$database = 'prms_db';

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Successfully connected to '$database'.\n\n";

// 1. Verify Tables
$tables = [
    'users', 'tenant_profiles', 'categories', 'amenities', 'properties', 
    'property_amenities', 'property_images', 'property_documents', 
    'bookings', 'payments', 'maintenance_requests', 'reviews'
];

echo "Checking Tables:\n";
$missing_tables = [];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        $count_res = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $count_res->fetch_assoc()['count'];
        echo " - [OK] Table '$table' exists. (Rows: $count)\n";
    } else {
        echo " - [FAIL] Table '$table' MISSING!\n";
        $missing_tables[] = $table;
    }
}

echo "\n";

// 2. Verify Data Constraints
echo "Checking Data Integrity:\n";

// Check Users
$admin = $conn->query("SELECT * FROM users WHERE role = 'Admin'")->fetch_assoc();
if ($admin) {
    echo " - [OK] Admin user found: " . $admin['name'] . " (" . $admin['email'] . ")\n";
} else {
    echo " - [FAIL] Admin user NOT found.\n";
}

// Check Categories
$cat_res = $conn->query("SELECT * FROM categories LIMIT 3");
if ($cat_res->num_rows > 0) {
    echo " - [OK] Categories found: ";
    while ($row = $cat_res->fetch_assoc()) {
        echo $row['name'] . ", ";
    }
    echo "...\n";
} else {
    echo " - [FAIL] No categories found.\n";
}

// Check Properties with relations
$prop_check = $conn->query("
    SELECT p.title, u.name as owner, c.name as category, p.price 
    FROM properties p 
    JOIN users u ON p.owner_id = u.id 
    JOIN categories c ON p.category_id = c.id
    LIMIT 1
");

if ($prop_check && $prop_check->num_rows > 0) {
    $p = $prop_check->fetch_assoc();
    echo " - [OK] Property Relation Check: '{$p['title']}' owned by '{$p['owner']}' is a '{$p['category']}' listed at \${$p['price']}\n";
} else {
    echo " - [FAIL] Property relations check failed (Joins returned no rows).\n";
}

$conn->close();

if (empty($missing_tables)) {
    echo "\nOVERALL STATUS: SUCCESS - Schema is correctly implemented.\n";
} else {
    echo "\nOVERALL STATUS: FAILED - Missing tables found.\n";
}
?>
