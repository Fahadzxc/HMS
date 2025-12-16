<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PrescriptionModel;
use App\Models\SettingModel;
use App\Models\LabTestRequestModel;
use App\Models\LabTestPriceModel;
use App\Models\LabSpecimenTrackingModel;
use App\Models\PatientModel;

class Nurse extends Controller
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return redirect()->to('/login');
        }

        $nurseName = session()->get('name') ?? 'Nurse';
        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        $metrics = [
            'assignedPatients' => 0,
            'pendingMedications' => 0,
            'vitalChecksDue' => 0,
            'dischargesToday' => 0,
        ];
        $todayTasks = [];
        $patientsUnderCare = [];
        $recentActivities = [];

        // Assigned patients & recent activities from treatment updates
        if ($db->tableExists('treatment_updates')) {
            $metrics['assignedPatients'] = (int) ($db->table('treatment_updates')
                ->select('COUNT(DISTINCT patient_id) as total')
                ->where('nurse_name', $nurseName)
                ->get()
                ->getRow('total') ?? 0);

            // Vital checks due (no update today)
            $patientsLastUpdate = $db->table('treatment_updates')
                ->select('patient_id, MAX(created_at) as last_update')
                ->where('nurse_name', $nurseName)
                ->groupBy('patient_id')
                ->get()
                ->getResultArray();

            $metrics['vitalChecksDue'] = count(array_filter($patientsLastUpdate, static function ($row) use ($today) {
                return !empty($row['last_update']) && date('Y-m-d', strtotime($row['last_update'])) < $today;
            }));

            $patientsUnderCare = $db->table('treatment_updates tu')
                ->select('tu.*, p.full_name as patient_name, p.patient_id as patient_code')
                ->join('patients p', 'p.id = tu.patient_id', 'left')
                ->where('tu.nurse_name', $nurseName)
                ->orderBy('tu.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            $recentActivities = $db->table('treatment_updates')
                ->select('patient_id, notes, created_at, nurse_name, blood_pressure, heart_rate, temperature')
                ->where('nurse_name', $nurseName)
                ->orderBy('created_at', 'DESC')
                ->limit(6)
                ->get()
                ->getResultArray();
        }

        // Pending medications
        if ($db->tableExists('prescriptions')) {
            $metrics['pendingMedications'] = (int) ($db->table('prescriptions')
                ->whereIn('status', ['pending', 'in-progress', 'scheduled'])
                ->countAllResults() ?? 0);

            $todayTasks = $db->table('prescriptions pr')
                ->select('pr.id, pr.created_at, pr.status, p.full_name as patient_name')
                ->join('patients p', 'p.id = pr.patient_id', 'left')
                ->whereIn('pr.status', ['pending', 'in-progress'])
                ->orderBy('pr.created_at', 'DESC')
                ->limit(4)
                ->get()
                ->getResultArray();
        }

        // Discharges today from patients table
        if ($db->tableExists('patients')) {
            $metrics['dischargesToday'] = (int) ($db->table('patients')
                ->where('status', 'discharged')
                ->where('DATE(updated_at)', $today)
                ->countAllResults() ?? 0);
        }

        $data = [
            'title' => 'Nurse Dashboard - HMS',
            'user_role' => 'nurse',
            'user_name' => $nurseName,
            'metrics' => $metrics,
            'todayTasks' => $todayTasks,
            'patientsUnderCare' => $patientsUnderCare,
            'recentActivities' => $recentActivities,
        ];

        return view('nurse/dashboard', $data);
    }

    public function patients()
    {
        $model = new \App\Models\PatientModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get patients with their doctor from appointments OR admissions
        // Exclude discharged patients
        $db = \Config\Database::connect();
        
        $sql = "
            SELECT p.*, 
                   COALESCE(adm_doc.name, appt_doc.name) as assigned_doctor_name,
                   COALESCE(adm.admission_date, a.appointment_date) as last_appointment_date,
                   COALESCE(adm.status, a.status) as appointment_status,
                   COALESCE(adm_room.room_number, appt_room.room_number, p.room_number) as room_number
            FROM patients p
            LEFT JOIN (
                SELECT patient_id, doctor_id, appointment_date, status, room_id,
                       ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                FROM appointments 
                WHERE status != 'cancelled'
            ) a ON a.patient_id = p.id AND a.rn = 1
            LEFT JOIN users appt_doc ON appt_doc.id = a.doctor_id
            LEFT JOIN rooms appt_room ON appt_room.id = a.room_id
            LEFT JOIN admissions adm ON adm.patient_id = p.id AND adm.status = 'Admitted'
            LEFT JOIN users adm_doc ON adm_doc.id = adm.doctor_id
            LEFT JOIN rooms adm_room ON adm_room.id = adm.room_id
            WHERE p.status != 'discharged'
            ORDER BY p.id DESC
        ";
        
        $patients = $db->query($sql)->getResultArray();
        
        // Get pending discharge orders (inpatients with discharge ordered but not ready)
        $dischargeOrders = $db->table('admissions a')
            ->select('a.*, p.full_name as patient_name, p.contact, r.room_number, r.room_type, u.name as doctor_name, ord.name as ordered_by_name')
            ->join('patients p', 'p.id = a.patient_id', 'left')
            ->join('rooms r', 'r.id = a.room_id', 'left')
            ->join('users u', 'u.id = a.doctor_id', 'left')
            ->join('users ord', 'ord.id = a.discharge_ordered_by', 'left')
            ->where('a.status', 'Admitted')
            ->where('a.discharge_ordered_at IS NOT NULL')
            ->where('a.discharge_ready_at IS NULL')
            ->orderBy('a.discharge_ordered_at', 'ASC')
            ->get()->getResultArray();
        
        // Get patients ready for final discharge (ready but not yet discharged)
        $readyForDischarge = $db->table('admissions a')
            ->select('a.*, p.full_name as patient_name, p.contact, r.room_number, r.room_type, u.name as doctor_name, rdy.name as ready_by_name')
            ->join('patients p', 'p.id = a.patient_id', 'left')
            ->join('rooms r', 'r.id = a.room_id', 'left')
            ->join('users u', 'u.id = a.doctor_id', 'left')
            ->join('users rdy', 'rdy.id = a.discharge_ready_by', 'left')
            ->where('a.status', 'Admitted')
            ->where('a.discharge_ready_at IS NOT NULL')
            ->orderBy('a.discharge_ready_at', 'ASC')
            ->get()->getResultArray();
        
        // Add billing status for each ready patient
        if ($db->tableExists('bills')) {
            foreach ($readyForDischarge as &$ready) {
                $patientId = $ready['patient_id'];
                
                // Get all bills for this patient
                $bills = $db->table('bills')
                    ->where('patient_id', $patientId)
                    ->whereIn('status', ['pending', 'partial', 'overdue'])
                    ->get()
                    ->getResultArray();
                
                $totalBalance = 0;
                $totalBills = 0;
                $unpaidBills = 0;
                
                foreach ($bills as $bill) {
                    $balance = floatval($bill['balance'] ?? 0);
                    $totalBalance += $balance;
                    $totalBills++;
                    if ($balance > 0) {
                        $unpaidBills++;
                    }
                }
                
                $ready['billing_status'] = $totalBalance <= 0 ? 'fully_paid' : 'unpaid';
                $ready['total_balance'] = $totalBalance;
                $ready['unpaid_bills_count'] = $unpaidBills;
                $ready['total_bills_count'] = $totalBills;
            }
            unset($ready);
        } else {
            // If bills table doesn't exist, mark all as fully paid
            foreach ($readyForDischarge as &$ready) {
                $ready['billing_status'] = 'fully_paid';
                $ready['total_balance'] = 0;
                $ready['unpaid_bills_count'] = 0;
                $ready['total_bills_count'] = 0;
            }
            unset($ready);
        }

        $data = [
            'title' => 'Patient Monitoring - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'patients' => $patients,
            'discharge_orders' => $dischargeOrders,
            'ready_for_discharge' => $readyForDischarge,
        ];

        return view('nurse/patients', $data);
    }
    
    public function markDischargeReady()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $json = $this->request->getJSON(true);
        if (!$json) {
            $json = $this->request->getPost();
        }
        
        $admissionId = $json['admission_id'] ?? null;
        
        if (!$admissionId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admission ID is required']);
        }
        
        $db = \Config\Database::connect();
        $admission = $db->table('admissions')->where('id', $admissionId)->get()->getRowArray();
        
        if (!$admission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admission not found']);
        }
        
        if (empty($admission['discharge_ordered_at'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No discharge order found for this patient']);
        }
        
        if (!empty($admission['discharge_ready_at'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Patient already marked as ready']);
        }
        
        // Mark patient as ready for discharge
        $db->table('admissions')->where('id', $admissionId)->update([
            'discharge_ready_at' => date('Y-m-d H:i:s'),
            'discharge_ready_by' => session()->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->response->setJSON(['success' => true, 'message' => 'Patient marked as ready for discharge']);
    }
    
    public function finalDischarge()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $json = $this->request->getJSON(true);
        if (!$json) {
            $json = $this->request->getPost();
        }
        
        $admissionId = $json['admission_id'] ?? null;
        
        if (!$admissionId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admission ID is required']);
        }
        
        $db = \Config\Database::connect();
        $admission = $db->table('admissions')->where('id', $admissionId)->get()->getRowArray();
        
        if (!$admission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admission not found']);
        }
        
        if (empty($admission['discharge_ready_at'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Patient must be marked as ready first']);
        }
        
        // Check if billing is fully paid - REQUIRED for discharge
        $patientId = $admission['patient_id'];
        $billingCleared = true;
        $totalBalance = 0;
        $unpaidBills = [];
        
        if ($db->tableExists('bills')) {
            $bills = $db->table('bills')
                ->where('patient_id', $patientId)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->get()
                ->getResultArray();
            
            foreach ($bills as $bill) {
                $balance = floatval($bill['balance'] ?? 0);
                if ($balance > 0) {
                    $billingCleared = false;
                    $totalBalance += $balance;
                    $unpaidBills[] = [
                        'bill_number' => $bill['bill_number'] ?? 'N/A',
                        'balance' => $balance
                    ];
                }
            }
        }
        
        if (!$billingCleared) {
            $billNumbers = implode(', ', array_column($unpaidBills, 'bill_number'));
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Cannot discharge patient. Billing is not fully paid. Outstanding balance: ₱' . number_format($totalBalance, 2) . '. Unpaid bills: ' . $billNumbers
            ]);
        }
        
        // Update admission status to Discharged
        $db->table('admissions')->where('id', $admissionId)->update([
            'status' => 'Discharged',
            'discharged_at' => date('Y-m-d H:i:s'),
            'discharged_by' => session()->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update room occupancy - decrement if room was assigned
        if (!empty($admission['room_id'])) {
            $roomModel = new \App\Models\RoomModel();
            $roomModel->updateOccupancy($admission['room_id'], false); // false = decrement
        }
        
        // Update patient status to discharged (keep patient_type as inpatient for history)
        $patientModel = new \App\Models\PatientModel();
        $patientModel->update($admission['patient_id'], [
            // Don't change patient_type - keep as 'inpatient' for historical record
            'status' => 'discharged',
            'discharge_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->response->setJSON(['success' => true, 'message' => 'Patient discharged successfully. Room is now available.']);
    }


    public function treatmentUpdates()
    {
        $model = new \App\Models\PatientModel();
        
        // Get patients with their doctor assignment from BOTH appointments AND admissions
        $db = \Config\Database::connect();
        
        // Use COALESCE to prefer admission data for inpatients
        // Exclude walk-in patients who only have lab test appointments (no consultation needed)
        $sql = "
            SELECT p.*, 
                   COALESCE(adm_doc.name, appt_doc.name) as assigned_doctor_name,
                   COALESCE(adm.admission_date, a.appointment_date) as last_appointment_date,
                   COALESCE(adm.status, a.status) as appointment_status,
                         p.room_number,
                   COALESCE(adm_room.room_number, appt_room.room_number) as appointment_room_number
            FROM patients p
            LEFT JOIN (
                SELECT patient_id, doctor_id, appointment_date, status, room_id, appointment_type,
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments 
                WHERE status != 'cancelled'
            ) a ON a.patient_id = p.id AND a.rn = 1
            LEFT JOIN users appt_doc ON appt_doc.id = a.doctor_id
            LEFT JOIN rooms appt_room ON appt_room.id = a.room_id
            LEFT JOIN admissions adm ON adm.patient_id = p.id AND adm.status = 'Admitted'
            LEFT JOIN users adm_doc ON adm_doc.id = adm.doctor_id
            LEFT JOIN rooms adm_room ON adm_room.id = adm.room_id
            WHERE p.status = 'active'
            AND (
                -- Include inpatients (admitted patients) - they need nurse care
                adm.patient_id IS NOT NULL
                OR
                -- Include patients with doctor assignments (consultation appointments)
                a.doctor_id IS NOT NULL
                OR
                -- Include patients with consultation appointment type
                a.appointment_type = 'consultation'
                OR
                -- Include patients who have at least one consultation appointment (even if latest is lab test)
                EXISTS (
                    SELECT 1 FROM appointments ap
                    WHERE ap.patient_id = p.id
                    AND ap.status != 'cancelled'
                    AND (ap.appointment_type = 'consultation' OR ap.doctor_id IS NOT NULL)
                )
            )
            -- Exclude walk-in patients who ONLY have lab test appointments (no consultation, no doctor, not admitted)
            AND NOT (
                -- Patient is not admitted
                adm.patient_id IS NULL
                AND
                -- Latest appointment is walk-in or lab test only
                (a.appointment_type IN ('walk-in', 'laboratory_test', 'lab_test') OR a.appointment_type IS NULL)
                AND
                -- No doctor assigned
                a.doctor_id IS NULL
                AND
                -- No consultation appointments exist
                NOT EXISTS (
                    SELECT 1 FROM appointments ap
                    WHERE ap.patient_id = p.id
                    AND ap.status != 'cancelled'
                    AND (ap.appointment_type = 'consultation' OR ap.doctor_id IS NOT NULL)
                )
            )
            ORDER BY p.id DESC
        ";
        
        $patients = $db->query($sql)->getResultArray();

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

                    $pendingPrescriptions = $pendingPrescriptions ?? [];
                    $completedPrescriptions = $completedPrescriptions ?? [];

                    $allForStock = array_merge($pendingPrescriptions, $completedPrescriptions);
                    $medicationStockMap = $this->buildMedicationStockMap($allForStock);
                    $this->attachStockInfoToPrescriptions($pendingPrescriptions, $medicationStockMap);
                    $this->attachStockInfoToPrescriptions($completedPrescriptions, $medicationStockMap);

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
            $height = $data['height'] ?? '';
            $weight = $data['weight'] ?? '';
            $bmi = $data['bmi'] ?? '';
            $nurseName = $data['nurse_name'] ?? session()->get('name');
            $notes = $data['notes'] ?? '';
            
            if (!$patientId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Patient ID is required'
                ]);
            }
            
            // Height/Weight no longer required for inpatient patients (per request)
            
            // Ensure table exists
            if (!$db->tableExists('treatment_updates')) {
                $this->createTreatmentUpdatesTable($db);
            } else {
                // Check if height, weight, and BMI columns exist, if not add them
                $fields = $db->getFieldData('treatment_updates');
                $fieldNames = array_column($fields, 'name');
                
                if (!in_array('height', $fieldNames)) {
                    try {
                        $db->query("ALTER TABLE treatment_updates ADD COLUMN height VARCHAR(50) DEFAULT NULL AFTER oxygen_saturation");
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to add height column: ' . $e->getMessage());
                    }
                }
                if (!in_array('weight', $fieldNames)) {
                    try {
                        $db->query("ALTER TABLE treatment_updates ADD COLUMN weight VARCHAR(50) DEFAULT NULL AFTER height");
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to add weight column: ' . $e->getMessage());
                    }
                }
                if (!in_array('bmi', $fieldNames)) {
                    try {
                        $db->query("ALTER TABLE treatment_updates ADD COLUMN bmi VARCHAR(50) DEFAULT NULL AFTER weight");
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to add BMI column: ' . $e->getMessage());
                    }
                }
            }
            
            // Insert directly to database
            $insertData = [
                'patient_id' => (int)$patientId,
                'time' => $time ?: null,
                'blood_pressure' => $bloodPressure ?: null,
                'heart_rate' => $heartRate ?: null,
                'temperature' => $temperature ?: null,
                'oxygen_saturation' => $oxygenSaturation ?: null,
                'height' => $height ?: null,
                'weight' => $weight ?: null,
                'bmi' => $bmi ?: null,
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

    protected function buildMedicationStockMap(array $prescriptions): array
    {
        if (empty($prescriptions)) {
            return [];
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('pharmacy_inventory')) {
            return [];
        }

        $medicationIds = [];
        $medicationNames = [];

        foreach ($prescriptions as $rx) {
            $items = json_decode($rx['items_json'] ?? '[]', true) ?: [];
            foreach ($items as $item) {
                if (!empty($item['med_id'])) {
                    $medicationIds[] = (int) $item['med_id'];
                }
                if (!empty($item['name'])) {
                    $medicationNames[] = trim($item['name']);
                }
            }
        }

        $medicationIds = array_values(array_unique(array_filter($medicationIds)));
        $medicationNames = array_values(array_unique(array_filter($medicationNames)));

        if (empty($medicationIds) && empty($medicationNames)) {
            return [];
        }

        $stockMap = [];

        if (!empty($medicationIds)) {
            $records = $db->table('pharmacy_inventory')
                ->whereIn('medication_id', $medicationIds)
                ->get()
                ->getResultArray();

            foreach ($records as $record) {
                $formatted = $this->formatStockRecord($record);
                if (!empty($record['medication_id'])) {
                    $stockMap['id:' . (int)$record['medication_id']] = $formatted;
                }
                if (!empty($record['name'])) {
                    $stockMap['name:' . strtolower(trim($record['name']))] = $formatted;
                }
            }
        }

        if (!empty($medicationNames)) {
            $remainingNames = array_filter($medicationNames, function ($name) use ($stockMap) {
                return !isset($stockMap['name:' . strtolower($name)]);
            });

            if (!empty($remainingNames)) {
                $nameRecords = $db->table('pharmacy_inventory')
                    ->whereIn('name', $remainingNames)
                    ->get()
                    ->getResultArray();

                foreach ($nameRecords as $record) {
                    if (empty($record['name'])) {
                        continue;
                    }
                    $key = 'name:' . strtolower(trim($record['name']));
                    if (!isset($stockMap[$key])) {
                        $stockMap[$key] = $this->formatStockRecord($record);
                    }
                }
            }
        }

        return $stockMap;
    }

    protected function formatStockRecord(array $record): array
    {
        $quantity = (int)($record['stock_quantity'] ?? 0);
        $reorderLevel = (int)($record['reorder_level'] ?? 0);
        $status = 'unknown';

        if ($quantity <= 0) {
            $status = 'out_of_stock';
        } elseif ($reorderLevel > 0 && $quantity <= $reorderLevel) {
            $status = 'low_stock';
        } else {
            $status = 'in_stock';
        }

        return [
            'quantity' => $quantity,
            'reorder_level' => $reorderLevel,
            'status' => $status,
        ];
    }

    protected function attachStockInfoToPrescriptions(array &$prescriptions, array $stockMap): void
    {
        if (empty($prescriptions)) {
            return;
        }

        foreach ($prescriptions as &$rx) {
            $items = json_decode($rx['items_json'] ?? '[]', true) ?: [];
            foreach ($items as &$item) {
                $item['stock'] = $this->resolveStockForItem($item, $stockMap);
            }
            $rx['items_with_stock'] = $items;
            $rx['first_item_stock'] = $items[0]['stock'] ?? null;
        }
    }

    protected function resolveStockForItem(array $item, array $stockMap): ?array
    {
        if (!empty($item['med_id'])) {
            $key = 'id:' . (int)$item['med_id'];
            if (isset($stockMap[$key])) {
                return $stockMap[$key];
            }
        }

        if (!empty($item['name'])) {
            $key = 'name:' . strtolower(trim($item['name']));
            if (isset($stockMap[$key])) {
                return $stockMap[$key];
            }
        }

        return null;
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
                    'height' => !empty($vitals['height']) ? $vitals['height'] : null,
                    'weight' => !empty($vitals['weight']) ? $vitals['weight'] : null,
                    'bmi' => !empty($vitals['bmi']) ? $vitals['bmi'] : null,
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
            'height' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'weight' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'bmi' => [
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
                    `height` varchar(50) DEFAULT NULL,
                    `weight` varchar(50) DEFAULT NULL,
                    `bmi` varchar(50) DEFAULT NULL,
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
        
        // Check if progress_days column exists, add it ONCE only
        $fields = $db->getFieldNames('prescriptions');
        
        if (!in_array('progress_days', $fields)) {
            try {
                // Double check before adding to avoid duplicate column error
                $checkQuery = $db->query("SHOW COLUMNS FROM prescriptions LIKE 'progress_days'");
                $columnExists = $checkQuery->getNumRows() > 0;
                
                if (!$columnExists) {
                    $db->query("ALTER TABLE prescriptions ADD COLUMN progress_days INT DEFAULT 0 AFTER status");
                    log_message('info', 'Successfully added progress_days column to prescriptions table');
                    
                    // Force reconnect to ensure schema is refreshed
                    $db->close();
                    $db = \Config\Database::connect();
                }
        } catch (\Exception $e) {
                // Column might already exist from another request, that's fine
                log_message('debug', 'progress_days column note: ' . $e->getMessage());
            }
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
        
        // Get current nurse name from session
        $nurseName = session()->get('name') ?? 'Unknown Nurse';
        
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
        }
        
        // Store nurse name and start date in notes
        $notes = trim($notes);
        $notesLines = [];
        if (!empty($notes)) {
            $notesLines = explode("\n", $notes);
        }
        
        // Remove old GIVEN_BY_NURSE and START_DATE lines
        $notesLines = array_filter($notesLines, function($line) {
            return !preg_match('/^(GIVEN_BY_NURSE|START_DATE):/', trim($line));
        });
        
        // Add new tracking info
        if ($startDate) {
            $notesLines[] = "START_DATE:" . $startDate;
        }
        $notesLines[] = "GIVEN_BY_NURSE:" . $nurseName;
        
        $notes = implode("\n", $notesLines);
        
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
        
        // Perform the update using RAW SQL to bypass query builder cache
        $result = false;
        $sql = '';
        
        try {
            // Build the SQL query manually
            $escapedStatus = $db->escape('completed');
            $escapedUpdatedAt = $db->escape(date('Y-m-d H:i:s'));
            $escapedProgress = (int) $existingProgress;
            $escapedId = (int) $prescriptionId;
        
            // Start building the UPDATE query
            $sql = "UPDATE prescriptions SET 
                    status = {$escapedStatus}, 
                    updated_at = {$escapedUpdatedAt}, 
                    progress_days = {$escapedProgress}";
        
        // Always update notes to include nurse name and start date
        $escapedNotes = $db->escape($notes);
        $sql .= ", notes = {$escapedNotes}";
        
            $sql .= " WHERE id = {$escapedId}";
            
            log_message('info', "Executing SQL: {$sql}");
            
            // Execute raw query
            $result = $db->query($sql);
            
            if ($result) {
                log_message('info', "Successfully marked prescription #{$prescriptionId} as given (progress: {$existingProgress}/{$durationDays})");
            } else {
                $error = $db->error();
                log_message('error', "Raw SQL update failed for prescription #{$prescriptionId}. Error: " . json_encode($error));
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Database update failed: ' . ($error['message'] ?? 'Unknown error')
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', "Exception updating prescription #{$prescriptionId}: " . $e->getMessage());
            log_message('error', "SQL attempted: " . $sql);
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        if (!$result) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update prescription. Please check the logs.'
            ]);
        }

        // Deduct stock from pharmacy_inventory when nurse marks prescription as given
        // (Only if prescription hasn't been dispensed yet to avoid double deduction)
        // Check if stock was already deducted by looking for STOCK_DEDUCTED flag in notes
        $shouldDeductStock = $result && $currentStatus !== 'dispensed' && $db->tableExists('pharmacy_inventory');
        
        // Check if stock was already deducted for this prescription
        $notes = $prescription['notes'] ?? '';
        $stockAlreadyDeducted = strpos($notes, 'STOCK_DEDUCTED:') !== false;
        
        if ($shouldDeductStock) {
            if ($stockAlreadyDeducted) {
                $shouldDeductStock = false;
                log_message('info', "Skipping stock deduction - already deducted for prescription #{$prescriptionId}");
            } else {
                log_message('info', "Will deduct FULL prescription quantity - first time marking prescription #{$prescriptionId}");
            }
        }
        
        if ($shouldDeductStock) {
            log_message('info', "Starting stock deduction for prescription #{$prescriptionId}");
            $items = json_decode($prescription['items_json'] ?? '[]', true);
            $medicationModel = new \App\Models\MedicationModel();
            
            foreach ($items as $item) {
                $medicineName = $item['name'] ?? '';
                
                if (empty($medicineName)) {
                    continue;
                }
                
                // Calculate quantity - check if quantity field exists, otherwise calculate from dosage/frequency/duration
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
                    
                    // If still no quantity, default to 1 (single dose)
                    if ($quantity <= 0) {
                        $quantity = 1;
                    }
                }
                
                log_message('info', "Processing stock deduction for: {$medicineName}, Quantity: {$quantity}");
                
                // Extract base medication name (remove strength/dosage like "500mg", "400mg")
                // e.g., "Amoxicillin 500mg" -> "Amoxicillin"
                $baseMedicineName = preg_replace('/\s+\d+.*?(mg|ml|g|tablet|capsule).*$/i', '', $medicineName);
                $baseMedicineName = trim($baseMedicineName);
                
                log_message('info', "Looking for medication: '{$medicineName}' (base: '{$baseMedicineName}')");
                
                // Try to find medication by exact name first
                $medication = $medicationModel->where('name', $medicineName)->first();
                
                // If not found, try base name
                if (!$medication && !empty($baseMedicineName)) {
                    $medication = $medicationModel->where('name', $baseMedicineName)->first();
                    log_message('info', "Trying medication by base name: '{$baseMedicineName}'");
                }
                
                // If still not found, try LIKE match
                if (!$medication && !empty($baseMedicineName)) {
                    $medication = $medicationModel->like('name', $baseMedicineName, 'both')->first();
                    log_message('info', "Trying medication by LIKE match: '{$baseMedicineName}'");
                }
                
                $inventoryRecord = null;
                
                // Try to find inventory record by medication_id first
                if ($medication && !empty($medication['id'])) {
                    $inventoryRecord = $db->table('pharmacy_inventory')
                        ->where('medication_id', $medication['id'])
                        ->get()
                        ->getRowArray();
                    log_message('info', "Found inventory by medication_id ({$medication['id']}): " . ($inventoryRecord ? 'YES' : 'NO'));
                }
                
                // If not found by medication_id, try by exact name match
                if (!$inventoryRecord) {
                    $inventoryRecord = $db->table('pharmacy_inventory')
                        ->where('name', $medicineName)
                        ->get()
                        ->getRowArray();
                    log_message('info', "Trying inventory by exact name '{$medicineName}': " . ($inventoryRecord ? 'YES' : 'NO'));
                }
                
                // If still not found, try base name match
                if (!$inventoryRecord && !empty($baseMedicineName)) {
                    $inventoryRecord = $db->table('pharmacy_inventory')
                        ->where('name', $baseMedicineName)
                        ->get()
                        ->getRowArray();
                    log_message('info', "Trying inventory by base name '{$baseMedicineName}': " . ($inventoryRecord ? 'YES' : 'NO'));
                }
                
                // If still not found, try LIKE match
                if (!$inventoryRecord && !empty($baseMedicineName)) {
                    $inventoryRecord = $db->table('pharmacy_inventory')
                        ->like('name', $baseMedicineName, 'both')
                        ->get()
                        ->getRowArray();
                    log_message('info', "Trying inventory by LIKE match '{$baseMedicineName}': " . ($inventoryRecord ? 'YES' : 'NO'));
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
                            log_message('info', "Found inventory by case-insensitive partial match: '{$inv['name']}'");
                            break;
                        }
                    }
                }
                
                if ($inventoryRecord) {
                    $currentStock = (int)($inventoryRecord['stock_quantity'] ?? 0);
                    $newStock = max(0, $currentStock - $quantity); // Don't go below 0
                    
                    log_message('info', "Stock update: {$medicineName} - Current: {$currentStock}, Deduct: {$quantity}, New: {$newStock}");
                    log_message('info', "Inventory record ID: {$inventoryRecord['id']}, Name: {$inventoryRecord['name']}");
                    
                    // Update stock using direct SQL to ensure it works
                    $updateResult = false;
                    try {
                        $updateResult = $db->table('pharmacy_inventory')
                            ->where('id', $inventoryRecord['id'])
                            ->update([
                                'stock_quantity' => $newStock,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        
                        // Verify the update
                        $verifyRecord = $db->table('pharmacy_inventory')
                            ->where('id', $inventoryRecord['id'])
                            ->get()
                            ->getRowArray();
                        $verifiedStock = (int)($verifyRecord['stock_quantity'] ?? 0);
                        
                        log_message('info', "Stock update result: " . ($updateResult ? 'SUCCESS' : 'FAILED'));
                        log_message('info', "Verified stock after update: {$verifiedStock} (expected: {$newStock})");
                        
                        if ($verifiedStock !== $newStock) {
                            log_message('error', "Stock update verification FAILED! Expected {$newStock} but got {$verifiedStock}");
                            // Try direct SQL update as fallback
                            $db->query("UPDATE pharmacy_inventory SET stock_quantity = {$newStock}, updated_at = NOW() WHERE id = {$inventoryRecord['id']}");
                            log_message('info', "Attempted direct SQL update as fallback");
                        }
                    } catch (\Exception $e) {
                        log_message('error', "Error updating stock: " . $e->getMessage());
                        // Try direct SQL as fallback
                        try {
                            $db->query("UPDATE pharmacy_inventory SET stock_quantity = {$newStock}, updated_at = NOW() WHERE id = {$inventoryRecord['id']}");
                            $updateResult = true;
                            log_message('info', "Direct SQL update succeeded");
                        } catch (\Exception $e2) {
                            log_message('error', "Direct SQL update also failed: " . $e2->getMessage());
                        }
                    }
                    
                    if ($updateResult) {
                        // Mark stock as deducted in prescription notes to prevent double deduction
                        $currentNotes = $prescription['notes'] ?? '';
                        if (strpos($currentNotes, 'STOCK_DEDUCTED:') === false) {
                            $deductedFlag = 'STOCK_DEDUCTED:' . date('Y-m-d H:i:s') . ':' . $medicineName . ':' . $quantity;
                            $newNotes = trim($currentNotes);
                            if (!empty($newNotes) && !str_contains($newNotes, 'STOCK_DEDUCTED:')) {
                                $newNotes .= "\n" . $deductedFlag;
                            } elseif (empty($newNotes)) {
                                $newNotes = $deductedFlag;
                            }
                            
                            // Update prescription notes with deduction flag
                            $db->table('prescriptions')
                                ->where('id', $prescriptionId)
                                ->update(['notes' => $newNotes]);
                            log_message('info', "Marked stock as deducted in prescription notes");
                        }
                    }
                    
                    // Log stock movement
                    if ($db->tableExists('pharmacy_stock_movements')) {
                        $movementResult = $db->table('pharmacy_stock_movements')->insert([
                            'medication_id' => $medication['id'] ?? null,
                            'medicine_name' => $medicineName,
                            'movement_type' => 'dispense',
                            'quantity_change' => -$quantity,
                            'previous_stock' => $currentStock,
                            'new_stock' => $newStock,
                            'action_by' => session()->get('user_id'),
                            'notes' => 'Given to patient via prescription RX#' . str_pad((string)$prescriptionId, 6, '0', STR_PAD_LEFT) . ' by nurse',
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        log_message('info', "Stock movement log result: " . ($movementResult ? 'SUCCESS' : 'FAILED'));
                    }
                } else {
                    log_message('warning', "No inventory record found for medication: {$medicineName}");
                    // Debug: Show available inventory records
                    $allInventory = $db->table('pharmacy_inventory')->select('id, name, medication_id, stock_quantity')->get()->getResultArray();
                    log_message('debug', "Available inventory records: " . json_encode($allInventory));
                }
            }
            
            log_message('info', "Completed stock deduction process for prescription #{$prescriptionId}");
        } else {
            log_message('info', "Skipping stock deduction - conditions not met (result: " . ($result ? 'true' : 'false') . ", status: {$currentStatus}, table exists: " . ($db->tableExists('pharmacy_inventory') ? 'true' : 'false') . ")");
        }

        // DISABLED: Auto-create bill for prescription medications
        // Medications should be included in main bill items, not as separate bills
        // if ($result && !$wasCompleted) {
        //     // Auto-create bill for prescription medications (first time only)
        //     $this->autoCreatePrescriptionBill($prescriptionId, $prescription);
        // }

        // Note: Follow-up appointments are now created when doctor saves prescription, not when nurse marks as given
        // This ensures follow-ups are scheduled immediately when doctor indicates they're needed

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
                if (!empty($item['requires_followup']) && $item['requires_followup'] === true) {
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
            $patientModel = new \App\Models\PatientModel();
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
            
            // Calculate follow-up date (7 days from today, or based on duration)
            $followupDate = date('Y-m-d', strtotime('+7 days'));
            
            // Try to get duration from first medication item
            if (!empty($items[0]['duration'])) {
                $durationText = $items[0]['duration'];
                if (preg_match('/(\d+)/', $durationText, $matches)) {
                    $durationDays = (int) $matches[1];
                    // Follow-up should be after medication duration ends
                    $followupDate = date('Y-m-d', strtotime("+{$durationDays} days"));
                }
            }
            
            // Get doctor's schedule to find available time
            $doctorId = $prescription['doctor_id'];
            
            // Try to find available time slot for follow-up date
            $availableTimes = ['09:00:00', '10:00:00', '11:00:00', '14:00:00', '15:00:00', '16:00:00'];
            $appointmentTime = null;
            
            $appointmentModel = new \App\Models\AppointmentModel();
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
            
            // Create follow-up appointment
            $appointmentData = [
                'patient_id' => $prescription['patient_id'],
                'doctor_id' => $doctorId,
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
                log_message('error', "Failed to create follow-up appointment for prescription #{$prescription['id']}");
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error creating follow-up appointment: " . $e->getMessage());
        }
    }
    
    private function autoCreatePrescriptionBill($prescriptionId, $prescription)
    {
        try {
            // Check if bill already exists for this prescription
            $db = \Config\Database::connect();
            $existingBill = $db->table('bills')
                ->where('prescription_id', $prescriptionId)
                ->get()
                ->getFirstRow('array');
            
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
                
                // Check if patient is buying from hospital - only add to bill if true
                $buyFromHospital = isset($item['buy_from_hospital']) ? (bool)$item['buy_from_hospital'] : true;
                if (!$buyFromHospital) {
                    $medName = $item['name'] ?? 'unknown';
                    log_message('debug', "Skipping medication '{$medName}' - patient not buying from hospital");
                    continue; // Skip medications not bought from hospital
                }
                
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
            'amoxicillin' => 8.00, // Updated to match actual supplier price
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
        
        $db = \Config\Database::connect();
        $basePrice = 0;
        
        // Try to get price from database first
        if ($db->tableExists('medications')) {
            $med = $db->table('medications')
                ->where('name', $medicationName)
                ->orLike('name', $medicationName)
                ->get()
                ->getFirstRow('array');
            
            if ($med && isset($med['price'])) {
                $basePrice = floatval($med['price']);
            }
        }
        
        // Use default pricing based on medication name if no database price
        if ($basePrice <= 0) {
        $nameLower = strtolower($medicationName);
        foreach ($defaultPrices as $key => $price) {
            if (strpos($nameLower, $key) !== false) {
                    $basePrice = $price;
                    break;
                }
            }
        }
        
        // Default price if not found
        if ($basePrice <= 0) {
            $basePrice = 50.00;
        }
        
        // Double the price for patient billing (patient pays 2x the inventory price)
        return $basePrice * 2;
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

    public function reports()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return redirect()->to('/login');
        }

        $nurseId = session()->get('user_id');
        $db = \Config\Database::connect();
        $patientModel = new \App\Models\PatientModel();
        
        // Get filters
        $reportType = $this->request->getGet('type') ?? 'treatment_updates';
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

        $data = [
            'title' => 'Nurse Reports - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'report_type' => $reportType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'treatment_updates' => [],
            'vital_signs' => [],
            'patient_assignments' => [],
            'summary' => [
                'total_updates' => 0,
                'total_patients' => 0,
                'avg_vitals_per_day' => 0,
                'total_vital_checks' => 0,
            ]
        ];

        try {
            // Treatment Updates Report
            if ($db->tableExists('treatment_updates')) {
                // Get nurse name from session
                $nurseName = session()->get('name');
                
                // Check if table has nurse_id column, otherwise use nurse_name
                $fields = $db->getFieldData('treatment_updates');
                $hasNurseId = false;
                foreach ($fields as $field) {
                    if (strtolower($field->name) === 'nurse_id') {
                        $hasNurseId = true;
                        break;
                    }
                }
                
                $updates = $db->table('treatment_updates tu')
                    ->select('tu.*, p.full_name as patient_name, p.patient_id as patient_code');
                
                // Filter by nurse_id if column exists, otherwise by nurse_name
                if ($hasNurseId) {
                    $updates->join('users u', 'u.id = tu.nurse_id', 'left')
                            ->where('tu.nurse_id', $nurseId);
                } else {
                    $updates->where('tu.nurse_name', $nurseName);
                }
                
                $updates = $updates
                    ->join('patients p', 'p.id = tu.patient_id', 'left')
                    ->where('DATE(tu.created_at) >=', $dateFrom)
                    ->where('DATE(tu.created_at) <=', $dateTo)
                    ->orderBy('tu.created_at', 'DESC')
                    ->get()
                    ->getResultArray();
                
                // Get prescriptions for these patients to show "marked as given" status
                $prescriptionModel = new PrescriptionModel();
                $patientIds = array_unique(array_column($updates, 'patient_id'));
                $prescriptionsByPatient = [];
                
                if (!empty($patientIds) && $db->tableExists('prescriptions')) {
                    $allPrescriptions = $prescriptionModel
                        ->whereIn('patient_id', $patientIds)
                        ->orderBy('created_at', 'DESC')
                        ->findAll();
                    
                    foreach ($allPrescriptions as $rx) {
                        $pid = (int)$rx['patient_id'];
                        if (!isset($prescriptionsByPatient[$pid])) {
                            $prescriptionsByPatient[$pid] = [];
                        }
                        $prescriptionsByPatient[$pid][] = $rx;
                    }
                }
                
                // Attach prescription info to updates
                foreach ($updates as &$update) {
                    $pid = (int)$update['patient_id'];
                    $patientPrescriptions = $prescriptionsByPatient[$pid] ?? [];
                    $medicationsGiven = [];
                    
                    foreach ($patientPrescriptions as $rx) {
                        $items = json_decode($rx['items_json'] ?? '[]', true) ?: [];
                        $status = $rx['status'] ?? 'pending';
                        $rxDate = !empty($rx['updated_at']) ? date('Y-m-d', strtotime($rx['updated_at'])) : date('Y-m-d', strtotime($rx['created_at']));
                        $updateDate = !empty($update['created_at']) ? date('Y-m-d', strtotime($update['created_at'])) : '';
                        
                        // If prescription was marked as given on or before this update date
                        if ($status !== 'pending' && $rxDate <= $updateDate) {
                            foreach ($items as $item) {
                                $medicationsGiven[] = ($item['name'] ?? 'N/A') . ' (' . ($item['quantity'] ?? 0) . ')';
                            }
                        }
                    }
                    
                    $update['medications_given'] = !empty($medicationsGiven) ? implode(', ', $medicationsGiven) : '—';
                    $update['treatment_notes'] = $update['notes'] ?? '—';
                }
                unset($update);
                
                $data['treatment_updates'] = $updates;
                $data['summary']['total_updates'] = count($updates);
                
                // Get unique patients
                $patientIds = array_unique(array_column($updates, 'patient_id'));
                $data['summary']['total_patients'] = count($patientIds);
                
                // Calculate average vitals per day
                $days = max(1, (strtotime($dateTo) - strtotime($dateFrom)) / 86400);
                $data['summary']['avg_vitals_per_day'] = $days > 0 ? round(count($updates) / $days, 2) : 0;
                
                // Count vital signs entries (those with height, weight, or vital signs)
                $vitalChecks = array_filter($updates, function($update) {
                    return !empty($update['height']) || !empty($update['weight']) || 
                           !empty($update['blood_pressure']) || !empty($update['heart_rate']) ||
                           !empty($update['temperature']) || !empty($update['oxygen_saturation']);
                });
                $data['summary']['total_vital_checks'] = count($vitalChecks);
            }

            // Patient Assignments (from appointments where nurse is involved)
            $appointmentModel = new \App\Models\AppointmentModel();
            $assignments = $appointmentModel
                ->select('appointments.*, patients.full_name as patient_name, patients.patient_id as patient_code, users.name as doctor_name')
                ->join('patients', 'patients.id = appointments.patient_id', 'left')
                ->join('users', 'users.id = appointments.doctor_id', 'left')
                ->where('DATE(appointments.appointment_date) >=', $dateFrom)
                ->where('DATE(appointments.appointment_date) <=', $dateTo)
                ->where('appointments.status !=', 'cancelled')
                ->orderBy('appointments.appointment_date', 'DESC')
                ->findAll();
            
            // Filter to show only patients that have treatment updates from this nurse
            $assignedPatientIds = [];
            if ($db->tableExists('treatment_updates')) {
                $fields = $db->getFieldData('treatment_updates');
                $hasNurseId = false;
                foreach ($fields as $field) {
                    if (strtolower($field->name) === 'nurse_id') {
                        $hasNurseId = true;
                        break;
                    }
                }
                
                $query = $db->table('treatment_updates')
                    ->select('patient_id');
                
                if ($hasNurseId) {
                    $query->where('nurse_id', $nurseId);
                } else {
                    $query->where('nurse_name', session()->get('name'));
                }
                
                $assignedPatientIds = $query
                    ->where('DATE(created_at) >=', $dateFrom)
                    ->where('DATE(created_at) <=', $dateTo)
                    ->distinct()
                    ->get()
                    ->getResultArray();
                $assignedPatientIds = array_column($assignedPatientIds, 'patient_id');
            }
            
            $data['patient_assignments'] = array_filter($assignments, function($apt) use ($assignedPatientIds) {
                return in_array($apt['patient_id'], $assignedPatientIds);
            });

        } catch (\Exception $e) {
            log_message('error', 'Error fetching nurse reports: ' . $e->getMessage());
        }

        return view('nurse/reports', $data);
    }

    public function settings()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return redirect()->to('/login');
        }

        $model = new SettingModel();
        $defaults = [
            'nurse_shift_start'       => '07:00',
            'nurse_shift_end'         => '19:00',
            'nurse_max_patients'      => '10',
            'nurse_require_vitals'    => '1',
            'nurse_task_reminders'    => '1',
            'nurse_handover_template' => "Patient status\nPending meds\nFollow-up tests",
        ];
        $settings = array_merge($defaults, $model->getAllAsMap());

        $data = [
            'title'     => 'Nurse Settings - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'pageTitle' => 'Settings',
            'settings'  => $settings,
        ];

        return view('nurse/settings', $data);
    }

    public function saveSettings()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return redirect()->to('/login');
        }

        $model = new SettingModel();
        $post = $this->request->getPost();
        $keys = [
            'nurse_shift_start',
            'nurse_shift_end',
            'nurse_max_patients',
            'nurse_require_vitals',
            'nurse_task_reminders',
            'nurse_handover_template',
        ];

        foreach ($keys as $key) {
            $model->setValue($key, (string)($post[$key] ?? ''), 'nurse');
        }

        return redirect()->to('/nurse/settings')->with('success', 'Settings saved successfully.');
    }

    public function labRequests()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return redirect()->to('/login');
        }

        $requestModel = new LabTestRequestModel();
        $patientModel = new PatientModel();
        $db = \Config\Database::connect();
        
        // Get status filter from query string
        $statusFilter = $this->request->getGet('status') ?? 'pending';
        
        // Get all lab requests with patient and doctor info
        $allRequests = $requestModel->getAllWithRelations([]);
        
        // Filter by status
        $filteredRequests = [];
        foreach ($allRequests as $req) {
            $status = $req['status'] ?? 'pending';
            
            if ($statusFilter === 'pending' && $status === 'pending') {
                $filteredRequests[] = $req;
            } elseif ($statusFilter === 'sent_to_lab' && $status === 'sent_to_lab') {
                $filteredRequests[] = $req;
            } elseif ($statusFilter === 'completed' && $status === 'completed') {
                $filteredRequests[] = $req;
            } elseif ($statusFilter === 'all') {
                $filteredRequests[] = $req;
            }
        }
        
        // Sort by requested_at DESC
        usort($filteredRequests, function($a, $b) {
            $dateA = strtotime($a['requested_at'] ?? $a['created_at'] ?? '1970-01-01');
            $dateB = strtotime($b['requested_at'] ?? $b['created_at'] ?? '1970-01-01');
            return $dateB - $dateA;
        });
        
        // Count by status
        $pendingCount = 0;
        $sentCount = 0;
        $completedCount = 0;
        foreach ($allRequests as $req) {
            $status = $req['status'] ?? 'pending';
            if ($status === 'pending') $pendingCount++;
            elseif ($status === 'sent_to_lab') $sentCount++;
            elseif ($status === 'completed') $completedCount++;
        }

        $data = [
            'title' => 'Lab Requests - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'requests' => $filteredRequests,
            'status_filter' => $statusFilter,
            'pending_count' => $pendingCount,
            'sent_count' => $sentCount,
            'completed_count' => $completedCount,
        ];

        return view('nurse/lab_requests', $data);
    }

    public function markLabRequestAsSent()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');

        $requestId = $this->request->getPost('request_id') ?? $this->request->getJSON(true)['request_id'] ?? null;
        
        if (!$requestId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Request ID is required']);
        }

        try {
            $requestModel = new LabTestRequestModel();
            $request = $requestModel->find($requestId);
            
            if (!$request) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lab request not found']);
            }
            
            $currentStatus = $request['status'] ?? 'pending';
            if ($currentStatus !== 'pending') {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'This request has already been processed. Current status: ' . $currentStatus
                ]);
            }
            
            // Check if specimen needs to be collected
            $requiresSpecimen = (int)($request['requires_specimen'] ?? 0);
            $nurseId = session()->get('user_id');
            
            // Update status to sent_to_lab
            $updateData = [
                'status' => 'sent_to_lab',
                'sent_by_nurse_id' => $nurseId,
                'sent_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // If specimen is required, mark it as collected by this nurse
            if ($requiresSpecimen === 1) {
                $updateData['specimen_collected_by'] = $nurseId;
                $updateData['specimen_collected_at'] = date('Y-m-d H:i:s');
            }
            
            if ($requestModel->update($requestId, $updateData)) {
                $message = 'Lab request marked as sent to lab successfully';
                if ($requiresSpecimen === 1) {
                    $message = 'Specimen collected and request sent to lab successfully';
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update request status',
                    'errors' => $requestModel->errors()
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error marking lab request as sent: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function collectSpecimen()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');

        $requestId = $this->request->getPost('request_id') ?? $this->request->getJSON(true)['request_id'] ?? null;
        
        if (!$requestId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Request ID is required']);
        }

        try {
            $requestModel = new LabTestRequestModel();
            $request = $requestModel->find($requestId);
            
            if (!$request) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lab request not found']);
            }
            
            $requiresSpecimen = (int)($request['requires_specimen'] ?? 0);
            if ($requiresSpecimen !== 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This test does not require specimen collection'
                ]);
            }
            
            $currentStatus = $request['status'] ?? 'pending';
            if ($currentStatus !== 'pending') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Specimen can only be collected for pending requests'
                ]);
            }
            
            // Mark specimen as collected
            $updateData = [
                'specimen_collected_by' => session()->get('user_id'),
                'specimen_collected_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            if ($requestModel->update($requestId, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Specimen collected successfully. You can now send the request to lab.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update specimen collection status',
                    'errors' => $requestModel->errors()
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error collecting specimen: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
