<?php

namespace App\Controllers\Admin\Lab;

use CodeIgniter\Controller;
use App\Models\LabTestRequestModel;
use App\Models\PatientModel;
use App\Models\AppointmentModel;

class WalkIn extends Controller
{
    public function index()
    {
        // Redirect to dashboard since walk-in is now integrated there
        return redirect()->to('/admin/lab');
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
