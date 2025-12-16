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
        'admission_id', // Links prescription to specific admission for inpatients
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
        'status' => 'permit_empty|in_list[pending,dispensed,cancelled,completed,printed]'
    ];

    public function getDoctorPrescriptions(int $doctorId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('prescriptions p');
        $builder->select('p.*, COALESCE(pt.full_name, CONCAT("Patient #", p.patient_id)) as patient_name');
        $builder->join('patients pt', 'pt.id = p.patient_id', 'left');
        $builder->where('p.doctor_id', $doctorId);
        $builder->orderBy('p.created_at', 'DESC');
        $builder->limit(100);
        
        $prescriptions = $builder->get()->getResultArray();
        
        // Fallback: If patient_name is still null/empty, fetch from patients table directly
        foreach ($prescriptions as &$rx) {
            if (empty($rx['patient_name']) || $rx['patient_name'] === null) {
                $patient = $db->table('patients')->where('id', $rx['patient_id'])->get()->getRowArray();
                if ($patient) {
                    $rx['patient_name'] = $patient['full_name'] ?? 'Patient #' . $rx['patient_id'];
                } else {
                    $rx['patient_name'] = 'Patient #' . $rx['patient_id'];
                }
            }
        }
        unset($rx);
        
        return $prescriptions;
    }

    /**
     * Get prescriptions by admission_id (for inpatients)
     */
    public function getPrescriptionsByAdmission(int $admissionId): array
    {
        return $this->where('admission_id', $admissionId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get prescriptions for a patient's current admission (inpatient)
     */
    public function getCurrentAdmissionPrescriptions(int $patientId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('prescriptions p');
        $builder->select('p.*, a.appointment_date as admission_date, a.room_id');
        $builder->join('appointments a', 'a.id = p.admission_id', 'left');
        $builder->where('p.patient_id', $patientId);
        $builder->where('p.admission_id IS NOT NULL');
        $builder->orderBy('p.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}
