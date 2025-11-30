<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DoctorScheduleModel;
use App\Models\AppointmentModel;
use App\Models\PrescriptionModel;
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

		// Get today's appointments
		$today = date('Y-m-d');
		$todayAppointments = $appointmentModel
			->where('doctor_id', $doctorId)
			->where('appointment_date', $today)
			->where('status !=', 'cancelled')
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

		// Get recent appointments (past week, excluding today)
		$weekAgo = date('Y-m-d', strtotime('-7 days'));
		$recentAppointmentsRaw = $appointmentModel
			->where('doctor_id', $doctorId)
			->where('appointment_date <', $today)
			->where('appointment_date >=', $weekAgo)
			->where('status !=', 'cancelled')
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

		$model = new \App\Models\PatientModel();
		$doctorId = session()->get('user_id');
		
		// Get only patients assigned to this doctor through appointments
		$db = \Config\Database::connect();
		$builder = $db->table('patients p');
		$builder->select('p.*, 
						 u.name as assigned_doctor_name,
						 a.appointment_date as last_appointment_date,
						 a.status as appointment_status');
		$builder->join('(SELECT patient_id, doctor_id, appointment_date, status, 
								ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
						 FROM appointments 
						 WHERE status != "cancelled" AND doctor_id = ' . (int)$doctorId . ') a', 'a.patient_id = p.id AND a.rn = 1', 'inner');
		$builder->join('users u', 'u.id = a.doctor_id', 'left');
		$builder->orderBy('p.id', 'DESC');
		
		$patients = $builder->get()->getResultArray();

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
		$builder = $appointmentModel
			->where('doctor_id', $doctorId)
			->where('status !=', 'cancelled')
			->where('status !=', 'no-show')
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

		// Get active inpatient assignments for this doctor from latest appointments
		$builder = $db->table('appointments a');
		$builder->select('a.*, p.full_name as patient_name, p.age, p.gender, r.room_number');
		$builder->join('patients p', 'p.id = a.patient_id', 'left');
		$builder->join('rooms r', 'r.id = a.room_id', 'left');
		$builder->where('a.doctor_id', $doctorId);
		$builder->where('p.patient_type', 'inpatient');
		$builder->where('a.status !=', 'cancelled');
		$builder->orderBy('a.appointment_date', 'DESC');
		$builder->orderBy('a.appointment_time', 'DESC');
		$inpatients = $builder->get()->getResultArray();

		$data = [
			'title' => 'Inpatients - HMS',
			'user_role' => 'doctor',
			'user_name' => session()->get('name'),
			'inpatients' => $inpatients,
		];

		return view('doctor/inpatients', $data);
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

        // Patients assigned to this doctor for selection
        $db = \Config\Database::connect();
        $builder = $db->table('patients p');
        $builder->select('p.id, p.full_name, p.date_of_birth, p.gender');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date,
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments WHERE status != "cancelled" AND doctor_id = ' . (int) $doctorId . ') a', 'a.patient_id = p.id AND a.rn = 1', 'inner');
        $builder->orderBy('p.full_name', 'ASC');
        $patientsRaw = $builder->get()->getResultArray();
        
        // Calculate age for each patient
        $patients = [];
        foreach ($patientsRaw as $pt) {
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

        $data = [
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'appointment_id' => $payload['appointment_id'] ?? null,
            'items_json' => json_encode($items),
            'notes' => $notes,
            'status' => 'pending',
        ];

        if (!$rxModel->insert($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to save prescription', 'errors' => $rxModel->errors()]);
        }

        return $this->response->setJSON(['success' => true]);
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
                'constraint' => ['pending', 'dispensed', 'cancelled', 'completed'],
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
        
        // Get patients assigned to this doctor for selection
        $db = \Config\Database::connect();
        $builder = $db->table('patients p');
        $builder->select('p.id, p.full_name, p.date_of_birth, p.gender, p.patient_id');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date,
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments WHERE status != "cancelled" AND doctor_id = ' . (int) $doctorId . ') a', 'a.patient_id = p.id AND a.rn = 1', 'inner');
        $builder->orderBy('p.full_name', 'ASC');
        $patientsRaw = $builder->get()->getResultArray();
        
        // Calculate age for each patient
        $patients = [];
        foreach ($patientsRaw as $pt) {
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

        $data = [
            'title' => 'Lab Requests - HMS',
            'user_role' => 'doctor',
            'user_name' => session()->get('name'),
            'requests' => $myRequests,
            'patients' => $patients,
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
            
            $data = [
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'test_type' => $testType,
                'priority' => $priority,
                'status' => 'pending',
                'requested_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
            ];
            
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
}




