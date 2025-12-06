<?php
/**
 * Simple script to check lab test requests for a patient
 * 
 * Usage: php check_lab_simple.php [patient_id]
 * Example: php check_lab_simple.php 1
 */

// Database configuration - adjust if needed
$dbConfig = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'hms', // Change this to your database name
];

$patientId = $argv[1] ?? null;

if (!$patientId) {
    echo "Usage: php check_lab_simple.php [patient_id]\n";
    echo "Example: php check_lab_simple.php 1\n\n";
    echo "To find patient IDs, run: php check_lab_simple.php list\n";
    exit(1);
}

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

    if ($patientId === 'list') {
        // List all patients with lab requests
        echo "=== Patients with Lab Test Requests ===\n\n";
        $result = $mysqli->query("
            SELECT DISTINCT 
                rq.patient_id,
                p.full_name,
                p.patient_id as patient_code,
                COUNT(rq.id) as lab_count
            FROM lab_test_requests rq
            LEFT JOIN patients p ON p.id = rq.patient_id
            GROUP BY rq.patient_id, p.full_name, p.patient_id
            ORDER BY lab_count DESC
        ");
        
        if ($result->num_rows > 0) {
            printf("%-8s %-40s %-20s %s\n", "ID", "Patient Name", "Patient Code", "Lab Requests");
            echo str_repeat("-", 80) . "\n";
            while ($row = $result->fetch_assoc()) {
                printf("%-8s %-40s %-20s %s\n",
                    $row['patient_id'],
                    substr($row['full_name'] ?? 'Unknown', 0, 38),
                    $row['patient_code'] ?? 'N/A',
                    $row['lab_count']
                );
            }
        } else {
            echo "No patients with lab requests found.\n";
        }
        exit(0);
    }

    echo "=== Checking Lab Test Requests for Patient ID: $patientId ===\n\n";

    // Get patient info
    $result = $mysqli->query("SELECT id, full_name, patient_id as patient_code FROM patients WHERE id = $patientId");
    if ($result->num_rows == 0) {
        echo "ERROR: Patient ID $patientId not found!\n";
        echo "Run: php check_lab_simple.php list\n";
        exit(1);
    }
    $patient = $result->fetch_assoc();
    echo "Patient: " . $patient['full_name'] . " (" . $patient['patient_code'] . ")\n\n";

    // Check if billing_status column exists
    $result = $mysqli->query("
        SELECT COUNT(*) as col_count 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'lab_test_requests' 
        AND COLUMN_NAME = 'billing_status'
    ");
    $row = $result->fetch_assoc();
    $hasBillingStatus = ($row['col_count'] > 0);
    
    echo "billing_status column exists: " . ($hasBillingStatus ? "YES" : "NO") . "\n\n";
    
    // Get all lab requests for this patient
    $query = "SELECT id, test_type, doctor_id, status, requested_at";
    if ($hasBillingStatus) {
        $query .= ", billing_status";
    }
    $query .= " FROM lab_test_requests WHERE patient_id = $patientId ORDER BY requested_at DESC";
    
    $result = $mysqli->query($query);
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    echo "Total lab test requests found: " . count($requests) . "\n\n";
    
    if (empty($requests)) {
        echo "No lab test requests found for this patient.\n";
        exit(0);
    }
    
    echo "Lab Test Requests:\n";
    echo str_repeat("-", 100) . "\n";
    $header = "%-8s %-30s %-15s %-15s %-20s";
    if ($hasBillingStatus) {
        $header .= " %-15s";
    }
    printf($header . "\n", "ID", "Test Type", "Doctor ID", "Status", "Requested At", $hasBillingStatus ? "Billing Status" : "");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($requests as $req) {
        $billingStatus = $hasBillingStatus ? ($req['billing_status'] ?? 'NULL') : 'N/A';
        printf($header . "\n",
            $req['id'],
            substr($req['test_type'] ?? 'N/A', 0, 28),
            $req['doctor_id'] ?? 'NULL',
            $req['status'] ?? 'N/A',
            $req['requested_at'] ?? 'N/A',
            $hasBillingStatus ? $billingStatus : ""
        );
    }
    
    echo "\n";
    
    // Count by billing status
    if ($hasBillingStatus) {
        $unbilled = array_filter($requests, function($r) {
            $status = $r['billing_status'] ?? null;
            return ($status === null || $status === '' || $status === 'unbilled');
        });
        
        echo "Unbilled requests: " . count($unbilled) . " (these SHOULD appear in billing)\n";
        echo "Billed requests: " . (count($requests) - count($unbilled)) . "\n";
        
        if (count($unbilled) > 0) {
            echo "\n✓ These " . count($unbilled) . " requests SHOULD appear in billing.\n";
            echo "  If they don't, refresh the billing page or check the browser console for errors.\n";
        } else {
            echo "\n⚠ WARNING: No unbilled requests found! They won't appear in billing.\n";
            echo "Run: php update_lab_billing_simple.php to fix this.\n";
        }
    } else {
        echo "⚠ WARNING: billing_status column doesn't exist!\n";
        echo "All requests should appear in billing, but the column needs to be added.\n";
        echo "Run: php update_lab_billing_simple.php to add the column.\n";
    }
    
    $mysqli->close();
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

