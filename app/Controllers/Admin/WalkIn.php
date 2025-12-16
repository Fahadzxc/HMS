<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\LabTestRequestModel;
use App\Models\PatientModel;
use App\Models\AppointmentModel;

class WalkIn extends Controller
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $patientModel = new PatientModel();
        $labRequestModel = new LabTestRequestModel();
        $appointmentModel = new AppointmentModel();

        // Get patients for dropdown - exclude those with ANY appointment (except cancelled) or who are inpatients
        $db = \Config\Database::connect();
        
        // Use LEFT JOIN to exclude patients with appointments (except cancelled) or who are admitted
        // Only show patients who have NO appointments OR only cancelled appointments, and are NOT admitted
        $patients = $db->table('patients p')
            ->select('p.*')
            ->join('appointments a', "a.patient_id = p.id AND a.status != 'cancelled'", 'left')
            ->join('admissions adm', "adm.patient_id = p.id AND adm.status = 'Admitted'", 'left')
            ->where('a.id IS NULL', null, false) // No active appointments (only cancelled or no appointments)
            ->where('adm.id IS NULL', null, false) // Not admitted
            ->groupBy('p.id')
            ->orderBy('p.full_name', 'ASC')
            ->get()
            ->getResultArray();

        // Get walk-in lab requests (requests without doctor_id)
        $walkInRequests = $labRequestModel->where('doctor_id IS NULL', null, false)
            ->orderBy('requested_at', 'DESC')
            ->findAll();

        // Get lab test appointments (appointments with appointment_type = 'laboratory_test')
        $labAppointments = $appointmentModel
            ->where('appointment_type', 'laboratory_test')
            ->where('doctor_id IS NULL', null, false)
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('appointment_time', 'DESC')
            ->findAll();

        // Check and update lab test request status if bill is paid
        $billingModel = new \App\Models\BillingModel();
        foreach ($walkInRequests as &$request) {
            // Check if there's a paid bill for this lab test request
            $paidBill = $db->table('bills')
                ->where('lab_test_id', $request['id'])
                ->where('status', 'paid')
                ->get()
                ->getRowArray();
            
            // Also check bill_items for reference (by reference_id matching lab test request id)
            if (!$paidBill && $db->tableExists('bill_items')) {
                $billItem = $db->table('bill_items')
                    ->select('bills.*')
                    ->join('bills', 'bills.id = bill_items.bill_id', 'inner')
                    ->where('bill_items.reference_id', $request['id'])
                    ->where('bills.status', 'paid')
                    ->get()
                    ->getRowArray();
                
                if ($billItem) {
                    $paidBill = $billItem;
                }
            }
            
            // If bill is paid and request is not completed, update it
            if ($paidBill && $request['status'] !== 'completed') {
                $labRequestModel->update($request['id'], [
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $request['status'] = 'completed';
                log_message('info', "Updated walk-in lab test request #{$request['id']} to completed (has paid bill)");
            }
            
            // Get patient details
            if (!empty($request['patient_id'])) {
                $patient = $patientModel->find($request['patient_id']);
                $request['patient_name'] = $patient['full_name'] ?? 'Unknown';
                $request['patient_contact'] = $patient['contact'] ?? '';
            }
        }
        
        // Get patient details for lab test appointments
        foreach ($labAppointments as &$appointment) {
            if (!empty($appointment['patient_id'])) {
                $patient = $patientModel->find($appointment['patient_id']);
                $appointment['patient_name'] = $patient['full_name'] ?? 'Unknown';
                $appointment['patient_contact'] = $patient['contact'] ?? '';
            }
        }

        $data = [
            'pageTitle' => 'Walk In - Lab Tests',
            'patients' => $patients,
            'walkInRequests' => $walkInRequests,
            'labAppointments' => $labAppointments,
        ];

        return view('admin/walkin', $data);
    }

    public function create()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');

        try {
            $requestModel = new LabTestRequestModel();
            $patientModel = new PatientModel();

            $patientId = $this->request->getPost('patient_id');
            $testType = $this->request->getPost('test_type');
            $priority = $this->request->getPost('priority') ?? 'normal';
            $notes = $this->request->getPost('notes') ?? '';

            // Validation
            if (empty($patientId) || empty($testType)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please fill in all required fields (Patient, Test Type).'
                ]);
            }

            // Verify patient exists
            $patient = $patientModel->find($patientId);
            if (!$patient) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Patient not found.'
                ]);
            }

            // Get test pricing and specimen requirement from master table
            $labTestMasterModel = new \App\Models\LabTestMasterModel();
            $testInfo = $labTestMasterModel->getTestByName($testType);
            
            $price = 0.00;
            $requiresSpecimen = 0;
            
            if ($testInfo) {
                $price = (float)($testInfo['price'] ?? 0.00);
                $requiresSpecimen = (int)($testInfo['requires_specimen'] ?? 0);
            }
            
            // Walk-in requests with specimens must go through nurse
            // Per user requirement: all requests with specimens must go through nurse
            $status = ($requiresSpecimen === 1) ? 'pending' : 'pending'; // Always pending to go through nurse
            
            $data = [
                'patient_id' => $patientId,
                'doctor_id' => null, // Walk-in requests have no doctor
                'admission_id' => null, // Walk-in requests have no admission
                'test_type' => $testType,
                'price' => $price,
                'requires_specimen' => $requiresSpecimen,
                'priority' => $priority,
                'status' => $status, // Go through nurse if specimen needed
                'requested_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
                'billing_status' => 'unbilled', // Ensure walk-in lab test is billable
            ];
            
            // Ensure billing_status column exists before inserting
            $this->ensureLabBillingColumn();
            
            // Use skipValidation to allow null doctor_id
            $requestModel->skipValidation(true)->insert($data);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Walk-in lab test request created successfully!'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error creating walk-in lab request: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error creating request: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getTestInfo()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');
        
        $testType = $this->request->getGet('test_type');
        
        if (empty($testType)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Test type is required']);
        }

        try {
            $labTestMasterModel = new \App\Models\LabTestMasterModel();
            $testInfo = $labTestMasterModel->getTestByName($testType);
            
            if ($testInfo) {
                return $this->response->setJSON([
                    'success' => true,
                    'test' => [
                        'price' => (float)($testInfo['price'] ?? 0.00),
                        'requires_specimen' => (int)($testInfo['requires_specimen'] ?? 0),
                        'test_category' => $testInfo['test_category'] ?? '',
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Test information not found'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching test info: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Ensure billing_status column exists in lab_test_requests table
     */
    private function ensureLabBillingColumn(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();
        
        if ($db->tableExists('lab_test_requests')) {
            try {
                $fields = $db->getFieldData('lab_test_requests');
                $has = false;
                foreach ($fields as $f) {
                    if (strtolower($f->name) === 'billing_status') {
                        $has = true;
                        break;
                    }
                }
                if (!$has) {
                    $forge->addColumn('lab_test_requests', [
                        'billing_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true]
                    ]);
                }
            } catch (\Exception $e) {
                log_message('debug', 'ensureLabBillingColumn skip: ' . $e->getMessage());
            }
        }
    }
}
