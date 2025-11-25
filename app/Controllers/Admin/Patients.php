<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PatientModel;

class Patients extends Controller
{
    public function index()
    {
        $model = new PatientModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get patients with their most recent doctor assignment from appointments
        $db = \Config\Database::connect();
        $builder = $db->table('patients p');
        $builder->select('p.*, 
                         u.name as assigned_doctor_name,
                         a.appointment_date as last_appointment_date,
                         a.status as appointment_status');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date, status, 
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments 
                         WHERE status != "cancelled") a', 'a.patient_id = p.id AND a.rn = 1', 'left');
        $builder->join('users u', 'u.id = a.doctor_id', 'left');
        $builder->orderBy('p.id', 'DESC');
        
        $patients = $builder->get()->getResultArray();
        
        // Debug: Log the first patient to see the data structure
        if (!empty($patients)) {
            log_message('info', 'First patient data: ' . json_encode($patients[0]));
        }

        $data = [
            'pageTitle' => 'Patients',
            'patients'  => $patients,
        ];

        return view('admin/patients', $data);
    }

    public function create()
    {
        $request = $this->request;

        $validationRules = [
            'full_name'     => 'required|min_length[2]|max_length[255]',
            'date_of_birth' => 'required',
            'gender'        => 'required|in_list[Male,Female]',
            'blood_type'    => 'permit_empty|in_list[A+,A-,B+,B-,AB+,AB-,O+,O-]',
            'contact'       => 'required|regex_match[/^09[0-9]{2} [0-9]{3} [0-9]{4}$/]',
            'email'         => 'permit_empty|valid_email',
            'address'       => 'required|in_list[Lagao,Bula,San Isidro,Calumpang,Tambler,City Heights]',
            'concern'       => 'required|min_length[3]'
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        // Custom date validation
        $dateOfBirth = $request->getPost('date_of_birth');
        $dateValidation = $this->validateDateOfBirth($dateOfBirth);
        if ($dateValidation !== true) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => ['date_of_birth' => $dateValidation]]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('patients');

        // Convert date from MM/DD/YYYY to YYYY-MM-DD format
        $dateOfBirth = $request->getPost('date_of_birth');
        if (!empty($dateOfBirth) && strpos($dateOfBirth, '/') !== false) {
            $parts = explode('/', $dateOfBirth);
            if (count($parts) === 3) {
                // Ensure proper formatting: MM/DD/YYYY -> YYYY-MM-DD
                $month = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                $day = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                $year = $parts[2];
                $dateOfBirth = $year . '-' . $month . '-' . $day;
            }
        }

        $data = [
            'full_name'     => $request->getPost('full_name'),
            'date_of_birth' => $dateOfBirth,
            'gender'        => $request->getPost('gender'),
            'blood_type'    => $request->getPost('blood_type'),
            'contact'       => str_replace(' ', '', $request->getPost('contact')), // Remove spaces for storage
            'email'         => $request->getPost('email'),
            'address'       => $request->getPost('address'),
            'concern'       => $request->getPost('concern'),
            'created_at'    => date('Y-m-d H:i:s')
        ];

        $builder->insert($data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Patient added successfully']);
    }

    private function validateDateOfBirth($date)
    {
        if (empty($date)) {
            return 'Date of birth is required';
        }

        // Check if date contains slashes (MM/DD/YYYY format)
        if (strpos($date, '/') !== false) {
            $parts = explode('/', $date);
            if (count($parts) !== 3) {
                return 'Invalid date format. Use MM/DD/YYYY';
            }

            $month = (int)$parts[0];
            $day = (int)$parts[1];
            $year = (int)$parts[2];

            // Validate month (1-12)
            if ($month < 1 || $month > 12) {
                return 'Month must be between 01 and 12';
            }

            // Validate day (1-31)
            if ($day < 1 || $day > 31) {
                return 'Day must be between 01 and 31';
            }

            // Validate year (not future, reasonable range)
            $currentYear = (int)date('Y');
            if ($year > $currentYear || $year < 1900) {
                return 'Year must be between 1900 and ' . $currentYear;
            }

            // Check if date is valid (e.g., Feb 30 doesn't exist)
            if (!checkdate($month, $day, $year)) {
                return 'Invalid date. Please check month and day.';
            }

            // Check if date is in the future
            $inputDate = new DateTime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT));
            $today = new DateTime();
            if ($inputDate > $today) {
                return 'Date of birth cannot be in the future';
            }
        }

