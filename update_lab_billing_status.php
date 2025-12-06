<?php
/**
 * Update all existing lab test requests to have billing_status = 'unbilled'
 * Run this script once to fix existing records
 * 
 * Usage: php update_lab_billing_status.php
 */

// Bootstrap CodeIgniter
// Path to the front controller
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// Load paths config
$pathsConfig = __DIR__ . '/app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;

$paths = new Config\Paths();

// Location of the framework bootstrap file
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
require realpath($bootstrap) ?: $bootstrap;

$app = Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);

$db = \Config\Database::connect();

echo "=== Updating Lab Test Requests Billing Status ===\n\n";

try {
    // Step 1: Check if table exists
    if (!$db->tableExists('lab_test_requests')) {
        echo "ERROR: lab_test_requests table does not exist!\n";
        exit(1);
    }
    
    // Step 2: Check if billing_status column exists
    $fields = $db->getFieldData('lab_test_requests');
    $hasBillingStatus = false;
    foreach ($fields as $f) {
        if (strtolower($f->name) === 'billing_status') {
            $hasBillingStatus = true;
            break;
        }
    }
    
    if (!$hasBillingStatus) {
        echo "Adding billing_status column...\n";
        $forge = \Config\Database::forge();
        $forge->addColumn('lab_test_requests', [
            'billing_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true]
        ]);
        echo "✓ Column added successfully\n\n";
    } else {
        echo "✓ billing_status column already exists\n\n";
    }
    
    // Step 3: Count total lab requests
    $totalCount = $db->table('lab_test_requests')->countAllResults();
    echo "Total lab test requests in database: $totalCount\n";
    
    // Step 4: Count requests that need updating
    $needsUpdate = $db->table('lab_test_requests')
        ->groupStart()
            ->where('billing_status IS NULL')
            ->orWhere('billing_status', '')
        ->groupEnd()
        ->countAllResults();
    
    echo "Lab requests that need updating: $needsUpdate\n\n";
    
    if ($needsUpdate > 0) {
        // Step 5: Update all lab requests to 'unbilled'
        echo "Updating all lab test requests to billing_status = 'unbilled'...\n";
        $updated = $db->table('lab_test_requests')
            ->set('billing_status', 'unbilled')
            ->groupStart()
                ->where('billing_status IS NULL')
                ->orWhere('billing_status', '')
            ->groupEnd()
            ->update();
        
        echo "✓ Updated $updated lab test requests\n\n";
    } else {
        echo "✓ All lab requests already have billing_status set\n\n";
    }
    
    // Step 6: Verify results
    $unbilledCount = $db->table('lab_test_requests')
        ->where('billing_status', 'unbilled')
        ->countAllResults();
    
    $billedCount = $db->table('lab_test_requests')
        ->where('billing_status', 'billed')
        ->countAllResults();
    
    $nullCount = $db->table('lab_test_requests')
        ->where('billing_status IS NULL')
        ->countAllResults();
    
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
    
    echo "\nDone!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

