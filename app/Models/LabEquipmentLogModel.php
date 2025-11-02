<?php

namespace App\Models;

use CodeIgniter\Model;

class LabEquipmentLogModel extends Model
{
    protected $table = 'lab_equipment_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'equipment_id',
        'activity',
        'performed_by',
        'performed_at',
        'outcome',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getLogsForEquipment(int $equipmentId)
    {
        return $this
            ->select('lab_equipment_logs.*, lab_equipment.name AS equipment_name, tech_user.name AS staff_name')
            ->join('lab_equipment', 'lab_equipment.id = lab_equipment_logs.equipment_id', 'left')
            ->join('lab_staff', 'lab_staff.id = lab_equipment_logs.performed_by', 'left')
            ->join('users AS tech_user', 'tech_user.id = lab_staff.user_id', 'left')
            ->where('lab_equipment_logs.equipment_id', $equipmentId)
            ->orderBy('performed_at', 'DESC')
            ->findAll();
    }
}
