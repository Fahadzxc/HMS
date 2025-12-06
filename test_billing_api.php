<?php
/**
 * Test script to check what the billing API returns for a patient
 * Usage: php test_billing_api.php [patient_id]
 */

$patientId = $argv[1] ?? 38; // Default to patient 38

echo "=== Testing Billing API for Patient ID: $patientId ===\n\n";

// Simulate the API call by including the controller logic
require __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter properly
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

$pathsConfig = __DIR__ . '/app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;

$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
require realpath($bootstrap) ?: $bootstrap;

// Set environment
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

$app = Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);

$db = \Config\Database::connect();

echo "Database connected.\n\n";

// Get patient info
$patientModel = new \App\Models\PatientModel();
$patient = $patientModel->find($patientId);

if (!$patient) {
    echo "ERROR: Patient ID $patientId not found!\n";
    exit(1);
}

echo "Patient: " . $patient['full_name'] . " (" . $patient['patient_id'] . ")\n\n";

$billableItems = [];

// Check lab_test_requests
if ($db->tableExists('lab_test_requests')) {
    echo "Checking lab_test_requests table...\n";
    
    // Get ALL lab requests for this patient
    $allRequests = $db->table('lab_test_requests rq')
        ->select('rq.id, rq.test_type, rq.requested_at, rq.status, rq.appointment_id, rq.doctor_id')
        ->where('rq.patient_id', $patientId)
        ->orderBy('rq.requested_at', 'DESC')
        ->get()->getResultArray();
    
    echo "Found " . count($allRequests) . " total lab requests\n";
    
    // Get billing_status
    if (!empty($allRequests)) {
        $requestIds = array_column($allRequests, 'id');
        $requestsWithBilling = $db->table('lab_test_requests')
            ->select('id, billing_status')
            ->whereIn('id', $requestIds)
            ->get()->getResultArray();
        
        $billingStatusMap = [];
        foreach ($requestsWithBilling as $req) {
            $billingStatusMap[$req['id']] = $req['billing_status'] ?? null;
        }
        
        foreach ($allRequests as &$req) {
            $req['billing_status'] = $billingStatusMap[$req['id']] ?? null;
        }
    }
    
    // Filter
    $allRequests = array_filter($allRequests, function($req) {
        $status = $req['billing_status'] ?? null;
        return ($status === null || $status === '' || $status === 'unbilled');
    });
    
    echo "After filtering: " . count($allRequests) . " unbilled requests\n\n";
    
    // Check for billed results
    $requestIdsWithBilledResults = [];
    if ($db->tableExists('lab_test_results') && !empty($allRequests)) {
        $requestIds = array_column($allRequests, 'id');
        try {
            $billedResultsCheck = $db->table('lab_test_results')
                ->select('request_id')
                ->whereIn('request_id', $requestIds)
                ->where('billing_status', 'billed')
                ->get()->getResultArray();
            $requestIdsWithBilledResults = array_column($billedResultsCheck, 'request_id');
        } catch (\Exception $e) {
            echo "Note: Could not check billed results: " . $e->getMessage() . "\n";
        }
    }
    
    $requests = [];
    foreach ($allRequests as $row) {
        if (!in_array($row['id'], $requestIdsWithBilledResults)) {
            $requests[] = $row;
        }
    }
    
    echo "Final requests to bill: " . count($requests) . "\n\n";
    
    if (!empty($requests)) {
        echo "Lab Test Requests that SHOULD appear in billing:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($requests as $req) {
            echo "ID: " . $req['id'] . " | Test: " . $req['test_type'] . " | Status: " . ($req['billing_status'] ?? 'NULL') . "\n";
            
            $billableItems[] = [
                'category' => 'laboratory',
                'code' => 'LABRQ-' . str_pad((string)$req['id'], 6, '0', STR_PAD_LEFT),
                'date_time' => !empty($req['requested_at']) ? date('Y-m-d\TH:i', strtotime($req['requested_at'])) : date('Y-m-d\TH:i'),
                'item_name' => strtoupper($req['test_type'] ?? 'Lab Test'),
                'unit_price' => 500.00, // Default price
                'quantity' => 1,
                'reference_id' => $req['id'],
                'reference_type' => 'lab_request'
            ];
        }
        echo "\n";
    } else {
        echo "⚠ WARNING: No lab requests will be added to billing!\n\n";
    }
}

echo "=== Summary ===\n";
echo "Total billable lab items: " . count($billableItems) . "\n";

if (count($billableItems) > 0) {
    echo "\n✓ These items SHOULD appear in the billing modal.\n";
    echo "If they don't, check:\n";
    echo "1. Browser console for JavaScript errors\n";
    echo "2. Network tab to see the API response\n";
    echo "3. Server logs in writable/logs/\n";
} else {
    echo "\n⚠ No lab items found. Check the database and run update_lab_billing_simple.php\n";
}

