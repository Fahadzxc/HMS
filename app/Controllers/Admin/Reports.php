<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Reports extends Controller
{
    public function index()
    {
        try {
            if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
                return redirect()->to('/login');
            }

            $data = [
                'title' => 'Reports - HMS',
                'user_role' => 'admin',
                'user_name' => session()->get('name'),
                'selected_role' => $this->request->getGet('role') ?? 'all',
                'date_from' => $this->request->getGet('date_from') ?? date('Y-m-01'),
                'date_to' => $this->request->getGet('date_to') ?? date('Y-m-d'),
                'nurse_reports' => ['treatment_updates' => [], 'summary' => ['total_updates' => 0, 'total_patients' => 0, 'avg_vitals_per_day' => 0]],
                'doctor_reports' => ['appointments' => [], 'prescriptions' => [], 'summary' => ['total_appointments' => 0, 'total_prescriptions' => 0, 'total_consultations' => 0]],
                'lab_reports' => ['test_requests' => [], 'test_results' => [], 'summary' => ['total_requests' => 0, 'total_results' => 0, 'critical_count' => 0, 'completion_rate' => 0]],
                'pharmacy_reports' => ['dispensed_prescriptions' => [], 'summary' => ['total_dispensed' => 0]],
                'reception_reports' => ['new_patients' => [], 'appointments' => [], 'summary' => ['total_new_patients' => 0, 'total_appointments' => 0]],
                'accounts_reports' => ['bills' => [], 'payments' => [], 'summary' => ['total_revenue' => 0, 'total_bills' => 0, 'total_payments' => 0]],
            ];

            $role = $data['selected_role'];
            
            if ($role === 'all' || $role === 'nurse') {
                try {
                    $data['nurse_reports'] = $this->getNurseReports($data['date_from'], $data['date_to']);
                } catch (\Exception $e) {
                    log_message('error', 'Nurse reports fetch error: ' . $e->getMessage());
                }
            }
            if ($role === 'all' || $role === 'doctor') {
                try {
                    $data['doctor_reports'] = $this->getDoctorReports($data['date_from'], $data['date_to']);
                } catch (\Exception $e) {
                    log_message('error', 'Doctor reports fetch error: ' . $e->getMessage());
                }
            }
            if ($role === 'all' || $role === 'laboratory') {
                try {
                    $data['lab_reports'] = $this->getLaboratoryReports($data['date_from'], $data['date_to']);
                } catch (\Exception $e) {
                    log_message('error', 'Lab reports fetch error: ' . $e->getMessage());
                }
            }
            if ($role === 'all' || $role === 'pharmacy') {
                try {
                    $data['pharmacy_reports'] = $this->getPharmacyReports($data['date_from'], $data['date_to']);
                } catch (\Exception $e) {
                    log_message('error', 'Pharmacy reports fetch error: ' . $e->getMessage());
                }
            }
            if ($role === 'all' || $role === 'reception') {
                try {
                    $data['reception_reports'] = $this->getReceptionReports($data['date_from'], $data['date_to']);
                } catch (\Exception $e) {
                    log_message('error', 'Reception reports fetch error: ' . $e->getMessage());
                }
            }
            if ($role === 'all' || $role === 'accounts') {
                try {
                    $data['accounts_reports'] = $this->getAccountsReports($data['date_from'], $data['date_to']);
                } catch (\Exception $e) {
                    log_message('error', 'Accounts reports fetch error: ' . $e->getMessage());
                }
            }

            return view('admin/reports', $data);
        } catch (\Throwable $e) {
            log_message('error', 'Reports controller fatal error: ' . $e->getMessage());
            log_message('error', 'File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            log_message('error', 'Trace: ' . $e->getTraceAsString());
            die('Error loading reports. Check logs for details. Error: ' . $e->getMessage());
        }
    }

    private function getNurseReports($dateFrom, $dateTo)
    {
        $reports = ['treatment_updates' => [], 'summary' => ['total_updates' => 0, 'total_patients' => 0, 'avg_vitals_per_day' => 0]];
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('treatment_updates')) {
                $updates = $db->table('treatment_updates tu')
                    ->select('tu.*, p.full_name as patient_name')
                    ->join('patients p', 'p.id = tu.patient_id', 'left')
                    ->where('DATE(tu.created_at) >=', $dateFrom)
                    ->where('DATE(tu.created_at) <=', $dateTo)
                    ->orderBy('tu.created_at', 'DESC')
                    ->get()->getResultArray();
                $reports['treatment_updates'] = $updates ?: [];
                $reports['summary']['total_updates'] = count($updates);
                $patientIds = array_filter(array_column($updates ?: [], 'patient_id'));
                $reports['summary']['total_patients'] = count(array_unique($patientIds));
                $days = max(1, (strtotime($dateTo) - strtotime($dateFrom)) / 86400);
                $reports['summary']['avg_vitals_per_day'] = $days > 0 ? round(count($updates) / $days, 2) : 0;
            }
        } catch (\Exception $e) {
            log_message('error', 'Nurse: ' . $e->getMessage());
        }
        return $reports;
    }

    private function getDoctorReports($dateFrom, $dateTo)
    {
        $reports = ['appointments' => [], 'prescriptions' => [], 'summary' => ['total_appointments' => 0, 'total_prescriptions' => 0, 'total_consultations' => 0]];
        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            $prescriptionModel = new \App\Models\PrescriptionModel();
            $appointments = $appointmentModel->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name')
                ->join('patients', 'patients.id = appointments.patient_id', 'left')
                ->join('users', 'users.id = appointments.doctor_id', 'left')
                ->where('DATE(appointments.appointment_date) >=', $dateFrom)
                ->where('DATE(appointments.appointment_date) <=', $dateTo)
                ->where('appointments.status !=', 'cancelled')
                ->orderBy('appointments.appointment_date', 'DESC')
                ->findAll();
            $reports['appointments'] = $appointments ?: [];
            $reports['summary']['total_appointments'] = count($appointments);
            $prescriptions = $prescriptionModel->select('prescriptions.*, patients.full_name as patient_name, users.name as doctor_name')
                ->join('patients', 'patients.id = prescriptions.patient_id', 'left')
                ->join('users', 'users.id = prescriptions.doctor_id', 'left')
                ->where('DATE(prescriptions.created_at) >=', $dateFrom)
                ->where('DATE(prescriptions.created_at) <=', $dateTo)
                ->where('prescriptions.status !=', 'cancelled')
                ->orderBy('prescriptions.created_at', 'DESC')
                ->findAll();
            $reports['prescriptions'] = $prescriptions ?: [];
            $reports['summary']['total_prescriptions'] = count($prescriptions);
            $reports['summary']['total_consultations'] = count($appointments);
        } catch (\Exception $e) {
            log_message('error', 'Doctor: ' . $e->getMessage());
        }
        return $reports;
    }

    private function getLaboratoryReports($dateFrom, $dateTo)
    {
        $reports = ['test_requests' => [], 'test_results' => [], 'summary' => ['total_requests' => 0, 'total_results' => 0, 'critical_count' => 0, 'completion_rate' => 0]];
        try {
            $requestModel = new \App\Models\LabTestRequestModel();
            $requests = $requestModel->select('lab_test_requests.*, patients.full_name as patient_name, users.name as doctor_name')
                ->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
                ->join('users', 'users.id = lab_test_requests.doctor_id', 'left')
                ->where('DATE(lab_test_requests.requested_at) >=', $dateFrom)
                ->where('DATE(lab_test_requests.requested_at) <=', $dateTo)
                ->orderBy('lab_test_requests.requested_at', 'DESC')
                ->findAll();
            $reports['test_requests'] = $requests ?: [];
            $reports['summary']['total_requests'] = count($requests);
            $db = \Config\Database::connect();
            if ($db->tableExists('lab_test_results')) {
                $resultModel = new \App\Models\LabTestResultModel();
                $results = $resultModel->select('lab_test_results.*, lab_test_requests.test_type, patients.full_name as patient_name')
                    ->join('lab_test_requests', 'lab_test_requests.id = lab_test_results.request_id', 'left')
                    ->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
                    ->where('DATE(lab_test_results.released_at) >=', $dateFrom)
                    ->where('DATE(lab_test_results.released_at) <=', $dateTo)
                    ->orderBy('lab_test_results.released_at', 'DESC')
                    ->findAll();
                $reports['test_results'] = $results ?: [];
                $reports['summary']['total_results'] = count($results);
                $critical = array_filter($results ?: [], function($r) { return !empty($r['critical_flag']) && $r['critical_flag'] == 1; });
                $reports['summary']['critical_count'] = count($critical);
                $reports['summary']['completion_rate'] = count($requests) > 0 ? round((count($results) / count($requests)) * 100, 2) : 0;
            }
        } catch (\Exception $e) {
            log_message('error', 'Lab: ' . $e->getMessage());
        }
        return $reports;
    }

    private function getPharmacyReports($dateFrom, $dateTo)
    {
        $reports = ['dispensed_prescriptions' => [], 'summary' => ['total_dispensed' => 0]];
        try {
            $prescriptionModel = new \App\Models\PrescriptionModel();
            $prescriptions = $prescriptionModel->select('prescriptions.*, patients.full_name as patient_name, users.name as doctor_name')
                ->join('patients', 'patients.id = prescriptions.patient_id', 'left')
                ->join('users', 'users.id = prescriptions.doctor_id', 'left')
                ->whereIn('prescriptions.status', ['dispensed', 'completed'])
                ->where('DATE(prescriptions.updated_at) >=', $dateFrom)
                ->where('DATE(prescriptions.updated_at) <=', $dateTo)
                ->orderBy('prescriptions.updated_at', 'DESC')
                ->findAll();
            $reports['dispensed_prescriptions'] = $prescriptions ?: [];
            $reports['summary']['total_dispensed'] = count($prescriptions);
        } catch (\Exception $e) {
            log_message('error', 'Pharmacy: ' . $e->getMessage());
        }
        return $reports;
    }

    private function getReceptionReports($dateFrom, $dateTo)
    {
        $reports = ['new_patients' => [], 'appointments' => [], 'summary' => ['total_new_patients' => 0, 'total_appointments' => 0]];
        try {
            $patientModel = new \App\Models\PatientModel();
            $appointmentModel = new \App\Models\AppointmentModel();
            $patients = $patientModel->where('DATE(created_at) >=', $dateFrom)->where('DATE(created_at) <=', $dateTo)->orderBy('created_at', 'DESC')->findAll();
            $reports['new_patients'] = $patients ?: [];
            $reports['summary']['total_new_patients'] = count($patients);
            $appointments = $appointmentModel->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name')
                ->join('patients', 'patients.id = appointments.patient_id', 'left')
                ->join('users', 'users.id = appointments.doctor_id', 'left')
                ->where('DATE(appointments.appointment_date) >=', $dateFrom)
                ->where('DATE(appointments.appointment_date) <=', $dateTo)
                ->orderBy('appointments.appointment_date', 'DESC')
                ->findAll();
            $reports['appointments'] = $appointments ?: [];
            $reports['summary']['total_appointments'] = count($appointments);
        } catch (\Exception $e) {
            log_message('error', 'Reception: ' . $e->getMessage());
        }
        return $reports;
    }

    private function getAccountsReports($dateFrom, $dateTo)
    {
        $reports = ['bills' => [], 'payments' => [], 'summary' => ['total_revenue' => 0, 'total_bills' => 0, 'total_payments' => 0]];
        try {
            $billingModel = new \App\Models\BillingModel();
            $bills = $billingModel->select('bills.*, patients.full_name as patient_name')
                ->join('patients', 'patients.id = bills.patient_id', 'left')
                ->where('DATE(bills.created_at) >=', $dateFrom)
                ->where('DATE(bills.created_at) <=', $dateTo)
                ->orderBy('bills.created_at', 'DESC')
                ->findAll();
            $reports['bills'] = $bills ?: [];
            $reports['summary']['total_bills'] = count($bills);
            $paymentModel = new \App\Models\PaymentModel();
            if (method_exists($paymentModel, 'getPaymentsWithPatient')) {
                $payments = $paymentModel->getPaymentsWithPatient(['date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => 'completed']);
                $reports['payments'] = is_array($payments) ? $payments : [];
                $reports['summary']['total_payments'] = count($reports['payments']);
                $amounts = array_column($reports['payments'], 'amount');
                $reports['summary']['total_revenue'] = array_sum(array_filter($amounts));
            }
        } catch (\Exception $e) {
            log_message('error', 'Accounts: ' . $e->getMessage());
        }
        return $reports;
    }
}
