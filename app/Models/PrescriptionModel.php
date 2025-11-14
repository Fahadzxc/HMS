<?php

namespace App\Models;

use CodeIgniter\Model;

class PrescriptionModel extends Model
{
    protected $table            = 'prescriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'items_json', // JSON array of items {name, dosage, frequency, duration, quantity, instructions}
        'notes',
        'status', // pending, dispensed, cancelled
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'patient_id' => 'required|integer',
        'doctor_id' => 'required|integer',
        'items_json' => 'required',
        'status' => 'permit_empty|in_list[pending,dispensed,cancelled,completed]'
    ];

    public function getDoctorPrescriptions(int $doctorId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('prescriptions p');
        $builder->select('p.*, pt.full_name as patient_name');
        $builder->join('patients pt', 'pt.id = p.patient_id', 'left');
        $builder->where('p.doctor_id', $doctorId);
        $builder->orderBy('p.created_at', 'DESC');
        $builder->limit(100);
        
        return $builder->get()->getResultArray();
    }
}
