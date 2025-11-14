<?php

namespace App\Models;

use CodeIgniter\Model;

class BillItemModel extends Model
{
    protected $table            = 'bill_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'bill_id',
        'item_type',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'reference_id',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'bill_id' => 'required|integer',
        'item_name' => 'required|max_length[255]',
        'quantity' => 'required|decimal',
        'unit_price' => 'required|decimal',
        'total_price' => 'required|decimal'
    ];
}

