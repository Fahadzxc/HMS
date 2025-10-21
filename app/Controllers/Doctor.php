<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DoctorScheduleModel;
use App\Models\AppointmentModel;

class Doctor extends Controller
{
	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Doctor Dashboard - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function schedule()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$scheduleModel = new DoctorScheduleModel();
		$doctorId = session()->get('user_id');
		
		// Get doctor's current schedule
		$weeklySchedule = $scheduleModel->getDoctorWeeklySchedule($doctorId);
		
		$data = [
			'title' => 'My Schedule - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'schedule' => $weeklySchedule
		];

		return view('doctor/schedule', $data);
	}

	public function appointments()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$appointmentModel = new \App\Models\AppointmentModel();
		$doctorId = session()->get('user_id');
		
		// Get doctor's appointments
		$todaysAppointments = $appointmentModel->getAppointmentsByDoctor($doctorId, date('Y-m-d'));
		$upcomingAppointments = $appointmentModel->getUpcomingAppointmentsByDoctor($doctorId);
		
		$data = [
			'title' => 'My Appointments - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'appointments' => $todaysAppointments,
			'upcoming_appointments' => $upcomingAppointments
		];

		return view('doctor/appointments', $data);
	}

	public function updateSchedule()
	{
		// Debug logging
		log_message('info', 'Doctor updateSchedule called. Session: ' . json_encode([
			'isLoggedIn' => session()->get('isLoggedIn'),
			'role' => session()->get('role'),
			'user_id' => session()->get('user_id')
		]));

		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		$method = strtolower($this->request->getMethod());
		log_message('info', 'Request method: ' . $method);
		
		if ($method !== 'post') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method. Received: ' . $method]);
		}

		$scheduleModel = new DoctorScheduleModel();
		$doctorId = session()->get('user_id');

		$data = [
			'doctor_id' => $doctorId,
			'day_of_week' => $this->request->getPost('day_of_week'),
			'start_time' => $this->request->getPost('start_time'),
			'end_time' => $this->request->getPost('end_time'),
			'is_available' => $this->request->getPost('is_available') ? true : false,
			'notes' => $this->request->getPost('notes')
		];

		// Debug logging
		log_message('info', 'Schedule data: ' . json_encode($data));

		// Check if schedule already exists for this day
		$existingSchedule = $scheduleModel->where('doctor_id', $doctorId)
										 ->where('day_of_week', $data['day_of_week'])
										 ->first();

		if ($existingSchedule) {
			// Update existing schedule
			if ($scheduleModel->update($existingSchedule['id'], $data)) {
				return $this->response->setJSON([
					'status' => 'success',
					'message' => 'Schedule updated successfully'
				]);
			}
		} else {
			// Create new schedule
			if ($scheduleModel->insert($data)) {
				return $this->response->setJSON([
					'status' => 'success',
					'message' => 'Schedule created successfully'
				]);
			}
		}

		return $this->response->setJSON([
			'status' => 'error',
			'message' => 'Failed to save schedule',
			'errors' => $scheduleModel->errors()
		]);
	}

	public function getAvailableSlots()
	{
		if (!session()->get('isLoggedIn')) {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		$doctorId = $this->request->getGet('doctor_id');
		$date = $this->request->getGet('date');

		if (!$doctorId || !$date) {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Missing required parameters']);
		}

		$scheduleModel = new DoctorScheduleModel();
		$appointmentModel = new AppointmentModel();

		// Get available time slots from schedule
		$availableSlots = $scheduleModel->getAvailableTimeSlots($doctorId, $date);
		
		// Remove slots that are already booked
		$bookedAppointments = $appointmentModel->where('doctor_id', $doctorId)
											  ->where('appointment_date', $date)
											  ->where('status !=', 'cancelled')
											  ->findAll();

		$bookedTimes = array_column($bookedAppointments, 'appointment_time');
		$freeSlots = array_diff($availableSlots, $bookedTimes);

		return $this->response->setJSON([
			'status' => 'success',
			'slots' => array_values($freeSlots)
		]);
	}
}




