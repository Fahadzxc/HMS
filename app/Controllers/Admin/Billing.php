<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\BillingModel;
use App\Models\PaymentModel;
use App\Models\PatientModel;

class Billing extends Controller
{
    public function __construct()
    {
        $this->ensureBillingTables();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $billingModel = new BillingModel();
        $paymentModel = new PaymentModel();
        
        // Get filters
        $filters = [
            'status' => $this->request->getGet('status'),
            'patient_id' => $this->request->getGet('patient_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];
        
        // Get all bills (exclude prescription bills - those are handled by Pharmacy)
        $filters['exclude_prescription'] = true;
        $bills = $billingModel->getBillsWithPatient($filters);
        
        // Get stats
        $totalRevenue = $paymentModel->selectSum('amount')
            ->where('status', 'completed')
            ->get()->getRowArray();
        
        $pendingAmount = $billingModel->selectSum('balance')
            ->where('status', 'pending')
            ->get()->getRowArray();
        
        $overdueAmount = $billingModel->selectSum('balance')
            ->where('status', 'overdue')
            ->get()->getRowArray();
        
        $thisMonth = date('Y-m');
        $thisMonthRevenue = $paymentModel->selectSum('amount')
            ->where('status', 'completed')
            ->like('payment_date', $thisMonth, 'after')
            ->get()->getRowArray();
        
        // Get patients for dropdown
        $patientModel = new PatientModel();
        $patients = $patientModel->select('id, full_name, patient_id, contact')->orderBy('full_name', 'ASC')->findAll();

        $data = [
            'pageTitle' => 'Billing & Payments',
            'title' => 'Billing & Payments - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'bills' => $bills,
            'patients' => $patients,
            'total_revenue' => $totalRevenue['amount'] ?? 0,
            'pending_amount' => $pendingAmount['balance'] ?? 0,
            'overdue_amount' => $overdueAmount['balance'] ?? 0,
            'this_month_revenue' => $thisMonthRevenue['amount'] ?? 0,
            'filters' => $filters,
        ];
        
        return view('admin/billing', $data);
    }
    
    public function createBillForPrescription($prescriptionId)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }
        
        $prescriptionModel = new \App\Models\PrescriptionModel();
        $prescription = $prescriptionModel->find($prescriptionId);
        
        if (!$prescription) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prescription not found']);
        }
        
        // Check if bill already exists
        $db = \Config\Database::connect();
        $existingBill = $db->table('bills')
            ->where('prescription_id', $prescriptionId)
            ->first();
        
