<?php

namespace App\Models;

use CodeIgniter\Model;

class LabStaffModel extends Model
{
    protected $table = 'lab_staff';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'department_id',
        'role',
        'shift',
        'contact_number',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithRelations()
    {
        return $this
            ->select('lab_staff.*, users.name, users.email, lab_departments.name AS department_name')
            ->join('users', 'users.id = lab_staff.user_id', 'left')
            ->join('lab_departments', 'lab_departments.id = lab_staff.department_id', 'left')
            ->orderBy('users.name', 'ASC')
            ->findAll();
    }
}
