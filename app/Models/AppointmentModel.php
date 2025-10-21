<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'patient_id',
        'doctor_id', 
        'appointment_date',
        'appointment_time',
        'appointment_type',
        'status',
        'notes',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'patient_id' => 'required|integer',
        'doctor_id' => 'required|integer',
        'appointment_date' => 'required|valid_date',
        'appointment_time' => 'required',
        'appointment_type' => 'required|in_list[consultation,follow-up,emergency,routine]',
        'status' => 'required|in_list[scheduled,confirmed,completed,cancelled,no-show]'
    ];

    protected $validationMessages = [
        'patient_id' => [
            'required' => 'Patient is required',
            'integer' => 'Invalid patient selected'
        ],
        'doctor_id' => [
            'required' => 'Doctor is required',
            'integer' => 'Invalid doctor selected'
        ],
        'appointment_date' => [
            'required' => 'Appointment date is required',
            'valid_date' => 'Please enter a valid date'
        ],
        'appointment_time' => [
            'required' => 'Appointment time is required'
        ],
        'appointment_type' => [
            'required' => 'Appointment type is required',
            'in_list' => 'Invalid appointment type'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Invalid status'
        ]
    ];

    // Get appointments with patient and doctor details
    public function getAppointmentsWithDetails($limit = null, $offset = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name');
        $builder->join('patients', 'patients.id = appointments.patient_id', 'left');
        $builder->join('users', 'users.id = appointments.doctor_id', 'left');
        $builder->orderBy('appointments.appointment_date', 'ASC');
        $builder->orderBy('appointments.appointment_time', 'ASC');
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }

    // Get appointments for a specific date
    public function getAppointmentsByDate($date)
    {
        $builder = $this->db->table($this->table);
        $builder->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name');
        $builder->join('patients', 'patients.id = appointments.patient_id', 'left');
        $builder->join('users', 'users.id = appointments.doctor_id', 'left');
        $builder->where('appointment_date', $date);
        $builder->where('appointments.status !=', 'cancelled');
        $builder->orderBy('appointment_time', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    // Get appointments for a specific doctor
    public function getAppointmentsByDoctor($doctorId, $date = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name');
        $builder->join('patients', 'patients.id = appointments.patient_id', 'left');
        $builder->join('users', 'users.id = appointments.doctor_id', 'left');
        $builder->where('appointments.doctor_id', $doctorId);
        $builder->where('appointments.status !=', 'cancelled');
        
        if ($date) {
            $builder->where('appointment_date', $date);
        }
        
        return $builder->orderBy('appointment_date', 'ASC')
                      ->orderBy('appointment_time', 'ASC')
                      ->get()->getResultArray();
    }

    // Get upcoming appointments for a specific doctor
    public function getUpcomingAppointmentsByDoctor($doctorId, $limit = 50)
    {
        $builder = $this->db->table($this->table);
        $builder->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name');
        $builder->join('patients', 'patients.id = appointments.patient_id', 'left');
        $builder->join('users', 'users.id = appointments.doctor_id', 'left');
        $builder->where('appointments.doctor_id', $doctorId);
        $builder->where('appointment_date >=', date('Y-m-d'));
        $builder->where('appointments.status !=', 'cancelled');
        $builder->orderBy('appointment_date', 'ASC');
        $builder->orderBy('appointment_time', 'ASC');
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }

    // Check doctor availability
    public function isDoctorAvailable($doctorId, $date, $time, $excludeId = null)
    {
        // First check if doctor has a schedule for this day/time
        $scheduleModel = new \App\Models\DoctorScheduleModel();
        $hasSchedule = $scheduleModel->isDoctorAvailableOnSchedule($doctorId, $date, $time);
        
        if (!$hasSchedule) {
            return false; // Doctor not scheduled to work at this time
        }
        
        // Check if there's already an appointment at this time
        $builder = $this->db->table($this->table);
        $builder->where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->where('appointment_time', $time)
                ->where('status !=', 'cancelled');
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        $count = $builder->countAllResults();
        return $count == 0; // Available if no conflicting appointments
    }

    // Get today's appointments
    public function getTodaysAppointments()
    {
        return $this->getAppointmentsByDate(date('Y-m-d'));
    }

    // Get upcoming appointments
    public function getUpcomingAppointments($limit = 10)
    {
        $builder = $this->db->table($this->table);
        $builder->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name');
        $builder->join('patients', 'patients.id = appointments.patient_id', 'left');
        $builder->join('users', 'users.id = appointments.doctor_id', 'left');
        $builder->where('appointment_date >=', date('Y-m-d'));
        $builder->where('appointments.status !=', 'cancelled');
        $builder->orderBy('appointment_date', 'ASC');
        $builder->orderBy('appointment_time', 'ASC');
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }
}
