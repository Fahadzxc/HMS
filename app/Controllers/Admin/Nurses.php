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

        // Fetch treatment updates (vital signs) grouped by nurse name
        $db = \Config\Database::connect();
        $vitalSignsByNurse = [];
        
        if ($db->tableExists('treatment_updates')) {
            try {
                $updates = $db->table('treatment_updates')
                    ->select('nurse_name, patient_id, time, blood_pressure, heart_rate, temperature, oxygen_saturation, height, weight, bmi, notes, created_at')
                    ->where('nurse_name IS NOT NULL')
                    ->where('nurse_name !=', '')
                    ->orderBy('created_at', 'DESC')
                    ->get()
                    ->getResultArray();
                
                // Group by nurse name
                foreach ($updates as $update) {
                    $nurseName = $update['nurse_name'];
                    if (!isset($vitalSignsByNurse[$nurseName])) {
                        $vitalSignsByNurse[$nurseName] = [];
                    }
                    // Keep only recent 20 entries per nurse
                    if (count($vitalSignsByNurse[$nurseName]) < 20) {
                        $vitalSignsByNurse[$nurseName][] = $update;
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading vital signs: ' . $e->getMessage());
            }
        }

        $data = [
            'pageTitle' => 'Nurses',
            'title' => 'Nurses - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'nurses' => $nurses,
            'vitalSignsByNurse' => $vitalSignsByNurse,
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
}
