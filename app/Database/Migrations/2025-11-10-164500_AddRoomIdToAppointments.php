<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoomIdToAppointments extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->tableExists('appointments')) {
            $fields = $this->db->getFieldData('appointments');
            $hasRoomId = false;
            foreach ($fields as $field) {
                if ($field->name === 'room_id') {
                    $hasRoomId = true;
                    break;
                }
            }
            
            if (!$hasRoomId) {
                $this->forge->addColumn('appointments', [
                    'room_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'doctor_id',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('appointments')) {
            $this->forge->dropColumn('appointments', 'room_id');
        }
    }
}

