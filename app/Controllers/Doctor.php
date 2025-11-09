<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DoctorScheduleModel;
use App\Models\AppointmentModel;
use App\Models\PrescriptionModel;
use App\Models\MedicationModel;

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
        $builder->select('p.id, p.full_name');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date,
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments WHERE status != "cancelled" AND doctor_id = ' . (int) $doctorId . ') a', 'a.patient_id = p.id AND a.rn = 1', 'inner');
        $builder->orderBy('p.full_name', 'ASC');
        $patients = $builder->get()->getResultArray();

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
                'constraint' => ['pending', 'dispensed', 'cancelled'],
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
}




