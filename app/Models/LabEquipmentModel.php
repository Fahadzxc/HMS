<?php

namespace App\Models;

use CodeIgniter\Model;

class LabEquipmentModel extends Model
{
    protected $table = 'lab_equipment';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'serial_number',
        'department_id',
        'condition',
        'status',
        'last_maintenance',
        'next_maintenance',
        'technician_id',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithRelations()
    {
        return $this
            ->select('lab_equipment.*, lab_departments.name AS department_name, tech_user.name AS technician_name')
            ->join('lab_departments', 'lab_departments.id = lab_equipment.department_id', 'left')
            ->join('lab_staff', 'lab_staff.id = lab_equipment.technician_id', 'left')
            ->join('users AS tech_user', 'tech_user.id = lab_staff.user_id', 'left')
            ->orderBy('lab_equipment.name', 'ASC')
            ->findAll();
    }
}
