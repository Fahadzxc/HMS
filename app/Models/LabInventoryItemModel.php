<?php

namespace App\Models;

use CodeIgniter\Model;

class LabInventoryItemModel extends Model
{
    protected $table = 'lab_inventory_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'category',
        'quantity',
        'unit',
        'reorder_point',
        'expiration_date',
        'supplier',
        'status',
        'lot_number',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithBranch()
    {
        return $this
            ->orderBy('lab_inventory_items.name', 'ASC')
            ->findAll();
    }

    public function getAlerts(): array
    {
        $today = date('Y-m-d');

        return [
            'low_stock' => $this->where('quantity <= reorder_point')->countAllResults(),
            'expiring' => $this->where('expiration_date <=', date('Y-m-d', strtotime('+30 days')))->countAllResults(),
        ];
    }
}
