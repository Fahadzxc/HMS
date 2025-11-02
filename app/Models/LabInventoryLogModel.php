<?php

namespace App\Models;

use CodeIgniter\Model;

class LabInventoryLogModel extends Model
{
    protected $table = 'lab_inventory_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'item_id',
        'change_type',
        'quantity',
        'remarks',
        'recorded_by',
        'recorded_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getLogsForItem(int $itemId)
    {
        return $this
            ->select('lab_inventory_logs.*, lab_inventory_items.name AS item_name, users.name AS user_name')
            ->join('lab_inventory_items', 'lab_inventory_items.id = lab_inventory_logs.item_id', 'left')
            ->join('users', 'users.id = lab_inventory_logs.recorded_by', 'left')
            ->where('lab_inventory_logs.item_id', $itemId)
            ->orderBy('recorded_at', 'DESC')
            ->findAll();
    }
}
