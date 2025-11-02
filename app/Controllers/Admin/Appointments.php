<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\AppointmentModel;

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

        // Get all appointments with patient and doctor details
        $appointments = $this->appointmentModel->getAppointmentsWithDetails();

        $data = [
            'pageTitle' => 'Appointments Monitor',
            'title' => 'Appointments Monitor - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'appointments' => $appointments
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
}
