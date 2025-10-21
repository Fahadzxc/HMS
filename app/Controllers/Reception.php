<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PatientModel;
use App\Models\RoomModel;
use DateTime;

class Reception extends Controller
{
	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Reception Dashboard - HMS',
			'user_role' => 'receptionist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function patients()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return redirect()->to('/login');
		}

		$patientModel = new PatientModel();
		$roomModel = new RoomModel();
		$patients = $patientModel->findAll();
		$inpatientRooms = $roomModel->getInpatientRooms();
		$outpatientRooms = $roomModel->getOutpatientRooms();

		$data = [
			'title' => 'Patient Registration - HMS',
			'user_role' => 'receptionist',
			'user_name' => session()->get('name'),
			'patients' => $patients,
			'inpatient_rooms' => $inpatientRooms,
			'outpatient_rooms' => $outpatientRooms
		];

		return view('reception/patients', $data);
	}

	public function createPatient()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return redirect()->to('/login');
		}

		// Get all POST data
		$postData = $this->request->getPost();
		log_message('info', 'Received POST data: ' . json_encode($postData));

		// Clean contact number (remove spaces)
		$contact = str_replace(' ', '', $this->request->getPost('contact'));
		
		// Basic validation
		if (empty($this->request->getPost('full_name'))) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['full_name' => 'Full name is required']
			]);
		}

		if (empty($this->request->getPost('gender'))) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['gender' => 'Gender is required']
			]);
		}

		if (empty($this->request->getPost('address'))) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['address' => 'Address is required']
			]);
		}

		if (empty($this->request->getPost('concern'))) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['concern' => 'Medical concern is required']
			]);
		}

		if (empty($this->request->getPost('patient_type'))) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['patient_type' => 'Patient type is required']
			]);
		}

		// Validate room assignment based on patient type
		$patientType = $this->request->getPost('patient_type');
		if ($patientType === 'inpatient' && empty($this->request->getPost('room_number'))) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['room_number' => 'Inpatient room is required for inpatients']
			]);
		}
		
		if ($patientType === 'outpatient' && empty($this->request->getPost('doctor_room'))) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['doctor_room' => 'Doctor room is required for outpatients']
			]);
		}

		// Validate contact separately
		if (!preg_match('/^09[0-9]{9}$/', $contact)) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['contact' => 'Contact number must be 11 digits starting with 09']
			]);
		}

		// Custom date validation
		$dateOfBirth = $this->request->getPost('date_of_birth');
		try {
			// Handle MM/DD/YYYY format
			if (strpos($dateOfBirth, '/') !== false) {
				$parts = explode('/', $dateOfBirth);
				if (count($parts) === 3) {
					$dateOfBirth = $parts[2] . '-' . $parts[0] . '-' . $parts[1]; // Convert to YYYY-MM-DD
				}
			}
			
			$birthDate = new DateTime($dateOfBirth);
			$today = new DateTime();
			
			if ($birthDate > $today) {
				return $this->response->setJSON([
					'status' => 'error',
					'errors' => ['date_of_birth' => 'Date of birth cannot be in the future']
				]);
			}
		} catch (Exception $e) {
			return $this->response->setJSON([
				'status' => 'error',
				'errors' => ['date_of_birth' => 'Invalid date format']
			]);
		}

		$patientModel = new PatientModel();
		
		$data = [
			'full_name' => $this->request->getPost('full_name'),
			'date_of_birth' => $dateOfBirth,
			'gender' => $this->request->getPost('gender'),
			'blood_type' => $this->request->getPost('blood_type') ?: 'O+',
			'contact' => $contact,
			'email' => $this->request->getPost('email') ?: '',
			'address' => $this->request->getPost('address'),
			'concern' => $this->request->getPost('concern'),
			'patient_type' => $this->request->getPost('patient_type'),
			'room_number' => $this->request->getPost('room_number') ?: $this->request->getPost('doctor_room'),
			'status' => 'active',
			'created_at' => date('Y-m-d H:i:s')
		];

		// Set admission date for inpatients
		if ($patientType === 'inpatient') {
			$data['admission_date'] = date('Y-m-d');
		}

		// Debug: Log the data being inserted
		log_message('info', 'Attempting to insert patient: ' . json_encode($data));
		
		if ($patientModel->insert($data)) {
			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Patient registered successfully'
			]);
		} else {
			// Get detailed error information
			$errors = $patientModel->errors();
			log_message('error', 'Patient insert failed: ' . json_encode($errors));
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Failed to register patient',
				'errors' => $errors,
				'debug_data' => $data
			]);
		}
	}

	public function appointments()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
			return redirect()->to('/login');
		}

		// Load models
		$appointmentModel = new \App\Models\AppointmentModel();
		$patientModel = new PatientModel();
		$userModel = new \App\Models\UserModel();

		// Get today's appointments and upcoming appointments for reception desk
		$todaysAppointments = $appointmentModel->getAppointmentsByDate(date('Y-m-d'));
		$upcomingAppointments = $appointmentModel->getUpcomingAppointments(50);
		
		// Get patients and doctors for creating new appointments
		$patients = $patientModel->findAll();
		$doctors = $userModel->getDoctors();

		$data = [
			'title' => 'Appointments Management - HMS',
			'user_role' => 'receptionist',
			'user_name' => session()->get('name'),
			'appointments' => $todaysAppointments,
			'upcoming_appointments' => $upcomingAppointments,
			'patients' => $patients,
			'doctors' => $doctors
		];

		return view('reception/appointments', $data);
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




