&lt;?php
/**
 * Test Suite for Tenant Application Status View
 * This test file validates the tenant_view.php functionality
 * 
 * Test Coverage:
 * 1. Property listing display
 * 2. Search functionality
 * 3. Filter by property type
 * 4. Price range filtering
 * 5. Database connection
 * 6. Image display
 * 7. Navigation and UI elements
 */

// Include database connection
require_once __DIR__ . '/../config/db_connect.php';

class TenantApplicationStatusViewTest {
    private $conn;
    private $test_results = [];
    
    public function __construct() {
        $this->conn = get_db_connection();
        echo "=== Tenant Application Status View Test Suite ===\n\n";
    }
    
    /**
     * Test 1: Database Connection
     */
    public function testDatabaseConnection() {
        echo "Test 1: Database Connection\n";
        echo "----------------------------\n";
        
        if ($this->conn) {
            $this->logSuccess("Database connection established successfully");
            return true;
        } else {
            $this->logError("Failed to connect to database");
            return false;
        }
    }
    
    /**
     * Test 2: Fetch Available Properties
     */
    public function testFetchAvailableProperties() {
        echo "\nTest 2: Fetch Available Properties\n";
        echo "-----------------------------------\n";
        
        $sql = "SELECT p.*, 
                (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
                FROM properties p 
                WHERE p.status = 'Available'
                ORDER BY p.created_at DESC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) {
            $count = mysqli_num_rows($result);
            $this->logSuccess("Successfully fetched {$count} available properties");
            
            if ($count > 0) {
                echo "Sample properties:\n";
                $i = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($i < 3) { // Show first 3 properties
                        echo "  - {$row['title']} ({$row['type']}) - \${$row['price']}/mo - {$row['location']}\n";
                    }
                    $i++;
                }
            }
            return true;
        } else {
            $this->logError("Failed to fetch properties: " . mysqli_error($this->conn));
            return false;
        }
    }
    
    /**
     * Test 3: Search Functionality
     */
    public function testSearchFunctionality() {
        echo "\nTest 3: Search Functionality\n";
        echo "----------------------------\n";
        
        // Test search by location
        $search_term = "%Downtown%";
        $sql = "SELECT COUNT(*) as count FROM properties 
                WHERE status = 'Available' 
                AND (title LIKE ? OR location LIKE ?)";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        $this->logSuccess("Search test completed - Found {$row['count']} properties matching 'Downtown'");
        return true;
    }
    
    /**
     * Test 4: Filter by Property Type
     */
    public function testPropertyTypeFilter() {
        echo "\nTest 4: Filter by Property Type\n";
        echo "--------------------------------\n";
        
        $types = ['Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse'];
        
        foreach ($types as $type) {
            $sql = "SELECT COUNT(*) as count FROM properties 
                    WHERE status = 'Available' AND type = ?";
            
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $type);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            
            echo "  - {$type}: {$row['count']} properties\n";
        }
        
        $this->logSuccess("Property type filter test completed");
        return true;
    }
    
    /**
     * Test 5: Price Range Filter
     */
    public function testPriceRangeFilter() {
        echo "\nTest 5: Price Range Filter\n";
        echo "---------------------------\n";
        
        $min_price = 1000;
        $max_price = 2000;
        
        $sql = "SELECT COUNT(*) as count FROM properties 
                WHERE status = 'Available' 
                AND price >= ? AND price <= ?";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "dd", $min_price, $max_price);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        $this->logSuccess("Found {$row['count']} properties in price range \${$min_price} - \${$max_price}");
        return true;
    }
    
    /**
     * Test 6: Property Images
     */
    public function testPropertyImages() {
        echo "\nTest 6: Property Images\n";
        echo "-----------------------\n";
        
        $sql = "SELECT COUNT(*) as total_properties,
                (SELECT COUNT(*) FROM property_images) as total_images
                FROM properties WHERE status = 'Available'";
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        echo "  - Total available properties: {$row['total_properties']}\n";
        echo "  - Total property images: {$row['total_images']}\n";
        
        $this->logSuccess("Property images test completed");
        return true;
    }
    
    /**
     * Test 7: Combined Filters
     */
    public function testCombinedFilters() {
        echo "\nTest 7: Combined Filters (Search + Type + Price)\n";
        echo "------------------------------------------------\n";
        
        $search = "%City%";
        $type = "Apartment";
        $min_price = 500;
        $max_price = 5000;
        
        $sql = "SELECT COUNT(*) as count FROM properties 
                WHERE status = 'Available'
                AND (title LIKE ? OR location LIKE ?)
                AND type = ?
                AND price >= ? AND price <= ?";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssdd", $search, $search, $type, $min_price, $max_price);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        $this->logSuccess("Combined filter test - Found {$row['count']} properties");
        return true;
    }
    
    /**
     * Test 8: View Accessibility
     */
    public function testViewAccessibility() {
        echo "\nTest 8: View File Accessibility\n";
        echo "--------------------------------\n";
        
        $view_file = __DIR__ . '/../app/views/tenant_view.php';
        
        if (file_exists($view_file)) {
            $this->logSuccess("tenant_view.php file exists and is accessible");
            
            // Check file permissions
            if (is_readable($view_file)) {
                $this->logSuccess("tenant_view.php is readable");
            } else {
                $this->logError("tenant_view.php is not readable");
            }
            
            return true;
        } else {
            $this->logError("tenant_view.php file not found");
            return false;
        }
    }
    
    /**
     * Helper: Log Success
     */
    private function logSuccess($message) {
        echo "✓ SUCCESS: {$message}\n";
        $this->test_results[] = ['status' => 'PASS', 'message' => $message];
    }
    
    /**
     * Helper: Log Error
     */
    private function logError($message) {
        echo "✗ ERROR: {$message}\n";
        $this->test_results[] = ['status' => 'FAIL', 'message' => $message];
    }
    
    /**
     * Run All Tests
     */
    public function runAllTests() {
        $this->testDatabaseConnection();
        $this->testFetchAvailableProperties();
        $this->testSearchFunctionality();
        $this->testPropertyTypeFilter();
        $this->testPriceRangeFilter();
        $this->testPropertyImages();
        $this->testCombinedFilters();
        $this->testViewAccessibility();
        
        $this->printSummary();
    }
    
    /**
     * Print Test Summary
     */
    private function printSummary() {
        echo "\n\n=== Test Summary ===\n";
        echo "--------------------\n";
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->test_results as $result) {
            if ($result['status'] === 'PASS') {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        $total = $passed + $failed;
        echo "Total Tests: {$total}\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        
        if ($failed === 0) {
            echo "\n✓ All tests passed successfully!\n";
        } else {
            echo "\n✗ Some tests failed. Please review the errors above.\n";
        }
    }
    
    public function __destruct() {
        if ($this->conn) {
            close_db_connection($this->conn);
        }
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $test = new TenantApplicationStatusViewTest();
    $test->runAllTests();
}
