<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSpecimenRequirements extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('lab_tests_master')) {
            return;
        }

        // Check if requires_specimen column exists
        $fields = $this->db->getFieldData('lab_tests_master');
        $fieldNames = array_column($fields, 'name');
        $hasSpecimenColumn = in_array('requires_specimen', $fieldNames);
        
        if (!$hasSpecimenColumn) {
            // Add the column if it doesn't exist - find a suitable position
            $afterField = 'test_name';
            if (in_array('test_category', $fieldNames)) {
                $afterField = 'test_category';
            } elseif (in_array('price', $fieldNames)) {
                $afterField = 'price';
            }
            
            $this->forge->addColumn('lab_tests_master', [
                'requires_specimen' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => $afterField,
                    'comment'    => '1 = requires specimen collection, 0 = no specimen needed',
                ],
            ]);
        }

        // ✅ WITH SPECIMEN REQUIRED (requires_specimen = 1)
        $withSpecimen = [
            // Blood Tests
            'Complete Blood Count (CBC)',
            'Blood Glucose',
            'Lipid Profile',
            'Liver Function Test (LFT)',
            'Kidney Function Test (KFT)',
            'Thyroid Function Test',
            'Hemoglobin A1C',
            'Blood Culture',
            'Blood Typing',
            'Coagulation Profile',
            
            // Urine Tests
            'Urine Analysis',
            'Urine Culture',
            '24-Hour Urine Collection',
            'Urine Pregnancy Test',
            
            // Microbiology
            'Sputum Culture',
            'Stool Culture',
            'Throat Swab',
            'Wound Culture',
            
            // Other Tests
            'Biopsy',
        ];

        // ❌ WITHOUT SPECIMEN REQUIRED (requires_specimen = 0)
        $withoutSpecimen = [
            // Imaging Tests
            'X-Ray',
            'CT Scan',
            'MRI',
            'Ultrasound',
            'Echocardiogram',
            'Mammography',
            
            // Other Tests
            'ECG (Electrocardiogram)',
            'Pulmonary Function Test',
            'Bone Density Scan',
            'Pap Smear',
        ];

        // Update tests that require specimen
        foreach ($withSpecimen as $testName) {
            $this->db->table('lab_tests_master')
                ->where('test_name', $testName)
                ->update(['requires_specimen' => 1]);
        }

        // Update tests that don't require specimen
        foreach ($withoutSpecimen as $testName) {
            $this->db->table('lab_tests_master')
                ->where('test_name', $testName)
                ->update(['requires_specimen' => 0]);
        }
    }

    public function down()
    {
        // No rollback needed - this is a data update
    }
}
