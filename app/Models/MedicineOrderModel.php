<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicineOrderModel extends Model
{
    protected $table = 'medicine_orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
    protected $allowedFields = [
        'order_number',
        'medication_id',
        'medicine_name',
        'supplier_name',
        'quantity_ordered',
        'unit_price',
        'total_price',
        'order_date',
        'status',
        'received_by',
        'reference',
        'delivered_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'medicine_name' => 'required|max_length[255]',
        'supplier_name' => 'required|max_length[255]',
        'quantity_ordered' => 'required|integer|greater_than[0]',
        'order_date' => 'required|valid_date',
        'status' => 'required|in_list[pending,delivered,cancelled]',
    ];

    public function getAllWithPharmacist()
    {
        return $this->select('medicine_orders.*, users.name as received_by_name')
            ->join('users', 'users.id = medicine_orders.received_by', 'left')
            ->orderBy('medicine_orders.order_date', 'DESC')
            ->orderBy('medicine_orders.created_at', 'DESC')
            ->findAll();
    }

    public function getByStatus($status)
    {
        return $this->where('status', $status)
            ->orderBy('order_date', 'DESC')
            ->findAll();
    }

    public function generateOrderNumber()
    {
        $lastOrder = $this->orderBy('id', 'DESC')->first();
        $nextId = $lastOrder ? ((int)$lastOrder['id'] + 1) : 1;
        return 'PO-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);
    }
}

