<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PrescriptionModel;

class Nurse extends Controller
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        // Check if user is logged in and is a nurse
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Nurse Dashboard - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name')
        ];

        // Use the same dashboard system as admin and doctor
        return view('auth/dashboard', $data);
    }

    public function patients()
    {
        $model = new \App\Models\PatientModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get patients with their most recent doctor assignment from appointments
        $db = \Config\Database::connect();
        $builder = $db->table('patients p');
        $builder->select('p.*, 
                         u.name as assigned_doctor_name,
                         a.appointment_date as last_appointment_date,
                         a.status as appointment_status,
                         p.room_number,
                         r.room_number as appointment_room_number');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date, status, room_id, 
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments 
                         WHERE status != "cancelled") a', 'a.patient_id = p.id AND a.rn = 1', 'left');
        $builder->join('users u', 'u.id = a.doctor_id', 'left');
        $builder->join('rooms r', 'r.id = a.room_id', 'left');
        $builder->orderBy('p.id', 'DESC');
        
        $patients = $builder->get()->getResultArray();

        $data = [
            'title' => 'Patient Monitoring - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'patients' => $patients,
        ];

        return view('nurse/patients', $data);
    }


    public function treatmentUpdates()
    {
        $model = new \App\Models\PatientModel();
        
        // Get patients with their most recent doctor assignment
        $db = \Config\Database::connect();
        $builder = $db->table('patients p');
        $builder->select('p.*, 
                         u.name as assigned_doctor_name,
                         a.appointment_date as last_appointment_date,
                         a.status as appointment_status,
                         p.room_number,
                         r.room_number as appointment_room_number');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date, status, room_id, 
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments 
                         WHERE status != "cancelled") a', 'a.patient_id = p.id AND a.rn = 1', 'left');
        $builder->join('users u', 'u.id = a.doctor_id', 'left');
        $builder->join('rooms r', 'r.id = a.room_id', 'left');
        $builder->where('p.status', 'active');
        $builder->orderBy('p.id', 'DESC');
        
        $patients = $builder->get()->getResultArray();

        // Fetch prescriptions for these patients (latest first)
        $prescriptionsByPatient = [];
        if (!empty($patients)) {
            $patientIds = array_column($patients, 'id');
            $db = \Config\Database::connect();
            
            // Check if prescriptions table exists before querying
            if ($db->tableExists('prescriptions')) {
                try {
                    $rxModel = new PrescriptionModel();
                    
                    // Get all prescriptions for patient cards (latest first)
                    $rxRows = $rxModel->whereIn('patient_id', $patientIds)
                                      ->orderBy('created_at', 'DESC')
                                      ->findAll(200);
                    foreach ($rxRows as $rx) {
                        $rx['items'] = json_decode($rx['items_json'] ?? '[]', true) ?: [];
                        $pid = (int) $rx['patient_id'];
                        if (!isset($prescriptionsByPatient[$pid])) {
                            $prescriptionsByPatient[$pid] = [];
                        }
                        // keep only recent few per patient
                        if (count($prescriptionsByPatient[$pid]) < 3) {
                            $prescriptionsByPatient[$pid][] = $rx;
                        }
                    }
                    
                    // Get pending and completed prescriptions for display sections
                    $builder = $db->table('prescriptions p');
                    $builder->select('p.*, pt.full_name as patient_name, u.name as doctor_name');
                    $builder->join('patients pt', 'pt.id = p.patient_id', 'left');
                    $builder->join('users u', 'u.id = p.doctor_id', 'left');
                    $builder->whereIn('p.patient_id', $patientIds);
                    $builder->orderBy('p.created_at', 'DESC');
                    $allPrescriptions = $builder->get()->getResultArray();
                    
                    // Separate pending and completed, calculate duration progress
                    $pendingPrescriptions = [];
                    $completedPrescriptions = [];
                    
                    foreach ($allPrescriptions as $rx) {
                        $items = json_decode($rx['items_json'] ?? '[]', true) ?: [];
                        $firstItem = $items[0] ?? [];
                        $durationStr = $firstItem['duration'] ?? '';
                        
                        // Parse duration (e.g., "5 days", "7 days", "5")
                        $durationDays = 0;
                        if (!empty($durationStr)) {
                            preg_match('/(\d+)/', $durationStr, $matches);
                            if (!empty($matches[1])) {
                                $durationDays = (int)$matches[1];
                            }
                        }
                        
                        $rx['duration_days'] = $durationDays;
                        $status = $rx['status'] ?? 'pending';
                        
                        // Determine progress days (times marked as given)
                        $progressDays = isset($rx['progress_days']) ? (int) $rx['progress_days'] : 0;
                        
                        // Get start date from notes (first time marked as given)
                        $notes = $rx['notes'] ?? '';
                        $startDate = null;
                        if (preg_match('/START_DATE:(\d{4}-\d{2}-\d{2})/', $notes, $matches)) {
                            $startDate = $matches[1];
                        }
                        
                        // Fallback to updated_at if no start date in notes (for old records)
                        if (!$startDate) {
                            $startDate = !empty($rx['updated_at']) ? date('Y-m-d', strtotime($rx['updated_at'])) : date('Y-m-d', strtotime($rx['created_at']));
                        }
                        
                        $startDateTime = new \DateTime($startDate);
                        $startDateTime->setTime(0, 0, 0);
                        
                        $today = new \DateTime();
                        $today->setTime(0, 0, 0);
                        
                        $daysElapsed = $startDateTime->diff($today)->days;
                        $calculatedDay = $daysElapsed + 1; // Day 1 is the first day
                        
                        // Use progress_days if available, otherwise fallback to calculated day
                        if ($progressDays > 0) {
                            $currentDay = $progressDays;
                        } else {
                            $currentDay = $calculatedDay;
                        }
                        
                        if ($durationDays > 0) {
                            $currentDay = min($currentDay, $durationDays);
                        } else {
                            $currentDay = max(1, $currentDay);
                        }
                        
                        $rx['progress_days'] = $currentDay;
                        $rx['days_elapsed'] = $daysElapsed;
                        $rx['current_day'] = $currentDay;
                        $rx['days_remaining'] = $durationDays > 0 ? max(0, $durationDays - $currentDay) : 0;
                        $rx['is_completed_duration'] = ($durationDays > 0 && $currentDay >= $durationDays);
                        
                        $isDailyTracking = ($durationDays > 0);
                        $rx['is_daily_tracking'] = $isDailyTracking;
                        
                        if ($status === 'pending') {
                            $pendingPrescriptions[] = $rx;
                        } else {
                            $needsMoreDoses = ($durationDays > 0 && !$rx['is_completed_duration']);
                            
                            if ($needsMoreDoses) {
                                // Check if it was already given today
                                $lastGivenDate = !empty($rx['updated_at']) ? date('Y-m-d', strtotime($rx['updated_at'])) : null;
                                $todayDate = date('Y-m-d');
                                $wasGivenToday = ($lastGivenDate === $todayDate);
                                
                                if ($wasGivenToday) {
                                    // Already given today, show in completed (with progress indicator)
                                    $completedPrescriptions[] = $rx;
                                } else {
                                    // Not given today yet, show in pending so nurse can mark as given
                                    $pendingPrescriptions[] = $rx;
                                }
                            } else {
                                // Duration complete, show in completed
                                $completedPrescriptions[] = $rx;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error fetching prescriptions: ' . $e->getMessage());
                    // Continue without prescriptions
                }
            }
        }

        // Fetch treatment updates (vital signs history) for these patients
        $treatmentUpdatesByPatient = [];
        if (!empty($patients)) {
            $patientIds = array_column($patients, 'id');
            $db = \Config\Database::connect();
            
            log_message('info', 'Loading treatment updates for patient IDs: ' . json_encode($patientIds));
            
            // Check if table exists before querying
            if ($db->tableExists('treatment_updates')) {
                try {
                    // First, check total records in table
                    $totalRecords = $db->table('treatment_updates')->countAllResults();
                    log_message('info', 'Total records in treatment_updates table: ' . $totalRecords);
                    
                    // Get all records for debugging
                    $allRecords = $db->table('treatment_updates')
                        ->orderBy('created_at', 'DESC')
                        ->limit(10)
                        ->get()
                        ->getResultArray();
                    log_message('info', 'Sample records: ' . json_encode($allRecords));
                    
                    // Now get records for these specific patients
                    $updates = $db->table('treatment_updates')
                        ->whereIn('patient_id', $patientIds)
                        ->orderBy('created_at', 'DESC')
                        ->get()
                        ->getResultArray();
                    
                    log_message('info', 'Loaded ' . count($updates) . ' treatment updates from database for these patients');
                    log_message('info', 'Updates data: ' . json_encode($updates));
                    
                    foreach ($updates as $update) {
                        $pid = (int) $update['patient_id'];
                        log_message('info', 'Processing update for patient ID: ' . $pid);
                        if (!isset($treatmentUpdatesByPatient[$pid])) {
                            $treatmentUpdatesByPatient[$pid] = [];
                        }
                        // Keep only recent entries per patient (last 50)
                        if (count($treatmentUpdatesByPatient[$pid]) < 50) {
                            $treatmentUpdatesByPatient[$pid][] = $update;
                        }
                    }
                    
                    log_message('info', 'Final treatmentUpdatesByPatient structure: ' . json_encode(array_keys($treatmentUpdatesByPatient)));
                } catch (\Exception $e) {
                    log_message('error', 'Error loading treatment updates: ' . $e->getMessage());
                    log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                }
            } else {
                log_message('warning', 'treatment_updates table does not exist');
            }
        } else {
            log_message('warning', 'No patients found to load treatment updates for');
        }

        $data = [
            'title' => 'Treatment Updates - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'patients' => $patients,
            'prescriptionsByPatient' => $prescriptionsByPatient,
            'treatmentUpdatesByPatient' => $treatmentUpdatesByPatient,
            'pending_prescriptions' => $pendingPrescriptions ?? [],
            'completed_prescriptions' => $completedPrescriptions ?? [],
        ];

        return view('nurse/treatment_updates', $data);
    }



    // Patient Monitoring Functions
    public function updateVitals()
    {
        // Handle vital signs update - NEW SIMPLE VERSION
        // Log request details for debugging
        log_message('info', 'updateVitals called. Method: ' . $this->request->getMethod());
        log_message('info', 'Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
        
        // Check if POST method (case-insensitive)
        $method = strtolower($this->request->getMethod());
        if ($method === 'post') {
            $db = \Config\Database::connect();
            
            // Get data from POST or JSON
            $contentType = $this->request->getHeaderLine('Content-Type');
            if (str_contains($contentType, 'application/json')) {
                $data = $this->request->getJSON(true);
            } else {
                $data = $this->request->getPost();
            }
            
            // If no data from JSON, try getVar
            if (empty($data)) {
                $data = $this->request->getVar();
            }
            
            $patientId = $data['patient_id'] ?? null;
            $time = $data['time'] ?? '';
            $bloodPressure = $data['blood_pressure'] ?? '';
            $heartRate = $data['heart_rate'] ?? '';
            $temperature = $data['temperature'] ?? '';
            $oxygenSaturation = $data['oxygen_saturation'] ?? '';
            $nurseName = $data['nurse_name'] ?? session()->get('name');
            $notes = $data['notes'] ?? '';
            
            if (!$patientId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Patient ID is required'
                ]);
            }
            
            // Ensure table exists
            if (!$db->tableExists('treatment_updates')) {
                $this->createTreatmentUpdatesTable($db);
            }
            
            // Insert directly to database
            $insertData = [
                'patient_id' => (int)$patientId,
                'time' => $time ?: null,
                'blood_pressure' => $bloodPressure ?: null,
                'heart_rate' => $heartRate ?: null,
                'temperature' => $temperature ?: null,
                'oxygen_saturation' => $oxygenSaturation ?: null,
                'nurse_name' => $nurseName,
                'notes' => $notes ?: null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            try {
                $db->table('treatment_updates')->insert($insertData);
                $insertId = $db->insertID();
                
                if ($insertId) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Vital signs saved successfully',
                        'id' => $insertId
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to save vital signs'
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request method. Expected POST, got: ' . $this->request->getMethod()
        ]);
    }

    public function updateTreatment()
    {
        // Log that the method was called
        log_message('info', 'updateTreatment method called');
        log_message('info', 'Request method: ' . $this->request->getMethod());
        log_message('info', 'Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
        
        // Handle treatment updates
        if ($this->request->getMethod() === 'post') {
            // Check if it's a JSON request
            $contentType = $this->request->getHeaderLine('Content-Type');
            $isJson = str_contains($contentType, 'application/json');
            
            log_message('info', 'Is JSON request: ' . ($isJson ? 'YES' : 'NO'));
            
            if ($isJson) {
                $rawBody = $this->request->getBody();
                log_message('info', 'Raw request body: ' . $rawBody);
                
                $data = $this->request->getJSON(true);
                
                log_message('info', 'Parsed JSON data: ' . json_encode($data));
                
                if (!$data) {
                    log_message('error', 'Failed to parse JSON data');
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid JSON data'
                    ]);
                }
                
                $patientId = $data['patient_id'] ?? null;
                $time = $data['time'] ?? '';
                $vitals = $data['vitals'] ?? [];
                $notes = $data['notes'] ?? '';
                $nurseName = $data['nurse_name'] ?? session()->get('name');
                
                if (!$patientId) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Patient ID is required'
                    ]);
                }
                
                // Save to database
                $db = \Config\Database::connect();
                
                // Create table if it doesn't exist
                if (!$db->tableExists('treatment_updates')) {
                    log_message('warning', 'treatment_updates table does not exist, creating...');
                    $this->createTreatmentUpdatesTable($db);
                    
                    // Verify table was created
                    if (!$db->tableExists('treatment_updates')) {
                        log_message('error', 'Failed to create treatment_updates table');
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Database table does not exist and could not be created. Please run migrations.'
                        ]);
                    }
                }
                
                $insertData = [
                    'patient_id' => $patientId,
                    'time' => $time ?: null,
                    'blood_pressure' => !empty($vitals['blood_pressure']) ? $vitals['blood_pressure'] : null,
                    'heart_rate' => !empty($vitals['heart_rate']) ? $vitals['heart_rate'] : null,
                    'temperature' => !empty($vitals['temperature']) ? $vitals['temperature'] : null,
                    'oxygen_saturation' => !empty($vitals['oxygen_saturation']) ? $vitals['oxygen_saturation'] : null,
                    'nurse_name' => $nurseName,
                    'notes' => !empty($notes) ? $notes : null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                try {
                    // Log the data being saved
                    log_message('info', 'Saving treatment update: ' . json_encode($insertData));
                    
                    // Test database connection
                    $db->query('SELECT 1');
                    log_message('info', 'Database connection OK');
                    
                    // Ensure table exists
                    if (!$db->tableExists('treatment_updates')) {
                        log_message('warning', 'Table does not exist, creating...');
                        $this->createTreatmentUpdatesTable($db);
                    }
                    
                    // Insert data
                    $result = $db->table('treatment_updates')->insert($insertData);
                    log_message('info', 'Insert result: ' . ($result ? 'TRUE' : 'FALSE'));
                    
                    $insertId = $db->insertID();
                    log_message('info', 'Insert ID: ' . ($insertId ?: 'NULL'));
                    
                    // Verify the insert worked by querying the record
                    if ($insertId) {
                        $verify = $db->table('treatment_updates')->where('id', $insertId)->get()->getRowArray();
                        log_message('info', 'Verified record: ' . json_encode($verify));
                    }
                    
                    // Verify the insert worked
                    if (!$insertId) {
                        log_message('error', 'Failed to get insert ID after treatment update insert');
                        // Try to get last error
                        $error = $db->error();
                        log_message('error', 'Database error: ' . json_encode($error));
                        
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Failed to save treatment update - no insert ID returned. Error: ' . json_encode($error)
                        ]);
                    }
                    
                    log_message('info', 'Treatment update saved successfully with ID: ' . $insertId);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Treatment update saved successfully',
                        'data' => [
                            'id' => $insertId,
                            'patient_id' => $patientId,
                            'time' => $time,
                            'nurse_name' => $nurseName
                        ]
                    ]);
                } catch (\Exception $e) {
                    log_message('error', 'Error saving treatment update: ' . $e->getMessage());
                    log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                    
                    // Get database error if available
                    $dbError = $db->error();
                    if ($dbError['code'] != 0) {
                        log_message('error', 'Database error code: ' . $dbError['code'] . ', message: ' . $dbError['message']);
                    }
                    
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error saving treatment update: ' . $e->getMessage() . ' | DB Error: ' . json_encode($dbError)
                    ]);
                }
            } else {
                // Handle form POST
                $patientId = $this->request->getPost('patient_id');
                return redirect()->back()->with('success', 'Treatment updated successfully for Patient ID: ' . $patientId);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }

    private function createTreatmentUpdatesTable($db)
    {
        try {
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
            'time' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'blood_pressure' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'heart_rate' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'temperature' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'oxygen_saturation' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nurse_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $forge->addKey('id', true);
        $forge->addKey('patient_id');
        $forge->addKey('created_at');
        $forge->createTable('treatment_updates', true);
        } catch (\Exception $e) {
            log_message('error', 'Failed to create treatment_updates table: ' . $e->getMessage());
            // Try direct SQL as fallback
            try {
                $sql = "CREATE TABLE IF NOT EXISTS `treatment_updates` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `patient_id` int(11) unsigned NOT NULL,
                    `time` varchar(20) DEFAULT NULL,
                    `blood_pressure` varchar(50) DEFAULT NULL,
                    `heart_rate` varchar(50) DEFAULT NULL,
                    `temperature` varchar(50) DEFAULT NULL,
                    `oxygen_saturation` varchar(50) DEFAULT NULL,
                    `nurse_name` varchar(255) DEFAULT NULL,
                    `notes` text DEFAULT NULL,
                    `created_at` datetime DEFAULT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `patient_id` (`patient_id`),
                    KEY `created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                $db->query($sql);
            } catch (\Exception $e2) {
                log_message('error', 'Failed to create treatment_updates table via SQL: ' . $e2->getMessage());
            }
        }
    }

    public function assignPatient()
    {
        // Handle patient assignment to nurse
        if ($this->request->getMethod() === 'post') {
            $patientId = $this->request->getPost('patient_id');
            
            // Here you would save assignment to database
            // For now, we'll just redirect back with success message
            
            return redirect()->back()->with('success', 'Patient ID: ' . $patientId . ' assigned successfully.');
        }

        return redirect()->back()->with('error', 'Invalid request method.');
    }

    public function markPrescriptionAsGiven()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $prescriptionId = $this->request->getPost('prescription_id') ?? $this->request->getJSON(true)['prescription_id'] ?? null;

        if (!$prescriptionId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prescription ID is required']);
        }

        $rxModel = new PrescriptionModel();
        $prescription = $rxModel->find($prescriptionId);

        if (!$prescription) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prescription not found']);
        }

        // Update status to completed - use direct DB query to bypass validation
        $db = \Config\Database::connect();
        
        // First, try to update the ENUM if 'completed' is not in the list
        try {
            $db->query("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('pending', 'dispensed', 'cancelled', 'completed') DEFAULT 'pending'");
        } catch (\Exception $e) {
            // ENUM might already be updated, or table doesn't exist - continue
            log_message('debug', 'ENUM update attempt: ' . $e->getMessage());
        }
        
        // Ensure progress tracking column exists
        try {
            $db->query("ALTER TABLE prescriptions ADD COLUMN progress_days INT DEFAULT 0");
        } catch (\Exception $e) {
            // Column might already exist
            log_message('debug', 'progress_days column check: ' . $e->getMessage());
        }
        
        // Determine prescription duration (in days)
        $durationDays = 0;
        $itemsForDuration = json_decode($prescription['items_json'] ?? '[]', true) ?: [];
        $durationSource = $itemsForDuration[0]['duration'] ?? '';
        if (!empty($durationSource) && preg_match('/(\d+)/', $durationSource, $durationMatch)) {
            $durationDays = (int) $durationMatch[1];
        }
        
        // Check if this is the first time marking as given
        $currentStatus = $prescription['status'] ?? 'pending';
        $wasCompleted = ($currentStatus === 'completed');
        
        // Get or set the start date (first time it was marked as given)
        $notes = $prescription['notes'] ?? '';
        $startDate = null;
        
        // Try to extract start date from notes (format: START_DATE:YYYY-MM-DD)
        if (preg_match('/START_DATE:(\d{4}-\d{2}-\d{2})/', $notes, $matches)) {
            $startDate = $matches[1];
        }
        
        // If no start date found and this is first time, set it
        if (!$startDate && !$wasCompleted) {
            $startDate = date('Y-m-d');
            // Store start date in notes
            $notes = trim($notes);
            if (!empty($notes) && !str_contains($notes, 'START_DATE:')) {
                $notes .= "\nSTART_DATE:" . $startDate;
            } elseif (empty($notes)) {
                $notes = "START_DATE:" . $startDate;
            }
        }
        
        // Determine progress days (how many days/doses already given)
        $existingProgress = isset($prescription['progress_days']) ? (int) $prescription['progress_days'] : 0;
        $lastGivenDate = !empty($prescription['updated_at']) ? date('Y-m-d', strtotime($prescription['updated_at'])) : null;
        $todayDate = date('Y-m-d');
        $shouldIncrementProgress = true;
        if ($lastGivenDate === $todayDate && $existingProgress > 0) {
            // Already marked as given today, don't increment to avoid double counting
            $shouldIncrementProgress = false;
        }
        
        if ($shouldIncrementProgress) {
            $existingProgress++;
            if ($durationDays > 0) {
                $existingProgress = min($existingProgress, $durationDays);
            }
        }
        
        // Update using direct query
        $builder = $db->table('prescriptions');
        $builder->where('id', $prescriptionId);
        
        // Always update status to completed and updated_at (for last given check)
        $updateData = [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s'), // Track last given date
            'progress_days' => $existingProgress
        ];
        
        // If first time, also update notes with start date
        if (!$wasCompleted && $startDate) {
            $updateData['notes'] = $notes;
        }
        
        $result = $builder->update($updateData);

        if ($result && !$wasCompleted) {
            // Auto-create bill for prescription medications (first time only)
            $this->autoCreatePrescriptionBill($prescriptionId, $prescription);
        }

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Prescription marked as given successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update prescription status. Please check database connection.'
            ]);
        }
    }
    
    private function autoCreatePrescriptionBill($prescriptionId, $prescription)
    {
        try {
            // Check if bill already exists for this prescription
            $db = \Config\Database::connect();
            $existingBill = $db->table('bills')
                ->where('prescription_id', $prescriptionId)
                ->first();
            
            if ($existingBill) {
                log_message('debug', "Bill already exists for prescription #{$prescriptionId}");
                return; // Bill already exists
            }
            
            // Ensure billing tables exist
            $this->ensureBillingTables();
            
            $billingModel = new \App\Models\BillingModel();
            $billItemModel = new \App\Models\BillItemModel();
            
            // Parse prescription items
            $itemsJson = $prescription['items_json'] ?? '[]';
            $items = json_decode($itemsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', "Failed to parse prescription items JSON for prescription #{$prescriptionId}: " . json_last_error_msg());
                return;
            }
            
            if (empty($items) || !is_array($items)) {
                log_message('debug', "No items found in prescription #{$prescriptionId}");
                return; // No items to bill
            }
            
            // Calculate medication costs
            $subtotal = 0;
            $billItems = [];
            
            foreach ($items as $item) {
                if (!is_array($item)) continue;
                
                // Get medication name - check multiple possible fields
                $medicationName = $item['name'] ?? $item['medication'] ?? $item['med_name'] ?? '';
                
                if (empty($medicationName)) {
                    // Try to get from med_id if name is not available
                    if (!empty($item['med_id'])) {
                        $medModel = new \App\Models\MedicationModel();
                        $med = $medModel->find($item['med_id']);
                        if ($med) {
                            $medicationName = $med['name'] ?? '';
                            if (!empty($med['strength'])) {
                                $medicationName .= ' ' . $med['strength'];
                            }
                        }
                    }
                }
                
                if (empty($medicationName)) {
                    log_message('debug', "Skipping item without name in prescription #{$prescriptionId}");
                    continue; // Skip items without name
                }
                
                // Get quantity - default to 1 if not specified
                $quantity = 1;
                if (isset($item['quantity']) && $item['quantity'] > 0) {
                    $quantity = floatval($item['quantity']);
                } else {
                    // Try to calculate quantity from duration if available
                    $durationStr = $item['duration'] ?? '';
                    if (!empty($durationStr)) {
                        preg_match('/(\d+)/', $durationStr, $matches);
                        if (!empty($matches[1])) {
                            $durationDays = (int)$matches[1];
                            // Estimate quantity based on frequency
                            $frequency = $item['frequency'] ?? '';
                            if (strpos(strtolower($frequency), '2x') !== false || strpos(strtolower($frequency), 'twice') !== false) {
                                $quantity = $durationDays * 2;
                            } elseif (strpos(strtolower($frequency), '3x') !== false || strpos(strtolower($frequency), 'thrice') !== false) {
                                $quantity = $durationDays * 3;
                            } else {
                                $quantity = $durationDays; // Once a day
                            }
                        }
                    }
                }
                
                $dosage = $item['dosage'] ?? '';
                $frequency = $item['frequency'] ?? '';
                $duration = $item['duration'] ?? '';
                $mealInstruction = $item['meal_instruction'] ?? '';
                
                // Get medication price (default pricing if not in database)
                $unitPrice = $this->getMedicationPrice($medicationName);
                $totalPrice = $quantity * $unitPrice;
                
                $subtotal += $totalPrice;
                
                $description = [];
                if ($dosage) $description[] = "Dosage: {$dosage}";
                if ($frequency) $description[] = "Frequency: {$frequency}";
                if ($mealInstruction) $description[] = "Meal: {$mealInstruction}";
                if ($duration) $description[] = "Duration: {$duration}";
                
                $billItems[] = [
                    'item_type' => 'medication',
                    'item_name' => $medicationName,
                    'description' => implode(', ', $description),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'reference_id' => $prescriptionId,
                ];
            }
            
            if ($subtotal <= 0 || empty($billItems)) {
                log_message('debug', "No valid items to bill for prescription #{$prescriptionId}");
                return; // No cost to bill
            }
            
            // Calculate tax (12%)
            $tax = $subtotal * 0.12;
            $totalAmount = $subtotal + $tax;
            
            // Generate bill number
            $billNumber = $billingModel->generateBillNumber();
            
            // Create bill
            $billData = [
                'bill_number' => $billNumber,
                'patient_id' => $prescription['patient_id'],
                'prescription_id' => $prescriptionId,
                'bill_type' => 'prescription',
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance' => $totalAmount,
                'status' => 'pending',
                'due_date' => date('Y-m-d', strtotime('+7 days')),
                'notes' => 'Auto-generated from prescription #' . $prescriptionId,
                'created_by' => session()->get('user_id'),
            ];
            
            $billId = $billingModel->insert($billData);
            
            if (!$billId) {
                log_message('error', "Failed to create bill for prescription #{$prescriptionId}: " . json_encode($billingModel->errors()));
                return;
            }
            
            // Create bill items
            foreach ($billItems as $item) {
                $item['bill_id'] = $billId;
                if (!$billItemModel->insert($item)) {
                    log_message('error', "Failed to create bill item: " . json_encode($billItemModel->errors()));
                }
            }
            
            log_message('info', "Auto-created bill #{$billNumber} for prescription #{$prescriptionId}, amount: ₱{$totalAmount}");
            
        } catch (\Exception $e) {
            log_message('error', "Error auto-creating bill for prescription #{$prescriptionId}: " . $e->getMessage());
        }
    }
    
    private function getMedicationPrice($medicationName)
    {
        // Default medication pricing (can be customized)
        $defaultPrices = [
            'amoxicillin' => 50.00,
            'paracetamol' => 25.00,
            'ibuprofen' => 30.00,
            'aspirin' => 20.00,
            'metformin' => 40.00,
            'losartan' => 45.00,
            'atorvastatin' => 60.00,
            'omeprazole' => 35.00,
            'cefuroxime' => 80.00,
            'azithromycin' => 75.00,
        ];
        
        // Try to get price from database first
        $db = \Config\Database::connect();
        if ($db->tableExists('medications')) {
            $med = $db->table('medications')
                ->where('name', $medicationName)
                ->orLike('name', $medicationName)
                ->first();
            
            if ($med && isset($med['price'])) {
                return floatval($med['price']);
            }
        }
        
        // Use default pricing based on medication name
        $nameLower = strtolower($medicationName);
        foreach ($defaultPrices as $key => $price) {
            if (strpos($nameLower, $key) !== false) {
                return $price;
            }
        }
        
        // Default price if not found
        return 50.00;
    }
    
    private function ensureBillingTables()
    {
        $db = \Config\Database::connect();
        
        // Create bills table if not exists
        if (!$db->tableExists('bills')) {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'bill_number' => ['type' => 'VARCHAR', 'constraint' => 50],
                'patient_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'appointment_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'prescription_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'lab_test_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'room_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'bill_type' => ['type' => 'ENUM', 'constraint' => ['appointment', 'prescription', 'lab_test', 'room', 'consultation', 'procedure', 'other'], 'default' => 'other'],
                'subtotal' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'discount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'tax' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'total_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'paid_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'balance' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'], 'default' => 'pending'],
                'due_date' => ['type' => 'DATE', 'null' => true],
                'payment_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('patient_id');
            $forge->addKey('bill_number');
            $forge->createTable('bills', true);
        }
        
        // Create bill_items table if not exists
        if (!$db->tableExists('bill_items')) {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'bill_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'item_type' => ['type' => 'VARCHAR', 'constraint' => 50],
                'item_name' => ['type' => 'VARCHAR', 'constraint' => 255],
                'description' => ['type' => 'TEXT', 'null' => true],
                'quantity' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 1],
                'unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'total_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'reference_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('bill_id');
            $forge->createTable('bill_items', true);
        }
    }
}
