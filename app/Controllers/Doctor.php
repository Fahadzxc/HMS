<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DoctorScheduleModel;
use App\Models\AppointmentModel;
use App\Models\PrescriptionModel;
use App\Models\PatientModel;
use App\Models\MedicationModel;
use App\Models\LabTestRequestModel;
use App\Models\LabTestResultModel;
use App\Models\SettingModel;

class Doctor extends Controller
{
	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$doctorId = session()->get('user_id');
		$appointmentModel = new AppointmentModel();
		$patientModel = new \App\Models\PatientModel();
		$db = \Config\Database::connect();

		// Get today's appointments (exclude lab test appointments)
		$today = date('Y-m-d');
		$todayAppointments = $appointmentModel
			->where('doctor_id', $doctorId)
			->where('appointment_date', $today)
			->where('status !=', 'cancelled')
			->where('appointment_type !=', 'laboratory_test') // Exclude lab tests
			->where('doctor_id IS NOT NULL', null, false) // Ensure doctor_id is not null
			->orderBy('appointment_time', 'ASC')
			->findAll();

		// Format appointments for display
		$appointments = [];
		foreach ($todayAppointments as $apt) {
			$patient = $patientModel->find($apt['patient_id']);
			$appointments[] = [
				'id' => $apt['id'],
				'patient_name' => $patient['full_name'] ?? 'N/A',
				'type' => $apt['appointment_type'] ?? 'Consultation',
				'appointment_time' => $apt['appointment_time'] ?? null,
				'status' => $apt['status'] ?? 'upcoming',
			];
		}

		// Get total patients (unique patients seen by this doctor)
		$totalPatients = $db->query("
			SELECT COUNT(DISTINCT patient_id) as total 
			FROM appointments 
			WHERE doctor_id = ? AND status != 'cancelled'
		", [$doctorId])->getRow()->total ?? 0;

		// Get pending reports (prescriptions with pending status)
		$pendingReports = $db->table('prescriptions')
			->where('doctor_id', $doctorId)
			->where('status', 'pending')
			->countAllResults();

		// Get revenue this month (from bills)
		$monthStart = date('Y-m-01');
		$monthRevenue = $db->table('bills')
			->selectSum('total_amount')
			->where('created_at >=', $monthStart)
			->get()
			->getRow()->total_amount ?? 0;

		// Count today's appointments
		$todayAppointmentsCount = count($todayAppointments);

		// Get recent appointments (past week, excluding today) - exclude lab tests
		$weekAgo = date('Y-m-d', strtotime('-7 days'));
		$recentAppointmentsRaw = $appointmentModel
			->where('doctor_id', $doctorId)
			->where('appointment_date <', $today)
			->where('appointment_date >=', $weekAgo)
			->where('status !=', 'cancelled')
			->where('appointment_type !=', 'laboratory_test') // Exclude lab tests
			->where('doctor_id IS NOT NULL', null, false) // Ensure doctor_id is not null
			->orderBy('appointment_date', 'DESC')
			->orderBy('appointment_time', 'DESC')
			->limit(10)
			->findAll();

		// Format recent appointments for display
		$recentAppointments = [];
		foreach ($recentAppointmentsRaw as $apt) {
			$patient = $patientModel->find($apt['patient_id']);
			$recentAppointments[] = [
				'id' => $apt['id'],
				'patient_name' => $patient['full_name'] ?? 'N/A',
				'type' => $apt['appointment_type'] ?? 'Consultation',
				'appointment_date' => $apt['appointment_date'] ?? null,
				'appointment_time' => $apt['appointment_time'] ?? null,
				'status' => $apt['status'] ?? 'completed',
			];
		}

		$data = [
			'title' => 'Doctor Dashboard - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'appointments' => $appointments,
			'recentAppointments' => $recentAppointments,
			'todayAppointments' => $todayAppointmentsCount,
			'totalPatients' => $totalPatients,
			'pendingReports' => $pendingReports,
			'monthRevenue' => $monthRevenue,
		];

		return view('auth/dashboard', $data);
	}

	public function patients()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$doctorId = session()->get('user_id');
		$db = \Config\Database::connect();
		
		// Get patients from APPOINTMENTS (outpatients) - exclude discharged patients
		$appointmentPatients = $db->table('patients p')
			->select('p.*, 
					 u.name as assigned_doctor_name,
					 a.appointment_date as last_appointment_date,
					 a.status as appointment_status,
					 "appointment" as source')
			->join('(SELECT patient_id, doctor_id, appointment_date, status, 
							ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
					 FROM appointments 
					 WHERE status != "cancelled" AND doctor_id = ' . (int)$doctorId . ') a', 'a.patient_id = p.id AND a.rn = 1', 'inner')
			->join('users u', 'u.id = a.doctor_id', 'left')
			->where('p.status !=', 'discharged')
			->get()->getResultArray();
		
		// Get patients from ADMISSIONS (inpatients) - only Admitted status
		$admissionPatients = $db->table('patients p')
			->select('p.*, 
					 u.name as assigned_doctor_name,
					 adm.admission_date as last_appointment_date,
					 adm.status as appointment_status,
					 "admission" as source')
			->join('admissions adm', 'adm.patient_id = p.id AND adm.doctor_id = ' . (int)$doctorId, 'inner')
			->join('users u', 'u.id = adm.doctor_id', 'left')
			->where('adm.status', 'Admitted')
			->where('p.status !=', 'discharged')
			->get()->getResultArray();
		
		// Merge and remove duplicates (prefer admission over appointment for same patient)
		$patientIds = [];
		$patients = [];
		
		// Add admission patients first (priority)
		foreach ($admissionPatients as $p) {
			if (!in_array($p['id'], $patientIds)) {
				$patientIds[] = $p['id'];
				$patients[] = $p;
			}
		}
		
		// Add appointment patients (if not already added from admissions)
		foreach ($appointmentPatients as $p) {
			if (!in_array($p['id'], $patientIds)) {
				$patientIds[] = $p['id'];
				$patients[] = $p;
			}
		}
		
		// Sort by ID descending
		usort($patients, fn($a, $b) => $b['id'] - $a['id']);

		$data = [
			'title' => 'Patient Records - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'patients' => $patients,
		];

		return view('doctor/patients', $data);
	}

	public function schedule()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$scheduleModel = new DoctorScheduleModel();
		$doctorId = session()->get('user_id');
		
		// Get current month and year from query params or use current
		$month = (int)($this->request->getGet('month') ?? date('n'));
		$year = (int)($this->request->getGet('year') ?? date('Y'));
		
		// Validate month and year
		if ($month < 1 || $month > 12) $month = (int)date('n');
		if ($year < 2020 || $year > 2100) $year = (int)date('Y');
		
		// Calculate calendar dates first
		$firstDay = mktime(0, 0, 0, $month, 1, $year);
		$daysInMonth = date('t', $firstDay);
		
		// Get doctor's schedules for this month (date-specific and weekly)
		$monthStart = sprintf('%04d-%02d-01', $year, $month);
		$monthEnd = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);
		
		// Get date-specific schedules for this month
		$dateSpecificSchedules = $scheduleModel
			->where('doctor_id', $doctorId)
			->where('schedule_date >=', $monthStart)
			->where('schedule_date <=', $monthEnd)
			->findAll();
		
		// Get weekly schedules (for backward compatibility)
		$weeklySchedule = $scheduleModel->getDoctorWeeklySchedule($doctorId);
		
		// Group schedules by date
		$scheduleByDate = [];
		foreach ($dateSpecificSchedules as $sched) {
			$date = $sched['schedule_date'];
			$scheduleByDate[$date][] = $sched;
		}
		
		// Also group weekly schedules by day of week (for days without date-specific schedules)
		$scheduleByDay = [];
		foreach ($weeklySchedule as $sched) {
			// Only include if no schedule_date (weekly recurring)
			if (empty($sched['schedule_date'])) {
				$scheduleByDay[$sched['day_of_week']][] = $sched;
			}
		}
		$dayOfWeek = date('w', $firstDay); // 0 = Sunday, 1 = Monday, etc.
		
		// Convert to Monday = 0 format
		$startDay = ($dayOfWeek == 0) ? 6 : $dayOfWeek - 1;
		
		// Get previous and next month/year
		$prevMonth = $month - 1;
		$prevYear = $year;
		if ($prevMonth < 1) {
			$prevMonth = 12;
			$prevYear--;
		}
		
		$nextMonth = $month + 1;
		$nextYear = $year;
		if ($nextMonth > 12) {
			$nextMonth = 1;
			$nextYear++;
		}
		
		// Get appointments for this doctor for the current month with patient names
		$appointmentModel = new \App\Models\AppointmentModel();
		$db = \Config\Database::connect();
		$builder = $db->table('appointments a');
		$builder->select('a.*, p.full_name as patient_name');
		$builder->join('patients p', 'p.id = a.patient_id', 'left');
		$builder->where('a.doctor_id', $doctorId);
		$builder->where('a.appointment_date >=', $monthStart);
		$builder->where('a.appointment_date <=', $monthEnd);
		$builder->where('a.status !=', 'cancelled');
		$builder->orderBy('a.appointment_date', 'ASC');
		$builder->orderBy('a.appointment_time', 'ASC');
		$appointments = $builder->get()->getResultArray();
		
