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

        // Get doctors from users table with their patient assignments and appointments
        $db = \Config\Database::connect();
        
        // Get all doctors with their appointment counts and recent appointments
        $builder = $db->table('users u');
        $builder->select('u.id, u.name, u.email, u.status, u.created_at,
                         COUNT(DISTINCT a.patient_id) as total_patients,
                         COUNT(a.id) as total_appointments,
                         COUNT(CASE WHEN a.appointment_date = CURDATE() THEN 1 END) as todays_appointments,
                         COUNT(CASE WHEN a.appointment_date > CURDATE() THEN 1 END) as upcoming_appointments');
        $builder->join('appointments a', 'a.doctor_id = u.id', 'left');
        $builder->where('u.role', 'doctor');
        $builder->where('u.status', 'active');
        $builder->groupBy('u.id, u.name, u.email, u.status, u.created_at');
        $builder->orderBy('u.name', 'ASC');
        
        $doctors = $builder->get()->getResultArray();

        // Get recent appointments for each doctor
        foreach ($doctors as &$doctor) {
            $appointmentBuilder = $db->table('appointments a');
            $appointmentBuilder->select('a.*, p.full_name as patient_name, r.room_number');
            $appointmentBuilder->join('patients p', 'p.id = a.patient_id', 'left');
            $appointmentBuilder->join('rooms r', 'r.id = a.room_id', 'left');
            $appointmentBuilder->where('a.doctor_id', $doctor['id']);
            $appointmentBuilder->where('a.status !=', 'cancelled');
            $appointmentBuilder->orderBy('a.appointment_date', 'DESC');
            $appointmentBuilder->orderBy('a.appointment_time', 'DESC');
            $appointmentBuilder->limit(5);
            
            $doctor['recent_appointments'] = $appointmentBuilder->get()->getResultArray();
        }

        $data = [
            'pageTitle' => 'Doctors',
            'title' => 'Doctors - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'doctors' => $doctors,
        ];

        return view('admin/doctors/index', $data);
    }
}
