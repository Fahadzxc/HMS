<?php
/**
 * Quick check script to see lab test requests for a patient
 * Usage: php check_lab_requests.php [patient_id]
 */

require __DIR__ . '/vendor/autoload.php';

$pathsConfig = FCPATH . '../app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;

$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
require realpath($bootstrap) ?: $bootstrap;

$app = Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);

$db = \Config\Database::connect();

$patientId = $argv[1] ?? null;

if (!$patientId) {
    echo "Usage: php check_lab_requests.php [patient_id]\n";
    echo "Example: php check_lab_requests.php 1\n";
    exit(1);
}

echo "=== Checking Lab Test Requests for Patient ID: $patientId ===\n\n";

try {
    // Check if billing_status column exists
    $fields = $db->getFieldData('lab_test_requests');
    $hasBillingStatus = false;
    foreach ($fields as $f) {
        if (strtolower($f->name) === 'billing_status') {
            $hasBillingStatus = true;
            break;
        }
    }
    
    echo "billing_status column exists: " . ($hasBillingStatus ? "YES" : "NO") . "\n\n";
    
    // Get all lab requests for this patient
    $requests = $db->table('lab_test_requests')
        ->where('patient_id', $patientId)
        ->orderBy('requested_at', 'DESC')
        ->get()
        ->getResultArray();
    
    echo "Total lab test requests found: " . count($requests) . "\n\n";
    
    if (empty($requests)) {
        echo "No lab test requests found for this patient.\n";
        exit(0);
    }
    
    echo "Lab Test Requests:\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-8s %-30s %-15s %-15s %-20s\n", "ID", "Test Type", "Doctor ID", "Billing Status", "Requested At");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($requests as $req) {
        $billingStatus = $hasBillingStatus ? ($req['billing_status'] ?? 'NULL') : 'N/A (column missing)';
        printf("%-8s %-30s %-15s %-15s %-20s\n",
            $req['id'],
            substr($req['test_type'] ?? 'N/A', 0, 28),
            $req['doctor_id'] ?? 'NULL',
            $billingStatus,
            $req['requested_at'] ?? 'N/A'
        );
    }
    
    echo "\n";
    
    // Count by billing status
    if ($hasBillingStatus) {
        $unbilled = array_filter($requests, function($r) {
            $status = $r['billing_status'] ?? null;
            return ($status === null || $status === '' || $status === 'unbilled');
        });
        
        echo "Unbilled requests: " . count($unbilled) . "\n";
        echo "Billed requests: " . (count($requests) - count($unbilled)) . "\n";
        
        if (count($unbilled) > 0) {
            echo "\nThese " . count($unbilled) . " requests SHOULD appear in billing.\n";
        } else {
            echo "\n⚠ WARNING: No unbilled requests found! They won't appear in billing.\n";
            echo "Run: php update_lab_billing_status.php to fix this.\n";
        }
    } else {
        echo "⚠ WARNING: billing_status column doesn't exist!\n";
        echo "All requests should appear in billing, but the column needs to be added.\n";
        echo "Run: php update_lab_billing_status.php to add the column.\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
