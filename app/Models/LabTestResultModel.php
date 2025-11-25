<?php

namespace App\Models;

use CodeIgniter\Model;

class LabTestResultModel extends Model
{
    protected $table = 'lab_test_results';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'request_id',
        'result_summary',
        'detailed_report_path',
        'released_by',
        'released_at',
        'status',
        'critical_flag',
        'audited_by',
        'audited_at',
        'audit_notes',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithRelations(array $filters = [])
    {
        $db = \Config\Database::connect();

        // Ensure required base tables exist
        $requiredExist = $db->tableExists('lab_test_results') &&
                         $db->tableExists('lab_test_requests') &&
                         $db->tableExists('patients') &&
                         $db->tableExists('users');
        if (!$requiredExist) {
            return [];
        }

        // Build select dynamically depending on available optional tables
        $select = 'lab_test_results.*, patients.full_name AS patient_name, lab_test_requests.test_type, auditors.name AS audited_by_name';
        $joinStaff = $db->tableExists('lab_staff');
        if ($joinStaff) {
            $select .= ', staff_user.name AS released_by_name';
        }

        $builder = $this->select($select)
            ->join('lab_test_requests', 'lab_test_requests.id = lab_test_results.request_id', 'left')
            ->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
            ->join('users AS auditors', 'auditors.id = lab_test_results.audited_by', 'left');

        // Only join lab_staff if it exists (it may have been removed)
        if ($joinStaff) {
            $builder
                ->join('lab_staff', 'lab_staff.id = lab_test_results.released_by', 'left')
                ->join('users AS staff_user', 'staff_user.id = lab_staff.user_id', 'left');
        }

        if (!empty($filters['status'])) {
            $builder->where('lab_test_results.status', $filters['status']);
        }

        if (!empty($filters['critical'])) {
            $builder->where('lab_test_results.critical_flag', $filters['critical']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('lab_test_results.released_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('lab_test_results.released_at <=', $filters['date_to']);
        }

        return $builder->orderBy('lab_test_results.released_at', 'DESC')->findAll();
    }
}
