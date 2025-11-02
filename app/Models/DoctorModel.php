<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorModel extends Model
{
    protected $table = 'doctors';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'employee_id',
        'license_number',
        'specialization',
        'department',
        'consultation_fee',
        'shift',
        'profile_picture',
        'gender',
        'date_of_birth',
        'contact_number',
        'alternate_email',
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
        'status',
        'license_expiry_date',
        'years_of_experience',
        'educational_background',
        'certifications',
        'board_exam_passed',
        'medical_council_registration_no',
        'room_number',
        'branch_id',
        'assigned_ward_unit',
        'supervisor_id',
        'on_call_availability',
        'rotation_type',
        'duty_hours_per_week',
        'latest_patient_queue_count',
        'last_login_at',
        'access_permissions',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getDoctorWithUser(int $id)
    {
        return $this->select('doctors.*, users.name, users.email, users.role')
                    ->join('users', 'users.id = doctors.user_id', 'left')
                    ->where('doctors.id', $id)
                    ->first();
    }

    public function getAllWithUser()
    {
        return $this->select('doctors.*, users.name, users.email, users.role')
                    ->join('users', 'users.id = doctors.user_id', 'left')
                    ->orderBy('users.name', 'ASC')
                    ->findAll();
    }
}
