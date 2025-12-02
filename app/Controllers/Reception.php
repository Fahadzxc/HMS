<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PatientModel;
use App\Models\ReceptionistModel;
use App\Models\SettingModel;
use App\Models\AppointmentModel;
use Config\Services;
use DateTime;

class Reception extends Controller
{
    private array $bloodTypes = [
        'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'
    ];

    private function isReceptionist(): bool
    {
        return (bool) (session()->get('isLoggedIn') && session()->get('role') === 'receptionist');
    }

    private function unauthorizedResponse(): ResponseInterface
    {
        return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
            ->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized access.',
            ]);
    }

    private function validationRules(?int $id = null): array
    {
        $uniqueContactRule = 'is_unique[patients.contact]';
        $uniqueEmailRule   = 'permit_empty|valid_email|max_length[150]|is_unique[patients.email]';

        if ($id !== null) {
            $uniqueContactRule = 'is_unique[patients.contact,id,' . $id . ']';
            $uniqueEmailRule   = 'permit_empty|valid_email|max_length[150]|is_unique[patients.email,id,' . $id . ']';
        }

        $rules = [
            'full_name'         => 'required|min_length[2]|max_length[200]',
            'gender'            => 'required|in_list[Male,Female,Other]',
            'date_of_birth'     => 'required',
            'contact'           => 'required|regex_match[/^09[0-9]{9}$/]|' . $uniqueContactRule,
            'email'             => $uniqueEmailRule,
            'address'           => 'required|max_length[100]',
            'blood_type'        => 'permit_empty|in_list[' . implode(',', $this->bloodTypes) . ']',
            'patient_type'      => 'required|in_list[outpatient,inpatient]',
            'concern'           => 'required|max_length[500]',
        ];

        // Emergency contact fields are REQUIRED for inpatients
        $patientType = $this->request->getPost('patient_type');
        if (strtolower($patientType) === 'inpatient') {
            $rules['ec_name'] = 'required|min_length[2]|max_length[100]';
            $rules['ec_contact'] = 'required|regex_match[/^09[0-9]{9}$/]';
            $rules['ec_relationship'] = 'required|min_length[2]|max_length[50]';
        }

        return $rules;
    }

    private function sanitizePostData(): array
    {
        $data = $this->request->getPost();

        // Parse full name into components
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $nameParts = explode(' ', $fullName);
        $data['first_name'] = $nameParts[0] ?? '';
        $data['middle_name'] = isset($nameParts[1]) && count($nameParts) > 2 ? $nameParts[1] : '';
        $data['last_name'] = count($nameParts) > 1 ? end($nameParts) : '';
        
        $data['full_name']         = $fullName;
        $data['gender']            = (string) ($data['gender'] ?? '');
        $data['date_of_birth']     = (string) ($data['date_of_birth'] ?? '');
        $data['contact']           = preg_replace('/\D/', '', (string) ($data['contact'] ?? ''));
        $data['email']             = trim((string) ($data['email'] ?? ''));
        $data['address']           = trim((string) ($data['address'] ?? ''));
        $data['blood_type']        = (string) ($data['blood_type'] ?? '');
        $data['patient_type']      = (string) ($data['patient_type'] ?? '');
        $data['concern']           = trim((string) ($data['concern'] ?? ''));
        
        // Map emergency contact fields from form (ec_name, ec_contact, ec_relationship) to database fields
        $data['emergency_name']    = trim((string) ($data['ec_name'] ?? $data['emergency_name'] ?? ''));
        $data['emergency_contact'] = preg_replace('/\D/', '', (string) ($data['ec_contact'] ?? $data['emergency_contact'] ?? ''));
        $data['relationship']      = trim((string) ($data['ec_relationship'] ?? $data['relationship'] ?? ''));
        
        // Insurance fields
        $data['insurance_provider']     = trim((string) ($data['insurance_provider'] ?? ''));
        $data['insurance_policy_number'] = trim((string) ($data['insurance_policy_number'] ?? ''));
        $data['insurance_member_id']    = trim((string) ($data['insurance_member_id'] ?? ''));

        $status         = strtolower((string) ($data['status'] ?? 'active'));
        $data['status'] = in_array($status, ['active', 'inactive'], true) ? $status : 'active';

        $normalizedDob = $this->normalizeDob($data['date_of_birth']);
        if ($normalizedDob !== null) {
            $data['date_of_birth'] = $normalizedDob;
        }

        return $data;
    }

    private function normalizeDob(?string $dob): ?string
    {
        $dob = trim((string) $dob);
        if ($dob === '') {
            return null;
        }

        $formats = ['m/d/Y', 'Y-m-d', 'm-d-Y', 'd/m/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dob);
            if ($date instanceof DateTime && $date->format($format) === $dob) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function buildAddress(array $data): string
    {
        return trim(implode(', ', array_filter([
            $data['address_street'] ?? '',
            $data['address_barangay'] ?? '',
            $data['address_city'] ?? '',
        ])));
    }

    private function extractPayload(array $data): array
    {
        try {
            $dob = new DateTime($data['date_of_birth']);
            $age = (int) $dob->diff(new DateTime())->y;
        } catch (Exception $e) {
            log_message('error', 'Date parsing error: ' . $e->getMessage() . ' for date: ' . $data['date_of_birth']);
            throw new \Exception('Invalid date format: ' . $data['date_of_birth']);
        }

        return [
            'first_name'        => $data['first_name'],
            'middle_name'       => $data['middle_name'] !== '' ? $data['middle_name'] : null,
            'last_name'         => $data['last_name'],
            'full_name'         => $data['full_name'],
            'gender'            => $data['gender'],
            'date_of_birth'     => $dob->format('Y-m-d'),
            'age'               => $age,
            'contact'           => $data['contact'],
            'email'             => $data['email'] !== '' ? $data['email'] : null,
            'address'           => $data['address'],
            'blood_type'        => $data['blood_type'] !== '' ? $data['blood_type'] : null,
            'patient_type'      => $data['patient_type'],
            'concern'           => $data['concern'],
            'status'            => $data['status'],
            'emergency_name'    => !empty($data['emergency_name']) ? $data['emergency_name'] : null,
            'emergency_contact' => !empty($data['emergency_contact']) ? $data['emergency_contact'] : null,
            'relationship'      => !empty($data['relationship']) ? $data['relationship'] : null,
        ];
    }

    private function generatePatientId(): string
    {
        $db = \Config\Database::connect();
        $today = date('Ymd');
        $baseId = 'PT-' . $today . '-';
        
        // Use a loop to ensure uniqueness with retry mechanism
        $maxRetries = 10;
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            // Get count of patients created today
            $count = (int) $db->table('patients')
                ->where('DATE(created_at)', date('Y-m-d'))
                ->countAllResults();
            
            // Generate patient ID
            $patientId = $baseId . str_pad((string)($count + 1), 3, '0', STR_PAD_LEFT);
            
            // Check if this ID already exists
            $exists = $db->table('patients')
                ->where('patient_id', $patientId)
                ->countAllResults() > 0;
            
            if (!$exists) {
                return $patientId;
            }
            
            // If exists, increment and try again
            $attempt++;
            usleep(100000); // Wait 0.1 seconds before retry
        }
        
        // If all retries failed, use timestamp-based ID as fallback
        return $baseId . date('His') . '-' . str_pad((string)rand(0, 99), 2, '0', STR_PAD_LEFT);
    }

    public function dashboard()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
            return redirect()->to('/login');
        }

        $receptionistProfile = null;
        $userId = session()->get('user_id');
        if ($userId) {
            $receptionistModel = new ReceptionistModel();
            $receptionistProfile = $receptionistModel->where('user_id', $userId)->first();
        }

        $patientModel = new PatientModel();
        $appointmentModel = new AppointmentModel();
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $newPatientsToday = (int) ($patientModel->builder()
            ->selectCount('id', 'total')
            ->where('DATE(created_at)', $today)
            ->get()->getRow('total') ?? 0);

        $newPatientsYesterday = (int) ($patientModel->builder()
            ->selectCount('id', 'total')
            ->where('DATE(created_at)', $yesterday)
            ->get()->getRow('total') ?? 0);

        $appointmentsToday = (int) ($appointmentModel->builder()
            ->selectCount('id', 'total')
            ->where('DATE(appointment_date)', $today)
            ->get()->getRow('total') ?? 0);

        $walkInsToday = (int) ($appointmentModel->builder()
            ->selectCount('id', 'total')
            ->where('DATE(appointment_date)', $today)
            ->where('appointment_type', 'walk-in')
            ->get()->getRow('total') ?? 0);

        $dischargedToday = (int) ($patientModel->builder()
            ->selectCount('id', 'total')
            ->where('status', 'discharged')
            ->where('DATE(updated_at)', $today)
            ->get()->getRow('total') ?? 0);

        $appointmentsByStatus = [];
        $statusRows = $appointmentModel->builder()
            ->select('status, COUNT(*) as total')
            ->where('DATE(appointment_date)', $today)
            ->groupBy('status')
            ->get()->getResultArray();
        foreach ($statusRows as $row) {
            $statusKey = strtolower($row['status'] ?? 'pending');
            $appointmentsByStatus[$statusKey] = (int) ($row['total'] ?? 0);
        }

        $upcomingAppointments = $appointmentModel->builder()
            ->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name')
            ->join('patients', 'patients.id = appointments.patient_id', 'left')
            ->join('users', 'users.id = appointments.doctor_id', 'left')
            ->where('appointments.status !=', 'cancelled')
            ->where('appointments.appointment_date >=', $today)
            ->orderBy('appointments.appointment_date', 'ASC')
            ->orderBy('appointments.appointment_time', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        $pendingConfirmations = $appointmentsByStatus['pending'] ?? 0;

        $tasks = [
            [
                'title' => 'Patient Registration',
                'description' => $newPatientsToday > 0 ? $newPatientsToday . ' new walk-ins waiting' : 'No new walk-ins',
                'status' => $newPatientsToday > 0 ? 'pending' : 'clear',
                'link' => base_url('reception/patients'),
            ],
            [
                'title' => 'Appointment Confirmations',
                'description' => $pendingConfirmations > 0 ? $pendingConfirmations . ' appointments need confirmation' : 'All appointments confirmed',
                'status' => $pendingConfirmations > 0 ? 'urgent' : 'clear',
                'link' => base_url('reception/appointments'),
            ],
        ];

        $quickActions = [
            ['label' => 'Register Patient', 'url' => base_url('reception/patients')],
            ['label' => 'Book Appointment', 'url' => base_url('reception/appointments')],
            ['label' => 'Patient Check-in', 'url' => base_url('reception/checkin')],
            ['label' => 'Process Billing', 'url' => base_url('reception/billing')],
        ];

        $metrics = [
            'newPatientsToday'  => $newPatientsToday,
            'newPatientsChange' => $newPatientsToday - $newPatientsYesterday,
            'appointmentsToday' => $appointmentsToday,
            'walkInsToday'      => $walkInsToday,
            'dischargedToday'   => $dischargedToday,
        ];

        $data = [
            'title'                => 'Reception Dashboard - HMS',
            'user_role'            => 'receptionist',
            'user_name'            => session()->get('name'),
            'user_email'           => session()->get('email'),
            'receptionistProfile'  => $receptionistProfile,
            'metrics'              => $metrics,
            'tasks'                => $tasks,
            'quickActions'         => $quickActions,
            'appointmentsByStatus' => $appointmentsByStatus,
            'upcomingAppointments' => $upcomingAppointments,
        ];

        return view('reception/dashboard', $data);
    }

    public function patients(): ResponseInterface|RedirectResponse
    {
        if (!$this->isReceptionist()) {
            return redirect()->to('/login');
        }

        $model = new PatientModel();
        $search = trim((string) $this->request->getGet('search'));

        $builder = $model->builder();
        if ($search !== '') {
            $builder
                ->groupStart()
                    ->like('patient_id', $search)
                    ->orLike('first_name', $search)
                    ->orLike('middle_name', $search)
                    ->orLike('last_name', $search)
                    ->orLike('contact', $search)
                    ->orLike('email', $search)
                ->groupEnd();
        }

        $patients = $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'       => 'Patient Registration - HMS',
            'user_role'   => 'receptionist',
            'user_name'   => session()->get('name'),
            'patients'    => $patients,
            'search'      => $search,
            'bloodTypes'  => $this->bloodTypes,
        ];

        $html = view('reception/patients', $data);

        return $this->response->setBody($html);
    }

	public function appointments(): ResponseInterface|RedirectResponse
	{
		if (!$this->isReceptionist()) {
			return redirect()->to('/login');
		}

		$appointmentModel = new \App\Models\AppointmentModel();
		$patientModel      = new \App\Models\PatientModel();
		$userModel         = new \App\Models\UserModel();
		$roomModel         = new \App\Models\RoomModel();

		$todaysAppointments   = $appointmentModel->getAppointmentsByDate(date('Y-m-d'));
		$upcomingAppointments = $appointmentModel->getUpcomingAppointments(50);
		
		// Get only OUTPATIENTS for appointment booking (inpatients have their own appointment system)
		$patients = $patientModel->where('patient_type', 'outpatient')->orderBy('id', 'DESC')->findAll();
		
		$doctors = $userModel->getDoctors();
		
		// Initial rooms - will be loaded dynamically based on patient type
		$rooms = [];

		$data = [
			'title'                 => 'Reception Appointments - HMS',
			'user_role'             => 'receptionist',
			'user_name'             => session()->get('name'),
			'appointments'          => $todaysAppointments,
			'upcoming_appointments' => $upcomingAppointments,
			'patients'              => $patients,
			'doctors'               => $doctors,
			'rooms'                 => $rooms,
		];

		$html = view('reception/appointments', $data);

		return $this->response->setBody($html);
	}

    public function store(): ResponseInterface
    {
        log_message('info', 'Store method called - Request method: ' . $this->request->getMethod());
        log_message('info', 'Raw POST data: ' . json_encode($this->request->getPost()));
        
        if (!$this->isReceptionist()) {
            return $this->unauthorizedResponse();
        }

        $data = $this->sanitizePostData();
        log_message('info', 'Sanitized data: ' . json_encode($data));
        
        $validation = Services::validation();
        $rules = $this->validationRules();

        if (!$validation->setRules($rules)->run($data)) {
            log_message('error', 'Validation failed: ' . json_encode($validation->getErrors()));
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $validation->getErrors(),
                ]);
        }

        $payload = $this->extractPayload($data);
        $payload['patient_id'] = $this->generatePatientId();

        $model = new PatientModel();

        try {
            // Log the payload for debugging
            log_message('info', 'Patient payload: ' . json_encode($payload));
            
            if (!$model->insert($payload)) {
                $errors = $model->errors();
                log_message('error', 'Model validation errors: ' . json_encode($errors));
                return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                    ->setJSON([
                        'status' => 'error',
                        'errors' => $errors,
                        'message' => 'Validation failed: ' . implode(', ', $errors)
                    ]);
            }

            // If created successfully, process additional data
            $newPatientId = (int) $model->getInsertID();
            $rawPost = $this->request->getPost();
            $db = \Config\Database::connect();
            $isInpatient = strtolower($payload['patient_type'] ?? '') === 'inpatient';
            
            // Create insurance record if insurance_provider is provided and not "none" (for both inpatients and outpatients)
            $insuranceProvider = trim((string)($rawPost['insurance_provider'] ?? ''));
            if ($newPatientId > 0 && !empty($insuranceProvider) && strtolower($insuranceProvider) !== 'none' && $db->tableExists('insurance_claims')) {
                try {
                    // Use direct database insert to bypass model validation (bill_id is required in model but not during registration)
                    $insuranceModel = new \App\Models\InsuranceModel();
                    
                    // Generate a basic claim number for reference
                    $claimNumber = $insuranceModel->generateClaimNumber();
                    
                    // Format insurance provider name
                    $providerName = ucfirst(str_replace('_', ' ', $insuranceProvider));
                    
                    // Auto-generate Policy Number if not provided
                    $policyNumber = trim((string)($rawPost['insurance_policy_number'] ?? ''));
                    if (empty($policyNumber)) {
                        $year = date('Y');
                        $month = date('m');
                        $providerCode = strtoupper(substr($providerName, 0, 3));
                        $randomNum = str_pad((string)rand(0, 9999), 4, '0', STR_PAD_LEFT);
                        $policyNumber = $providerCode . '-' . $year . $month . '-' . $randomNum;
                    }
                    
                    // Auto-generate Member ID if not provided
                    $memberId = trim((string)($rawPost['insurance_member_id'] ?? ''));
                    if (empty($memberId)) {
                        $providerCode = strtoupper(substr($providerName, 0, 2));
                        $randomNum = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
                        $memberId = $providerCode . $randomNum;
                    }
                    
                    // Create a basic insurance record (without bill_id since no bill yet)
                    // This will be updated later when bills are created
                    $insuranceData = [
                        'claim_number' => $claimNumber,
                        'bill_id' => 0, // Use 0 instead of null to satisfy validation, will be updated when bill is created
                        'patient_id' => $newPatientId,
                        'insurance_provider' => $providerName,
                        'policy_number' => $policyNumber,
                        'member_id' => $memberId,
                        'claim_amount' => 0, // Will be updated when bill is created
                        'approved_amount' => 0,
                        'deductible' => 0,
                        'co_payment' => 0,
                        'status' => 'pending', // Pending until bill is created
                        'submitted_date' => null,
                        'notes' => 'Insurance information recorded during patient registration',
                        'created_by' => (int) (session()->get('user_id') ?? 0),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    
                    // Insert directly using database builder to bypass model validation
                    $db->table('insurance_claims')->insert($insuranceData);
                } catch (\Throwable $ie) {
                    log_message('error', 'Failed creating insurance record for patient '.$newPatientId.': '.$ie->getMessage());
                }
            }
            
            // If inpatient, seed initial vitals so nurses can see it immediately
            if ($newPatientId > 0 && $isInpatient) {

                // Ensure treatment_updates table exists (minimal schema)
                if (!$db->tableExists('treatment_updates')) {
                    $db->query("CREATE TABLE IF NOT EXISTS treatment_updates (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        patient_id INT NOT NULL,
                        time DATETIME NULL,
                        blood_pressure VARCHAR(50) NULL,
                        heart_rate VARCHAR(50) NULL,
                        temperature VARCHAR(50) NULL,
                        oxygen_saturation VARCHAR(50) NULL,
                        nurse_name VARCHAR(150) NULL,
                        notes TEXT NULL,
                        created_at DATETIME NULL,
                        updated_at DATETIME NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                }

                // Map inpatient modal fields
                $admitDt = trim((string)($rawPost['admission_datetime'] ?? ''));
                $timeVal = null;
                if ($admitDt !== '') {
                    // admission_datetime is in 'Y-m-dTH:i' from datetime-local
                    $timeVal = str_replace('T', ' ', $admitDt) . ':00';
                }

                $insertData = [
                    'patient_id'        => $newPatientId,
                    'time'              => $timeVal ?: date('Y-m-d H:i:s'),
                    'blood_pressure'    => trim((string)($rawPost['vs_bp'] ?? '')) ?: null,
                    'heart_rate'        => trim((string)($rawPost['vs_hr'] ?? '')) ?: null,
                    'temperature'       => trim((string)($rawPost['vs_temperature'] ?? '')) ?: null,
                    'oxygen_saturation' => trim((string)($rawPost['vs_o2'] ?? '')) ?: null,
                    'nurse_name'        => 'System (Reception)',
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ];

                try {
                    $db->table('treatment_updates')->insert($insertData);
                } catch (\Throwable $te) {
                    log_message('error', 'Failed to seed initial vitals for patient '.$newPatientId.': '.$te->getMessage());
                }

                // Get room_number from room_id and update patient record
                $roomNumber = null;
                $admissionDate = null;
                
                $roomId = (int) ($rawPost['room_id'] ?? 0);
                if ($roomId > 0 && $db->tableExists('rooms')) {
                    try {
                        $roomBuilder = $db->table('rooms');
                        $roomBuilder->where('id', $roomId);
                        $roomResult = $roomBuilder->get()->getRowArray();
                        if ($roomResult && !empty($roomResult['room_number'])) {
                            $roomNumber = $roomResult['room_number'];
                        }
                    } catch (\Throwable $re) {
                        log_message('error', 'Failed to get room number for patient '.$newPatientId.': '.$re->getMessage());
                    }
                }
                
                // Extract admission date from admission_datetime
                if (!empty($admitDt)) {
                    $parts = explode('T', $admitDt);
                    $admissionDate = $parts[0] ?? null;
                } else {
                    // If no admission_datetime provided, use current date
                    $admissionDate = date('Y-m-d');
                }
                
                // Update patient record with room_number and admission_date
                if ($roomNumber || $admissionDate) {
                    try {
                        $updateData = [];
                        if ($roomNumber) {
                            $updateData['room_number'] = $roomNumber;
                        }
                        if ($admissionDate) {
                            $updateData['admission_date'] = $admissionDate;
                        }
                        if (!empty($updateData)) {
                            $db->table('patients')->where('id', $newPatientId)->update($updateData);
                        }
                    } catch (\Throwable $ue) {
                        log_message('error', 'Failed to update patient room/admission date for patient '.$newPatientId.': '.$ue->getMessage());
                    }
                }

                // Create an admission record in admissions table (NOT appointments table)
                try {
                    if ($db->tableExists('admissions')) {
                        $doctorId = (int) ($rawPost['attending_doctor_id'] ?? 0);
                        $apptDate = $admissionDate ?: date('Y-m-d');
                        
                        // Map admission type to case_type enum (Emergency or Regular)
                        $admissionType = !empty($rawPost['admission_type']) ? ucfirst(strtolower($rawPost['admission_type'])) : 'Emergency';
                        if (!in_array($admissionType, ['Emergency', 'Regular'])) {
                            $admissionType = 'Emergency';
                        }
                        
                        $admissionPayload = [
                            'patient_id'           => $newPatientId,
                            'doctor_id'            => $doctorId ?: 2, // Default to first doctor if none selected
                            'room_id'              => $roomId ?: null,
                            'admission_date'       => $apptDate,
                            'case_type'            => $admissionType,
                            'reason_for_admission' => $data['concern'] ?? 'Inpatient admission',
                            'notes'                => '',
                            'status'               => 'Admitted',
                            'created_by'           => (int) (session()->get('user_id') ?? 0),
                            'created_at'           => date('Y-m-d H:i:s'),
                            'updated_at'           => date('Y-m-d H:i:s'),
                        ];
                        $db->table('admissions')->insert($admissionPayload);
                    }
                } catch (\Throwable $ae) {
                    log_message('error', 'Failed creating admission record for patient '.$newPatientId.': '.$ae->getMessage());
                }
            }
            
            // Create insurance record if insurance_provider is provided and not "none" (for both inpatients and outpatients)
            $rawPost = $this->request->getPost();
            $insuranceProvider = trim((string)($rawPost['insurance_provider'] ?? ''));
            if (!empty($insuranceProvider) && strtolower($insuranceProvider) !== 'none' && $db->tableExists('insurance_claims')) {
                    try {
                        // Use direct database insert to bypass model validation (bill_id is required in model but not during registration)
                        $insuranceModel = new \App\Models\InsuranceModel();
                        
                        // Generate a basic claim number for reference
                        $claimNumber = $insuranceModel->generateClaimNumber();
                        
                        // Format insurance provider name
                        $providerName = ucfirst(str_replace('_', ' ', $insuranceProvider));
                        
                        // Auto-generate Policy Number if not provided
                        $policyNumber = trim((string)($rawPost['insurance_policy_number'] ?? ''));
                        if (empty($policyNumber)) {
                            $year = date('Y');
                            $month = date('m');
                            $providerCode = strtoupper(substr($providerName, 0, 3));
                            $randomNum = str_pad((string)rand(0, 9999), 4, '0', STR_PAD_LEFT);
                            $policyNumber = $providerCode . '-' . $year . $month . '-' . $randomNum;
                        }
                        
                        // Auto-generate Member ID if not provided
                        $memberId = trim((string)($rawPost['insurance_member_id'] ?? ''));
                        if (empty($memberId)) {
                            $providerCode = strtoupper(substr($providerName, 0, 2));
                            $randomNum = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
                            $memberId = $providerCode . $randomNum;
                        }
                        
                        // Create a basic insurance record (without bill_id since no bill yet)
                        // This will be updated later when bills are created
                        $insuranceData = [
                            'claim_number' => $claimNumber,
                            'bill_id' => 0, // Use 0 instead of null to satisfy validation, will be updated when bill is created
                            'patient_id' => $newPatientId,
                            'insurance_provider' => $providerName,
                            'policy_number' => $policyNumber,
                            'member_id' => $memberId,
                            'claim_amount' => 0, // Will be updated when bill is created
                            'approved_amount' => 0,
                            'deductible' => 0,
                            'co_payment' => 0,
                            'status' => 'pending', // Pending until bill is created
                            'submitted_date' => null,
                            'notes' => 'Insurance information recorded during patient registration',
                            'created_by' => (int) (session()->get('user_id') ?? 0),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                        
                        // Insert directly using database builder to bypass model validation
                        $db->table('insurance_claims')->insert($insuranceData);
                    } catch (\Throwable $ie) {
                        log_message('error', 'Failed creating insurance record for patient '.$newPatientId.': '.$ie->getMessage());
                    }
                }
            
            // Return success response
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Patient registered successfully',
                'patient_id' => $newPatientId,
            ]);
        } catch (DatabaseException $ex) {
            log_message('error', 'Database exception: ' . $ex->getMessage());
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Unable to save patient record: ' . $ex->getMessage(),
                    'errors'  => ['database' => $ex->getMessage()],
                ]);
        } catch (\Exception $ex) {
            log_message('error', 'General exception: ' . $ex->getMessage());
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Unexpected error: ' . $ex->getMessage(),
                ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Patient registered successfully.',
        ]);
    }

    public function show(int $id): ResponseInterface
    {
        if (!$this->isReceptionist()) {
            return $this->unauthorizedResponse();
        }

        $model = new PatientModel();
        $patient = $model->find($id);

        if (!$patient) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Patient not found.',
                ]);
        }

        // Get latest vital signs and assigned nurse
        $db = \Config\Database::connect();
        $assignedNurse = null;
        $latestVitals = null;
        
        if ($db->tableExists('treatment_updates')) {
            // Get latest treatment update with vital signs
            $builder = $db->table('treatment_updates');
            $builder->where('patient_id', $id);
            $builder->orderBy('created_at', 'DESC');
            $builder->limit(1);
            $latestUpdate = $builder->get()->getRowArray();
            
            if ($latestUpdate) {
                $assignedNurse = $latestUpdate['nurse_name'];
                $latestVitals = [
                    'time' => $latestUpdate['time'] ?? null,
                    'blood_pressure' => $latestUpdate['blood_pressure'] ?? null,
                    'heart_rate' => $latestUpdate['heart_rate'] ?? null,
                    'temperature' => $latestUpdate['temperature'] ?? null,
                    'oxygen_saturation' => $latestUpdate['oxygen_saturation'] ?? null,
                    'recorded_at' => $latestUpdate['created_at'] ?? null,
                    'nurse_name' => $latestUpdate['nurse_name'] ?? null
                ];
            }
            
            // Get all recent vital signs (last 5 records)
            $builder = $db->table('treatment_updates');
            $builder->where('patient_id', $id);
            $builder->where('(blood_pressure IS NOT NULL OR heart_rate IS NOT NULL OR temperature IS NOT NULL OR oxygen_saturation IS NOT NULL)');
            $builder->orderBy('created_at', 'DESC');
            $builder->limit(5);
            $vitalSignsHistory = $builder->get()->getResultArray();
        } else {
            $vitalSignsHistory = [];
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'patient' => $patient,
            'assigned_nurse' => $assignedNurse,
            'latest_vitals' => $latestVitals,
            'vital_signs_history' => $vitalSignsHistory
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        if (!$this->isReceptionist()) {
            return $this->unauthorizedResponse();
        }
        $model = new PatientModel();
        $existing = $model->find($id);
        if (!$existing) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Patient not found.',
                ]);
        }

        $data = $this->sanitizePostData();
        
        $validation = Services::validation();
        $rules = $this->validationRules($id);

        if (!$validation->setRules($rules)->run($data)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $validation->getErrors(),
                ]);
        }

        $payload = $this->extractPayload($data);

        try {
            if (!$model->update($id, $payload)) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                    ->setJSON([
                        'status' => 'error',
                        'errors' => $model->errors(),
                    ]);
            }
        } catch (DatabaseException $ex) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Unable to update patient record.',
                    'errors'  => ['database' => $ex->getMessage()],
                ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Patient updated successfully.',
        ]);
    }

    public function delete(int $id): ResponseInterface
    {
        if (!$this->isReceptionist()) {
            return $this->unauthorizedResponse();
        }

        $model = new PatientModel();
        $patient = $model->find($id);
        if (!$patient) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Patient not found.',
                ]);
        }

        if (!$model->delete($id)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Unable to delete patient.',
                ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Patient deleted successfully.',
        ]);
    }

	public function createAppointment()
	{
		try {
			// Always return JSON for this endpoint
			$this->response->setContentType('application/json');
			
			if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
				return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized - not logged in as receptionist']);
			}

			$appointmentModel = new \App\Models\AppointmentModel();
			$patientModel = new PatientModel();
			$roomModel = new \App\Models\RoomModel();

		$userId = session()->get('user_id');
		if (!$userId) {
			$userId = 1; // Fallback to admin user
		}
		
		$patientId = $this->request->getPost('patient_id');
		
		// Validate patient exists
		$patient = $patientModel->find($patientId);
		if (!$patient) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Patient not found',
				'errors' => ['patient_id' => 'Patient not found']
			]);
		}
		
		$patientType = $patient['patient_type'] ?? 'outpatient';
		$appointmentType = $this->request->getPost('appointment_type');
		$roomId = $this->request->getPost('room_id') ?: null;
		
		// Validate based on patient type
		if ($patientType === 'inpatient') {
			// For inpatient: room is REQUIRED, appointment_type is NOT required
			if (empty($roomId)) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Room selection is required for inpatient admission',
					'errors' => ['room_id' => 'Room is required for inpatient patients']
				]);
			}
		} else {
			// For outpatient: appointment_type is REQUIRED, room is optional
			if (empty($appointmentType)) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Appointment type is required for outpatient appointments',
					'errors' => ['appointment_type' => 'Appointment type is required']
				]);
			}
		}
		
		// Validate room if selected
		if (!empty($roomId)) {
			$room = $roomModel->find($roomId);
			
			if (!$room) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Selected room not found',
					'errors' => ['room_id' => 'Invalid room selection']
				]);
			}
			
			// Validate room type matches patient type
			if ($patientType === 'inpatient' && $room['room_type'] !== 'inpatient') {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Inpatient patients must be assigned to hospital admission rooms, not OPD clinic rooms',
					'errors' => ['room_id' => 'Invalid room type for inpatient']
				]);
			}
			
			if ($patientType === 'outpatient' && $room['room_type'] !== 'outpatient') {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Outpatient appointments can only use OPD clinic rooms, not hospital admission rooms',
					'errors' => ['room_id' => 'Invalid room type for outpatient']
				]);
			}
			
			// Check room availability
			if ($room['current_occupancy'] >= $room['capacity']) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Selected room is full. Please choose another room.',
					'errors' => ['room_id' => 'Room is full']
				]);
			}
		}
		
		$data = [
			'patient_id' => $patientId,
			'doctor_id' => $this->request->getPost('doctor_id'),
			'room_id' => $roomId,
			'appointment_date' => $this->request->getPost('appointment_date'),
			'appointment_time' => $this->request->getPost('appointment_time'),
			'appointment_type' => $patientType === 'inpatient' ? 'emergency' : ($appointmentType ?: 'consultation'), // Use 'emergency' for inpatient
			'status' => 'scheduled',
			'notes' => $this->request->getPost('notes'),
			'created_by' => $userId
		];

		// Debug logging
		log_message('info', 'Appointment data: ' . json_encode($data));

		// Check doctor availability
		log_message('info', 'Checking doctor availability...');
		$isAvailable = $appointmentModel->isDoctorAvailable($data['doctor_id'], $data['appointment_date'], $data['appointment_time']);
		log_message('info', 'Doctor available: ' . ($isAvailable ? 'Yes' : 'No'));
		
		if (!$isAvailable) {
			log_message('info', 'Doctor not available, returning error');
			
			// Check if it's a schedule issue or appointment conflict
			$scheduleModel = new \App\Models\DoctorScheduleModel();
			$hasSchedule = $scheduleModel->isDoctorAvailableOnSchedule($data['doctor_id'], $data['appointment_date'], $data['appointment_time']);
			
			$message = $hasSchedule ? 
				'Doctor already has an appointment at the selected date and time' : 
				'Doctor has not set their schedule for the selected date and time. Please ask the doctor to set their availability first.';
			
			return $this->response->setJSON([
				'status' => 'error',
				'message' => $message
			]);
		}

		log_message('info', 'Attempting to insert appointment...');
		$insertResult = $appointmentModel->insert($data);
		log_message('info', 'Insert result: ' . ($insertResult ? 'Success' : 'Failed'));
		
		if ($insertResult) {
			// Update room occupancy if room is assigned
			if (!empty($roomId)) {
				$roomModel->updateOccupancy($roomId, true);
			}
			// Always return JSON for this endpoint
			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Appointment created successfully'
			]);
		} else {
			$errors = $appointmentModel->errors();
			log_message('error', 'Failed to insert appointment. Errors: ' . json_encode($errors));
			
			// Build error message
			$errorMessage = 'Failed to create appointment';
			if (!empty($errors)) {
				$errorMessage .= ': ' . implode(', ', array_values($errors));
			}
			
			// Always return JSON for this endpoint
			return $this->response->setJSON([
				'status' => 'error',
				'message' => $errorMessage,
				'errors' => $errors
			]);
		}
		} catch (\Exception $e) {
			log_message('error', 'Exception in createAppointment: ' . $e->getMessage());
			log_message('error', 'Stack trace: ' . $e->getTraceAsString());
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'An error occurred: ' . $e->getMessage()
			]);
		}
	}

	public function checkInPatient()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		if ($this->request->getMethod() !== 'post') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
		}

		$appointmentId = $this->request->getPost('appointment_id');
		$appointmentModel = new \App\Models\AppointmentModel();
		
		$appointment = $appointmentModel->find($appointmentId);
		if (!$appointment) {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Appointment not found']);
		}

		// Update appointment status to confirmed
		if ($appointmentModel->update($appointmentId, ['status' => 'confirmed'])) {
			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Patient checked in successfully'
			]);
		} else {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Failed to check in patient'
			]);
		}
	}

	public function checkin()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Patient Check-in - HMS',
			'user_role' => 'receptionist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function billing()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Billing - HMS',
			'user_role' => 'receptionist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function schedule()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Schedule - HMS',
			'user_role' => 'receptionist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function getDoctorSchedule($doctorId = null)
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		if (!$doctorId) {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Doctor ID is required']);
		}

		$scheduleModel = new \App\Models\DoctorScheduleModel();
		$userModel = new \App\Models\UserModel();
		
		// Get doctor info
		$doctor = $userModel->find($doctorId);
		if (!$doctor || $doctor['role'] !== 'doctor') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Doctor not found']);
		}

		// Get selected date if provided
		$selectedDate = $this->request->getGet('date');
		
		// Get doctor's weekly schedule (for recurring schedules)
		$weeklySchedule = $scheduleModel->getDoctorWeeklySchedule($doctorId);
		
		// Get date-specific schedules for current month if date is provided
		$dateSpecificSchedules = [];
		if ($selectedDate) {
			$monthStart = date('Y-m-01', strtotime($selectedDate));
			$monthEnd = date('Y-m-t', strtotime($selectedDate));
			$dateSpecificSchedules = $scheduleModel
				->where('doctor_id', $doctorId)
				->where('schedule_date >=', $monthStart)
				->where('schedule_date <=', $monthEnd)
				->findAll();
		}
		
		// Format schedule by day (weekly recurring)
		$scheduleByDay = [
			'monday' => [],
			'tuesday' => [],
			'wednesday' => [],
			'thursday' => [],
			'friday' => [],
			'saturday' => [],
			'sunday' => []
		];

		foreach ($weeklySchedule as $schedule) {
			// Only include weekly schedules (no schedule_date)
			if (empty($schedule['schedule_date'])) {
				$scheduleByDay[$schedule['day_of_week']][] = [
					'start_time' => date('g:i A', strtotime($schedule['start_time'])),
					'end_time' => date('g:i A', strtotime($schedule['end_time'])),
					'is_available' => (bool) $schedule['is_available'],
					'notes' => $schedule['notes'] ?? ''
				];
			}
		}
		
		// Format date-specific schedules
		$scheduleByDate = [];
		foreach ($dateSpecificSchedules as $schedule) {
			$date = $schedule['schedule_date'];
			$scheduleByDate[$date][] = [
				'start_time' => date('g:i A', strtotime($schedule['start_time'])),
				'end_time' => date('g:i A', strtotime($schedule['end_time'])),
				'is_available' => (bool) $schedule['is_available'],
				'notes' => $schedule['notes'] ?? ''
			];
		}
		
		// Get availability for selected date
		$selectedDateAvailability = null;
		if ($selectedDate) {
			$dayOfWeek = strtolower(date('l', strtotime($selectedDate)));
			// Check date-specific first
			if (isset($scheduleByDate[$selectedDate])) {
				$selectedDateAvailability = $scheduleByDate[$selectedDate];
			} elseif (isset($scheduleByDay[$dayOfWeek])) {
				$selectedDateAvailability = $scheduleByDay[$dayOfWeek];
			}
		}

		return $this->response->setJSON([
			'status' => 'success',
			'doctor' => [
				'id' => $doctor['id'],
				'name' => $doctor['name'],
				'email' => $doctor['email']
			],
			'schedule' => $scheduleByDay,
			'scheduleByDate' => $scheduleByDate,
			'selectedDateAvailability' => $selectedDateAvailability,
			'selectedDate' => $selectedDate
		]);
	}

	public function getDoctorUnavailableDates($doctorId = null)
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		if (!$doctorId) {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Doctor ID is required']);
		}

		$scheduleModel = new \App\Models\DoctorScheduleModel();
		$userModel = new \App\Models\UserModel();
		
		// Get doctor info
		$doctor = $userModel->find($doctorId);
		if (!$doctor || $doctor['role'] !== 'doctor') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Doctor not found']);
		}

		// Get doctor's weekly schedule
		$weeklySchedule = $scheduleModel->getDoctorWeeklySchedule($doctorId);
		
		// Get unavailable days of the week
		$unavailableDays = [];
		$availableDays = [];
		
		foreach ($weeklySchedule as $schedule) {
			if (!(bool) $schedule['is_available']) {
				$unavailableDays[] = $schedule['day_of_week'];
			} else {
				$availableDays[] = $schedule['day_of_week'];
			}
		}

		// Convert day names to numbers (0=Sunday, 1=Monday, etc.)
		$dayMapping = [
			'sunday' => 0,
			'monday' => 1,
			'tuesday' => 2,
			'wednesday' => 3,
			'thursday' => 4,
			'friday' => 5,
			'saturday' => 6
		];

		$unavailableDayNumbers = [];
		foreach ($unavailableDays as $day) {
			if (isset($dayMapping[$day])) {
				$unavailableDayNumbers[] = $dayMapping[$day];
			}
		}

		// Also check for days with no schedule set
		$allDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
		$scheduledDays = array_column($weeklySchedule, 'day_of_week');
		$unscheduledDays = array_diff($allDays, $scheduledDays);
		
		foreach ($unscheduledDays as $day) {
			if (isset($dayMapping[$day])) {
				$unavailableDayNumbers[] = $dayMapping[$day];
			}
		}

		return $this->response->setJSON([
			'status' => 'success',
			'doctor' => [
				'id' => $doctor['id'],
				'name' => $doctor['name']
			],
			'unavailable_days' => array_unique($unavailableDayNumbers)
		]);
	}

	public function getRoomsByType()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		$type = $this->request->getGet('type') ?? 'outpatient';
		$appointmentType = $this->request->getGet('appointment_type') ?? null;
		
		// Validate type
		if (!in_array($type, ['outpatient', 'inpatient'])) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Invalid room type. Must be "outpatient" or "inpatient"'
			]);
		}

		$roomModel = new \App\Models\RoomModel();
		
		// If appointment type is provided, filter by it
		if ($appointmentType && $type === 'outpatient') {
			$rooms = $roomModel->getRoomsByAppointmentType($appointmentType, $type);
		} else if ($type === 'outpatient') {
			$rooms = $roomModel->getOutpatientRooms();
		} else {
			$rooms = $roomModel->getInpatientRooms();
		}

		return $this->response->setJSON([
			'status' => 'success',
			'rooms' => $rooms
		]);
	}

	public function getDoctors()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		$userModel = new \App\Models\UserModel();
		$allDoctors = $userModel->getDoctors();
		
		// Get optional date/time filter
		$admissionDateTime = $this->request->getGet('admission_datetime');
		
		// If no date/time provided, return all doctors
		if (empty($admissionDateTime)) {
			return $this->response->setJSON([
				'status'  => 'success',
				'doctors' => array_map(static function ($d) {
					return [
						'id'   => $d['id'] ?? null,
						'name' => $d['name'] ?? ($d['email'] ?? 'Doctor')
					];
				}, $allDoctors ?? [])
			]);
		}
		
		// Parse admission datetime
		try {
			// Handle both "YYYY-MM-DD HH:MM:SS" and "YYYY-MM-DD HH:MM" formats
			if (strpos($admissionDateTime, ' ') !== false) {
				$parts = explode(' ', $admissionDateTime);
				$admissionDate = $parts[0];
				$admissionTime = isset($parts[1]) ? $parts[1] : '00:00:00';
				if (strlen($admissionTime) == 5) {
					$admissionTime .= ':00'; // Add seconds if missing
				}
			} else {
				$admissionDate = date('Y-m-d', strtotime($admissionDateTime));
				$admissionTime = date('H:i:s', strtotime($admissionDateTime));
			}
			$dayOfWeek = strtolower(date('l', strtotime($admissionDate)));
			
			// Debug logging
			log_message('debug', 'Admission datetime: ' . $admissionDateTime);
			log_message('debug', 'Parsed date: ' . $admissionDate . ', time: ' . $admissionTime . ', day: ' . $dayOfWeek);
		} catch (\Exception $e) {
			// If date parsing fails, return all doctors
			return $this->response->setJSON([
				'status'  => 'success',
				'doctors' => array_map(static function ($d) {
					return [
						'id'   => $d['id'] ?? null,
						'name' => $d['name'] ?? ($d['email'] ?? 'Doctor')
					];
				}, $allDoctors ?? [])
			]);
		}
		
		// Filter doctors by availability
		$scheduleModel = new \App\Models\DoctorScheduleModel();
		$availableDoctors = [];
		
		foreach ($allDoctors as $doctor) {
			$doctorId = $doctor['id'] ?? null;
			if (!$doctorId) continue;
			
			$isAvailable = false;
			
			// Check for date-specific schedule first
			$dateSpecificSchedules = $scheduleModel
				->where('doctor_id', $doctorId)
				->where('schedule_date', $admissionDate)
				->where('is_available', true)
				->findAll();
			
			foreach ($dateSpecificSchedules as $schedule) {
				$startTime = $schedule['start_time'];
				$endTime = $schedule['end_time'];
				
				// Handle overnight schedules (end_time < start_time means it spans midnight)
				if ($endTime < $startTime) {
					// Overnight schedule: time is valid if >= start_time OR <= end_time
					if ($admissionTime >= $startTime || $admissionTime <= $endTime) {
						$isAvailable = true;
						break;
					}
				} else {
					// Normal schedule: time must be between start and end
					if ($admissionTime >= $startTime && $admissionTime <= $endTime) {
						$isAvailable = true;
						break;
					}
				}
			}
			
			if ($isAvailable) {
				$availableDoctors[] = [
					'id'   => $doctorId,
					'name' => $doctor['name'] ?? ($doctor['email'] ?? 'Doctor')
				];
				continue;
			}
			
			// Check for recurring weekly schedule (schedule_date is NULL)
			$recurringSchedules = $scheduleModel
				->where('doctor_id', $doctorId)
				->where('day_of_week', $dayOfWeek)
				->where('schedule_date IS NULL', null, false)
				->where('is_available', true)
				->findAll();
			
			log_message('debug', 'Doctor ' . $doctorId . ' has ' . count($recurringSchedules) . ' recurring schedules for ' . $dayOfWeek);
			
			foreach ($recurringSchedules as $schedule) {
				$startTime = $schedule['start_time'];
				$endTime = $schedule['end_time'];
				
				log_message('debug', 'Checking schedule: start=' . $startTime . ', end=' . $endTime . ', admission=' . $admissionTime);
				
				// Handle overnight schedules (end_time < start_time means it spans midnight)
				if ($endTime < $startTime) {
					// Overnight schedule: time is valid if >= start_time OR <= end_time
					$timeCheck = ($admissionTime >= $startTime || $admissionTime <= $endTime);
					log_message('debug', 'Overnight schedule check: ' . ($timeCheck ? 'PASS' : 'FAIL'));
					if ($timeCheck) {
						$isAvailable = true;
						break;
					}
				} else {
					// Normal schedule: time must be between start and end
					$timeCheck = ($admissionTime >= $startTime && $admissionTime <= $endTime);
					log_message('debug', 'Normal schedule check: ' . ($timeCheck ? 'PASS' : 'FAIL'));
					if ($timeCheck) {
						$isAvailable = true;
						break;
					}
				}
			}
			
			if ($isAvailable) {
				$availableDoctors[] = [
					'id'   => $doctorId,
					'name' => $doctor['name'] ?? ($doctor['email'] ?? 'Doctor')
				];
			}
		}

		return $this->response->setJSON([
			'status'  => 'success',
			'doctors' => $availableDoctors
		]);
	}

	public function reports(): ResponseInterface|RedirectResponse
	{
		if (!$this->isReceptionist()) {
			return redirect()->to('/login');
		}

		$patientModel = new PatientModel();
		$appointmentModel = new \App\Models\AppointmentModel();
		
		// Get filters
		$reportType = $this->request->getGet('type') ?? 'patients';
		$dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
		$dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

		$data = [
			'title' => 'Reception Reports - HMS',
			'user_role' => 'receptionist',
			'user_name' => session()->get('name'),
			'report_type' => $reportType,
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'new_patients' => [],
			'appointments' => [],
			'summary' => [
				'total_new_patients' => 0,
				'total_appointments' => 0,
				'total_checkins' => 0,
				'appointments_by_status' => [],
			]
		];

		try {
			// New Patients Report
			$newPatients = $patientModel
				->where('DATE(created_at) >=', $dateFrom)
				->where('DATE(created_at) <=', $dateTo)
				->orderBy('created_at', 'DESC')
				->findAll();
			
			$data['new_patients'] = $newPatients;
			$data['summary']['total_new_patients'] = count($newPatients);

			// Appointments Report
			$appointments = $appointmentModel
				->select('appointments.*, patients.full_name as patient_name, patients.patient_id as patient_code, users.name as doctor_name')
				->join('patients', 'patients.id = appointments.patient_id', 'left')
				->join('users', 'users.id = appointments.doctor_id', 'left')
				->where('DATE(appointments.appointment_date) >=', $dateFrom)
				->where('DATE(appointments.appointment_date) <=', $dateTo)
				->orderBy('appointments.appointment_date', 'DESC')
				->orderBy('appointments.appointment_time', 'DESC')
				->findAll();
			
			$data['appointments'] = $appointments;
			$data['summary']['total_appointments'] = count($appointments);
			
			// Appointments by Status
			$statusCounts = [];
			foreach ($appointments as $apt) {
				$status = $apt['status'] ?? 'pending';
				$statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
			}
			$data['summary']['appointments_by_status'] = $statusCounts;

			// Check-ins (appointments with check-in status or completed)
			$checkins = array_filter($appointments, function($apt) {
				return in_array($apt['status'] ?? '', ['checked_in', 'completed']);
			});
			$data['summary']['total_checkins'] = count($checkins);

		} catch (\Exception $e) {
			log_message('error', 'Error fetching reception reports: ' . $e->getMessage());
		}

		$html = view('reception/reports', $data);
		return $this->response->setBody($html);
	}

    public function settings()
    {
        if (!$this->isReceptionist()) {
            return redirect()->to('/login');
        }

        $model = new SettingModel();
        $defaults = [
            'reception_queue_alert'        => '20',
            'reception_auto_assign_room'   => '1',
            'reception_default_room'       => 'OPD-1',
            'reception_notification_email' => session()->get('email') ?? 'reception@hospital.local',
            'reception_checkin_message'    => "Welcome to MediCare Hospital!\nPlease prepare your ID and appointment slip.",
        ];
        $settings = array_merge($defaults, $model->getAllAsMap());

        $data = [
            'title'     => 'Reception Settings - HMS',
            'user_role' => 'receptionist',
            'user_name' => session()->get('name'),
            'pageTitle' => 'Settings',
            'settings'  => $settings,
        ];

        return view('reception/settings', $data);
    }

    public function saveSettings()
    {
        if (!$this->isReceptionist()) {
            return redirect()->to('/login');
        }

        $model = new SettingModel();
        $post = $this->request->getPost();
        $keys = [
            'reception_queue_alert',
            'reception_auto_assign_room',
            'reception_default_room',
            'reception_notification_email',
            'reception_checkin_message',
        ];

        foreach ($keys as $key) {
            $model->setValue($key, (string)($post[$key] ?? ''), 'reception');
        }

        return redirect()->to('/reception/settings')->with('success', 'Settings saved successfully.');
    }
}

