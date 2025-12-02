<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoomPriceToRoomsTable extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->tableExists('rooms')) {
            $fields = $this->db->getFieldData('rooms');
            $hasRoomPrice = false;
            foreach ($fields as $field) {
                if ($field->name === 'room_price') {
                    $hasRoomPrice = true;
                    break;
                }
            }
            
            if (!$hasRoomPrice) {
                $this->forge->addColumn('rooms', [
                    'room_price' => [
                        'type'       => 'DECIMAL',
                        'constraint' => '10,2',
                        'default'    => 0.00,
                        'after'      => 'specialization',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('rooms')) {
            $this->forge->dropColumn('rooms', 'room_price');
        }
    }
}

