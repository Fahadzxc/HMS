<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PrescriptionModel;

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
        $model = new \App\Models\PatientModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get patients with their most recent doctor assignment from appointments
        $db = \Config\Database::connect();
        $builder = $db->table('patients p');
        $builder->select('p.*, 
                         u.name as assigned_doctor_name,
                         a.appointment_date as last_appointment_date,
                         a.status as appointment_status,
                         p.room_number,
                         r.room_number as appointment_room_number');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date, status, room_id, 
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments 
                         WHERE status != "cancelled") a', 'a.patient_id = p.id AND a.rn = 1', 'left');
        $builder->join('users u', 'u.id = a.doctor_id', 'left');
        $builder->join('rooms r', 'r.id = a.room_id', 'left');
        $builder->orderBy('p.id', 'DESC');
        
        $patients = $builder->get()->getResultArray();

        $data = [
            'title' => 'Patient Monitoring - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'patients' => $patients,
        ];

        return view('nurse/patients', $data);
    }


    public function treatmentUpdates()
    {
        $model = new \App\Models\PatientModel();
        
        // Get patients with their most recent doctor assignment
        $db = \Config\Database::connect();
        $builder = $db->table('patients p');
        $builder->select('p.*, 
                         u.name as assigned_doctor_name,
                         a.appointment_date as last_appointment_date,
                         a.status as appointment_status,
                         p.room_number,
                         r.room_number as appointment_room_number');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date, status, room_id, 
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments 
                         WHERE status != "cancelled") a', 'a.patient_id = p.id AND a.rn = 1', 'left');
        $builder->join('users u', 'u.id = a.doctor_id', 'left');
        $builder->join('rooms r', 'r.id = a.room_id', 'left');
        $builder->where('p.status', 'active');
        $builder->orderBy('p.id', 'DESC');
        
        $patients = $builder->get()->getResultArray();

        // Fetch prescriptions for these patients (latest first)
        $prescriptionsByPatient = [];
        if (!empty($patients)) {
            $patientIds = array_column($patients, 'id');
            $rxModel = new PrescriptionModel();
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
        }

        $data = [
            'title' => 'Treatment Updates - HMS',
            'user_role' => 'nurse',
            'user_name' => session()->get('name'),
            'patients' => $patients,
            'prescriptionsByPatient' => $prescriptionsByPatient,
        ];

        return view('nurse/treatment_updates', $data);
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

}
