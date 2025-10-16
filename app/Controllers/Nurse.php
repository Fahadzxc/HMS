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
        // Load patients from database (same as admin)
        $patientModel = new \App\Models\PatientModel();
        $patients = $patientModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'title' => 'Patient Appointments - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'patients' => $patients
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
        // Handle adding new appointment
        if ($this->request->getMethod() === 'post') {
            $patientId = $this->request->getPost('patient_id');
            $patientName = $this->request->getPost('patient_name');
            $doctorId = $this->request->getPost('doctor_id');
            $appointmentType = $this->request->getPost('appointment_type');
            $appointmentDate = $this->request->getPost('appointment_date');
            $appointmentTime = $this->request->getPost('appointment_time');
            $status = $this->request->getPost('status');
            $notes = $this->request->getPost('notes');
            
            // Here you would save to database
            // For now, we'll just redirect back with success message
            
            return redirect()->back()->with('success', 'Appointment added successfully for ' . $patientName . ' with ' . $doctorId);
        }

        return redirect()->back()->with('error', 'Invalid request method.');
    }

    public function updateAppointment()
    {
        // Handle updating appointment
        if ($this->request->getMethod() === 'post') {
            $appointmentId = $this->request->getPost('appointment_id');
            $patientId = $this->request->getPost('patient_id');
            
            // Here you would update in database
            // For now, we'll just redirect back with success message
            
            return redirect()->back()->with('success', 'Appointment updated successfully for Patient ' . $patientId);
        }

        return redirect()->back()->with('error', 'Invalid request method.');
    }

    public function deleteAppointment()
    {
        // Handle deleting appointment
        if ($this->request->getMethod() === 'post') {
            $appointmentId = $this->request->getPost('appointment_id');
            
            // Here you would delete from database
            // For now, we'll just redirect back with success message
            
            return redirect()->back()->with('success', 'Appointment deleted successfully.');
        }

        return redirect()->back()->with('error', 'Invalid request method.');
    }
}
