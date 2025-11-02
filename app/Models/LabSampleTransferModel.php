<?php

namespace App\Models;

use CodeIgniter\Model;

class LabSampleTransferModel extends Model
{
    protected $table = 'lab_sample_transfers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'request_id',
        'priority_flag',
        'status',
        'dispatched_at',
        'received_at',
        'comments',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithRelations()
    {
        return $this
            ->select('lab_sample_transfers.*, requests.test_type, patients.full_name AS patient_name')
            ->join('lab_test_requests AS requests', 'requests.id = lab_sample_transfers.request_id', 'left')
            ->join('patients', 'patients.id = requests.patient_id', 'left')
            ->orderBy('lab_sample_transfers.created_at', 'DESC')
            ->findAll();
    }
}
