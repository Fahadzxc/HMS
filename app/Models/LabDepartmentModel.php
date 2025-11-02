<?php

namespace App\Models;

use CodeIgniter\Model;

class LabDepartmentModel extends Model
{
    protected $table = 'lab_departments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'description',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithBranch()
    {
        return $this->orderBy('lab_departments.name', 'ASC')->findAll();
    }
}
