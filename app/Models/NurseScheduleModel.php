<?php

namespace App\Models;

use CodeIgniter\Model;

class NurseScheduleModel extends Model
{
    protected $table = 'nurse_schedules';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'nurse_id',
        'day_of_week',
        'shift_type',
        'start_time',
        'end_time',
        'ward_assignment',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nurse_id' => 'required|integer',
        'day_of_week' => 'required|in_list[monday,tuesday,wednesday,thursday,friday,saturday,sunday]',
        'shift_type' => 'required|in_list[morning,afternoon,night,double]',
        'start_time' => 'required',
        'end_time' => 'required',
        'is_active' => 'in_list[0,1]'
    ];

    /**
     * Get nurse schedule with user details
     */
    public function getNurseScheduleWithUser($nurseId)
    {
        return $this->select('nurse_schedules.*, users.name as nurse_name, users.email')
                    ->join('users', 'users.id = nurse_schedules.nurse_id', 'left')
                    ->where('nurse_schedules.nurse_id', $nurseId)
                    ->where('nurse_schedules.is_active', 1)
                    ->orderBy('FIELD(nurse_schedules.day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday")')
                    ->findAll();
    }

    /**
     * Get all nurse schedules with user details
     */
    public function getAllSchedulesWithUsers()
    {
        return $this->select('nurse_schedules.*, users.name as nurse_name, users.email, users.status')
                    ->join('users', 'users.id = nurse_schedules.nurse_id', 'left')
                    ->where('nurse_schedules.is_active', 1)
                    ->orderBy('users.name', 'ASC')
                    ->orderBy('FIELD(nurse_schedules.day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday")')
                    ->findAll();
    }

    /**
     * Get nurses with their current schedules
     */
    public function getNursesWithSchedules()
    {
        $db = \Config\Database::connect();
        
        // Get all nurses from users table
        $builder = $db->table('users u');
        $builder->select('u.id, u.name, u.email, u.status, u.created_at,
                         COUNT(ns.id) as total_shifts,
                         GROUP_CONCAT(DISTINCT ns.shift_type) as shift_types,
                         GROUP_CONCAT(DISTINCT ns.ward_assignment) as wards');
        $builder->join('nurse_schedules ns', 'ns.nurse_id = u.id AND ns.is_active = 1', 'left');
        $builder->where('u.role', 'nurse');
        $builder->where('u.status', 'active');
        $builder->groupBy('u.id, u.name, u.email, u.status, u.created_at');
        $builder->orderBy('u.name', 'ASC');
        
        $nurses = $builder->get()->getResultArray();

        // Get detailed schedules for each nurse
        foreach ($nurses as &$nurse) {
            $scheduleBuilder = $db->table('nurse_schedules ns');
            $scheduleBuilder->select('ns.*');
            $scheduleBuilder->where('ns.nurse_id', $nurse['id']);
            $scheduleBuilder->where('ns.is_active', 1);
            $scheduleBuilder->orderBy('FIELD(ns.day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday")');
            
            $nurse['schedules'] = $scheduleBuilder->get()->getResultArray();
        }

        return $nurses;
    }

    /**
     * Get nurses available for a specific day and shift
     */
    public function getAvailableNurses($dayOfWeek, $shiftType = null)
    {
        $builder = $this->select('nurse_schedules.*, users.name as nurse_name, users.email')
                        ->join('users', 'users.id = nurse_schedules.nurse_id', 'left')
                        ->where('nurse_schedules.day_of_week', $dayOfWeek)
                        ->where('nurse_schedules.is_active', 1)
                        ->where('users.status', 'active');
        
        if ($shiftType) {
            $builder->where('nurse_schedules.shift_type', $shiftType);
        }
        
        return $builder->findAll();
    }

    /**
     * Update or create nurse schedule
     */
    public function updateNurseSchedule($nurseId, $schedules)
    {
        $db = \Config\Database::connect();
        
        // Start transaction
        $db->transStart();
        
        // Deactivate existing schedules
        $this->where('nurse_id', $nurseId)->set(['is_active' => 0])->update();
        
        // Insert new schedules
        foreach ($schedules as $schedule) {
            $schedule['nurse_id'] = $nurseId;
            $schedule['is_active'] = 1;
            $this->insert($schedule);
        }
        
        // Complete transaction
        $db->transComplete();
        
        return $db->transStatus();
    }
}
