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

        // Get all patients for dropdown
        $patients = $patientModel->orderBy('full_name', 'ASC')->findAll();

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

        // Get patient details for each request
        foreach ($walkInRequests as &$request) {
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

            $data = [
                'patient_id' => $patientId,
                'doctor_id' => null, // Walk-in requests have no doctor
                'admission_id' => null, // Walk-in requests have no admission
                'test_type' => $testType,
                'priority' => $priority,
                'status' => 'sent_to_lab', // Immediately send to lab for walk-in requests
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
