<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLabTestsMasterTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'test_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'test_category' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Blood Tests, Urine Tests, Imaging, Microbiology, etc.',
            ],
            'requires_specimen' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = requires specimen collection, 0 = no specimen needed',
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('test_name');
        $this->forge->addKey('requires_specimen');
        $this->forge->createTable('lab_tests_master');
        
        // Insert default test data with pricing and specimen requirements
        // Based on user requirements:
        // ✅ WITH SPECIMEN REQUIRED: Blood Tests, Urine Tests, Microbiology, Biopsy
        // ❌ WITHOUT SPECIMEN REQUIRED: Imaging Tests, ECG, Pulmonary Function Test, Bone Density Scan, Pap Smear
        $defaultTests = [
            // ✅ Blood Tests (require specimen - 🩸 dugo)
            ['Complete Blood Count (CBC)', 'Blood Tests', 1, 500.00],
            ['Blood Glucose', 'Blood Tests', 1, 300.00],
            ['Lipid Profile', 'Blood Tests', 1, 600.00],
            ['Liver Function Test (LFT)', 'Blood Tests', 1, 800.00],
            ['Kidney Function Test (KFT)', 'Blood Tests', 1, 800.00],
            ['Thyroid Function Test', 'Blood Tests', 1, 700.00],
            ['Hemoglobin A1C', 'Blood Tests', 1, 500.00],
            ['Blood Culture', 'Blood Tests', 1, 1000.00],
            ['Blood Typing', 'Blood Tests', 1, 400.00],
            ['Coagulation Profile', 'Blood Tests', 1, 600.00],
            
            // ✅ Urine Tests (require specimen - 🧪 ihi)
            ['Urine Analysis', 'Urine Tests', 1, 300.00],
            ['Urine Culture', 'Urine Tests', 1, 500.00],
            ['24-Hour Urine Collection', 'Urine Tests', 1, 600.00],
            ['Urine Pregnancy Test', 'Urine Tests', 1, 250.00],
            
            // ❌ Imaging Tests (no specimen required)
            ['X-Ray', 'Imaging Tests', 0, 800.00],
            ['CT Scan', 'Imaging Tests', 0, 3000.00],
            ['MRI', 'Imaging Tests', 0, 5000.00],
            ['Ultrasound', 'Imaging Tests', 0, 1200.00],
            ['Echocardiogram', 'Imaging Tests', 0, 2000.00],
            ['Mammography', 'Imaging Tests', 0, 1500.00],
            
            // ✅ Microbiology (require specimen - 🫁 plema, 💩 dumi, swab)
            ['Sputum Culture', 'Microbiology', 1, 600.00],
            ['Stool Culture', 'Microbiology', 1, 500.00],
            ['Throat Swab', 'Microbiology', 1, 400.00],
            ['Wound Culture', 'Microbiology', 1, 600.00],
            
            // ❌ Other Tests (no specimen required)
            ['ECG (Electrocardiogram)', 'Other Tests', 0, 500.00],
            ['Pulmonary Function Test', 'Other Tests', 0, 800.00],
            ['Bone Density Scan', 'Other Tests', 0, 1500.00],
            ['Pap Smear', 'Other Tests', 0, 600.00], // Note: procedure, not lab specimen
            
            // ✅ Other Tests (require specimen)
            ['Biopsy', 'Other Tests', 1, 2000.00], // tissue sample
        ];
        
        $db = \Config\Database::connect();
        foreach ($defaultTests as $test) {
            $db->table('lab_tests_master')->insert([
                'test_name' => $test[0],
                'test_category' => $test[1],
                'requires_specimen' => $test[2],
                'price' => $test[3],
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('lab_tests_master', true);
    }
}
