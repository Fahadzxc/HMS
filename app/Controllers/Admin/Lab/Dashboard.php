<?php

namespace App\Controllers\Admin\Lab;

use CodeIgniter\Controller;
use App\Models\LabTestRequestModel;
use App\Models\LabTestResultModel;
use App\Models\LabStaffModel;
use App\Models\PatientModel;
use App\Models\AppointmentModel;
use Throwable;

class Dashboard extends Controller
{
    protected LabTestRequestModel $requestModel;
    protected LabTestResultModel $resultModel;
    protected LabStaffModel $staffModel;

    public function __construct()
    {
        $this->requestModel = new LabTestRequestModel();
        $this->resultModel = new LabTestResultModel();
        $this->staffModel = new LabStaffModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $data = [
            'pageTitle' => 'Laboratory Dashboard',
            'title' => 'Laboratory Dashboard - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'metrics' => [
                'pendingRequests' => 0,
                'completedToday' => 0,
                'criticalResults' => 0,
                'activeStaff' => 0,
            ],
            'recentRequests' => [],
            'recentResults' => [],
            'loadError' => null,
        ];

        try {
            $today = date('Y-m-d');
            $db = \Config\Database::connect();

            $pendingRequests = (clone $this->requestModel)
                ->where('status', 'pending')
                ->countAllResults();

            $completedToday = (clone $this->requestModel)
                ->where('status', 'completed')
                ->where('DATE(updated_at)', $today)
                ->countAllResults();

            $criticalResults = 0;
            if ($db->tableExists('lab_test_results')) {
                try {
                    $criticalResults = (clone $this->resultModel)
                        ->where('critical_flag', 1)
                        ->countAllResults();
                } catch (\Exception $e) {
                    log_message('warning', 'Error counting critical results: ' . $e->getMessage());
                }
            }

            $activeStaff = 0;
            if ($db->tableExists('lab_staff')) {
                try {
                    $activeStaff = (clone $this->staffModel)
                        ->where('status', 'active')
                        ->countAllResults();
                } catch (\Exception $e) {
                    log_message('warning', 'Error counting active staff: ' . $e->getMessage());
                }
            }

            $recentRequests = [];
            try {
                $recentRequests = $this->requestModel->getAllWithRelations([
                    'date_from' => date('Y-m-d', strtotime('-7 days')),
                ]);
            } catch (\Exception $e) {
                log_message('warning', 'Error fetching recent requests: ' . $e->getMessage());
            }

            $recentResults = [];
            try {
                $recentResults = $this->resultModel->getAllWithRelations([
                    'date_from' => date('Y-m-d', strtotime('-7 days')),
                ]);
            } catch (\Exception $e) {
                log_message('warning', 'Error fetching recent results: ' . $e->getMessage());
            }

            $data['metrics'] = [
                'pendingRequests' => $pendingRequests,
                'completedToday' => $completedToday,
                'criticalResults' => $criticalResults,
                'activeStaff' => $activeStaff,
            ];
            $data['recentRequests'] = $recentRequests;
            $data['recentResults'] = $recentResults;
        } catch (Throwable $e) {
            log_message('error', 'Failed to load lab dashboard data: ' . $e->getMessage());
            $data['loadError'] = 'Laboratory data is not ready yet. Please ensure the latest migrations are run and sample data is available.';
        }

        // Get walk-in data
        try {
            $patientModel = new PatientModel();
            $appointmentModel = new AppointmentModel();
            
            // Get patients for dropdown - exclude those with ANY appointment (except cancelled) or who are inpatients
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
            $walkInRequests = $this->requestModel->where('doctor_id IS NULL', null, false)
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
                    $this->requestModel->update($request['id'], [
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

            $data['patients'] = $patients;
            $data['walkInRequests'] = $walkInRequests;
            $data['labAppointments'] = $labAppointments;
        } catch (Throwable $e) {
            log_message('error', 'Failed to load walk-in data: ' . $e->getMessage());
            $data['patients'] = [];
            $data['walkInRequests'] = [];
            $data['labAppointments'] = [];
        }

        return view('admin/lab/dashboard', $data);
    }
}
