<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'bill_id',
        'patient_id',
        'payment_number',
        'amount',
        'payment_method',
        'payment_date',
        'transaction_id',
        'reference_number',
        'notes',
        'status',
        'processed_by',
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
        'amount' => 'required|decimal',
        'payment_method' => 'required|in_list[cash,credit_card,debit_card,insurance,check,bank_transfer,online]',
        'status' => 'permit_empty|in_list[pending,completed,failed,refunded]'
    ];

    public function generatePaymentNumber()
    {
        $year = date('Y');
        $lastPayment = $this->select('payment_number')
            ->like('payment_number', "PAY-{$year}-", 'after')
            ->orderBy('id', 'DESC')
            ->first();
        
        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment['payment_number'], -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "PAY-{$year}-" . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    public function getPaymentsWithPatient($filters = [])
    {
        $builder = $this->db->table('payments p');
        $builder->select('p.*, pt.full_name as patient_name, b.bill_number');
        $builder->join('patients pt', 'pt.id = p.patient_id', 'left');
        $builder->join('bills b', 'b.id = p.bill_id', 'left');
        
        if (!empty($filters['status'])) {
            $builder->where('p.status', $filters['status']);
        }
        
        if (!empty($filters['patient_id'])) {
            $builder->where('p.patient_id', $filters['patient_id']);
        }
        
        if (!empty($filters['payment_method'])) {
            $builder->where('p.payment_method', $filters['payment_method']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('p.payment_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('p.payment_date <=', $filters['date_to']);
        }
        
        $builder->orderBy('p.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}

