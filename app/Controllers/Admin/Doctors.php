<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\DoctorModel;
use App\Models\BranchModel;
use App\Models\UserModel;

class Doctors extends Controller
{
    protected DoctorModel $doctorModel;
    protected BranchModel $branchModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->doctorModel = new DoctorModel();
        $this->branchModel = new BranchModel();
        $this->userModel   = new UserModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        // Get doctors from users table with their patient assignments from BOTH appointments AND admissions
        $db = \Config\Database::connect();
        
        // Get all doctors first
        $doctors = $db->table('users')
            ->select('id, name, email, status, created_at')
            ->where('role', 'doctor')
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();
        
        // Then get counts for each doctor
        foreach ($doctors as &$doctor) {
            $doctorId = $doctor['id'];
            
            // Count unique patients from appointments
            $apptPatients = $db->query("SELECT COUNT(DISTINCT patient_id) as cnt FROM appointments WHERE doctor_id = ? AND status != 'cancelled'", [$doctorId])->getRow()->cnt ?? 0;
            
            // Count unique patients from admissions
            $admPatients = $db->query("SELECT COUNT(DISTINCT patient_id) as cnt FROM admissions WHERE doctor_id = ?", [$doctorId])->getRow()->cnt ?? 0;
            
            // Total unique patients (simple sum for now, may have overlap)
            $doctor['total_patients'] = $apptPatients + $admPatients;
            
            // Today's appointments
            $doctor['todays_appointments'] = $db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', date('Y-m-d'))
                ->where('status !=', 'cancelled')
                ->countAllResults();
            
            // Upcoming = future appointments + current admitted patients
            $upcomingAppts = $db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date >', date('Y-m-d'))
                ->where('status !=', 'cancelled')
                ->countAllResults();
            
            $currentAdmissions = $db->table('admissions')
                ->where('doctor_id', $doctorId)
                ->where('status', 'Admitted')
                ->countAllResults();
            
            $doctor['upcoming_appointments'] = $upcomingAppts + $currentAdmissions;
        }

        // Get recent appointments AND admissions for each doctor
        foreach ($doctors as &$doctor) {
            // Get appointments
            $appointments = $db->table('appointments a')
                ->select('a.id, a.patient_id, a.appointment_date as date, a.appointment_time as time, a.status, p.full_name as patient_name, r.room_number, "appointment" as source')
                ->join('patients p', 'p.id = a.patient_id', 'left')
                ->join('rooms r', 'r.id = a.room_id', 'left')
                ->where('a.doctor_id', $doctor['id'])
                ->where('a.status !=', 'cancelled')
                ->orderBy('a.appointment_date', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
            
            // Get admissions
            $admissions = $db->table('admissions adm')
                ->select('adm.id, adm.patient_id, adm.admission_date as date, NULL as time, adm.status, p.full_name as patient_name, r.room_number, "admission" as source')
                ->join('patients p', 'p.id = adm.patient_id', 'left')
                ->join('rooms r', 'r.id = adm.room_id', 'left')
                ->where('adm.doctor_id', $doctor['id'])
                ->orderBy('adm.admission_date', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
            
            // Merge and sort by date
            $combined = array_merge($appointments, $admissions);
            usort($combined, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            $doctor['recent_appointments'] = array_slice($combined, 0, 5);
        }

        $data = [
            'pageTitle' => 'Doctors',
            'title' => 'Doctors - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'doctors' => $doctors,
        ];

        return view('admin/doctor', $data);
    }
}
