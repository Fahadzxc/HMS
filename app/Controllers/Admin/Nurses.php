<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\NurseModel;
use App\Models\UserModel;
use App\Models\NurseScheduleModel;

class Nurses extends Controller
{
    protected NurseModel $nurseModel;
    protected UserModel $userModel;
    protected NurseScheduleModel $nurseScheduleModel;

    public function __construct()
    {
        $this->nurseModel = new NurseModel();
        $this->userModel  = new UserModel();
        $this->nurseScheduleModel = new NurseScheduleModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        // Get nurses with their schedules using the new method
        $nurses = $this->nurseScheduleModel->getNursesWithSchedules();

        // Fetch ALL nurse activities grouped by nurse name
        $db = \Config\Database::connect();
        $nurseActivities = []; // Comprehensive activities log
        
        // Get nurse user IDs for matching
        $nurseUserIds = [];
        foreach ($nurses as $nurse) {
            if (!empty($nurse['id'])) {
                $nurseUserIds[] = $nurse['id'];
            }
        }
        
        // 1. Vital Signs & Treatment Updates
        if ($db->tableExists('treatment_updates')) {
            try {
                $updates = $db->table('treatment_updates')
                    ->select('nurse_name, patient_id, time, blood_pressure, heart_rate, temperature, oxygen_saturation, height, weight, bmi, notes, created_at')
                    ->where('nurse_name IS NOT NULL')
                    ->where('nurse_name !=', '')
                    ->orderBy('created_at', 'DESC')
                    ->get()
                    ->getResultArray();
                
                foreach ($updates as $update) {
                    $nurseName = $update['nurse_name'];
                    if (!isset($nurseActivities[$nurseName])) {
                        $nurseActivities[$nurseName] = [];
                    }
                    
                    // Add vital signs activity
                    $nurseActivities[$nurseName][] = [
                        'type' => 'vital_signs',
                        'icon' => '💉',
                        'title' => 'Vital Signs Recorded',
                        'description' => $this->formatVitalSignsDescription($update),
                        'patient_id' => $update['patient_id'],
                        'timestamp' => $update['created_at'],
                        'data' => $update
                    ];
                    
                    // Add treatment notes if present
                    if (!empty($update['notes']) && trim($update['notes']) !== '') {
                        $nurseActivities[$nurseName][] = [
                            'type' => 'treatment_notes',
                            'icon' => '📝',
                            'title' => 'Treatment Notes',
                            'description' => substr(trim($update['notes']), 0, 100) . (strlen(trim($update['notes'])) > 100 ? '...' : ''),
                            'patient_id' => $update['patient_id'],
                            'timestamp' => $update['created_at'],
                            'data' => $update
                        ];
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading vital signs: ' . $e->getMessage());
            }
        }
        
        // 2. Lab Requests Marked as Sent
        if ($db->tableExists('lab_test_requests') && !empty($nurseUserIds)) {
            try {
                $labRequests = $db->table('lab_test_requests lr')
                    ->select('lr.*, u.name as nurse_name, p.full_name as patient_name')
                    ->join('users u', 'u.id = lr.sent_by_nurse_id', 'left')
                    ->join('patients p', 'p.id = lr.patient_id', 'left')
                    ->where('lr.sent_by_nurse_id IS NOT NULL')
                    ->whereIn('lr.sent_by_nurse_id', $nurseUserIds)
                    ->orderBy('lr.sent_at', 'DESC')
                    ->get()
                    ->getResultArray();
                
                foreach ($labRequests as $request) {
                    $nurseName = $request['nurse_name'] ?? 'Unknown';
                    if (!isset($nurseActivities[$nurseName])) {
                        $nurseActivities[$nurseName] = [];
                    }
                    $nurseActivities[$nurseName][] = [
                        'type' => 'lab_request',
                        'icon' => '🔬',
                        'title' => 'Lab Request Sent',
                        'description' => "Sent lab request for {$request['test_type']} - Patient: {$request['patient_name']}",
                        'patient_id' => $request['patient_id'],
                        'timestamp' => $request['sent_at'] ?? $request['created_at'],
                        'data' => $request
                    ];
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading lab requests: ' . $e->getMessage());
            }
        }
        
        // 3. Prescriptions Marked as Given (extract nurse name from notes)
        if ($db->tableExists('prescriptions')) {
            try {
                // Get completed prescriptions
                $prescriptions = $db->table('prescriptions p')
                    ->select('p.*, pt.full_name as patient_name')
                    ->join('patients pt', 'pt.id = p.patient_id', 'left')
                    ->where('p.status', 'completed')
                    ->orderBy('p.updated_at', 'DESC')
                    ->get()
                    ->getResultArray();
                
                foreach ($prescriptions as $prescription) {
                    // Extract nurse name from notes (format: GIVEN_BY_NURSE:Name)
                    $notes = $prescription['notes'] ?? '';
                    $nurseName = null;
                    
                    if (preg_match('/GIVEN_BY_NURSE:(.+?)(?:\n|$)/', $notes, $matches)) {
                        $nurseName = trim($matches[1]);
                    }
                    
                    // Fallback: if no nurse name in notes, try matching with treatment updates
                    if (empty($nurseName) && $db->tableExists('treatment_updates')) {
                        $prescriptionDate = $prescription['updated_at'] ?? $prescription['created_at'];
                        $patientId = $prescription['patient_id'];
                        
                        $treatmentUpdate = $db->table('treatment_updates')
                            ->where('patient_id', $patientId)
                            ->where('nurse_name IS NOT NULL')
                            ->where('nurse_name !=', '')
                            ->where('created_at >=', date('Y-m-d 00:00:00', strtotime($prescriptionDate)))
                            ->where('created_at <=', date('Y-m-d 23:59:59', strtotime($prescriptionDate)))
                            ->orderBy('created_at', 'DESC')
                            ->get()
                            ->getRowArray();
                        
                        if ($treatmentUpdate && !empty($treatmentUpdate['nurse_name'])) {
                            $nurseName = $treatmentUpdate['nurse_name'];
                        }
                    }
                    
                    if (!empty($nurseName)) {
                        if (!isset($nurseActivities[$nurseName])) {
                            $nurseActivities[$nurseName] = [];
                        }
                        $items = json_decode($prescription['items_json'] ?? '[]', true);
                        $medNames = [];
                        foreach ($items as $item) {
                            $medNames[] = ($item['name'] ?? 'N/A') . ' (' . ($item['quantity'] ?? 0) . ')';
                        }
                        $medList = !empty($medNames) ? implode(', ', array_slice($medNames, 0, 3)) : 'medication(s)';
                        if (count($medNames) > 3) {
                            $medList .= ' +' . (count($medNames) - 3) . ' more';
                        }
                        
                        $nurseActivities[$nurseName][] = [
                            'type' => 'prescription',
                            'icon' => '💊',
                            'title' => 'Medication Given',
                            'description' => "Administered {$medList} to Patient: {$prescription['patient_name']}",
                            'patient_id' => $prescription['patient_id'],
                            'timestamp' => $prescription['updated_at'] ?? $prescription['created_at'],
                            'data' => $prescription
                        ];
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading prescriptions: ' . $e->getMessage());
            }
        }
        
        // 4. Discharge Ready Actions
        if ($db->tableExists('admissions') && !empty($nurseUserIds)) {
            try {
                $dischargeReady = $db->table('admissions a')
                    ->select('a.*, u.name as nurse_name, p.full_name as patient_name')
                    ->join('users u', 'u.id = a.discharge_ready_by', 'left')
                    ->join('patients p', 'p.id = a.patient_id', 'left')
                    ->where('a.discharge_ready_by IS NOT NULL')
                    ->whereIn('a.discharge_ready_by', $nurseUserIds)
                    ->orderBy('a.discharge_ready_at', 'DESC')
                    ->get()
                    ->getResultArray();
                
                foreach ($dischargeReady as $discharge) {
                    $nurseName = $discharge['nurse_name'] ?? 'Unknown';
                    if (!isset($nurseActivities[$nurseName])) {
                        $nurseActivities[$nurseName] = [];
                    }
                    $nurseActivities[$nurseName][] = [
                        'type' => 'discharge_ready',
                        'icon' => '✅',
                        'title' => 'Patient Ready for Discharge',
                        'description' => "Marked patient {$discharge['patient_name']} as ready for discharge",
                        'patient_id' => $discharge['patient_id'],
                        'timestamp' => $discharge['discharge_ready_at'],
                        'data' => $discharge
                    ];
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading discharge ready: ' . $e->getMessage());
            }
        }
        
        // 5. Final Discharge Actions
        if ($db->tableExists('admissions') && !empty($nurseUserIds)) {
            try {
                $finalDischarge = $db->table('admissions a')
                    ->select('a.*, u.name as nurse_name, p.full_name as patient_name')
                    ->join('users u', 'u.id = a.discharged_by', 'left')
                    ->join('patients p', 'p.id = a.patient_id', 'left')
                    ->where('a.discharged_by IS NOT NULL')
                    ->whereIn('a.discharged_by', $nurseUserIds)
                    ->where('a.status', 'Discharged')
                    ->orderBy('a.discharged_at', 'DESC')
                    ->get()
                    ->getResultArray();
                
                foreach ($finalDischarge as $discharge) {
                    $nurseName = $discharge['nurse_name'] ?? 'Unknown';
                    if (!isset($nurseActivities[$nurseName])) {
                        $nurseActivities[$nurseName] = [];
                    }
                    $nurseActivities[$nurseName][] = [
                        'type' => 'final_discharge',
                        'icon' => '🏠',
                        'title' => 'Final Discharge',
                        'description' => "Completed final discharge for patient {$discharge['patient_name']}",
                        'patient_id' => $discharge['patient_id'],
                        'timestamp' => $discharge['discharged_at'],
                        'data' => $discharge
                    ];
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading final discharge: ' . $e->getMessage());
            }
        }
        
        // Sort activities by timestamp (most recent first) for each nurse
        foreach ($nurseActivities as &$activities) {
            usort($activities, function($a, $b) {
                $timeA = strtotime($a['timestamp'] ?? '1970-01-01');
                $timeB = strtotime($b['timestamp'] ?? '1970-01-01');
                return $timeB - $timeA;
            });
            // Limit to 30 most recent activities per nurse
            $activities = array_slice($activities, 0, 30);
        }
        unset($activities);
        
        // Keep vital signs separate for backward compatibility
        $vitalSignsByNurse = [];
        foreach ($nurseActivities as $nurseName => $activities) {
            $vitalSignsByNurse[$nurseName] = array_filter($activities, function($act) {
                return $act['type'] === 'vital_signs';
            });
        }

        $data = [
            'pageTitle' => 'Nurses',
            'title' => 'Nurses - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'nurses' => $nurses,
            'vitalSignsByNurse' => $vitalSignsByNurse,
            'nurseActivities' => $nurseActivities, // All activities
        ];

        return view('admin/nurse', $data);
    }

    public function createSchedule()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        // Get JSON input
        $json = $this->request->getJSON(true);
        $nurseId = $json['nurse_id'] ?? null;
        $schedules = $json['schedules'] ?? null;

        if (!$nurseId || !$schedules) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing required data']);
        }

        // Validate nurse exists
        $nurse = $this->userModel->find($nurseId);
        if (!$nurse || $nurse['role'] !== 'nurse') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nurse not found']);
        }

        // Update nurse schedule
        $result = $this->nurseScheduleModel->updateNurseSchedule($nurseId, $schedules);

        if ($result) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Schedule updated successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update schedule']);
        }
    }

    public function getSchedule($nurseId = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if (!$nurseId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nurse ID is required']);
        }

        $schedule = $this->nurseScheduleModel->getNurseScheduleWithUser($nurseId);
        $nurse = $this->userModel->find($nurseId);

        if (!$nurse || $nurse['role'] !== 'nurse') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nurse not found']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'nurse' => [
                'id' => $nurse['id'],
                'name' => $nurse['name'],
                'email' => $nurse['email']
            ],
            'schedule' => $schedule
        ]);
    }

