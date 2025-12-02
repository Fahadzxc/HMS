<?php

namespace App\Models;

use CodeIgniter\Model;

class InsuranceProviderModel extends Model
{
    protected $table            = 'insurance_providers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'coverage_room',
        'coverage_lab',
        'coverage_meds',
        'coverage_pf',
        'coverage_procedure',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'coverage_room' => 'permit_empty|decimal',
        'coverage_lab' => 'permit_empty|decimal',
        'coverage_meds' => 'permit_empty|decimal',
        'coverage_pf' => 'permit_empty|decimal',
        'coverage_procedure' => 'permit_empty|decimal',
    ];

    /**
     * Get active insurance providers
     */
    public function getActiveProviders()
    {
        return $this->where('is_active', true)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Get coverage by provider name
     */
    public function getCoverageByName($providerName)
    {
        $provider = $this->where('name', $providerName)
                        ->where('is_active', true)
                        ->first();
        
        if (!$provider) {
            return null;
        }

        return [
            'room' => floatval($provider['coverage_room'] ?? 0),
            'laboratory' => floatval($provider['coverage_lab'] ?? 0),
            'medication' => floatval($provider['coverage_meds'] ?? 0),
            'professional' => floatval($provider['coverage_pf'] ?? 0),
            'procedure' => floatval($provider['coverage_procedure'] ?? 0),
        ];
    }

    /**
     * Get all coverage mappings for all providers
     */
    public function getAllCoverageMappings()
    {
        $providers = $this->where('is_active', true)->findAll();
        $mappings = [];

        foreach ($providers as $provider) {
            $mappings[$provider['name']] = [
                'room' => floatval($provider['coverage_room'] ?? 0),
                'laboratory' => floatval($provider['coverage_lab'] ?? 0),
                'medication' => floatval($provider['coverage_meds'] ?? 0),
                'professional' => floatval($provider['coverage_pf'] ?? 0),
                'procedure' => floatval($provider['coverage_procedure'] ?? 0),
            ];
        }

        return $mappings;
    }
}