        return true;
    }

    public function view($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $model = new PatientModel();
        $patient = $model->find($id);

        if (!$patient) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Patient not found']);
        }

        $db = \Config\Database::connect();

        // Calculate age
        $age = '—';
        if (!empty($patient['date_of_birth']) && $patient['date_of_birth'] !== '0000-00-00' && $patient['date_of_birth'] !== '') {
            try {
                $dateStr = $patient['date_of_birth'];
                if (strpos($dateStr, '/') !== false) {
                    $parts = explode('/', $dateStr);
                    if (count($parts) === 3) {
                        $dateStr = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                    }
                }
                $birthDate = new \DateTime($dateStr);
                $today = new \DateTime();
                $ageDiff = $today->diff($birthDate);
                $age = $ageDiff->y;
            } catch (\Exception $e) {
                $age = '—';
            }
        }

        // Format date of birth for display
        $dateOfBirth = '—';
        if (!empty($patient['date_of_birth']) && $patient['date_of_birth'] !== '0000-00-00') {
            try {
                $dateStr = $patient['date_of_birth'];
                if (strpos($dateStr, '/') === false) {
                    $dateObj = new \DateTime($dateStr);
                    $dateOfBirth = $dateObj->format('M j, Y');
                } else {
                    $dateOfBirth = $dateStr;
                }
            } catch (\Exception $e) {
                $dateOfBirth = $patient['date_of_birth'];
            }
        }

        // Get assigned doctor from latest appointment
        $assignedDoctor = null;
        if ($db->tableExists('appointments')) {
            $doctorBuilder = $db->table('appointments a');
            $doctorBuilder->select('u.name as doctor_name, a.appointment_date, a.appointment_time');
            $doctorBuilder->join('users u', 'u.id = a.doctor_id', 'left');
            $doctorBuilder->where('a.patient_id', $id);
            $doctorBuilder->where('a.status !=', 'cancelled');
            $doctorBuilder->orderBy('a.appointment_date', 'DESC');
            $doctorBuilder->orderBy('a.created_at', 'DESC');
            $doctorBuilder->limit(1);
            $doctorResult = $doctorBuilder->get()->getRowArray();
            if ($doctorResult) {
                $assignedDoctor = $doctorResult;
            }
        }

        // Get assigned nurse from treatment_updates
        $assignedNurse = null;
        if ($db->tableExists('treatment_updates')) {
            // Check if table has nurse_id column
            $fields = $db->getFieldData('treatment_updates');
            $hasNurseId = false;
            foreach ($fields as $field) {
                if (strtolower($field->name) === 'nurse_id') {
                    $hasNurseId = true;
                    break;
                }
            }
            
            $nurseBuilder = $db->table('treatment_updates tu');
            
            if ($hasNurseId) {
                // If nurse_id column exists, join with users table to get nurse name
                $nurseBuilder->select('u.name as nurse_name, tu.created_at');
                $nurseBuilder->join('users u', 'u.id = tu.nurse_id', 'left');
                $nurseBuilder->where('tu.patient_id', $id);
                $nurseBuilder->where('tu.nurse_id IS NOT NULL');
                $nurseBuilder->orderBy('tu.created_at', 'DESC');
            } else {
                // Otherwise, use nurse_name column
                $nurseBuilder->select('tu.nurse_name, tu.created_at');
                $nurseBuilder->where('tu.patient_id', $id);
                $nurseBuilder->where('tu.nurse_name IS NOT NULL');
                $nurseBuilder->where('tu.nurse_name !=', '');
                $nurseBuilder->orderBy('tu.created_at', 'DESC');
            }
            
            $nurseBuilder->limit(1);
            $nurseResult = $nurseBuilder->get()->getRowArray();
            if ($nurseResult && !empty($nurseResult['nurse_name'])) {
                $assignedNurse = $nurseResult['nurse_name'];
            }
        }

        // Get prescriptions
        $prescriptions = [];
        if ($db->tableExists('prescriptions')) {
            try {
                $prescriptionBuilder = $db->table('prescriptions p');
                $prescriptionBuilder->select('p.*, u.name as doctor_name');
                $prescriptionBuilder->join('users u', 'u.id = p.doctor_id', 'left');
                $prescriptionBuilder->where('p.patient_id', $id);
                $prescriptionBuilder->where('p.status !=', 'cancelled');
                $prescriptionBuilder->orderBy('p.created_at', 'DESC');
                $prescriptionResults = $prescriptionBuilder->get()->getResultArray();
                
                foreach ($prescriptionResults as $rx) {
                    $items = [];
                    if (!empty($rx['items_json'])) {
                        $items = json_decode($rx['items_json'], true) ?? [];
                    }
                    
                    $prescriptions[] = [
                        'id' => $rx['id'],
                        'rx_number' => 'RX#' . str_pad((string)$rx['id'], 6, '0', STR_PAD_LEFT),
                        'doctor_name' => $rx['doctor_name'] ?? 'N/A',
                        'items' => $items,
                        'notes' => $rx['notes'] ?? '',
                        'status' => $rx['status'] ?? 'pending',
                        'created_at' => $rx['created_at'] ?? '',
                        'created_at_formatted' => !empty($rx['created_at']) ? date('M j, Y g:i A', strtotime($rx['created_at'])) : '—'
                    ];
                }
            } catch (\Exception $e) {
                log_message('error', 'Error fetching prescriptions: ' . $e->getMessage());
            }
        }

        // Get lab tests
        $labTests = [];
        if ($db->tableExists('lab_test_requests')) {
            try {
                $labBuilder = $db->table('lab_test_requests ltr');
                $labBuilder->select('ltr.*, u.name as doctor_name');
                $labBuilder->join('users u', 'u.id = ltr.doctor_id', 'left');
                $labBuilder->where('ltr.patient_id', $id);
                $labBuilder->orderBy('ltr.created_at', 'DESC');
                $labResults = $labBuilder->get()->getResultArray();
                
                // Get results separately if table exists
                if ($db->tableExists('lab_test_results')) {
                    foreach ($labResults as &$lab) {
                        $resultBuilder = $db->table('lab_test_results');
                        $resultBuilder->where('request_id', $lab['id']);
                        $resultBuilder->orderBy('created_at', 'DESC');
                        $resultBuilder->limit(1);
                        $result = $resultBuilder->get()->getRowArray();
                        
                        if ($result) {
                            $lab['has_result'] = true;
                            $lab['result_status'] = $result['status'] ?? null;
                            $lab['result_summary'] = $result['result_summary'] ?? null;
                            $lab['is_critical'] = !empty($result['critical_flag']) || !empty($result['is_critical']);
                        } else {
                            $lab['has_result'] = false;
                            $lab['result_status'] = null;
                            $lab['result_summary'] = null;
                            $lab['is_critical'] = false;
                        }
                    }
                } else {
                    foreach ($labResults as &$lab) {
                        $lab['has_result'] = false;
                        $lab['result_status'] = null;
                        $lab['result_summary'] = null;
                        $lab['is_critical'] = false;
                    }
                }
                
                foreach ($labResults as $lab) {
                    $labTests[] = [
                        'id' => $lab['id'],
                        'test_type' => $lab['test_type'] ?? 'N/A',
                        'doctor_name' => $lab['doctor_name'] ?? 'N/A',
                        'priority' => $lab['priority'] ?? 'normal',
                        'status' => $lab['status'] ?? 'pending',
                        'has_result' => $lab['has_result'] ?? false,
                        'result_status' => $lab['result_status'] ?? null,
                        'result_summary' => $lab['result_summary'] ?? null,
                        'is_critical' => $lab['is_critical'] ?? false,
                        'created_at' => $lab['created_at'] ?? '',
                        'created_at_formatted' => !empty($lab['created_at']) ? date('M j, Y g:i A', strtotime($lab['created_at'])) : '—'
                    ];
                }
            } catch (\Exception $e) {
                log_message('error', 'Error fetching lab tests: ' . $e->getMessage());
            }
        }

        $patient['age'] = $age;
        $patient['date_of_birth_formatted'] = $dateOfBirth;
        $patient['assigned_doctor'] = $assignedDoctor;
        $patient['assigned_nurse'] = $assignedNurse;
        $patient['prescriptions'] = $prescriptions;
        $patient['lab_tests'] = $labTests;

        return $this->response->setJSON(['status' => 'success', 'patient' => $patient]);
    }

    public function edit($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $model = new PatientModel();
        $patient = $model->find($id);

        if (!$patient) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Patient not found']);
        }

        // Format date of birth for input (MM/DD/YYYY)
        if (!empty($patient['date_of_birth']) && $patient['date_of_birth'] !== '0000-00-00') {
            try {
                $dateStr = $patient['date_of_birth'];
                if (strpos($dateStr, '/') === false) {
                    $dateObj = new \DateTime($dateStr);
                    $patient['date_of_birth'] = $dateObj->format('m/d/Y');
                }
            } catch (\Exception $e) {
                // Keep original format
            }
        }

        return $this->response->setJSON(['status' => 'success', 'patient' => $patient]);
    }

    public function update($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $request = $this->request;
        $model = new PatientModel();

        // Check if patient exists
        $patient = $model->find($id);
        if (!$patient) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Patient not found']);
        }

        $validationRules = [
            'full_name'     => 'required|min_length[2]|max_length[255]',
            'date_of_birth' => 'required',
            'gender'        => 'required|in_list[Male,Female]',
            'blood_type'    => 'permit_empty|in_list[A+,A-,B+,B-,AB+,AB-,O+,O-]',
            'contact'       => 'required',
            'email'         => 'permit_empty|valid_email',
            'address'       => 'permit_empty',
            'concern'       => 'permit_empty'
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        // Custom date validation
        $dateOfBirth = $request->getPost('date_of_birth');
        $dateValidation = $this->validateDateOfBirth($dateOfBirth);
        if ($dateValidation !== true) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => ['date_of_birth' => $dateValidation]]);
        }

        // Convert date from MM/DD/YYYY to YYYY-MM-DD format
        if (!empty($dateOfBirth) && strpos($dateOfBirth, '/') !== false) {
            $parts = explode('/', $dateOfBirth);
            if (count($parts) === 3) {
                $month = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                $day = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                $year = $parts[2];
                $dateOfBirth = $year . '-' . $month . '-' . $day;
            }
        }

        $data = [
            'full_name'     => $request->getPost('full_name'),
            'date_of_birth' => $dateOfBirth,
            'gender'        => $request->getPost('gender'),
            'blood_type'    => $request->getPost('blood_type'),
            'contact'       => str_replace(' ', '', $request->getPost('contact')),
            'email'         => $request->getPost('email'),
            'address'       => $request->getPost('address'),
            'concern'       => $request->getPost('concern'),
            'status'        => $request->getPost('status') ?? 'active',
            'patient_type'  => $request->getPost('patient_type') ?? 'outpatient',
        ];

        // Calculate and update age
        if (!empty($dateOfBirth)) {
            try {
                $birthDate = new \DateTime($dateOfBirth);
                $today = new \DateTime();
                $ageDiff = $today->diff($birthDate);
                $data['age'] = $ageDiff->y;
            } catch (\Exception $e) {
                // Age calculation failed, skip
            }
        }

        $model->update($id, $data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Patient updated successfully']);
    }

    public function delete($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $model = new PatientModel();
        $patient = $model->find($id);

        if (!$patient) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Patient not found']);
        }

        $model->delete($id);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Patient deleted successfully']);
    }
}


