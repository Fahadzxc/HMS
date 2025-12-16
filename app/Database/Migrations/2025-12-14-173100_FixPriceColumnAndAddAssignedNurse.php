<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixPriceColumnAndAddAssignedNurse extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('lab_test_requests')) {
            return;
        }

        $fields = $this->db->getFieldData('lab_test_requests');
        $fieldNames = array_column($fields, 'name');
        
        // Add price field if it doesn't exist
        if (!in_array('price', $fieldNames)) {
            try {
                $this->forge->addColumn('lab_test_requests', [
                    'price' => [
                        'type'       => 'DECIMAL',
                        'constraint' => '10,2',
                        'default'    => 0.00,
                        'null'       => false,
                        'after'      => 'test_type',
                        'comment'    => 'Price of the lab test',
                    ],
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Failed to add price column: ' . $e->getMessage());
            }
        }
        
        // Add assigned_nurse_id field if it doesn't exist (nurse assigned to collect specimen)
        if (!in_array('assigned_nurse_id', $fieldNames)) {
            try {
                $this->forge->addColumn('lab_test_requests', [
                    'assigned_nurse_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'requires_specimen',
                        'comment'    => 'Nurse ID assigned to collect specimen',
                    ],
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Failed to add assigned_nurse_id column: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('lab_test_requests')) {
            $fields = $this->db->getFieldData('lab_test_requests');
            $fieldNames = array_column($fields, 'name');
            
            if (in_array('assigned_nurse_id', $fieldNames)) {
                $this->forge->dropColumn('lab_test_requests', 'assigned_nurse_id');
            }
            if (in_array('price', $fieldNames)) {
                $this->forge->dropColumn('lab_test_requests', 'price');
            }
        }
    }
}
