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

        $data = [
            'pageTitle' => 'Nurses',
            'title' => 'Nurses - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'nurses' => $nurses,
        ];

        return view('admin/nurses/index', $data);
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
