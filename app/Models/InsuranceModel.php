<?php

namespace App\Models;

use CodeIgniter\Model;

class InsuranceModel extends Model
{
    protected $table            = 'insurance_claims';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'claim_number',
        'bill_id',
        'patient_id',
        'insurance_provider',
        'policy_number',
        'member_id',
        'claim_amount',
        'approved_amount',
        'deductible',
        'co_payment',
        'status',
        'submitted_date',
        'approved_date',
        'rejected_date',
        'rejection_reason',
        'notes',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'bill_id' => 'required|integer',
        'patient_id' => 'required|integer',
        'insurance_provider' => 'required|max_length[255]',
        'claim_amount' => 'required|decimal',
        'status' => 'permit_empty|in_list[pending,submitted,approved,rejected,paid,cancelled]'
    ];

    public function getClaimsWithPatient($filters = [])
    {
        $builder = $this->db->table('insurance_claims ic');
        $builder->select('ic.*, p.full_name as patient_name, p.patient_id as patient_code, b.bill_number, b.total_amount as bill_amount');
        $builder->join('patients p', 'p.id = ic.patient_id', 'left');
        $builder->join('bills b', 'b.id = ic.bill_id', 'left');
        
        if (!empty($filters['status'])) {
            $builder->where('ic.status', $filters['status']);
        }
        
        if (!empty($filters['patient_id'])) {
            $builder->where('ic.patient_id', $filters['patient_id']);
        }
        
        if (!empty($filters['insurance_provider'])) {
            $builder->where('ic.insurance_provider', $filters['insurance_provider']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('ic.submitted_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('ic.submitted_date <=', $filters['date_to']);
        }
        
        $builder->orderBy('ic.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    public function generateClaimNumber()
    {
        $year = date('Y');
        $lastClaim = $this->select('claim_number')
            ->like('claim_number', "CLM-{$year}-", 'after')
            ->orderBy('id', 'DESC')
            ->first();
        
        if ($lastClaim) {
            $lastNumber = (int) substr($lastClaim['claim_number'], -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "CLM-{$year}-" . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}

