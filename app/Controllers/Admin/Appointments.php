<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\AppointmentModel;
use App\Models\PatientModel;

class Appointments extends Controller
{
    protected $appointmentModel;
    protected $patientModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
    }

    public function index()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        // Get all appointments with patient and doctor details
        $appointments = $this->appointmentModel->getAppointmentsWithDetails();
        $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments(100);
        
        // Get all patients for the dropdown
        $patients = $this->patientModel->findAll();
        
        // Get all doctors (users with role 'doctor')
        $userModel = new \App\Models\UserModel();
        $doctors = $userModel->where('role', 'doctor')->findAll();

        $data = [
            'pageTitle' => 'Appointments Management',
            'title' => 'Appointments Management - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'appointments' => $appointments,
            'upcoming_appointments' => $upcomingAppointments,
            'patients' => $patients,
            'doctors' => $doctors
        ];
        
        return view('admin/appointments', $data);
    }

    public function create()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $data = [
            'patient_id' => $this->request->getPost('patient_id'),
            'doctor_id' => $this->request->getPost('doctor_id'),
            'appointment_date' => $this->request->getPost('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'status' => $this->request->getPost('status') ?: 'scheduled',
            'notes' => $this->request->getPost('notes'),
            'created_by' => session()->get('user_id')
        ];

        // Check doctor availability
        if (!$this->appointmentModel->isDoctorAvailable($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Doctor is not available at the selected date and time'
            ]);
        }

        if ($this->appointmentModel->insert($data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Appointment created successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create appointment',
                'errors' => $this->appointmentModel->errors()
            ]);
        }
    }

    public function update($id)
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $appointment = $this->appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Appointment not found']);
        }

        $data = [
            'patient_id' => $this->request->getPost('patient_id'),
            'doctor_id' => $this->request->getPost('doctor_id'),
            'appointment_date' => $this->request->getPost('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes')
        ];

        // Check doctor availability (exclude current appointment)
        if (!$this->appointmentModel->isDoctorAvailable($data['doctor_id'], $data['appointment_date'], $data['appointment_time'], $id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Doctor is not available at the selected date and time'
            ]);
        }

        if ($this->appointmentModel->update($id, $data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Appointment updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update appointment',
                'errors' => $this->appointmentModel->errors()
            ]);
        }
    }

    public function delete($id)
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $appointment = $this->appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Appointment not found']);
        }

        if ($this->appointmentModel->delete($id)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Appointment deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete appointment'
            ]);
        }
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
}
