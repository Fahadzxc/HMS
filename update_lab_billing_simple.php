<?php
/**
 * Simple script to update lab test requests billing_status
 * This uses direct database connection - no CodeIgniter bootstrap needed
 * 
 * Usage: php update_lab_billing_simple.php
 */

// Database configuration - adjust if needed
$dbConfig = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'hms', // Change this to your database name
];

try {
    // Connect to database
    $mysqli = new mysqli(
        $dbConfig['hostname'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error . "\n");
    }

    echo "=== Updating Lab Test Requests Billing Status ===\n\n";

    // Step 1: Check if table exists
    $result = $mysqli->query("SHOW TABLES LIKE 'lab_test_requests'");
    if ($result->num_rows == 0) {
        echo "ERROR: lab_test_requests table does not exist!\n";
        exit(1);
    }
    
    // Step 2: Check if billing_status column exists
    $result = $mysqli->query("
        SELECT COUNT(*) as col_count 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'lab_test_requests' 
        AND COLUMN_NAME = 'billing_status'
    ");
    $row = $result->fetch_assoc();
    $hasBillingStatus = ($row['col_count'] > 0);
    
    if (!$hasBillingStatus) {
        echo "Adding billing_status column...\n";
        $mysqli->query("ALTER TABLE lab_test_requests ADD COLUMN billing_status VARCHAR(20) NULL");
        echo "✓ Column added successfully\n\n";
    } else {
        echo "✓ billing_status column already exists\n\n";
    }
    
    // Step 3: Count total lab requests
    $result = $mysqli->query("SELECT COUNT(*) as total FROM lab_test_requests");
    $row = $result->fetch_assoc();
    $totalCount = $row['total'];
    echo "Total lab test requests in database: $totalCount\n";
    
    // Step 4: Count requests that need updating
    $result = $mysqli->query("
        SELECT COUNT(*) as count 
        FROM lab_test_requests 
        WHERE billing_status IS NULL OR billing_status = ''
    ");
    $row = $result->fetch_assoc();
    $needsUpdate = $row['count'];
    
    echo "Lab requests that need updating: $needsUpdate\n\n";
    
    if ($needsUpdate > 0) {
        // Step 5: Update all lab requests to 'unbilled'
        echo "Updating all lab test requests to billing_status = 'unbilled'...\n";
        $mysqli->query("
            UPDATE lab_test_requests 
            SET billing_status = 'unbilled' 
            WHERE billing_status IS NULL OR billing_status = ''
        ");
        
        $updated = $mysqli->affected_rows;
        echo "✓ Updated $updated lab test requests\n\n";
    } else {
        echo "✓ All lab requests already have billing_status set\n\n";
    }
    
    // Step 6: Verify results
    $result = $mysqli->query("SELECT COUNT(*) as count FROM lab_test_requests WHERE billing_status = 'unbilled'");
    $row = $result->fetch_assoc();
    $unbilledCount = $row['count'];
    
    $result = $mysqli->query("SELECT COUNT(*) as count FROM lab_test_requests WHERE billing_status = 'billed'");
    $row = $result->fetch_assoc();
    $billedCount = $row['count'];
    
    $result = $mysqli->query("SELECT COUNT(*) as count FROM lab_test_requests WHERE billing_status IS NULL");
    $row = $result->fetch_assoc();
    $nullCount = $row['count'];
    
    echo "=== Summary ===\n";
    echo "Unbilled: $unbilledCount\n";
    echo "Billed: $billedCount\n";
    echo "NULL: $nullCount\n";
    echo "Total: $totalCount\n\n";
    
    if ($nullCount == 0 && $unbilledCount > 0) {
        echo "✓ SUCCESS! All lab test requests are now set to 'unbilled' and will appear in billing.\n";
    } else if ($nullCount > 0) {
        echo "⚠ WARNING: $nullCount requests still have NULL billing_status. Run this script again.\n";
    }
    
    $mysqli->close();
    echo "\nDone! Now refresh your billing page to see the lab tests.\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

