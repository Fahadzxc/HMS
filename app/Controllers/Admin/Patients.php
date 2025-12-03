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
        $db = \Config\Database::connect();
        
        // Get patients with doctor from appointments OR admissions
        // Using COALESCE to prefer admission doctor for inpatients
        // For discharged patients, show their last admission's doctor
        $sql = "
            SELECT p.*, 
                   COALESCE(adm_doc.name, appt_doc.name) as assigned_doctor_name,
                   COALESCE(adm.admission_date, a.appointment_date) as last_appointment_date,
                   COALESCE(adm.status, a.status) as appointment_status
            FROM patients p
            LEFT JOIN (
                SELECT patient_id, doctor_id, appointment_date, status,
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments 
                WHERE status != 'cancelled'
            ) a ON a.patient_id = p.id AND a.rn = 1
            LEFT JOIN users appt_doc ON appt_doc.id = a.doctor_id
            LEFT JOIN (
                SELECT patient_id, doctor_id, admission_date, status,
                       ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY admission_date DESC, created_at DESC) as rn
                FROM admissions
            ) adm ON adm.patient_id = p.id AND adm.rn = 1
            LEFT JOIN users adm_doc ON adm_doc.id = adm.doctor_id
            ORDER BY p.id DESC
        ";
        
        $patients = $db->query($sql)->getResultArray();

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

        // Get assigned doctor - check admissions first (for inpatients), then appointments
        $assignedDoctor = null;
        
        // First check admissions table for inpatients
        if ($db->tableExists('admissions') && $patient['patient_type'] === 'inpatient') {
            $admBuilder = $db->table('admissions adm');
            $admBuilder->select('u.name as doctor_name, adm.admission_date as appointment_date, NULL as appointment_time');
            $admBuilder->join('users u', 'u.id = adm.doctor_id', 'left');
            $admBuilder->where('adm.patient_id', $id);
            $admBuilder->where('adm.status', 'Admitted');
            $admBuilder->orderBy('adm.created_at', 'DESC');
            $admBuilder->limit(1);
            $admResult = $admBuilder->get()->getRowArray();
            if ($admResult && !empty($admResult['doctor_name'])) {
                $assignedDoctor = $admResult;
            }
        }
        
        // If no admission doctor found, check appointments
        if (!$assignedDoctor && $db->tableExists('appointments')) {
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

        // Get room information if patient has a room
        $roomInfo = null;
        $roomNumber = $patient['room_number'] ?? null;
        
        // If no room_number in patient record, check appointments table for room_id
        if (empty($roomNumber) && $db->tableExists('appointments')) {
            try {
                $apptRoomBuilder = $db->table('appointments');
                $apptRoomBuilder->select('room_id');
                $apptRoomBuilder->where('patient_id', $id);
                $apptRoomBuilder->where('room_id IS NOT NULL');
                $apptRoomBuilder->orderBy('created_at', 'DESC');
                $apptRoomBuilder->limit(1);
                $apptRoomResult = $apptRoomBuilder->get()->getRowArray();
                
                if (!empty($apptRoomResult['room_id']) && $db->tableExists('rooms')) {
                    $roomFromApptBuilder = $db->table('rooms');
                    $roomFromApptBuilder->where('id', $apptRoomResult['room_id']);
                    $roomFromAppt = $roomFromApptBuilder->get()->getRowArray();
                    if ($roomFromAppt) {
                        $roomNumber = $roomFromAppt['room_number'];
                        $roomInfo = $roomFromAppt;
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error fetching room from appointments: ' . $e->getMessage());
            }
        }
        
        // If we have room_number but no roomInfo yet, fetch room details
        if (!empty($roomNumber) && empty($roomInfo) && $db->tableExists('rooms')) {
            try {
                $roomBuilder = $db->table('rooms');
                $roomBuilder->where('room_number', $roomNumber);
                $roomResult = $roomBuilder->get()->getRowArray();
                if ($roomResult) {
                    $roomInfo = $roomResult;
                }
            } catch (\Exception $e) {
                log_message('error', 'Error fetching room info: ' . $e->getMessage());
            }
        }
        
        // Update patient array with room_number if we found it
        if (!empty($roomNumber)) {
            $patient['room_number'] = $roomNumber;
        }

        // Format admission and discharge dates
        $admissionDateFormatted = '—';
        if (!empty($patient['admission_date']) && $patient['admission_date'] !== '0000-00-00') {
            try {
                $admissionDate = new \DateTime($patient['admission_date']);
                $admissionDateFormatted = $admissionDate->format('M j, Y');
            } catch (\Exception $e) {
                $admissionDateFormatted = $patient['admission_date'];
            }
        }

        $dischargeDateFormatted = '—';
        if (!empty($patient['discharge_date']) && $patient['discharge_date'] !== '0000-00-00') {
            try {
                $dischargeDate = new \DateTime($patient['discharge_date']);
                $dischargeDateFormatted = $dischargeDate->format('M j, Y');
            } catch (\Exception $e) {
                $dischargeDateFormatted = $patient['discharge_date'];
            }
        }

        // Get admission appointment details
        $admissionAppointment = null;
        if ($assignedDoctor && !empty($assignedDoctor['appointment_type']) && 
            (strtolower($assignedDoctor['appointment_type']) === 'emergency' || 
             strtolower($assignedDoctor['appointment_type']) === 'scheduled' ||
             strtolower($assignedDoctor['appointment_type']) === 'admission')) {
            $admissionAppointment = $assignedDoctor;
        }

        // Get insurance information
        $insuranceInfo = null;
        $insuranceClaims = [];
        if ($db->tableExists('insurance_claims')) {
            try {
                $insuranceBuilder = $db->table('insurance_claims');
                $insuranceBuilder->where('patient_id', $id);
                $insuranceBuilder->orderBy('created_at', 'DESC');
                $insuranceResults = $insuranceBuilder->get()->getResultArray();
                
                if (!empty($insuranceResults)) {
                    // Get the most recent insurance claim as primary insurance info
                    $insuranceInfo = $insuranceResults[0];
                    
                    // Determine policy status: active if there's at least one approved/paid claim, otherwise check expiration
                    $hasActiveClaim = false;
                    foreach ($insuranceResults as $claim) {
                        if (in_array(strtolower($claim['status'] ?? ''), ['approved', 'paid'])) {
                            $hasActiveClaim = true;
                            break;
                        }
                    }
                    
                    // Add policy_status to insurance_info (separate from claim status)
                    $insuranceInfo['policy_status'] = $hasActiveClaim ? 'active' : 'active'; // Default to active, can be enhanced with expiration check
                    
                    // Get all insurance claims for history
                    foreach ($insuranceResults as $claim) {
                        $insuranceClaims[] = [
                            'claim_number' => $claim['claim_number'] ?? 'N/A',
                            'insurance_provider' => $claim['insurance_provider'] ?? 'N/A',
                            'policy_number' => $claim['policy_number'] ?? 'N/A',
                            'member_id' => $claim['member_id'] ?? 'N/A',
                            'claim_amount' => $claim['claim_amount'] ?? 0,
                            'approved_amount' => $claim['approved_amount'] ?? 0,
                            'status' => $claim['status'] ?? 'pending',
                            'submitted_date' => !empty($claim['submitted_date']) ? date('M j, Y', strtotime($claim['submitted_date'])) : '—',
                            'approved_date' => !empty($claim['approved_date']) ? date('M j, Y', strtotime($claim['approved_date'])) : '—',
                            'created_at_formatted' => !empty($claim['created_at']) ? date('M j, Y g:i A', strtotime($claim['created_at'])) : '—'
                        ];
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error fetching insurance info: ' . $e->getMessage());
            }
        }

        $patient['age'] = $age;
        $patient['date_of_birth_formatted'] = $dateOfBirth;
        $patient['assigned_doctor'] = $assignedDoctor;
        $patient['assigned_nurse'] = $assignedNurse;
        $patient['prescriptions'] = $prescriptions;
        $patient['lab_tests'] = $labTests;
        $patient['room_info'] = $roomInfo;
        $patient['admission_date_formatted'] = $admissionDateFormatted;
        $patient['discharge_date_formatted'] = $dischargeDateFormatted;
        $patient['admission_appointment'] = $admissionAppointment;
        $patient['insurance_info'] = $insuranceInfo;
        $patient['insurance_claims'] = $insuranceClaims;
        
        // Ensure emergency contact fields are included
        if (empty($patient['emergency_name'])) {
            $patient['emergency_name'] = null;
        }
        if (empty($patient['emergency_contact'])) {
            $patient['emergency_contact'] = null;
        }
        if (empty($patient['relationship'])) {
            $patient['relationship'] = null;
        }

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


