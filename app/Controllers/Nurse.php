<?php

namespace App\Controllers;

use CodeIgniter\Controller;

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
        $data = [
            'title' => 'Patient Monitoring - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name')
        ];

        return view('nurse/patients', $data);
    }

    public function tasks()
    {
        $data = [
            'title' => 'Task Management - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name')
        ];

        return view('nurse/tasks', $data);
    }

    public function schedule()
    {
        $data = [
            'title' => 'Nurse Schedule - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name')
        ];

        return view('nurse/schedule', $data);
    }

    public function appointments()
    {
        // Check if user is logged in and is a nurse
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return redirect()->to('/login');
        }

        // Load models
        $appointmentModel = new \App\Models\AppointmentModel();
        $patientModel = new \App\Models\PatientModel();
        $userModel = new \App\Models\UserModel();

        // Get today's appointments and upcoming appointments
        $todaysAppointments = $appointmentModel->getAppointmentsByDate(date('Y-m-d'));
        $upcomingAppointments = $appointmentModel->getUpcomingAppointments(50);
        
        // Get patients and doctors for dropdowns
        $patients = $patientModel->orderBy('id', 'DESC')->findAll();
        $doctors = $userModel->getDoctors();

        $data = [
            'title' => 'Patient Appointments - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'appointments' => $todaysAppointments,
            'upcoming_appointments' => $upcomingAppointments,
            'patients' => $patients,
            'doctors' => $doctors
        ];

        return view('nurse/appointments', $data);
    }

    // Patient Monitoring Functions
    public function updateVitals()
    {
        // Handle vital signs update
        if ($this->request->getMethod() === 'post') {
            $patientId = $this->request->getPost('patient_id');
            
            // Here you would save to database
            // For now, we'll just redirect back with success message
            
            return redirect()->back()->with('success', 'Vital signs updated successfully for Patient ID: ' . $patientId);
        }

        return redirect()->back()->with('error', 'Invalid request method.');
    }

    public function updateTreatment()
    {
        // Handle treatment updates
        if ($this->request->getMethod() === 'post') {
            $patientId = $this->request->getPost('patient_id');
            
            // Here you would save to database
            // For now, we'll just redirect back with success message
            
            return redirect()->back()->with('success', 'Treatment updated successfully for Patient ID: ' . $patientId);
        }

        return redirect()->back()->with('error', 'Invalid request method.');
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

    // Scheduling Functions
    public function updateSchedule()
    {
        // Handle nurse schedule updates
        if ($this->request->getMethod() === 'post') {
            $date = $this->request->getPost('date');
            
            // Here you would save to database
            // For now, we'll just redirect back with success message
            
            return redirect()->back()->with('success', 'Schedule updated successfully for ' . $date);
        }

        return redirect()->back()->with('error', 'Invalid request method.');
    }

    public function requestScheduleChange()
    {
        // Handle schedule change requests
        if ($this->request->getMethod() === 'post') {
            // Here you would save to database
            // For now, we'll just redirect back with success message
            
            return redirect()->back()->with('success', 'Schedule change request submitted successfully.');
        }

        return redirect()->back()->with('error', 'Invalid request method.');
    }

    // Appointment Functions
    public function addAppointment()
    {
        // Check if user is logged in and is a nurse
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $appointmentModel = new \App\Models\AppointmentModel();

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
        if (!$appointmentModel->isDoctorAvailable($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Doctor is not available at the selected date and time'
            ]);
        }

        if ($appointmentModel->insert($data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Appointment created successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create appointment',
                'errors' => $appointmentModel->errors()
            ]);
        }
    }

    public function updateAppointment()
    {
        // Check if user is logged in and is a nurse
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $appointmentId = $this->request->getPost('appointment_id');
        $appointmentModel = new \App\Models\AppointmentModel();
        
        $appointment = $appointmentModel->find($appointmentId);
        if (!$appointment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Appointment not found']);
        }

        $data = [
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes')
        ];

        // Nurses can only update status and notes, not reschedule
        if ($appointmentModel->update($appointmentId, $data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Appointment updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update appointment',
                'errors' => $appointmentModel->errors()
            ]);
        }
    }

    public function checkDoctorAvailability()
    {
        // Check if user is logged in and is a nurse
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'nurse') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        
        $doctorId = $this->request->getGet('doctor_id');
        $date = $this->request->getGet('date');
        $time = $this->request->getGet('time');

        if (!$doctorId || !$date || !$time) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing required parameters']);
        }

        $available = $appointmentModel->isDoctorAvailable($doctorId, $date, $time);

        return $this->response->setJSON([
            'status' => 'success',
            'available' => $available,
            'message' => $available ? 'Doctor is available' : 'Doctor is not available at this time'
        ]);
    }
}
