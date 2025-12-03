<?php

namespace App\Models;

use CodeIgniter\Model;

class LabTestRequestModel extends Model
{
    protected $table = 'lab_test_requests';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'patient_id',
        'doctor_id',
        'admission_id', // Links lab request to specific admission for inpatients
        'test_type',
        'priority',
        'status',
        'requested_at',
        'assigned_staff_id',
        'sent_by_nurse_id', // Nurse who marked as sent to lab
        'sent_at', // Timestamp when nurse marked as sent
        'notes',
        'override_reason',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithRelations(array $filters = [])
    {
        $db = \Config\Database::connect();
        
        // Check if required tables exist
        $tablesExist = $db->tableExists('lab_test_requests') && 
                      $db->tableExists('patients') && 
                      $db->tableExists('users');
        
        if (!$tablesExist) {
            return [];
        }
        
        try {
            // Build select statement based on available tables
            $selectFields = 'lab_test_requests.*, patients.full_name AS patient_name, patients.patient_type AS patient_type, doctors.name AS doctor_name';
            
            if ($db->tableExists('lab_staff')) {
                $selectFields .= ', staff_user.name AS staff_name';
            }
            
            // Add nurse name if sent_by_nurse_id column exists
            // Check by querying information_schema
            $hasSentByNurseId = false;
            try {
                $checkColumn = $db->query("
                    SELECT COUNT(*) as col_count 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'lab_test_requests' 
                    AND COLUMN_NAME = 'sent_by_nurse_id'
                ")->getRow();
                $hasSentByNurseId = ($checkColumn && $checkColumn->col_count > 0);
            } catch (\Exception $e) {
                // If we can't check, assume column doesn't exist
                $hasSentByNurseId = false;
            }
            
            if ($hasSentByNurseId) {
                $selectFields .= ', nurse_user.name AS sent_by_nurse_name';
            }
            
            $builder = $this->select($selectFields);
            $builder->join('patients', 'patients.id = lab_test_requests.patient_id', 'left');
            $builder->join('users AS doctors', 'doctors.id = lab_test_requests.doctor_id', 'left');
            
            // Only join nurse if column exists
            if ($hasSentByNurseId) {
                $builder->join('users AS nurse_user', 'nurse_user.id = lab_test_requests.sent_by_nurse_id', 'left');
            }
            
            // Only join lab_staff if table exists
            if ($db->tableExists('lab_staff')) {
                $builder->join('lab_staff', 'lab_staff.id = lab_test_requests.assigned_staff_id', 'left');
                $builder->join('users AS staff_user', 'staff_user.id = lab_staff.user_id', 'left');
            }

            if (!empty($filters['status'])) {
                $builder->where('lab_test_requests.status', $filters['status']);
            }
            
            if (!empty($filters['id'])) {
                $builder->where('lab_test_requests.id', $filters['id']);
            }

            if (!empty($filters['priority'])) {
                $builder->where('lab_test_requests.priority', $filters['priority']);
            }

            if (!empty($filters['date_from'])) {
                $builder->where('lab_test_requests.requested_at >=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $builder->where('lab_test_requests.requested_at <=', $filters['date_to']);
            }
            
            // Filter for outpatient only (admission_id IS NULL)
            if (isset($filters['outpatient_only']) && $filters['outpatient_only'] === true) {
                $builder->where('lab_test_requests.admission_id IS NULL', null, false);
            }

            return $builder->orderBy('lab_test_requests.requested_at', 'DESC')->findAll();
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getAllWithRelations: ' . $e->getMessage());
            // Return empty array on error
            return [];
        }
    }

    public function getDashboardMetrics(): array
    {
        $today = date('Y-m-d');

        return [
            'pending_requests' => $this->where('status', 'pending')->countAllResults(),
            'completed_today' => $this->where('status', 'completed')->where('DATE(updated_at)', $today)->countAllResults(),
            'critical_results' => $this->where('priority', 'critical')->countAllResults(),
        ];
    }

    /**
     * Get lab requests by admission_id (for inpatients)
     */
    public function getRequestsByAdmission(int $admissionId): array
    {
        return $this->where('admission_id', $admissionId)
                    ->orderBy('requested_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get lab requests for a patient's current admission (inpatient)
     */
    public function getCurrentAdmissionRequests(int $patientId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('lab_test_requests lr');
        $builder->select('lr.*, a.appointment_date as admission_date, a.room_id');
        $builder->join('appointments a', 'a.id = lr.admission_id', 'left');
        $builder->where('lr.patient_id', $patientId);
        $builder->where('lr.admission_id IS NOT NULL');
        $builder->orderBy('lr.requested_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}
