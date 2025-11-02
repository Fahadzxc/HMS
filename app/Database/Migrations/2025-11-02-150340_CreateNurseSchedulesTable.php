<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNurseSchedulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nurse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'day_of_week' => [
                'type' => 'ENUM',
                'constraint' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            ],
            'shift_type' => [
                'type' => 'ENUM',
                'constraint' => ['morning', 'afternoon', 'night', 'double'],
            ],
            'start_time' => [
                'type' => 'TIME',
            ],
            'end_time' => [
                'type' => 'TIME',
            ],
            'ward_assignment' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey('nurse_id');
        $this->forge->addForeignKey('nurse_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('nurse_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('nurse_schedules');
    }
}
