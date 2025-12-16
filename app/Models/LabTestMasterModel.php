<?php

namespace App\Models;

use CodeIgniter\Model;

class LabTestMasterModel extends Model
{
    protected $table = 'lab_tests_master';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'test_name',
        'test_category',
        'requires_specimen',
        'price',
        'description',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get test information by test name
     */
    public function getTestByName(string $testName): ?array
    {
        $test = $this->where('test_name', $testName)
                     ->where('is_active', 1)
                     ->first();
        
        return $test ?: null;
    }

    /**
     * Get all active tests grouped by category
     */
    public function getTestsByCategory(): array
    {
        $tests = $this->where('is_active', 1)
                      ->orderBy('test_category', 'ASC')
                      ->orderBy('test_name', 'ASC')
                      ->findAll();
        
        $grouped = [];
        foreach ($tests as $test) {
            $category = $test['test_category'] ?? 'Other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $test;
        }
        
        return $grouped;
    }

    /**
     * Check if test requires specimen
     */
    public function requiresSpecimen(string $testName): bool
    {
        $test = $this->getTestByName($testName);
        return $test && (int)($test['requires_specimen'] ?? 0) === 1;
    }

    /**
     * Get price for a test
     */
    public function getTestPrice(string $testName): float
    {
        $test = $this->getTestByName($testName);
        return $test ? (float)($test['price'] ?? 0.00) : 0.00;
    }
}
