<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdmissionIdToLabTestRequestsTable extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->tableExists('lab_test_requests')) {
            $fields = $this->db->getFieldData('lab_test_requests');
            $hasAdmissionId = false;
            foreach ($fields as $field) {
                if ($field->name === 'admission_id') {
                    $hasAdmissionId = true;
                    break;
                }
            }
            
            if (!$hasAdmissionId) {
                $this->forge->addColumn('lab_test_requests', [
                    'admission_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'doctor_id',
                        'comment'    => 'Links lab request to specific admission for inpatients',
                    ],
                ]);
                
                // Add index for better query performance
                $this->forge->addKey('admission_id');
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('lab_test_requests')) {
            $this->forge->dropColumn('lab_test_requests', 'admission_id');
        }
    }
}

