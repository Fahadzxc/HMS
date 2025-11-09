<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicationModel extends Model
{
    protected $table            = 'medications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'name',        // Amoxicillin
        'strength',    // 500mg
        'form',        // tablet, capsule, syrup
        'default_dosage',
        'default_quantity',
        'notes',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function listOptions(): array
    {
        return $this->orderBy('name', 'ASC')->findAll(500);
    }
}
