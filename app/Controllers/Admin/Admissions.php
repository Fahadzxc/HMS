<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\PatientModel;
use App\Models\UserModel;

class Admissions extends Controller
{
    public function index()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        // Get all admissions from admissions table with patient, doctor, room details
        $builder = $db->table('admissions a');
        $builder->select('
            a.id as admission_id,
            a.patient_id,
            a.doctor_id,
            a.room_id,
            a.admission_date,
            a.case_type,
            a.reason_for_admission,
            a.notes,
            a.status,
            a.discharge_ordered_at,
            a.discharge_ordered_by,
            a.discharge_notes,
            a.discharge_ready_at,
            a.discharge_ready_by,
            a.billing_cleared,
            a.discharged_at,
            a.discharged_by,
            a.created_at,
            p.full_name as patient_name,
            p.date_of_birth,
            p.gender,
            p.contact,
            p.concern,
            r.room_number,
            r.room_type,
            r.floor,
            u.name as doctor_name
        ');
        $builder->join('patients p', 'p.id = a.patient_id', 'left');
        $builder->join('rooms r', 'r.id = a.room_id', 'left');
        $builder->join('users u', 'u.id = a.doctor_id', 'left');
        $builder->orderBy('a.created_at', 'DESC');
        
        $admissions = $builder->get()->getResultArray();
        
        // Get stats
        $totalAdmissions = count($admissions);
        $activeAdmissions = count(array_filter($admissions, fn($a) => $a['status'] === 'Admitted'));
        $todayAdmissions = count(array_filter($admissions, fn($a) => ($a['admission_date'] ?? '') === date('Y-m-d')));
        $dischargedToday = count(array_filter($admissions, fn($a) => $a['status'] === 'Discharged'));
        
        // Get doctors for edit modal
        $userModel = new UserModel();
        $doctors = $userModel->where('role', 'doctor')->where('status', 'active')->findAll();
        
        // Get rooms for edit modal
        $rooms = $db->table('rooms')->get()->getResultArray();

        $data = [
            'title' => 'Admissions Management - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'admissions' => $admissions,
            'total_admissions' => $totalAdmissions,
            'active_admissions' => $activeAdmissions,
            'today_admissions' => $todayAdmissions,
            'discharged_today' => $dischargedToday,
            'doctors' => $doctors,
            'rooms' => $rooms
        ];

        return view('admin/admissions', $data);
    }
    
    public function update()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
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
        
        $updateData = [
            'doctor_id' => $json['doctor_id'] ?? $admission['doctor_id'],
            'room_id' => !empty($json['room_id']) ? $json['room_id'] : $admission['room_id'],
            'status' => $json['status'] ?? $admission['status'],
            'notes' => $json['notes'] ?? $admission['notes'],
            'case_type' => $json['case_type'] ?? $admission['case_type'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($db->table('admissions')->where('id', $admissionId)->update($updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Admission updated successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update admission']);
        }
    }
    
    public function discharge($admissionId = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        if (!$admissionId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admission ID is required']);
        }
        
        $db = \Config\Database::connect();
        $patientModel = new PatientModel();
        
        $admission = $db->table('admissions')->where('id', $admissionId)->get()->getRowArray();
        if (!$admission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admission not found']);
        }
        
        // Update admission status to Discharged
        $db->table('admissions')->where('id', $admissionId)->update([
            'status' => 'Discharged',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update patient type to outpatient
        $patientModel->update($admission['patient_id'], [
            'patient_type' => 'outpatient',
            'discharge_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->response->setJSON(['success' => true, 'message' => 'Patient discharged successfully']);
    }
    
    public function delete($admissionId = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        if (!$admissionId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admission ID is required']);
        }
        
        $db = \Config\Database::connect();
        $admission = $db->table('admissions')->where('id', $admissionId)->get()->getRowArray();
        
        if (!$admission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admission not found']);
        }
        
        if ($db->table('admissions')->where('id', $admissionId)->delete()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Admission record deleted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete admission']);
        }
    }
}

