<?php

require 'vendor/autoload.php';

$db = \Config\Database::connect();

echo "=== Fixing doctor_id column in appointments table ===\n\n";

try {
    // Step 1: Disable foreign key checks
    echo "Step 1: Disabling foreign key checks...\n";
    $db->query("SET FOREIGN_KEY_CHECKS=0");
    echo "✓ Foreign key checks disabled\n\n";
    
    // Step 2: Modify column to allow NULL
    echo "Step 2: Modifying doctor_id column to allow NULL...\n";
    $db->query("ALTER TABLE appointments MODIFY doctor_id INT(11) UNSIGNED NULL");
    echo "✓ Column modified successfully\n\n";
    
    // Step 3: Re-enable foreign key checks
    echo "Step 3: Re-enabling foreign key checks...\n";
    $db->query("SET FOREIGN_KEY_CHECKS=1");
    echo "✓ Foreign key checks re-enabled\n\n";
    
    // Step 4: Verify
    echo "Step 4: Verifying changes...\n";
    $result = $db->query("SHOW COLUMNS FROM appointments WHERE Field = 'doctor_id'")->getRowArray();
    
    if ($result && ($result['Null'] ?? '') === 'YES') {
        echo "✓✓✓ SUCCESS! doctor_id column now allows NULL values! ✓✓✓\n";
        echo "\nYou can now create lab test appointments without a doctor.\n";
    } else {
        echo "✗✗✗ FAILED: Column still does not allow NULL\n";
        echo "Current column info:\n";
        print_r($result);
    }
    
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
