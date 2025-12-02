<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdmissionIdToPrescriptionsTable extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->tableExists('prescriptions')) {
            $fields = $this->db->getFieldData('prescriptions');
            $hasAdmissionId = false;
            foreach ($fields as $field) {
                if ($field->name === 'admission_id') {
                    $hasAdmissionId = true;
                    break;
                }
            }
            
            if (!$hasAdmissionId) {
                $this->forge->addColumn('prescriptions', [
                    'admission_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'appointment_id',
                        'comment'    => 'Links prescription to specific admission for inpatients',
                    ],
                ]);
                
                // Add index for better query performance
                $this->forge->addKey('admission_id');
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('prescriptions')) {
            $this->forge->dropColumn('prescriptions', 'admission_id');
        }
    }
}