    public function deleteSchedule()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $scheduleId = $this->request->getPost('schedule_id');

        if (!$scheduleId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Schedule ID is required']);
        }

        // Deactivate the schedule instead of deleting
        $result = $this->nurseScheduleModel->update($scheduleId, ['is_active' => 0]);

        if ($result) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Schedule removed successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to remove schedule']);
        }
    }

    public function getAvailableNurses()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $dayOfWeek = $this->request->getGet('day');
        $shiftType = $this->request->getGet('shift');

        if (!$dayOfWeek) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Day of week is required']);
        }

        $availableNurses = $this->nurseScheduleModel->getAvailableNurses($dayOfWeek, $shiftType);

        return $this->response->setJSON([
            'status' => 'success',
            'nurses' => $availableNurses
        ]);
    }
    
    /**
     * Format vital signs description for activity log
     */
    private function formatVitalSignsDescription($update)
    {
        $parts = [];
        if (!empty($update['blood_pressure'])) $parts[] = "BP: {$update['blood_pressure']}";
        if (!empty($update['heart_rate'])) $parts[] = "HR: {$update['heart_rate']}";
        if (!empty($update['temperature'])) $parts[] = "Temp: {$update['temperature']}";
        if (!empty($update['oxygen_saturation'])) $parts[] = "O2: {$update['oxygen_saturation']}";
        
        $description = !empty($parts) ? implode(', ', $parts) : 'Vital signs recorded';
        if (!empty($update['notes'])) {
            $description .= ' - ' . substr($update['notes'], 0, 50);
        }
        return $description;
    }
}
