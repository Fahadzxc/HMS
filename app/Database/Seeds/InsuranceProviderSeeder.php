<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InsuranceProviderSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'PhilHealth',
                'coverage_room' => 30.00,
                'coverage_lab' => 40.00,
                'coverage_meds' => 20.00,
                'coverage_pf' => 20.00,
                'coverage_procedure' => 30.00,
                'is_active' => true,
            ],
            [
                'name' => 'Maxicare',
                'coverage_room' => 50.00,
                'coverage_lab' => 60.00,
                'coverage_meds' => 30.00,
                'coverage_pf' => 40.00,
                'coverage_procedure' => 50.00,
                'is_active' => true,
            ],
            [
                'name' => 'Medicard',
                'coverage_room' => 60.00,
                'coverage_lab' => 50.00,
                'coverage_meds' => 25.00,
                'coverage_pf' => 50.00,
                'coverage_procedure' => 45.00,
                'is_active' => true,
            ],
            [
                'name' => 'Intellicare',
                'coverage_room' => 70.00,
                'coverage_lab' => 60.00,
                'coverage_meds' => 35.00,
                'coverage_pf' => 60.00,
                'coverage_procedure' => 55.00,
                'is_active' => true,
            ],
            [
                'name' => 'PhilCare',
                'coverage_room' => 40.00,
                'coverage_lab' => 50.00,
                'coverage_meds' => 20.00,
                'coverage_pf' => 40.00,
                'coverage_procedure' => 40.00,
                'is_active' => true,
            ],
            [
                'name' => 'Insular Healthcare',
                'coverage_room' => 45.00,
                'coverage_lab' => 55.00,
                'coverage_meds' => 25.00,
                'coverage_pf' => 35.00,
                'coverage_procedure' => 45.00,
                'is_active' => true,
            ],
            [
                'name' => 'Avega',
                'coverage_room' => 50.00,
                'coverage_lab' => 55.00,
                'coverage_meds' => 25.00,
                'coverage_pf' => 40.00,
                'coverage_procedure' => 50.00,
                'is_active' => true,
            ],
            [
                'name' => 'Pacific Cross',
                'coverage_room' => 60.00,
                'coverage_lab' => 70.00,
                'coverage_meds' => 40.00,
                'coverage_pf' => 50.00,
                'coverage_procedure' => 60.00,
                'is_active' => true,
            ],
            [
                'name' => 'None / Self-Pay',
                'coverage_room' => 0.00,
                'coverage_lab' => 0.00,
                'coverage_meds' => 0.00,
                'coverage_pf' => 0.00,
                'coverage_procedure' => 0.00,
                'is_active' => true,
            ],
        ];

        $this->db->table('insurance_providers')->insertBatch($data);
    }
}

