<?php

namespace App\Models;

use CodeIgniter\Model;

class PharmacyStockMovementModel extends Model
{
    protected $table = 'pharmacy_stock_movements';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
    protected $allowedFields = [
        'medication_id',
        'medicine_name',
        'movement_type',
        'quantity_change',
        'previous_stock',
        'new_stock',
        'action_by',
        'notes',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAllWithUser()
    {
        return $this->select('pharmacy_stock_movements.*, users.name as action_by_name')
            ->join('users', 'users.id = pharmacy_stock_movements.action_by', 'left')
            ->orderBy('pharmacy_stock_movements.created_at', 'DESC')
            ->findAll();
    }

    public function getByMovementType($type)
    {
        return $this->where('movement_type', $type)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}

