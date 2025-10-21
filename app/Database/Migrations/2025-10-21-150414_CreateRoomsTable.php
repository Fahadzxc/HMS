<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoomsTable extends Migration
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
            'room_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'room_type' => [
                'type' => 'ENUM',
                'constraint' => ['inpatient', 'outpatient', 'surgery', 'icu', 'emergency'],
                'default' => 'inpatient',
            ],
            'floor' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'capacity' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 1,
            ],
            'current_occupancy' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'doctor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'specialization' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'is_available' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('room_number');
        $this->forge->addKey('room_type');
        $this->forge->createTable('rooms', true);
    }

    public function down()
    {
        $this->forge->dropTable('rooms', true);
    }
}
