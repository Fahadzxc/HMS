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
}