        if ($existingBill) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bill already exists for this prescription']);
        }
        
        // Create bill using helper method
        $this->createPrescriptionBill($prescriptionId, $prescription);
        
        return $this->response->setJSON(['success' => true, 'message' => 'Bill created successfully']);
    }
    
    public function createBillsForCompletedPrescriptions()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }
        
        $prescriptionModel = new \App\Models\PrescriptionModel();
        $db = \Config\Database::connect();
        
        // Get all completed prescriptions without bills
        $completedPrescriptions = $prescriptionModel->where('status', 'completed')->findAll();
        $created = 0;
        $skipped = 0;
        
        foreach ($completedPrescriptions as $prescription) {
            $existingBill = $db->table('bills')
                ->where('prescription_id', $prescription['id'])
                ->first();
            
            if (!$existingBill) {
                // Create bill
                $this->createPrescriptionBill($prescription['id'], $prescription);
                $created++;
            } else {
                $skipped++;
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'message' => "Created {$created} bills, skipped {$skipped} (already have bills)"
        ]);
    }
    
    private function createPrescriptionBill($prescriptionId, $prescription)
    {
        try {
            // Check if bill already exists
            $db = \Config\Database::connect();
            $existingBill = $db->table('bills')
                ->where('prescription_id', $prescriptionId)
                ->first();
            
            if ($existingBill) {
                return; // Bill already exists
            }
            
            // Ensure billing tables exist
            $this->ensureBillingTables();
            
            $billingModel = new \App\Models\BillingModel();
            $billItemModel = new \App\Models\BillItemModel();
            
            // Parse prescription items
            $itemsJson = $prescription['items_json'] ?? '[]';
            $items = json_decode($itemsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE || empty($items) || !is_array($items)) {
                log_message('debug', "No valid items in prescription #{$prescriptionId}");
                return;
            }
            
            // Calculate medication costs
            $subtotal = 0;
            $billItems = [];
            
            foreach ($items as $item) {
                if (!is_array($item)) continue;
                
                // Check if patient is buying from hospital - only add to bill if true
                $buyFromHospital = isset($item['buy_from_hospital']) ? (bool)$item['buy_from_hospital'] : true;
                if (!$buyFromHospital) {
                    continue; // Skip medications not bought from hospital
                }
                
                // Get medication name
                $medicationName = $item['name'] ?? $item['medication'] ?? '';
                
                if (empty($medicationName) && !empty($item['med_id'])) {
                    $medModel = new \App\Models\MedicationModel();
                    $med = $medModel->find($item['med_id']);
                    if ($med) {
                        $medicationName = $med['name'] ?? '';
                        if (!empty($med['strength'])) {
                            $medicationName .= ' ' . $med['strength'];
                        }
                    }
                }
                
                if (empty($medicationName)) continue;
                
                // Calculate quantity
                $quantity = 1;
                if (isset($item['quantity']) && $item['quantity'] > 0) {
                    $quantity = floatval($item['quantity']);
                } else {
                    $durationStr = $item['duration'] ?? '';
                    if (!empty($durationStr)) {
                        preg_match('/(\d+)/', $durationStr, $matches);
                        if (!empty($matches[1])) {
                            $durationDays = (int)$matches[1];
                            $frequency = strtolower($item['frequency'] ?? '');
                            if (strpos($frequency, '2x') !== false || strpos($frequency, 'twice') !== false) {
                                $quantity = $durationDays * 2;
                            } elseif (strpos($frequency, '3x') !== false || strpos($frequency, 'thrice') !== false) {
                                $quantity = $durationDays * 3;
                            } else {
                                $quantity = $durationDays;
                            }
                        }
                    }
                }
                
                $dosage = $item['dosage'] ?? '';
                $frequency = $item['frequency'] ?? '';
                $duration = $item['duration'] ?? '';
                $mealInstruction = $item['meal_instruction'] ?? '';
                
                // Get price
                $unitPrice = $this->getMedicationPrice($medicationName);
                $totalPrice = $quantity * $unitPrice;
                $subtotal += $totalPrice;
                
                $description = [];
                if ($dosage) $description[] = "Dosage: {$dosage}";
                if ($frequency) $description[] = "Frequency: {$frequency}";
                if ($mealInstruction) $description[] = "Meal: {$mealInstruction}";
                if ($duration) $description[] = "Duration: {$duration}";
                
                $billItems[] = [
                    'item_type' => 'medication',
                    'item_name' => $medicationName,
                    'description' => implode(', ', $description),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'reference_id' => $prescriptionId,
                ];
            }
            
            if ($subtotal <= 0 || empty($billItems)) {
                return;
            }
            
            // No tax - total is subtotal
            $tax = 0;
            $totalAmount = $subtotal;
            $billNumber = $billingModel->generateBillNumber();
            
            $billData = [
                'bill_number' => $billNumber,
                'patient_id' => $prescription['patient_id'],
                'prescription_id' => $prescriptionId,
                'bill_type' => 'prescription',
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance' => $totalAmount,
                'status' => 'pending',
                'due_date' => date('Y-m-d', strtotime('+7 days')),
                'notes' => 'Auto-generated from prescription #' . $prescriptionId,
                'created_by' => session()->get('user_id'),
            ];
            
            $billId = $billingModel->insert($billData);
            
            if ($billId) {
                foreach ($billItems as $item) {
                    $item['bill_id'] = $billId;
                    $billItemModel->insert($item);
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Error creating bill for prescription #{$prescriptionId}: " . $e->getMessage());
        }
    }
    
    private function getMedicationPrice($medicationName)
    {
        $defaultPrices = [
            'amoxicillin' => 8.00, // Updated to match actual supplier price
            'paracetamol' => 25.00,
            'ibuprofen' => 30.00,
            'aspirin' => 20.00,
            'metformin' => 40.00,
            'losartan' => 45.00,
            'atorvastatin' => 60.00,
            'omeprazole' => 35.00,
            'cefuroxime' => 80.00,
            'azithromycin' => 75.00,
        ];
        
        $db = \Config\Database::connect();
        $basePrice = 0;
        
        if ($db->tableExists('medications')) {
            $med = $db->table('medications')
                ->where('name', $medicationName)
                ->orLike('name', $medicationName)
                ->first();
            
            if ($med && isset($med['price'])) {
                $basePrice = floatval($med['price']);
            }
        }
        
        // If no price from database, use default prices
        if ($basePrice <= 0) {
            $nameLower = strtolower($medicationName);
            foreach ($defaultPrices as $key => $price) {
                if (strpos($nameLower, $key) !== false) {
                    $basePrice = $price;
                    break;
                }
            }
        }
        
        // If still no price, use default
        if ($basePrice <= 0) {
            $basePrice = 50.00;
        }
        
        // Double the price for patient billing (patient pays 2x the inventory price)
        return $basePrice * 2;
    }
    
    private function ensureBillingTables()
    {
        $db = \Config\Database::connect();
        
        // Create bills table if not exists (same as Accounts controller)
        if (!$db->tableExists('bills')) {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'bill_number' => ['type' => 'VARCHAR', 'constraint' => 50],
                'patient_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'appointment_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'prescription_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'lab_test_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'room_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'bill_type' => ['type' => 'ENUM', 'constraint' => ['appointment', 'prescription', 'lab_test', 'room', 'consultation', 'procedure', 'other'], 'default' => 'other'],
                'subtotal' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'discount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'tax' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'total_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'paid_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'balance' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'], 'default' => 'pending'],
                'due_date' => ['type' => 'DATE', 'null' => true],
                'payment_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('patient_id');
            $forge->addKey('bill_number');
            $forge->createTable('bills', true);
        }
        
        // Create bill_items table if not exists
        if (!$db->tableExists('bill_items')) {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'bill_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'item_type' => ['type' => 'VARCHAR', 'constraint' => 50],
                'item_name' => ['type' => 'VARCHAR', 'constraint' => 255],
                'description' => ['type' => 'TEXT', 'null' => true],
                'quantity' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 1],
                'unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'total_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'reference_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('bill_id');
            $forge->createTable('bill_items', true);
        }
    }
}