		// Group appointments by date
		$appointmentsByDate = [];
		foreach ($appointments as $appointment) {
			$date = $appointment['appointment_date'];
			if (!isset($appointmentsByDate[$date])) {
				$appointmentsByDate[$date] = [];
			}
			$appointmentsByDate[$date][] = $appointment;
		}
		
		$data = [
			'title' => 'My Schedule - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'schedule' => $weeklySchedule,
			'scheduleByDay' => $scheduleByDay,
			'scheduleByDate' => $scheduleByDate,
			'appointmentsByDate' => $appointmentsByDate,
			'currentMonth' => $month,
			'currentYear' => $year,
			'prevMonth' => $prevMonth,
			'prevYear' => $prevYear,
			'nextMonth' => $nextMonth,
			'nextYear' => $nextYear,
			'daysInMonth' => $daysInMonth,
			'startDay' => $startDay,
			'monthName' => date('F', $firstDay),
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
		$db = \Config\Database::connect();
		$patientModel = new PatientModel();
		
		// Get doctor's appointments (exclude lab test appointments) with patient names
		$builder = $db->table('appointments a');
		$builder->select('a.*, COALESCE(p.full_name, CONCAT("Patient #", a.patient_id)) as patient_name');
		$builder->join('patients p', 'p.id = a.patient_id', 'left');
		$builder->where('a.doctor_id', $doctorId);
		$builder->where('a.appointment_date', date('Y-m-d'));
		$builder->where('a.appointment_type !=', 'laboratory_test');
		$builder->where('a.doctor_id IS NOT NULL', null, false);
		$builder->where('a.status !=', 'cancelled');
		$builder->orderBy('a.appointment_time', 'ASC');
		$todaysAppointments = $builder->get()->getResultArray();
		
		// Get upcoming appointments (exclude lab tests) with patient names
		$builder2 = $db->table('appointments a');
		$builder2->select('a.*, COALESCE(p.full_name, CONCAT("Patient #", a.patient_id)) as patient_name');
		$builder2->join('patients p', 'p.id = a.patient_id', 'left');
		$builder2->where('a.doctor_id', $doctorId);
		$builder2->where('a.appointment_date >', date('Y-m-d'));
		$builder2->where('a.appointment_type !=', 'laboratory_test');
		$builder2->where('a.doctor_id IS NOT NULL', null, false);
		$builder2->where('a.status !=', 'cancelled');
		$builder2->orderBy('a.appointment_date', 'ASC');
		$builder2->orderBy('a.appointment_time', 'ASC');
		$builder2->limit(50);
		$upcomingAppointments = $builder2->get()->getResultArray();
		
		// Fallback: If patient_name is still null/empty, fetch from patients table directly
		foreach ($todaysAppointments as &$appointment) {
			if (empty($appointment['patient_name']) || $appointment['patient_name'] === null) {
				$patient = $patientModel->find($appointment['patient_id'] ?? 0);
				if ($patient) {
					$appointment['patient_name'] = $patient['full_name'] ?? 'Patient #' . $appointment['patient_id'];
				} else {
					$appointment['patient_name'] = 'Patient #' . ($appointment['patient_id'] ?? 'Unknown');
				}
			}
		}
		unset($appointment);
		
		foreach ($upcomingAppointments as &$appointment) {
			if (empty($appointment['patient_name']) || $appointment['patient_name'] === null) {
				$patient = $patientModel->find($appointment['patient_id'] ?? 0);
				if ($patient) {
					$appointment['patient_name'] = $patient['full_name'] ?? 'Patient #' . $appointment['patient_id'];
				} else {
					$appointment['patient_name'] = 'Patient #' . ($appointment['patient_id'] ?? 'Unknown');
				}
			}
		}
		unset($appointment);
		
		// Check payment status and auto-update appointment status if bill is paid
		foreach ($todaysAppointments as &$appointment) {
			if ($appointment['status'] !== 'completed' && !empty($appointment['id'])) {
				$appointmentDate = $appointment['appointment_date'] ?? date('Y-m-d');
				$patientId = $appointment['patient_id'] ?? null;
				
				// Check if there's a paid bill linked to this appointment
				$paidBill = $db->table('bills')
					->where('appointment_id', $appointment['id'])
					->where('status', 'paid')
					->get()
					->getRowArray();
				
				// If no direct link, check by patient_id and date
				if (!$paidBill && $patientId && $appointmentDate) {
					$startDate = date('Y-m-d', strtotime($appointmentDate . ' -7 days'));
					$endDate = date('Y-m-d', strtotime($appointmentDate . ' +1 day'));
					
					$paidBill = $db->table('bills')
						->where('patient_id', $patientId)
						->where('status', 'paid')
						->where('created_at >=', $startDate . ' 00:00:00')
						->where('created_at <=', $endDate . ' 23:59:59')
						->orderBy('created_at', 'DESC')
						->get()
						->getRowArray();
					
					// If found, link the bill to this appointment
					if ($paidBill && empty($paidBill['appointment_id'])) {
						$db->table('bills')
							->where('id', $paidBill['id'])
							->update(['appointment_id' => $appointment['id']]);
					}
				}
				
				if ($paidBill) {
					// Auto-update appointment status to completed
					$appointmentModel->update($appointment['id'], [
						'status' => 'completed',
						'updated_at' => date('Y-m-d H:i:s')
					]);
					$appointment['status'] = 'completed';
				}
			}
		}
		
		$today = date('Y-m-d');
		
		foreach ($upcomingAppointments as &$appointment) {
			if ($appointment['status'] !== 'completed' && !empty($appointment['id'])) {
				$appointmentDate = $appointment['appointment_date'] ?? null;
				$patientId = $appointment['patient_id'] ?? null;
				$appointmentType = strtolower($appointment['appointment_type'] ?? '');
				
				// For follow-up appointments, only mark as completed if appointment date is today or in the past
				// Don't auto-complete future follow-up appointments
				if ($appointmentType === 'follow-up' && $appointmentDate && $appointmentDate > $today) {
					continue; // Skip future follow-up appointments
				}
				
				// Check if there's a paid bill linked to this appointment
				$paidBill = $db->table('bills')
					->where('appointment_id', $appointment['id'])
					->where('status', 'paid')
					->get()
					->getRowArray();
				
				// If no direct link, check by patient_id and date (only for appointments on or before today)
				if (!$paidBill && $patientId && $appointmentDate && $appointmentDate <= $today) {
					$startDate = date('Y-m-d', strtotime($appointmentDate . ' -7 days'));
					$endDate = date('Y-m-d', strtotime($appointmentDate . ' +1 day'));
					
					$paidBill = $db->table('bills')
						->where('patient_id', $patientId)
						->where('status', 'paid')
						->where('created_at >=', $startDate . ' 00:00:00')
						->where('created_at <=', $endDate . ' 23:59:59')
						->orderBy('created_at', 'DESC')
						->get()
						->getRowArray();
					
					// If found, link the bill to this appointment
					if ($paidBill && empty($paidBill['appointment_id'])) {
						$db->table('bills')
							->where('id', $paidBill['id'])
							->update(['appointment_id' => $appointment['id']]);
					}
				}
				
				if ($paidBill) {
					// Auto-update appointment status to completed (only if date is today or past)
					if ($appointmentDate && $appointmentDate <= $today) {
						$appointmentModel->update($appointment['id'], [
							'status' => 'completed',
							'updated_at' => date('Y-m-d H:i:s')
						]);
						$appointment['status'] = 'completed';
					}
				}
			}
		}
		
		$data = [
			'title' => 'My Appointments - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'appointments' => $todaysAppointments,
			'upcoming_appointments' => $upcomingAppointments
		];

		return view('doctor/appointments', $data);
	}

	public function consultations()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$doctorId = session()->get('user_id');
		$appointmentModel = new AppointmentModel();
		$patientModel = new \App\Models\PatientModel();
		$prescriptionModel = new PrescriptionModel();
		$db = \Config\Database::connect();

		// Get filter parameters
		$filterStatus = $this->request->getGet('status');
		$filterDateFrom = $this->request->getGet('date_from');
		$filterDateTo = $this->request->getGet('date_to');

		// Build query for consultations (show all except cancelled and no-show)
		// Include completed, confirmed, and scheduled appointments
		// Exclude lab test appointments
		$builder = $appointmentModel
			->where('doctor_id', $doctorId)
			->where('status !=', 'cancelled')
			->where('status !=', 'no-show')
			->where('appointment_type !=', 'laboratory_test') // Exclude lab tests
			->where('doctor_id IS NOT NULL', null, false) // Ensure doctor_id is not null
			->orderBy('appointment_date', 'DESC')
			->orderBy('appointment_time', 'DESC');

		// Apply date filters
		if (!empty($filterDateFrom)) {
			$builder->where('appointment_date >=', $filterDateFrom);
		}
		if (!empty($filterDateTo)) {
			$builder->where('appointment_date <=', $filterDateTo);
		}

		$consultationsRaw = $builder->findAll();

