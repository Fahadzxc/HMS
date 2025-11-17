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
        'test_type',
        'priority',
        'status',
        'requested_at',
        'assigned_staff_id',
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
            $selectFields = 'lab_test_requests.*, patients.full_name AS patient_name, doctors.name AS doctor_name';
            
            if ($db->tableExists('lab_staff')) {
                $selectFields .= ', staff_user.name AS staff_name';
            }
            
            $builder = $this->select($selectFields);
            $builder->join('patients', 'patients.id = lab_test_requests.patient_id', 'left');
            $builder->join('users AS doctors', 'doctors.id = lab_test_requests.doctor_id', 'left');
            
            // Only join lab_staff if table exists
            if ($db->tableExists('lab_staff')) {
                $builder->join('lab_staff', 'lab_staff.id = lab_test_requests.assigned_staff_id', 'left');
                $builder->join('users AS staff_user', 'staff_user.id = lab_staff.user_id', 'left');
            }

            if (!empty($filters['status'])) {
                $builder->where('lab_test_requests.status', $filters['status']);
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
}
