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
        $builder = $this->select('lab_test_results.*, patients.full_name AS patient_name, lab_test_requests.test_type, staff_user.name AS released_by_name, auditors.name AS audited_by_name')
            ->join('lab_test_requests', 'lab_test_requests.id = lab_test_results.request_id', 'left')
            ->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
            ->join('lab_staff', 'lab_staff.id = lab_test_results.released_by', 'left')
            ->join('users AS staff_user', 'staff_user.id = lab_staff.user_id', 'left')
            ->join('users AS auditors', 'auditors.id = lab_test_results.audited_by', 'left');

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
