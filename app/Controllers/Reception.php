<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PatientModel;
use App\Models\ReceptionistModel;
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

        return [
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
        ];
    }

    private function generatePatientId(): string
    {
        $today = date('Y-m-d');
        $model = new PatientModel();

        $count = $model->where('DATE(created_at)', $today)->countAllResults();

        return sprintf('PT-%s-%03d', date('Ymd'), $count + 1);
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

        $data = [
            'title'                => 'Reception Dashboard - HMS',
            'user_role'            => 'receptionist',
            'user_name'            => session()->get('name'),
            'user_email'           => session()->get('email'),
            'receptionistProfile'  => $receptionistProfile,
        ];

        return view('auth/dashboard', $data);
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
		$patients             = $patientModel->orderBy('id', 'DESC')->findAll();
		$doctors              = $userModel->getDoctors();
		$rooms                = $roomModel->getAvailableRooms();

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
		// Debug logging
		$sessionData = [
			'isLoggedIn' => session()->get('isLoggedIn'),
			'role' => session()->get('role'),
			'user_id' => session()->get('user_id'),
			'name' => session()->get('name'),
			'all_session' => session()->get()
		];
		log_message('info', 'CreateAppointment called. Session data: ' . json_encode($sessionData));

		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized - not logged in as receptionist']);
		}

		$method = strtolower($this->request->getMethod());
		log_message('info', 'Request method: ' . $method);
		
		// Temporarily disable method check for debugging
		// if ($method !== 'post') {
		// 	return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method. Received: ' . $method]);
		// }

		$appointmentModel = new \App\Models\AppointmentModel();

		$userId = session()->get('user_id');
		if (!$userId) {
			log_message('warning', 'No user_id in session, using default value 1');
			$userId = 1; // Fallback to admin user
		}
		
		$data = [
			'patient_id' => $this->request->getPost('patient_id'),
			'doctor_id' => $this->request->getPost('doctor_id'),
			'room_id' => $this->request->getPost('room_id') ?: null,
			'appointment_date' => $this->request->getPost('appointment_date'),
			'appointment_time' => $this->request->getPost('appointment_time'),
			'appointment_type' => $this->request->getPost('appointment_type'),
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
			// Check if this is an AJAX request
			if ($this->request->isAJAX()) {
				return $this->response->setJSON([
					'status' => 'success',
					'message' => 'Appointment created successfully'
				]);
			} else {
				// Regular form submission - redirect with success message
				return redirect()->to('reception/appointments')->with('success', 'Appointment created successfully');
			}
		} else {
			$errors = $appointmentModel->errors();
			log_message('error', 'Failed to insert appointment. Errors: ' . json_encode($errors));
			
			// Check if this is an AJAX request
			if ($this->request->isAJAX()) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Failed to create appointment',
					'errors' => $errors
				]);
			} else {
				// Regular form submission - redirect with error message
				return redirect()->to('reception/appointments')->with('error', 'Failed to create appointment: ' . implode(', ', $errors));
			}
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
}




