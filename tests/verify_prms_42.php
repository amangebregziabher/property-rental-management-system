<?php
/**
 * Verification Script for PRMS-42
 * 
 * This script tests the authorization logic in delete_property.php
 * by simulating different session states.
 */

require_once __DIR__ . '/../config/db_connect.php';

function test_auth($title, $user_id, $user_role, $property_owner_id, $expected_allowed) {
    echo "Testing: $title\n";
    echo "User ID: $user_id, Role: $user_role, Property Owner: $property_owner_id\n";
    
    $is_allowed = ($user_id != 0 && ($user_id == $property_owner_id || $user_role === 'admin'));
    
    if ($is_allowed === $expected_allowed) {
        echo "RESULT: PASS\n\n";
        return true;
    } else {
        echo "RESULT: FAIL (Expected " . ($expected_allowed ? 'Allowed' : 'Denied') . ", got " . ($is_allowed ? 'Allowed' : 'Denied') . ")\n\n";
        return false;
    }
}

// Case 1: Owner deletes their own property
test_auth("Owner deletes own property", 1, "owner", 1, true);

// Case 2: Owner deletes someone else's property
test_auth("Owner deletes someone else's property", 1, "owner", 2, false);

// Case 3: Admin deletes someone else's property
test_auth("Admin deletes someone else's property", 3, "admin", 1, true);

// Case 4: Tenant tries to delete a property
test_auth("Tenant tries to delete", 4, "tenant", 1, false);

// Case 5: Guest (not logged in) tries to delete
test_auth("Guest tries to delete", 0, "", 1, false);
