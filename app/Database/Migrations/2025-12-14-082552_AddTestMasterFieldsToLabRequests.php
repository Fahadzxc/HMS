<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTestMasterFieldsToLabRequests extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('lab_test_requests')) {
            $fields = $this->db->getFieldData('lab_test_requests');
            $fieldNames = array_column($fields, 'name');
            
            // Add price field if it doesn't exist
            if (!in_array('price', $fieldNames)) {
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
            }
            
            // Add requires_specimen field if it doesn't exist
            if (!in_array('requires_specimen', $fieldNames)) {
                $this->forge->addColumn('lab_test_requests', [
                    'requires_specimen' => [
                        'type'       => 'TINYINT',
                        'constraint' => 1,
                        'default'    => 0,
                        'null'       => false,
                        'after'      => 'price',
                        'comment'    => '1 = requires specimen collection by nurse, 0 = no specimen needed',
                    ],
                ]);
            }
            
            // Add specimen_collected_by field if it doesn't exist
            if (!in_array('specimen_collected_by', $fieldNames)) {
                $this->forge->addColumn('lab_test_requests', [
                    'specimen_collected_by' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'requires_specimen',
                        'comment'    => 'Nurse ID who collected the specimen',
                    ],
                ]);
            }
            
            // Add specimen_collected_at field if it doesn't exist
            if (!in_array('specimen_collected_at', $fieldNames)) {
                $this->forge->addColumn('lab_test_requests', [
                    'specimen_collected_at' => [
                        'type'    => 'DATETIME',
                        'null'    => true,
                        'after'   => 'specimen_collected_by',
                        'comment' => 'Timestamp when specimen was collected',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('lab_test_requests')) {
            $fields = $this->db->getFieldData('lab_test_requests');
            $fieldNames = array_column($fields, 'name');
            
            if (in_array('specimen_collected_at', $fieldNames)) {
                $this->forge->dropColumn('lab_test_requests', 'specimen_collected_at');
            }
            if (in_array('specimen_collected_by', $fieldNames)) {
                $this->forge->dropColumn('lab_test_requests', 'specimen_collected_by');
            }
            if (in_array('requires_specimen', $fieldNames)) {
                $this->forge->dropColumn('lab_test_requests', 'requires_specimen');
            }
            if (in_array('price', $fieldNames)) {
                $this->forge->dropColumn('lab_test_requests', 'price');
            }
        }
    }
}
