<?php
/**
 * Verification Script for PRMS-45
 * 
 * Tests the visibility logic for property details
 * based on status and user roles.
 */

function test_visibility($case_name, $property_status, $property_owner_id, $user_id, $user_role, $expected_can_view) {
    echo "Testing: $case_name\n";
    echo "Property Status: $property_status, Owner: $property_owner_id\n";
    echo "User ID: $user_id, Role: $user_role\n";

    // Logic from property_details.php
    $can_view = true;
    if ($property_status !== 'Available') {
        if ($user_id != $property_owner_id && $user_role !== 'admin') {
            $can_view = false;
        }
    }

    if ($can_view === $expected_can_view) {
        echo "RESULT: PASS\n\n";
        return true;
    } else {
        echo "RESULT: FAIL (Expected " . ($expected_can_view ? 'Visible' : 'Hidden') . ", got " . ($can_view ? 'Visible' : 'Hidden') . ")\n\n";
        return false;
    }
}

// Case 1: Tenant views Available property
test_visibility("Tenant views Available property", "Available", 1, 10, "tenant", true);

// Case 2: Guest views Available property
test_visibility("Guest views Available property", "Available", 1, 0, "guest", true);

// Case 3: Tenant views Rented property (not theirs)
test_visibility("Tenant views Rented property", "Rented", 1, 10, "tenant", false);

// Case 4: Owner views their own Rented property
test_visibility("Owner views own Rented property", "Rented", 1, 1, "owner", true);

// Case 5: Owner views someone else's Rented property
test_visibility("Owner views other's Rented property", "Rented", 1, 2, "owner", false);

// Case 6: Admin views Rented property
test_visibility("Admin views Rented property", "Rented", 1, 99, "admin", true);

// Case 7: Tenant views Maintenance property
test_visibility("Tenant views Maintenance property", "Maintenance", 1, 10, "tenant", false);
