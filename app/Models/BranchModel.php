<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table = 'branches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name',
        'code',
        'address',
        'contact_number',
        'email',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