		// Format consultations with patient and prescription data
		$consultations = [];
		foreach ($consultationsRaw as $consultation) {
			$patient = $patientModel->find($consultation['patient_id']);
			// Skip INPATIENT patients from consultations history
			if (!empty($patient['patient_type']) && strtolower($patient['patient_type']) === 'inpatient') {
				continue;
			}
			
			// Get prescription for this consultation
			// First try by appointment_id, then by patient_id and doctor_id if appointment_id is null
			$prescription = $prescriptionModel
				->where('appointment_id', $consultation['id'])
				->first();
			
			// If not found by appointment_id, try to find by patient_id and doctor_id
			// First try within the appointment date range, then fallback to most recent
			if (!$prescription) {
				$appointmentDate = $consultation['appointment_date'];
				$dateStart = date('Y-m-d 00:00:00', strtotime($appointmentDate));
				$dateEnd = date('Y-m-d 23:59:59', strtotime($appointmentDate . ' +1 day'));
				
				$prescription = $prescriptionModel
					->where('patient_id', $consultation['patient_id'])
					->where('doctor_id', $doctorId)
					->where('created_at >=', $dateStart)
					->where('created_at <=', $dateEnd)
					->orderBy('created_at', 'DESC')
					->first();
				
				// If still not found, get the most recent prescription for this patient by this doctor
				if (!$prescription) {
					$prescription = $prescriptionModel
						->where('patient_id', $consultation['patient_id'])
						->where('doctor_id', $doctorId)
						->orderBy('created_at', 'DESC')
						->first();
				}
			}

			$consultations[] = [
				'id' => $consultation['id'],
				'patient_id' => $consultation['patient_id'],
				'patient_name' => $patient['full_name'] ?? 'N/A',
				'patient_age' => $patient['age'] ?? null,
				'patient_gender' => $patient['gender'] ?? null,
				'appointment_date' => $consultation['appointment_date'],
				'appointment_time' => $consultation['appointment_time'],
				'appointment_type' => $consultation['appointment_type'] ?? 'consultation',
				'status' => $consultation['status'] ?? 'scheduled',
				'notes' => $consultation['notes'] ?? null,
				'prescription_id' => $prescription['id'] ?? null,
				'prescription_status' => $prescription['status'] ?? null,
				'created_at' => $consultation['created_at'] ?? null,
			];
		}

		// Get statistics (all consultations except cancelled/no-show)
		$totalConsultations = count($consultations);
		$monthStart = date('Y-m-01');
		$monthEnd = date('Y-m-t');
		$thisMonthConsultations = $appointmentModel
			->where('doctor_id', $doctorId)
			->where('status !=', 'cancelled')
			->where('status !=', 'no-show')
			->where('appointment_date >=', $monthStart)
			->where('appointment_date <=', $monthEnd)
			->countAllResults();

		$thisWeekConsultations = $appointmentModel
			->where('doctor_id', $doctorId)
			->where('status !=', 'cancelled')
			->where('status !=', 'no-show')
			->where('appointment_date >=', date('Y-m-d', strtotime('monday this week')))
			->countAllResults();

		$data = [
			'title' => 'Consultations - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'consultations' => $consultations,
			'totalConsultations' => $totalConsultations,
			'thisMonthConsultations' => $thisMonthConsultations,
			'thisWeekConsultations' => $thisWeekConsultations,
			'filterStatus' => $filterStatus,
			'filterDateFrom' => $filterDateFrom,
			'filterDateTo' => $filterDateTo,
		];

