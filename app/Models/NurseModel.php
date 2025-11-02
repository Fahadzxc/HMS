<?php

namespace App\Models;

use CodeIgniter\Model;

class NurseModel extends Model
{
    protected $table = 'nurses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'employee_id',
        'license_number',
        'specialization',
        'department',
        'shift',
        'ward_assignment',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getNurseWithUser(int $id)
    {
        return $this->select('nurses.*, users.name, users.email, users.status, users.role')
                    ->join('users', 'users.id = nurses.user_id', 'left')
                    ->where('nurses.id', $id)
                    ->first();
    }

    public function getAllWithUser()
    {
        return $this->select('nurses.*, users.name, users.email, users.status, users.role')
                    ->join('users', 'users.id = nurses.user_id', 'left')
                    ->orderBy('users.name', 'ASC')
                    ->findAll();
    }
}
