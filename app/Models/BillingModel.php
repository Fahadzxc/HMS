<?php

namespace App\Models;

use CodeIgniter\Model;

class BillingModel extends Model
{
    protected $table            = 'bills';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'bill_number',
        'patient_id',
        'appointment_id',
        'prescription_id',
        'lab_test_id',
        'room_id',
        'bill_type',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'due_date',
        'payment_method',
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
        'patient_id' => 'required|integer',
        'bill_type' => 'required|in_list[appointment,prescription,lab_test,room,consultation,procedure,other]',
        'total_amount' => 'required|decimal',
        'status' => 'permit_empty|in_list[pending,partial,paid,overdue,cancelled]'
    ];

    public function getBillsWithPatient($filters = [])
    {
        $builder = $this->db->table('bills b');
        $builder->select('b.*, p.full_name as patient_name, p.patient_id as patient_code, p.contact, p.email,
            (SELECT payment_method FROM payments WHERE bill_id = b.id AND status = "completed" ORDER BY created_at DESC LIMIT 1) as payment_method');
        $builder->join('patients p', 'p.id = b.patient_id', 'left');
        
        if (!empty($filters['status'])) {
            $builder->where('b.status', $filters['status']);
        }
        
        if (!empty($filters['patient_id'])) {
            $builder->where('b.patient_id', $filters['patient_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('b.created_at >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('b.created_at <=', $filters['date_to']);
        }
        
        $builder->orderBy('b.created_at', 'DESC');
        
        $bills = $builder->get()->getResultArray();
        
        // Determine bill_type from related records if it's 'other'
        foreach ($bills as &$bill) {
            if ($bill['bill_type'] === 'other') {
                if (!empty($bill['prescription_id'])) {
                    $bill['bill_type'] = 'prescription';
                } elseif (!empty($bill['appointment_id'])) {
                    $bill['bill_type'] = 'appointment';
                } elseif (!empty($bill['lab_test_id'])) {
                    $bill['bill_type'] = 'lab_test';
                } elseif (!empty($bill['room_id'])) {
                    $bill['bill_type'] = 'room';
                }
            }
        }
        
        return $bills;
    }

    public function getBillWithItems($billId)
    {
        $bill = $this->find($billId);
        if (!$bill) {
            return null;
        }
        
        // Get patient info
        $patientModel = new PatientModel();
        $bill['patient'] = $patientModel->find($bill['patient_id']);
        
        // Get bill items
        $itemsModel = new \App\Models\BillItemModel();
        $bill['items'] = $itemsModel->where('bill_id', $billId)->findAll();
        
        // Get payments
        $paymentModel = new \App\Models\PaymentModel();
        $bill['payments'] = $paymentModel->where('bill_id', $billId)->orderBy('created_at', 'DESC')->findAll();
        
        return $bill;
    }

    public function generateBillNumber()
    {
        $year = date('Y');
        $lastBill = $this->select('bill_number')
            ->like('bill_number', "BILL-{$year}-", 'after')
            ->orderBy('id', 'DESC')
            ->first();
        
        if ($lastBill) {
            $lastNumber = (int) substr($lastBill['bill_number'], -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "BILL-{$year}-" . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}

