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
            'first_name'        => 'required|min_length[2]|max_length[100]',
            'middle_name'       => 'permit_empty|max_length[100]',
            'last_name'         => 'required|min_length[2]|max_length[100]',
            'gender'            => 'required|in_list[Male,Female,Other]',
            'date_of_birth'     => 'required|valid_date[Y-m-d]',
            'contact'           => 'required|regex_match[/^09[0-9]{9}$/]|' . $uniqueContactRule,
            'email'             => $uniqueEmailRule,
            'address_city'      => 'required|max_length[100]',
            'address_barangay'  => 'required|max_length[100]',
            'address_street'    => 'required|max_length[150]',
            'blood_type'        => 'permit_empty|in_list[' . implode(',', $this->bloodTypes) . ']',
            'allergies'         => 'permit_empty|max_length[500]',
            'emergency_name'    => 'required|min_length[2]|max_length[100]',
            'emergency_contact' => 'required|regex_match[/^09[0-9]{9}$/]',
            'relationship'      => 'required|max_length[50]',
            'status'            => 'permit_empty|in_list[active,inactive]',
        ];
    }

    private function sanitizePostData(): array
    {
        $data = $this->request->getPost();

        $data['first_name']        = trim((string) ($data['first_name'] ?? ''));
        $data['middle_name']       = trim((string) ($data['middle_name'] ?? ''));
        $data['last_name']         = trim((string) ($data['last_name'] ?? ''));
        $data['gender']            = (string) ($data['gender'] ?? '');
        $data['date_of_birth']     = (string) ($data['date_of_birth'] ?? '');
        $data['contact']           = preg_replace('/\D/', '', (string) ($data['contact'] ?? ''));
        $data['email']             = trim((string) ($data['email'] ?? ''));
        $data['address_city']      = trim((string) ($data['address_city'] ?? ''));
        $data['address_barangay']  = trim((string) ($data['address_barangay'] ?? ''));
        $data['address_street']    = trim((string) ($data['address_street'] ?? ''));
        $data['blood_type']        = (string) ($data['blood_type'] ?? '');
        $data['allergies']         = trim((string) ($data['allergies'] ?? ''));
        $data['emergency_name']    = trim((string) ($data['emergency_name'] ?? ''));
        $data['emergency_contact'] = preg_replace('/\D/', '', (string) ($data['emergency_contact'] ?? ''));
        $data['relationship']      = trim((string) ($data['relationship'] ?? ''));

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

        $formats = ['Y-m-d', 'm/d/Y', 'm-d-Y', 'd/m/Y', 'd-m-Y'];

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
        $dob = new DateTime($data['date_of_birth']);
        $age = (int) $dob->diff(new DateTime())->y;

        return [
            'first_name'        => $data['first_name'],
            'middle_name'       => $data['middle_name'] !== '' ? $data['middle_name'] : null,
            'last_name'         => $data['last_name'],
            'full_name'         => trim(implode(' ', array_filter([
                $data['first_name'],
                $data['middle_name'],
                $data['last_name'],
            ]))),
            'gender'            => $data['gender'],
            'date_of_birth'     => $dob->format('Y-m-d'),
            'age'               => $age,
            'contact'           => $data['contact'],
            'email'             => $data['email'] !== '' ? $data['email'] : null,
            'address_city'      => $data['address_city'],
            'address_barangay'  => $data['address_barangay'],
            'address_street'    => $data['address_street'],
            'address'           => $this->buildAddress($data),
            'blood_type'        => $data['blood_type'] !== '' ? $data['blood_type'] : null,
            'allergies'         => $data['allergies'] !== '' ? $data['allergies'] : null,
            'emergency_name'    => $data['emergency_name'],
            'emergency_contact' => $data['emergency_contact'],
            'relationship'      => $data['relationship'],
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

        $todaysAppointments   = $appointmentModel->getAppointmentsByDate(date('Y-m-d'));
        $upcomingAppointments = $appointmentModel->getUpcomingAppointments(50);
        $patients             = $patientModel->orderBy('id', 'DESC')->findAll();
        $doctors              = $userModel->getDoctors();

        $data = [
            'title'                 => 'Reception Appointments - HMS',
            'user_role'             => 'receptionist',
            'user_name'             => session()->get('name'),
            'appointments'          => $todaysAppointments,
            'upcoming_appointments' => $upcomingAppointments,
            'patients'              => $patients,
            'doctors'               => $doctors,
        ];

        $html = view('reception/appointments', $data);

        return $this->response->setBody($html);
    }

    public function store(): ResponseInterface
    {
        if (!$this->isReceptionist()) {
            return $this->unauthorizedResponse();
        }

        $validation = Services::validation();
        $rules = $this->validationRules();

        if (!$validation->setRules($rules)->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $validation->getErrors(),
                ]);
        }

        $payload = $this->extractPayload();
        $payload['patient_id'] = $this->generatePatientId();

        $model = new PatientModel();

        try {
            if (!$model->insert($payload)) {
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
                    'message' => 'Unable to save patient record.',
                    'errors'  => ['database' => $ex->getMessage()],
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

        return $this->response->setJSON([
            'status'  => 'success',
            'patient' => $patient,
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

        $validation = Services::validation();
        $rules = $this->validationRules($id);

        if (!$validation->setRules($rules)->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $validation->getErrors(),
                ]);
        }

        $payload = $this->extractPayload();

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
}




