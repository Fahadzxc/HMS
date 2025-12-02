<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\AppointmentModel;
use App\Models\PatientModel;
use App\Models\UserModel;

class Appointments extends Controller
{
    protected $appointmentModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
    }

    public function index()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        // Get OUTPATIENT appointments only (inpatients go to Admissions)
        $db = \Config\Database::connect();
        $builder = $db->table('appointments a');
        $builder->select('a.*, p.full_name as patient_name, p.patient_type, u.name as doctor_name, r.room_number');
        $builder->join('patients p', 'p.id = a.patient_id', 'left');
        $builder->join('users u', 'u.id = a.doctor_id', 'left');
        $builder->join('rooms r', 'r.id = a.room_id', 'left');
        $builder->where('p.patient_type', 'outpatient');
        $builder->orderBy('a.appointment_date', 'ASC');
        $builder->orderBy('a.appointment_time', 'ASC');
        $appointments = $builder->get()->getResultArray();
        
        // Get outpatient patients only for edit modal
        $patientModel = new PatientModel();
        $patients = $patientModel->where('patient_type', 'outpatient')->findAll();
        
        // Get doctors for edit modal
        $userModel = new UserModel();
        $doctors = $userModel->where('role', 'doctor')->where('status', 'active')->findAll();
        
        // Get rooms for edit modal
        $rooms = $db->table('rooms')->get()->getResultArray();

        $data = [
            'pageTitle' => 'Appointments Monitor',
            'title' => 'Appointments Monitor - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'appointments' => $appointments,
            'patients' => $patients,
            'doctors' => $doctors,
            'rooms' => $rooms
        ];

        return view('admin/appointments', $data);
    }

    public function getAppointment($id)
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $appointment = $this->appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Appointment not found']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $appointment
        ]);
    }

    public function checkAvailability()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $doctorId = $this->request->getGet('doctor_id');
        $date = $this->request->getGet('date');
        $time = $this->request->getGet('time');
        $excludeId = $this->request->getGet('exclude_id');

        if (!$doctorId || !$date || !$time) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing required parameters']);
        }

        $available = $this->appointmentModel->isDoctorAvailable($doctorId, $date, $time, $excludeId);

        return $this->response->setJSON([
            'status' => 'success',
            'available' => $available
        ]);
    }
    
    public function update()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $json = $this->request->getJSON(true);
        if (!$json) {
            $json = $this->request->getPost();
        }
        
        $id = $json['id'] ?? null;
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment ID is required']);
        }
        
        $appointment = $this->appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }
        
        $updateData = [
            'appointment_date' => $json['appointment_date'] ?? $appointment['appointment_date'],
            'appointment_time' => $json['appointment_time'] ?? $appointment['appointment_time'],
            'patient_id' => $json['patient_id'] ?? $appointment['patient_id'],
            'doctor_id' => $json['doctor_id'] ?? $appointment['doctor_id'],
            'appointment_type' => $json['appointment_type'] ?? $appointment['appointment_type'],
            'status' => $json['status'] ?? $appointment['status'],
            'room_id' => !empty($json['room_id']) ? $json['room_id'] : null,
            'notes' => $json['notes'] ?? $appointment['notes'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->appointmentModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Appointment updated successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update appointment']);
        }
    }
    
    public function delete($id = null)
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment ID is required']);
        }
        
        $appointment = $this->appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }
        
        // Check if patient is inpatient - should be managed in Admissions instead
        $patientModel = new PatientModel();
        $patient = $patientModel->find($appointment['patient_id']);
        if ($patient && $patient['patient_type'] === 'inpatient') {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'This is an inpatient record. Please manage it in the Admissions section instead.'
            ]);
        }
        
        if ($this->appointmentModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Appointment deleted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete appointment']);
        }
    }
}