		return view('doctor/consultations', $data);
	}

	public function inpatients()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$doctorId = session()->get('user_id');
		$db = \Config\Database::connect();

		// Get active inpatient assignments for this doctor from admissions table
		$builder = $db->table('admissions a');
		$builder->select('a.*, p.full_name as patient_name, p.age, p.gender, p.contact, r.room_number, r.room_type');
		$builder->join('patients p', 'p.id = a.patient_id', 'left');
		$builder->join('rooms r', 'r.id = a.room_id', 'left');
		$builder->where('a.doctor_id', $doctorId);
		$builder->where('a.status', 'Admitted');
		$builder->orderBy('a.admission_date', 'DESC');
		$builder->orderBy('a.created_at', 'DESC');
		$inpatients = $builder->get()->getResultArray();

		$data = [
			'title' => 'Inpatients - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'inpatients' => $inpatients,
		];

		return view('doctor/inpatients', $data);
	}
	
	public function orderDischarge()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}
		
		$json = $this->request->getJSON(true);
		if (!$json) {
			$json = $this->request->getPost();
		}
		
		$admissionId = $json['admission_id'] ?? null;
		$dischargeNotes = $json['discharge_notes'] ?? '';
		
		if (!$admissionId) {
			return $this->response->setJSON(['success' => false, 'message' => 'Admission ID is required']);
		}
		
		$db = \Config\Database::connect();
		$admission = $db->table('admissions')->where('id', $admissionId)->get()->getRowArray();
		
		if (!$admission) {
			return $this->response->setJSON(['success' => false, 'message' => 'Admission not found']);
		}
		
		// Check if already has discharge order
		if (!empty($admission['discharge_ordered_at'])) {
			return $this->response->setJSON(['success' => false, 'message' => 'Discharge already ordered for this patient']);
		}
		
		// Update admission with discharge order
		$db->table('admissions')->where('id', $admissionId)->update([
			'discharge_ordered_at' => date('Y-m-d H:i:s'),
			'discharge_ordered_by' => session()->get('user_id'),
			'discharge_notes' => $dischargeNotes,
			'updated_at' => date('Y-m-d H:i:s')
		]);
		
		return $this->response->setJSON(['success' => true, 'message' => 'Discharge order created successfully']);
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

		$scheduleDate = $this->request->getPost('schedule_date');
		$dayOfWeek = $this->request->getPost('day_of_week');
		
		// If schedule_date is provided, use it; otherwise use day_of_week for weekly schedule
		$data = [
			'doctor_id' => $doctorId,
			'day_of_week' => $dayOfWeek,
			'schedule_date' => $scheduleDate ?: null,
			'start_time' => $this->request->getPost('start_time'),
			'end_time' => $this->request->getPost('end_time'),
			'is_available' => $this->request->getPost('is_available') ? true : false,
			'notes' => $this->request->getPost('notes')
		];

		// Debug logging
		log_message('info', 'Schedule data: ' . json_encode($data));

		// Check if schedule already exists
		$existingSchedule = null;
		if ($scheduleDate) {
			// Check for date-specific schedule
			$existingSchedule = $scheduleModel->where('doctor_id', $doctorId)
											 ->where('schedule_date', $scheduleDate)
											 ->where('start_time', $data['start_time'])
											 ->where('end_time', $data['end_time'])
											 ->first();
		} else {
			// Check for weekly schedule
			$existingSchedule = $scheduleModel->where('doctor_id', $doctorId)
											 ->where('day_of_week', $dayOfWeek)
											 ->where('schedule_date IS NULL')
											 ->first();
		}

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

	public function getRecurringSchedules()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		$doctorId = session()->get('user_id');
		$scheduleModel = new DoctorScheduleModel();
		
		// Get all recurring schedules (where schedule_date is NULL)
		$recurringSchedules = $scheduleModel
			->where('doctor_id', $doctorId)
			->where('schedule_date IS NULL', null, false)
			->orderBy('FIELD(day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday")')
			->findAll();
		
		return $this->response->setJSON([
			'status' => 'success',
			'schedules' => $recurringSchedules
		]);
	}

	public function updateRecurringSchedule()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
		}

		$doctorId = session()->get('user_id');
		$scheduleModel = new DoctorScheduleModel();
		
		$requestData = json_decode($this->request->getBody(), true);
		$schedules = $requestData['schedules'] ?? [];
		$action = $requestData['action'] ?? 'add';
		
		if (empty($schedules)) {
			return $this->response->setJSON(['status' => 'error', 'message' => 'No schedules provided']);
		}

		$db = \Config\Database::connect();
		$db->transStart();

		try {
			if ($action === 'add') {
				// For adding new schedules, check if schedule already exists for each day
				foreach ($schedules as $schedule) {
					$dayOfWeek = $schedule['day_of_week'] ?? '';
					$startTime = $schedule['start_time'] ?? '';
					$endTime = $schedule['end_time'] ?? '';
					
					if (empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
						throw new \Exception('Missing required fields: day_of_week, start_time, or end_time');
					}
					
					// Check if recurring schedule already exists for this day and time
					$existing = $scheduleModel->where('doctor_id', $doctorId)
											 ->where('day_of_week', $dayOfWeek)
											 ->where('schedule_date IS NULL', null, false)
											 ->where('start_time', $startTime)
											 ->where('end_time', $endTime)
											 ->first();
					
					if (!$existing) {
						$data = [
							'doctor_id' => $doctorId,
							'day_of_week' => $dayOfWeek,
							'schedule_date' => null, // NULL for recurring weekly schedule
							'start_time' => $startTime,
							'end_time' => $endTime,
							'is_available' => true,
							'notes' => $schedule['notes'] ?? null
						];
						
						// Use direct database insert to bypass validation if needed
						$insertId = $scheduleModel->insert($data);
						if (!$insertId) {
							$errors = $scheduleModel->errors();
							$errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown error';
							throw new \Exception('Failed to insert schedule for ' . $dayOfWeek . ': ' . $errorMsg);
						}
					}
				}
			} else {
				// For replacing all schedules, delete existing recurring schedules first
				$scheduleModel->where('doctor_id', $doctorId)
							 ->where('schedule_date IS NULL', null, false)
							 ->delete();

				// Insert new recurring schedules
				foreach ($schedules as $schedule) {
					$dayOfWeek = $schedule['day_of_week'] ?? '';
					$startTime = $schedule['start_time'] ?? '';
					$endTime = $schedule['end_time'] ?? '';
					
					if (empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
						throw new \Exception('Missing required fields: day_of_week, start_time, or end_time');
					}
					
					$data = [
						'doctor_id' => $doctorId,
						'day_of_week' => $dayOfWeek,
						'schedule_date' => null, // NULL for recurring weekly schedule
						'start_time' => $startTime,
						'end_time' => $endTime,
						'is_available' => true,
						'notes' => $schedule['notes'] ?? null
					];
					
					$insertId = $scheduleModel->insert($data);
					if (!$insertId) {
						$errors = $scheduleModel->errors();
						$errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown error';
						throw new \Exception('Failed to insert schedule for ' . $dayOfWeek . ': ' . $errorMsg);
					}
				}
			}

			$db->transComplete();

			if ($db->transStatus() === false) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Failed to save recurring schedule'
				]);
			}

			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Recurring weekly schedule saved successfully. This will apply to the whole year.'
			]);
		} catch (\Exception $e) {
			$db->transRollback();
			log_message('error', 'updateRecurringSchedule error: ' . $e->getMessage());
			log_message('error', 'Request data: ' . json_encode($requestData));
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Error: ' . $e->getMessage(),
				'debug' => [
					'schedules_count' => count($schedules),
					'action' => $action,
					'doctor_id' => $doctorId
				]
			]);
		}
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

    public function prescriptions()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
            return redirect()->to('/login');
        }

        // Ensure prescriptions table exists to avoid runtime errors on first use
        $this->ensurePrescriptionsTable();

        $doctorId = session()->get('user_id');
        $rxModel = new PrescriptionModel();
        $medModel = new MedicationModel();

        $prescriptions = $rxModel->getDoctorPrescriptions((int) $doctorId);
        
        // Fix existing prescriptions with null/empty status - set to 'completed' for outpatients
        $patientModel = new PatientModel();
        foreach ($prescriptions as &$rx) {
            if (empty($rx['status']) || $rx['status'] === null || trim($rx['status']) === '') {
                $patient = $patientModel->find($rx['patient_id'] ?? 0);
                if ($patient && strtolower($patient['patient_type'] ?? '') === 'outpatient') {
                    // Update prescription status to 'completed' for outpatients
                    $rxModel->update($rx['id'], [
                        'status' => 'completed',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $rx['status'] = 'completed';
                }
            }
        }
        unset($rx);
        
        // Re-fetch prescriptions to get updated statuses
        $prescriptions = $rxModel->getDoctorPrescriptions((int) $doctorId);

        // Patients assigned to this doctor for selection - from BOTH appointments AND admissions
        $db = \Config\Database::connect();
        
        // Get patients from appointments (outpatients)
        $appointmentPatients = $db->table('patients p')
            ->select('p.id, p.full_name, p.date_of_birth, p.gender, p.patient_type')
            ->join('(SELECT patient_id, doctor_id, appointment_date,
                            ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                     FROM appointments WHERE status != "cancelled" AND doctor_id = ' . (int) $doctorId . ') a', 'a.patient_id = p.id AND a.rn = 1', 'inner')
            ->get()->getResultArray();
        
        // Get patients from admissions (inpatients)
        $admissionPatients = $db->table('patients p')
            ->select('p.id, p.full_name, p.date_of_birth, p.gender, p.patient_type')
            ->join('admissions adm', 'adm.patient_id = p.id AND adm.doctor_id = ' . (int) $doctorId, 'inner')
            ->where('adm.status', 'Admitted')
            ->get()->getResultArray();
        
        // Merge and remove duplicates
        $patientIds = [];
        $patientsRaw = [];
        
        // Add admission patients first (inpatients)
        foreach ($admissionPatients as $p) {
            if (!in_array($p['id'], $patientIds)) {
                $patientIds[] = $p['id'];
                $patientsRaw[] = $p;
            }
        }
        
        // Add appointment patients
        foreach ($appointmentPatients as $p) {
            if (!in_array($p['id'], $patientIds)) {
                $patientIds[] = $p['id'];
                $patientsRaw[] = $p;
            }
        }
        
        // Filter out patients with future follow-up appointments OR completed+paid prescriptions/lab tests
        $today = date('Y-m-d');
        $filteredPatients = [];
        
        foreach ($patientsRaw as $patient) {
            $patientId = $patient['id'];
            $shouldInclude = true;
            
            // Check if patient has any future follow-up appointments
            if ($db->tableExists('appointments')) {
                $futureFollowUp = $db->table('appointments')
                    ->where('patient_id', $patientId)
                    ->where('doctor_id', $doctorId)
                    ->where('appointment_type', 'follow-up')
                    ->where('status !=', 'cancelled')
                    ->where('appointment_date >', $today) // Future follow-up
                    ->get()
                    ->getRowArray();
                
                if ($futureFollowUp) {
                    // Patient has a future follow-up - lock them (don't show in dropdown)
                    $shouldInclude = false;
                } else {
                    // Check if patient has completed follow-up appointments that are paid
                    $completedFollowUps = $db->table('appointments')
                        ->where('patient_id', $patientId)
                        ->where('doctor_id', $doctorId)
                        ->where('appointment_type', 'follow-up')
                        ->where('status', 'completed')
                        ->where('appointment_date <=', $today) // Today or past
                        ->get()
                        ->getResultArray();
                    
                    if (!empty($completedFollowUps)) {
                        // Check if all completed follow-up appointments have been paid
                        $allPaid = true;
                        foreach ($completedFollowUps as $followUp) {
                            $bill = $db->table('bills')
                                ->where('appointment_id', $followUp['id'])
                                ->where('status', 'paid')
                                ->get()
                                ->getRowArray();
                            
                            // If no direct link, check by patient_id and appointment date
                            if (!$bill && $db->tableExists('bills')) {
                                $followUpDate = $followUp['appointment_date'] ?? null;
                                if ($followUpDate) {
                                    $startDate = date('Y-m-d', strtotime($followUpDate . ' -1 day'));
                                    $endDate = date('Y-m-d', strtotime($followUpDate . ' +1 day'));
                                    
                                    $bill = $db->table('bills')
                                        ->where('patient_id', $patientId)
                                        ->where('status', 'paid')
                                        ->where('created_at >=', $startDate . ' 00:00:00')
                                        ->where('created_at <=', $endDate . ' 23:59:59')
                                        ->orderBy('created_at', 'DESC')
                                        ->get()
                                        ->getRowArray();
                                }
                            }
                            
                            if (!$bill) {
                                // This follow-up is not paid yet - patient can still be selected
                                $allPaid = false;
                                break;
                            }
                        }
                        
                        if ($allPaid && !empty($completedFollowUps)) {
                            // All completed follow-ups are paid - hide patient
                            $shouldInclude = false;
                        }
                    }
                }
            }
            
            // Check if patient has completed prescriptions that are paid
            if ($shouldInclude && $db->tableExists('prescriptions') && $db->tableExists('bills')) {
                $completedPrescriptions = $db->table('prescriptions')
                    ->where('patient_id', $patientId)
                    ->where('doctor_id', $doctorId)
                    ->where('status', 'completed')
                    ->get()
                    ->getResultArray();
                
                if (!empty($completedPrescriptions)) {
                    // Check if all completed prescriptions have been paid
                    $allPaid = true;
                    foreach ($completedPrescriptions as $rx) {
                        $bill = $db->table('bills')
                            ->where('prescription_id', $rx['id'])
                            ->where('status', 'paid')
                            ->get()
                            ->getRowArray();
                        
                        if (!$bill) {
                            // This prescription is not paid yet - patient can still be selected
                            $allPaid = false;
                            break;
                        }
                    }
                    
                    if ($allPaid && !empty($completedPrescriptions)) {
                        // All completed prescriptions are paid - hide patient
                        $shouldInclude = false;
                    }
                }
            }
            
            // Check if patient has completed lab tests that are paid
            if ($shouldInclude && $db->tableExists('lab_test_requests') && $db->tableExists('bills')) {
                $completedLabTests = $db->table('lab_test_requests')
                    ->where('patient_id', $patientId)
                    ->where('doctor_id', $doctorId)
                    ->where('status', 'completed')
                    ->get()
                    ->getResultArray();
                
                if (!empty($completedLabTests)) {
                    // Check if all completed lab tests have been paid
                    $allPaid = true;
                    foreach ($completedLabTests as $lab) {
                        $bill = $db->table('bills')
                            ->where('lab_test_id', $lab['id'])
                            ->where('status', 'paid')
                            ->get()
                            ->getRowArray();
                        
                        if (!$bill) {
                            // This lab test is not paid yet - patient can still be selected
                            $allPaid = false;
                            break;
                        }
                    }
                    
                    if ($allPaid && !empty($completedLabTests)) {
                        // All completed lab tests are paid - hide patient
                        $shouldInclude = false;
                    }
                }
            }
            
            if ($shouldInclude) {
                $filteredPatients[] = $patient;
            }
        }
        
        // Sort by patient_type (inpatients first) then by name
        usort($filteredPatients, function($a, $b) {
            if ($a['patient_type'] === $b['patient_type']) {
                return strcasecmp($a['full_name'], $b['full_name']);
            }
            return $a['patient_type'] === 'inpatient' ? -1 : 1;
        });
        
        // Calculate age for each patient
        $patients = [];
        foreach ($filteredPatients as $pt) {
            $age = '—';
            if (!empty($pt['date_of_birth']) && $pt['date_of_birth'] !== '0000-00-00' && $pt['date_of_birth'] !== '') {
                try {
                    $dateStr = $pt['date_of_birth'];
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
            $pt['age'] = $age;
            $patients[] = $pt;
        }

        $this->ensureMedicationsTable();
        $medications = $medModel->listOptions();

        // Get inventory/stock data for medications
        $inventoryByMedId = [];
        $inventoryByName = [];
        
        if ($db->tableExists('pharmacy_inventory')) {
            $inventoryRaw = $db->table('pharmacy_inventory')
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();
            
            foreach ($inventoryRaw as $item) {
                if (!empty($item['medication_id'])) {
                    $inventoryByMedId[$item['medication_id']] = $item;
                }
                $inventoryByName[strtolower(trim($item['name']))] = $item;
            }
        }

        // Attach stock information to each medication
        foreach ($medications as &$med) {
            $medId = $med['id'];
            $medName = strtolower(trim($med['name']));
            
            $invItem = null;
            if (isset($inventoryByMedId[$medId])) {
                $invItem = $inventoryByMedId[$medId];
            } elseif (isset($inventoryByName[$medName])) {
                $invItem = $inventoryByName[$medName];
            }
            
            $stockQty = $invItem ? (int)($invItem['stock_quantity'] ?? 0) : 0;
            $reorderLevel = $invItem ? (int)($invItem['reorder_level'] ?? 10) : 10;
            
            // Determine stock status
            $stockStatus = 'ok';
            if ($stockQty <= 0) {
                $stockStatus = 'out_of_stock';
            } elseif ($stockQty < $reorderLevel) {
                $stockStatus = 'low_stock';
            }
            
            $med['stock_quantity'] = $stockQty;
            $med['reorder_level'] = $reorderLevel;
            $med['stock_status'] = $stockStatus;
        }
        unset($med);

        $data = [
            'title' => 'Prescriptions - HMS',
            'user_role' => 'doctor',
            'user_name' => session()->get('name'),
            'prescriptions' => $prescriptions,
            'patients' => $patients,
            'medications' => $medications,
        ];

        return view('doctor/prescriptions', $data);
    }

    public function createPrescription()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        // Ensure table exists before inserting
        $this->ensurePrescriptionsTable();

        $payload = $this->request->getJSON(true) ?? [];

        $doctorId = (int) session()->get('user_id');
        $patientId = (int) ($payload['patient_id'] ?? 0);
        $items = $payload['items'] ?? [];
        $notes = trim((string) ($payload['notes'] ?? ''));

        if ($patientId <= 0 || empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Patient and at least one item are required.']);
        }

        $rxModel = new PrescriptionModel();
        $patientModel = new PatientModel();
        
        // Get patient info
        $patient = $patientModel->find($patientId);
        if (!$patient) {
            return $this->response->setJSON(['success' => false, 'message' => 'Patient not found.']);
        }
        
        $patientType = strtolower($patient['patient_type'] ?? 'outpatient');
        $isOutpatient = ($patientType !== 'inpatient');
        
        // Get latest appointment for this patient
        $appointmentModel = new AppointmentModel();
        $latestAppointment = $appointmentModel->where('patient_id', $patientId)
            ->where('status !=', 'cancelled')
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();
        
        $appointmentId = null;
        $admissionId = null;
        
        if ($latestAppointment && !empty($latestAppointment['id'])) {
            $appointmentId = $latestAppointment['id'];
            
            // Only set admission_id for INPATIENTS
            if (!$isOutpatient) {
                $admissionId = $latestAppointment['id'];
            }
        }

        // For outpatients: status = 'completed' (prescription is given/printed, no nurse needed)
        // For inpatients: status = 'pending' (nurse will administer)
        $prescriptionStatus = $isOutpatient ? 'completed' : 'pending';
        
        $data = [
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'appointment_id' => $appointmentId, // Set appointment_id for all patients
            'admission_id' => $admissionId, // Only set for inpatients
            'items_json' => json_encode($items),
            'notes' => $notes,
            'status' => $prescriptionStatus,
        ];

        if (!$rxModel->insert($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to save prescription', 'errors' => $rxModel->errors()]);
        }
        
        $prescriptionId = $rxModel->getInsertID();
        
        // Get the saved prescription for follow-up creation
        $savedPrescription = $rxModel->find($prescriptionId);
        
        // For outpatients: Deduct stock immediately since prescription is printed/given right away
        // For inpatients: Stock will be deducted when nurse marks as given
        if ($isOutpatient && $savedPrescription) {
            $this->deductPrescriptionStock($savedPrescription, $items);
        }
        
        // Check if any medication requires follow-up and create appointment immediately
        if ($savedPrescription) {
            $this->createFollowUpAppointment($savedPrescription);
        }

        return $this->response->setJSON([
            'success' => true, 
            'is_outpatient' => $isOutpatient,
            'prescription_id' => $prescriptionId,
            'message' => $isOutpatient ? 'Prescription saved! Ready to print.' : 'Prescription saved and sent to nurse station.'
        ]);
    }
    
    /**
     * Deduct medication stock from pharmacy inventory for outpatient prescriptions
     */
    private function deductPrescriptionStock($prescription, $items)
    {
        try {
            $db = \Config\Database::connect();
            
            if (!$db->tableExists('pharmacy_inventory')) {
                log_message('info', "pharmacy_inventory table does not exist, skipping stock deduction");
                return;
            }
            
            // Check if stock was already deducted for this prescription
            $notes = $prescription['notes'] ?? '';
            $stockAlreadyDeducted = strpos($notes, 'STOCK_DEDUCTED:') !== false;
            
            if ($stockAlreadyDeducted) {
                log_message('info', "Stock already deducted for prescription #{$prescription['id']}");
                return;
            }
            
            log_message('info', "Starting stock deduction for outpatient prescription #{$prescription['id']}");
            $medicationModel = new \App\Models\MedicationModel();
            $deductionNotes = [];
            
            foreach ($items as $item) {
                $medicineName = $item['name'] ?? '';
                
                if (empty($medicineName)) {
                    continue;
                }
                
                // Check if patient will buy from hospital (for outpatients only)
                // If buy_from_hospital is false or not set, skip stock deduction
                $buyFromHospital = isset($item['buy_from_hospital']) ? (bool)$item['buy_from_hospital'] : true;
                if (!$buyFromHospital) {
                    log_message('info', "Skipping stock deduction for {$medicineName} - patient will not buy from hospital");
                    continue;
                }
                
                // Get quantity from item
                $quantity = 0;
                if (isset($item['quantity']) && $item['quantity'] > 0) {
                    $quantity = (int)$item['quantity'];
                } else {
                    // Try to calculate quantity from duration and frequency
                    $durationStr = $item['duration'] ?? '';
                    $frequency = $item['frequency'] ?? '';
                    
                    if (!empty($durationStr)) {
                        preg_match('/(\d+)/', $durationStr, $matches);
                        if (!empty($matches[1])) {
                            $durationDays = (int)$matches[1];
                            
                            // Estimate quantity based on frequency
                            if (strpos(strtolower($frequency), '2x') !== false || 
                                strpos(strtolower($frequency), 'twice') !== false ||
                                strpos(strtolower($frequency), '2') !== false) {
                                $quantity = $durationDays * 2;
                            } elseif (strpos(strtolower($frequency), '3x') !== false || 
                                     strpos(strtolower($frequency), 'thrice') !== false ||
                                     strpos(strtolower($frequency), '3') !== false) {
                                $quantity = $durationDays * 3;
                            } elseif (strpos(strtolower($frequency), 'every 6 hours') !== false) {
                                $quantity = $durationDays * 4; // 4 times per day
                            } elseif (strpos(strtolower($frequency), 'every 8 hours') !== false) {
                                $quantity = $durationDays * 3; // 3 times per day
                            } else {
                                $quantity = $durationDays; // Once a day (default)
                            }
                        }
                    }
                    
                    // If still no quantity, default to 1
                    if ($quantity <= 0) {
                        $quantity = 1;
                    }
                }
                
                log_message('info', "Processing stock deduction for: {$medicineName}, Quantity: {$quantity}");
                
                // Extract base medication name
                $baseMedicineName = preg_replace('/\s+\d+.*?(mg|ml|g|tablet|capsule).*$/i', '', $medicineName);
                $baseMedicineName = trim($baseMedicineName);
                
                // Try to find medication by exact name first
                $medication = $medicationModel->where('name', $medicineName)->first();
                
                // If not found, try base name
                if (!$medication && !empty($baseMedicineName)) {
                    $medication = $medicationModel->where('name', $baseMedicineName)->first();
                }
                
                // If still not found, try LIKE match
                if (!$medication && !empty($baseMedicineName)) {
                    $medication = $medicationModel->like('name', $baseMedicineName, 'both')->first();
                }
                
                $inventoryRecord = null;
                
                // Try to find inventory record by medication_id first
                if ($medication && !empty($medication['id'])) {
                    $inventoryRecord = $db->table('pharmacy_inventory')
                        ->where('medication_id', $medication['id'])
                        ->get()
                        ->getRowArray();
                }
                
                // If not found by medication_id, try by exact name match
                if (!$inventoryRecord) {
                    $inventoryRecord = $db->table('pharmacy_inventory')
                        ->where('name', $medicineName)
                        ->get()
                        ->getRowArray();
                }
                
                // If still not found, try base name match
                if (!$inventoryRecord && !empty($baseMedicineName)) {
                    $inventoryRecord = $db->table('pharmacy_inventory')
                        ->where('name', $baseMedicineName)
                        ->get()
                        ->getRowArray();
                }
                
                // If still not found, try LIKE match
                if (!$inventoryRecord && !empty($baseMedicineName)) {
                    $inventoryRecord = $db->table('pharmacy_inventory')
                        ->like('name', $baseMedicineName, 'both')
                        ->get()
                        ->getRowArray();
                }
                
                // Last resort: Get all inventory and find by case-insensitive partial match
                if (!$inventoryRecord && !empty($baseMedicineName)) {
                    $allInventory = $db->table('pharmacy_inventory')
                        ->select('id, name, medication_id, stock_quantity')
                        ->get()
                        ->getResultArray();
                    
                    foreach ($allInventory as $inv) {
                        $invName = strtolower(trim($inv['name'] ?? ''));
                        $searchName = strtolower($baseMedicineName);
                        if (strpos($invName, $searchName) !== false || strpos($searchName, $invName) !== false) {
                            $inventoryRecord = $inv;
                            break;
                        }
                    }
                }
                
                if ($inventoryRecord) {
                    $currentStock = (int)($inventoryRecord['stock_quantity'] ?? 0);
                    $newStock = max(0, $currentStock - $quantity); // Don't go below 0
                    
                    log_message('info', "Stock update: {$medicineName} - Current: {$currentStock}, Deduct: {$quantity}, New: {$newStock}");
                    
                    // Update stock
                    $updateResult = $db->table('pharmacy_inventory')
                        ->where('id', $inventoryRecord['id'])
                        ->update([
                            'stock_quantity' => $newStock,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    
                    if ($updateResult) {
                        $deductionNotes[] = "STOCK_DEDUCTED:" . date('Y-m-d H:i:s') . ':' . $medicineName . ':' . $quantity;
                        
                        // Log stock movement
                        if ($db->tableExists('pharmacy_stock_movements')) {
                            $db->table('pharmacy_stock_movements')->insert([
                                'medication_id' => $medication['id'] ?? null,
                                'medicine_name' => $medicineName,
                                'movement_type' => 'dispense',
                                'quantity_change' => -$quantity,
                                'previous_stock' => $currentStock,
                                'new_stock' => $newStock,
                                'action_by' => session()->get('user_id'),
                                'notes' => 'Given to patient via prescription RX#' . str_pad((string)$prescription['id'], 3, '0', STR_PAD_LEFT) . ' by doctor (outpatient)',
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                } else {
                    log_message('warning', "No inventory record found for medication: {$medicineName}");
                }
            }
            
            // Update prescription notes with deduction flags
            if (!empty($deductionNotes)) {
                $currentNotes = $prescription['notes'] ?? '';
                $newNotes = trim($currentNotes);
                if (!empty($newNotes)) {
                    $newNotes .= "\n" . implode("\n", $deductionNotes);
                } else {
                    $newNotes = implode("\n", $deductionNotes);
                }
                
                $db->table('prescriptions')
                    ->where('id', $prescription['id'])
                    ->update(['notes' => $newNotes]);
                
                log_message('info', "Completed stock deduction for outpatient prescription #{$prescription['id']}");
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error deducting stock for prescription #{$prescription['id']}: " . $e->getMessage());
        }
    }
    
    /**
     * Create follow-up appointment if prescription has medications requiring follow-up
     */
    private function createFollowUpAppointment($prescription)
    {
        try {
            $db = \Config\Database::connect();
            $items = json_decode($prescription['items_json'] ?? '[]', true) ?: [];
            
            // Check if any medication requires follow-up
            $requiresFollowup = false;
            foreach ($items as $item) {
                // Check both boolean true and string "true" values
                if (!empty($item['requires_followup']) && 
                    ($item['requires_followup'] === true || $item['requires_followup'] === 'true' || $item['requires_followup'] === 1)) {
                    $requiresFollowup = true;
                    break;
                }
            }
            
            if (!$requiresFollowup) {
                return; // No follow-up needed
            }
            
            // Check if follow-up appointment already exists for this prescription
            $existingFollowup = $db->table('appointments')
                ->where('patient_id', $prescription['patient_id'])
                ->where('doctor_id', $prescription['doctor_id'])
                ->where('appointment_type', 'follow-up')
                ->where('status !=', 'cancelled')
                ->where('appointment_date >=', date('Y-m-d'))
                ->get()
                ->getRowArray();
            
            if ($existingFollowup) {
                log_message('info', "Follow-up appointment already exists for prescription #{$prescription['id']}");
                return; // Follow-up already scheduled
            }
            
            // Get patient info
            $patientModel = new PatientModel();
            $patient = $patientModel->find($prescription['patient_id']);
            
            if (!$patient) {
                log_message('error', "Patient not found for prescription #{$prescription['id']}");
                return;
            }
            
            // Only create follow-up for outpatients
            if (strtolower($patient['patient_type'] ?? '') !== 'outpatient') {
                log_message('info', "Skipping follow-up appointment - patient is not outpatient");
                return;
            }
            
            // Get follow-up date and time from prescription items
            $followupDate = null;
            $followupTime = null;
            
            // Find the first medication item that requires follow-up and has date/time specified
            foreach ($items as $item) {
                if (!empty($item['requires_followup']) && 
                    ($item['requires_followup'] === true || $item['requires_followup'] === 'true' || $item['requires_followup'] === 1)) {
                    
                    // Check if follow-up date and time are provided
                    if (!empty($item['followup_date'])) {
                        $followupDate = $item['followup_date'];
                    }
                    if (!empty($item['followup_time'])) {
                        $followupTime = $item['followup_time'];
                    }
                    
                    // If we found both date and time, use them
                    if ($followupDate && $followupTime) {
                        break;
                    }
                }
            }
            
            // If date/time not provided, calculate follow-up date (7 days from today, or based on duration)
            if (!$followupDate) {
                $followupDate = date('Y-m-d', strtotime('+7 days'));
                
                // Try to get duration from first medication item that requires follow-up
                foreach ($items as $item) {
                    if (!empty($item['requires_followup']) && 
                        ($item['requires_followup'] === true || $item['requires_followup'] === 'true' || $item['requires_followup'] === 1)) {
                        if (!empty($item['duration'])) {
                            $durationText = $item['duration'];
                            if (preg_match('/(\d+)/', $durationText, $matches)) {
                                $durationDays = (int) $matches[1];
                                // Follow-up should be after medication duration ends
                                $followupDate = date('Y-m-d', strtotime("+{$durationDays} days"));
                                break;
                            }
                        }
                    }
                }
            }
            
            // Get doctor's schedule to find available time
            $doctorId = $prescription['doctor_id'];
            $appointmentModel = new AppointmentModel();
            
            // If time not provided, try to find available time slot for follow-up date
            if (!$followupTime) {
                $availableTimes = ['09:00:00', '10:00:00', '11:00:00', '14:00:00', '15:00:00', '16:00:00'];
                $appointmentTime = null;
                
                foreach ($availableTimes as $time) {
                    if ($appointmentModel->isDoctorAvailable($doctorId, $followupDate, $time)) {
                        $appointmentTime = $time;
                        break;
                    }
                }
                
                // If no available time found, use default
                if (!$appointmentTime) {
                    $appointmentTime = '09:00:00';
                }
            } else {
                // Use the provided time, but ensure it's in HH:MM:SS format
                $appointmentTime = $followupTime;
                // If time is in HH:MM format, add seconds
                if (preg_match('/^(\d{2}):(\d{2})$/', $appointmentTime)) {
                    $appointmentTime .= ':00';
                }
            }
            
            // Get room for follow-up (use consultation room)
            $roomModel = new \App\Models\RoomModel();
            $rooms = $roomModel->getRoomsByAppointmentType('follow-up');
            $roomId = null;
            if (!empty($rooms)) {
                $roomId = $rooms[0]['id'] ?? null;
            }
            
            // Create follow-up appointment
            $appointmentData = [
                'patient_id' => $prescription['patient_id'],
                'doctor_id' => $doctorId,
                'room_id' => $roomId,
                'appointment_date' => $followupDate,
                'appointment_time' => $appointmentTime,
                'appointment_type' => 'follow-up',
                'status' => 'scheduled',
                'notes' => 'Auto-created follow-up from prescription RX#' . str_pad((string)$prescription['id'], 3, '0', STR_PAD_LEFT),
                'created_by' => session()->get('user_id') ?? 1
            ];
            
            $appointmentId = $appointmentModel->insert($appointmentData);
            
            if ($appointmentId) {
                log_message('info', "Created follow-up appointment #{$appointmentId} for prescription #{$prescription['id']}");
            } else {
                log_message('error', "Failed to create follow-up appointment for prescription #{$prescription['id']}: " . json_encode($appointmentModel->errors()));
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error creating follow-up appointment: " . $e->getMessage());
        }
    }

    /**
     * Create prescriptions table if it does not exist yet.
     */
    private function ensurePrescriptionsTable(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('prescriptions')) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'patient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'doctor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'appointment_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'items_json' => [
                'type' => 'TEXT',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'dispensed', 'cancelled', 'completed', 'printed'],
                'default'    => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('prescriptions', true);
    }

    private function ensureMedicationsTable(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('medications')) {
            return;
        }
        $forge = \Config\Database::forge();
        $forge->addField([
            'id' => [
                'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true
            ],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'strength' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'form' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'default_dosage' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'default_quantity' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('medications', true);

        // seed a few medications
        $db->table('medications')->insertBatch([
            ['name' => 'Amoxicillin', 'strength' => '500mg', 'form' => 'capsule', 'default_dosage' => '1 cap', 'default_quantity' => 21],
            ['name' => 'Paracetamol', 'strength' => '500mg', 'form' => 'tablet', 'default_dosage' => '1 tab', 'default_quantity' => 30],
            ['name' => 'Ibuprofen', 'strength' => '400mg', 'form' => 'tablet', 'default_dosage' => '1 tab', 'default_quantity' => 15],
        ]);
    }

    public function getPatientDetails(int $id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
            return $this->response->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $model = new \App\Models\PatientModel();
        $patient = $model->find($id);

        if (!$patient) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Patient not found']);
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
            'status' => 'success',
            'patient' => $patient,
            'assigned_nurse' => $assignedNurse,
            'latest_vitals' => $latestVitals,
            'vital_signs_history' => $vitalSignsHistory
        ]);
    }

    public function labRequests()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
            return redirect()->to('/login');
        }

        $doctorId = session()->get('user_id');
        $requestModel = new LabTestRequestModel();
        $resultModel = new LabTestResultModel();
        
        // Get lab requests created by this doctor
        $requests = $requestModel->getAllWithRelations([]);
        $myRequests = array_filter($requests, function($req) use ($doctorId) {
            return ($req['doctor_id'] ?? 0) == $doctorId;
        });
        $myRequests = array_values($myRequests);
        
        // Attach latest result data to each request
        if (!empty($myRequests)) {
            $requestIds = array_column($myRequests, 'id');
            $results = $resultModel->whereIn('request_id', $requestIds)
                ->orderBy('released_at', 'DESC')
                ->findAll();
            
            $resultsByRequest = [];
            foreach ($results as $resultRow) {
                $reqId = $resultRow['request_id'] ?? null;
                if ($reqId && !isset($resultsByRequest[$reqId])) {
                    $resultsByRequest[$reqId] = $resultRow;
                }
            }
            
            foreach ($myRequests as &$req) {
                $reqId = $req['id'] ?? null;
                if ($reqId && isset($resultsByRequest[$reqId])) {
                    $req['latest_result'] = $resultsByRequest[$reqId];
                }
            }
            unset($req);
        }
        
        // Get patients assigned to this doctor for selection - from BOTH appointments AND admissions
        $db = \Config\Database::connect();
        
        // Get patients from appointments (outpatients)
        $appointmentPatients = $db->table('patients p')
            ->select('p.id, p.full_name, p.date_of_birth, p.gender, p.patient_id, p.patient_type')
            ->join('(SELECT patient_id, doctor_id, appointment_date,
                            ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                     FROM appointments WHERE status != "cancelled" AND doctor_id = ' . (int) $doctorId . ') a', 'a.patient_id = p.id AND a.rn = 1', 'inner')
            ->get()->getResultArray();
        
        // Get patients from admissions (inpatients)
        $admissionPatients = $db->table('patients p')
            ->select('p.id, p.full_name, p.date_of_birth, p.gender, p.patient_id, p.patient_type')
            ->join('admissions adm', 'adm.patient_id = p.id AND adm.doctor_id = ' . (int) $doctorId, 'inner')
            ->where('adm.status', 'Admitted')
            ->get()->getResultArray();
        
        // Merge and remove duplicates
        $patientIds = [];
        $patientsRaw = [];
        
        // Add admission patients first (inpatients)
        foreach ($admissionPatients as $p) {
            if (!in_array($p['id'], $patientIds)) {
                $patientIds[] = $p['id'];
                $patientsRaw[] = $p;
            }
        }
        
        // Add appointment patients
        foreach ($appointmentPatients as $p) {
            if (!in_array($p['id'], $patientIds)) {
                $patientIds[] = $p['id'];
                $patientsRaw[] = $p;
            }
        }
        
        // Filter out patients with future follow-up appointments
        $today = date('Y-m-d');
        $filteredPatients = [];
        
        foreach ($patientsRaw as $patient) {
            $patientId = $patient['id'];
            $shouldInclude = true;
            
            // Check if patient has any future follow-up appointments
            if ($db->tableExists('appointments')) {
                $futureFollowUp = $db->table('appointments')
                    ->where('patient_id', $patientId)
                    ->where('doctor_id', $doctorId)
                    ->where('appointment_type', 'follow-up')
                    ->where('status !=', 'cancelled')
                    ->where('appointment_date >', $today) // Future follow-up
                    ->get()
                    ->getRowArray();
                
                if ($futureFollowUp) {
                    // Patient has a future follow-up - lock them (don't show in dropdown)
                    $shouldInclude = false;
                } else {
                    // Check if patient has completed follow-up appointments that are paid
                    $completedFollowUps = $db->table('appointments')
                        ->where('patient_id', $patientId)
                        ->where('doctor_id', $doctorId)
                        ->where('appointment_type', 'follow-up')
                        ->where('status', 'completed')
                        ->where('appointment_date <=', $today) // Today or past
                        ->get()
                        ->getResultArray();
                    
                    if (!empty($completedFollowUps)) {
                        // Check if all completed follow-up appointments have been paid
                        $allPaid = true;
                        foreach ($completedFollowUps as $followUp) {
                            $bill = $db->table('bills')
                                ->where('appointment_id', $followUp['id'])
                                ->where('status', 'paid')
                                ->get()
                                ->getRowArray();
                            
                            // If no direct link, check by patient_id and appointment date
                            if (!$bill && $db->tableExists('bills')) {
                                $followUpDate = $followUp['appointment_date'] ?? null;
                                if ($followUpDate) {
                                    $startDate = date('Y-m-d', strtotime($followUpDate . ' -1 day'));
                                    $endDate = date('Y-m-d', strtotime($followUpDate . ' +1 day'));
                                    
                                    $bill = $db->table('bills')
                                        ->where('patient_id', $patientId)
                                        ->where('status', 'paid')
                                        ->where('created_at >=', $startDate . ' 00:00:00')
                                        ->where('created_at <=', $endDate . ' 23:59:59')
                                        ->orderBy('created_at', 'DESC')
                                        ->get()
                                        ->getRowArray();
                                }
                            }
                            
                            if (!$bill) {
                                // This follow-up is not paid yet - patient can still be selected
                                $allPaid = false;
                                break;
                            }
                        }
                        
                        if ($allPaid && !empty($completedFollowUps)) {
                            // All completed follow-ups are paid - hide patient
                            $shouldInclude = false;
                        }
                    }
                }
            }
            
            // Check if patient has completed prescriptions that are paid
            if ($shouldInclude && $db->tableExists('prescriptions') && $db->tableExists('bills')) {
                $completedPrescriptions = $db->table('prescriptions')
                    ->where('patient_id', $patientId)
                    ->where('doctor_id', $doctorId)
                    ->where('status', 'completed')
                    ->get()
                    ->getResultArray();
                
                if (!empty($completedPrescriptions)) {
                    // Check if all completed prescriptions have been paid
                    $allPaid = true;
                    foreach ($completedPrescriptions as $rx) {
                        $bill = $db->table('bills')
                            ->where('prescription_id', $rx['id'])
                            ->where('status', 'paid')
                            ->get()
                            ->getRowArray();
                        
                        if (!$bill) {
                            // This prescription is not paid yet - patient can still be selected
                            $allPaid = false;
                            break;
                        }
                    }
                    
                    if ($allPaid && !empty($completedPrescriptions)) {
                        // All completed prescriptions are paid - hide patient
                        $shouldInclude = false;
                    }
                }
            }
            
            // Check if patient has completed lab tests that are paid
            if ($shouldInclude && $db->tableExists('lab_test_requests') && $db->tableExists('bills')) {
                $completedLabTests = $db->table('lab_test_requests')
                    ->where('patient_id', $patientId)
                    ->where('doctor_id', $doctorId)
                    ->where('status', 'completed')
                    ->get()
                    ->getResultArray();
                
                if (!empty($completedLabTests)) {
                    // Check if all completed lab tests have been paid
                    $allPaid = true;
                    foreach ($completedLabTests as $lab) {
                        $bill = $db->table('bills')
                            ->where('lab_test_id', $lab['id'])
                            ->where('status', 'paid')
                            ->get()
                            ->getRowArray();
                        
                        if (!$bill) {
                            // This lab test is not paid yet - patient can still be selected
                            $allPaid = false;
                            break;
                        }
                    }
                    
                    if ($allPaid && !empty($completedLabTests)) {
                        // All completed lab tests are paid - hide patient
                        $shouldInclude = false;
                    }
                }
            }
            
            if ($shouldInclude) {
                $filteredPatients[] = $patient;
            }
        }
        
        // Sort by name
        usort($filteredPatients, function($a, $b) {
            return strcasecmp($a['full_name'], $b['full_name']);
        });
        
        // Calculate age for each patient
        $patients = [];
        foreach ($filteredPatients as $pt) {
            $age = '—';
            if (!empty($pt['date_of_birth']) && $pt['date_of_birth'] !== '0000-00-00' && $pt['date_of_birth'] !== '') {
                try {
                    $dateStr = $pt['date_of_birth'];
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
            $pt['age'] = $age;
            $patients[] = $pt;
        }

        // Get all active nurses for specimen collection assignment
        $userModel = new \App\Models\UserModel();
        $nurses = $userModel->getNurses(); // Get all active nurses

        $data = [
            'title' => 'Lab Requests - HMS',
            'user_role' => 'doctor',
            'user_name' => session()->get('name'),
            'requests' => $myRequests,
            'patients' => $patients,
            'nurses' => $nurses, // Add nurses list for selection
        ];

        return view('doctor/lab_request', $data);
    }

    public function createLabRequest()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');

        try {
            $requestModel = new LabTestRequestModel();
            $doctorId = session()->get('user_id');
            
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
            
            // Check if patient is inpatient and get admission_id
            $admissionId = null;
            $patientModel = new PatientModel();
            $patient = $patientModel->find($patientId);
            
            if ($patient && strtolower($patient['patient_type'] ?? '') === 'inpatient') {
                // For inpatients, get the active admission from admissions table
                $db = \Config\Database::connect();
                if ($db->tableExists('admissions')) {
                    $activeAdmission = $db->table('admissions')
                        ->where('patient_id', $patientId)
                        ->where('status', 'Admitted') // Only active admissions
                        ->orderBy('admission_date', 'DESC')
                        ->orderBy('created_at', 'DESC')
                        ->get()
                        ->getRowArray();
                    
                    // Use admission id for inpatients
                    if ($activeAdmission && !empty($activeAdmission['id'])) {
                        $admissionId = $activeAdmission['id'];
                    }
                }
                
                // Fallback: if no active admission found, try appointments (for backward compatibility)
                if (empty($admissionId)) {
                    $appointmentModel = new AppointmentModel();
                    $latestAppointment = $appointmentModel->where('patient_id', $patientId)
                        ->where('status !=', 'cancelled')
                        ->orderBy('appointment_date', 'DESC')
                        ->orderBy('created_at', 'DESC')
                        ->first();
                    
                    if ($latestAppointment && !empty($latestAppointment['id'])) {
                        $admissionId = $latestAppointment['id'];
                    }
                }
            }
            
            // Get test pricing and specimen requirement from master table
            $labTestMasterModel = new \App\Models\LabTestMasterModel();
            $testInfo = $labTestMasterModel->getTestByName($testType);
            
            $price = 0.00;
            $requiresSpecimen = 0;
            
            if ($testInfo) {
                $price = (float)($testInfo['price'] ?? 0.00);
                $requiresSpecimen = (int)($testInfo['requires_specimen'] ?? 0);
            }
            
            // Determine if request needs to go through nurse:
            // 1. If test requires specimen (all patient types: outpatient, inpatient, walk-in)
            // 2. If patient is inpatient (always needs nurse)
            $isInpatient = ($patient && strtolower($patient['patient_type'] ?? '') === 'inpatient');
            $needsNurse = ($requiresSpecimen === 1 || $isInpatient);
            
            // Status: 'pending' means it goes to nurse first, then nurse sends to lab
            // If no specimen needed and not inpatient, it can go directly to lab
            // But per user requirement, ALL requests with specimens must go through nurse
            // And ALL inpatients must go through nurse
            $status = $needsNurse ? 'pending' : 'pending'; // Always pending to go through nurse per requirements
            
            // Get assigned nurse ID if specimen is required
            $assignedNurseId = null;
            if ($requiresSpecimen === 1 || $isInpatient) {
                $assignedNurseId = $this->request->getPost('assigned_nurse_id');
                if (empty($assignedNurseId)) {
                    $assignedNurseId = null; // Allow null if not specified
                }
            }
            
            $data = [
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'admission_id' => $admissionId, // Set admission_id for inpatients
                'test_type' => $testType,
                'price' => $price,
                'requires_specimen' => $requiresSpecimen,
                'assigned_nurse_id' => $assignedNurseId, // Nurse assigned to collect specimen
                'priority' => $priority,
                'status' => $status,
                'requested_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
                'billing_status' => 'unbilled', // Ensure lab test is billable
            ];
            
            // Ensure billing_status column exists before inserting
            $this->ensureLabBillingColumn();
            
            $requestModel->insert($data);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lab test request created successfully!'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error creating lab request: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error creating request: ' . $e->getMessage()
            ]);
        }
    }

    public function getTestInfo()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');
        
        $testType = $this->request->getGet('test_type');
        
        if (empty($testType)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Test type is required']);
        }

        try {
            $labTestMasterModel = new \App\Models\LabTestMasterModel();
            $testInfo = $labTestMasterModel->getTestByName($testType);
            
            if ($testInfo) {
                return $this->response->setJSON([
                    'success' => true,
                    'test' => [
                        'price' => (float)($testInfo['price'] ?? 0.00),
                        'requires_specimen' => (int)($testInfo['requires_specimen'] ?? 0),
                        'test_category' => $testInfo['test_category'] ?? '',
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Test information not found'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching test info: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function reports()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
            return redirect()->to('/login');
        }

        $doctorId = session()->get('user_id');
        $appointmentModel = new AppointmentModel();
        $prescriptionModel = new PrescriptionModel();
        $labRequestModel = new LabTestRequestModel();
        $patientModel = new \App\Models\PatientModel();
        
        // Get filters
        $reportType = $this->request->getGet('type') ?? 'appointments';
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

        $data = [
            'title' => 'Medical Reports - HMS',
            'user_role' => 'doctor',
            'user_name' => session()->get('name'),
            'report_type' => $reportType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'appointments' => [],
            'prescriptions' => [],
            'lab_requests' => [],
            'summary' => [
                'total_appointments' => 0,
                'total_prescriptions' => 0,
                'total_lab_requests' => 0,
                'total_patients' => 0,
            ]
        ];

        try {
            // Appointments Report
            $appointments = $appointmentModel
                ->select('appointments.*, patients.full_name as patient_name, patients.patient_id as patient_code')
                ->join('patients', 'patients.id = appointments.patient_id', 'left')
                ->where('appointments.doctor_id', $doctorId)
                ->where('DATE(appointments.appointment_date) >=', $dateFrom)
                ->where('DATE(appointments.appointment_date) <=', $dateTo)
                ->where('appointments.status !=', 'cancelled')
                ->orderBy('appointments.appointment_date', 'DESC')
                ->orderBy('appointments.appointment_time', 'DESC')
                ->findAll();
            
            $data['appointments'] = $appointments;
            $data['summary']['total_appointments'] = count($appointments);

            // Prescriptions Report
            $prescriptions = $prescriptionModel
                ->select('prescriptions.*, patients.full_name as patient_name, patients.patient_id as patient_code')
                ->join('patients', 'patients.id = prescriptions.patient_id', 'left')
                ->where('prescriptions.doctor_id', $doctorId)
                ->where('DATE(prescriptions.created_at) >=', $dateFrom)
                ->where('DATE(prescriptions.created_at) <=', $dateTo)
                ->where('prescriptions.status !=', 'cancelled')
                ->orderBy('prescriptions.created_at', 'DESC')
                ->findAll();
            
            $data['prescriptions'] = $prescriptions;
            $data['summary']['total_prescriptions'] = count($prescriptions);

            // Lab Requests Report
            $labRequests = $labRequestModel
                ->select('lab_test_requests.*, patients.full_name as patient_name, patients.patient_id as patient_code')
                ->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
                ->where('lab_test_requests.doctor_id', $doctorId)
                ->where('DATE(lab_test_requests.requested_at) >=', $dateFrom)
                ->where('DATE(lab_test_requests.requested_at) <=', $dateTo)
                ->orderBy('lab_test_requests.requested_at', 'DESC')
                ->findAll();
            
            $data['lab_requests'] = $labRequests;
            $data['summary']['total_lab_requests'] = count($labRequests);

            // Get unique patients count
            $patientIds = array_unique(array_merge(
                array_column($appointments, 'patient_id'),
                array_column($prescriptions, 'patient_id'),
                array_column($labRequests, 'patient_id')
            ));
            $data['summary']['total_patients'] = count($patientIds);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching doctor reports: ' . $e->getMessage());
        }

        return view('doctor/reports', $data);
    }

	public function settings()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$model = new SettingModel();
		$defaults = [
			'doctor_clinic_start'        => '09:00',
			'doctor_clinic_end'          => '17:00',
			'doctor_slot_duration'       => '30',
			'doctor_telemed_enabled'     => '1',
			'doctor_auto_notify_patient' => '1',
			'doctor_signature_block'     => "Dr. " . (session()->get('name') ?? 'Doctor') . "\nMediCare Hospital",
		];
		$settings = array_merge($defaults, $model->getAllAsMap());

		$data = [
			'title'     => 'Doctor Settings - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'pageTitle' => 'Settings',
			'settings'  => $settings,
		];

		return view('doctor/settings', $data);
	}

	public function saveSettings()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'doctor') {
			return redirect()->to('/login');
		}

		$model = new SettingModel();
		$post = $this->request->getPost();
		$keys = [
			'doctor_clinic_start',
			'doctor_clinic_end',
			'doctor_slot_duration',
			'doctor_telemed_enabled',
			'doctor_auto_notify_patient',
			'doctor_signature_block',
		];

		foreach ($keys as $key) {
			$model->setValue($key, (string)($post[$key] ?? ''), 'doctor');
		}

		return redirect()->to('/doctor/settings')->with('success', 'Settings saved successfully.');
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




