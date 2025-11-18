<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorScheduleModel extends Model
{
    protected $table = 'doctor_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'doctor_id',
        'day_of_week',
        'schedule_date',
        'start_time',
        'end_time',
        'is_available',
        'notes',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'doctor_id' => 'required|integer',
        'day_of_week' => 'required|in_list[monday,tuesday,wednesday,thursday,friday,saturday,sunday]',
        'start_time' => 'required',
        'end_time' => 'required'
    ];

    // Get doctor's schedule for a specific day
    public function getDoctorScheduleByDay($doctorId, $dayOfWeek)
    {
        return $this->where('doctor_id', $doctorId)
                   ->where('day_of_week', $dayOfWeek)
                   ->where('is_available', true)
                   ->findAll();
    }

    // Get doctor's full weekly schedule
    public function getDoctorWeeklySchedule($doctorId)
    {
        return $this->where('doctor_id', $doctorId)
                   ->orderBy('FIELD(day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday")')
                   ->orderBy('start_time', 'ASC')
                   ->findAll();
    }

    // Check if doctor is available on specific day and time
    public function isDoctorAvailableOnSchedule($doctorId, $date, $time)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        $schedule = $this->where('doctor_id', $doctorId)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('is_available', true)
                        ->where('start_time <=', $time)
                        ->where('end_time >=', $time)
                        ->first();
        
        return $schedule !== null;
    }

    // Get available time slots for a doctor on a specific date
    public function getAvailableTimeSlots($doctorId, $date)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        $schedules = $this->where('doctor_id', $doctorId)
                         ->where('day_of_week', $dayOfWeek)
                         ->where('is_available', true)
                         ->findAll();
        
        $timeSlots = [];
        foreach ($schedules as $schedule) {
            $startTime = strtotime($schedule['start_time']);
            $endTime = strtotime($schedule['end_time']);
            
            // Generate 30-minute time slots
            for ($time = $startTime; $time < $endTime; $time += 1800) { // 1800 seconds = 30 minutes
                $timeSlots[] = date('H:i', $time);
            }
        }
        
        return $timeSlots;
    }

    // Get all doctors with their schedules
    public function getAllDoctorsWithSchedules()
    {
        $builder = $this->db->table($this->table);
        $builder->select('doctor_schedules.*, users.name as doctor_name');
        $builder->join('users', 'users.id = doctor_schedules.doctor_id');
        $builder->where('users.role', 'doctor');
        $builder->orderBy('users.name', 'ASC');
        $builder->orderBy('FIELD(day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday")');
        $builder->orderBy('start_time', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}
