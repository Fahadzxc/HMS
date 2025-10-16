<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItStaffTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'employee_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'unique'     => true,
            ],
            'specialization' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'certifications' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'IT',
            ],
            'shift' => [
                'type'       => 'ENUM',
                'constraint' => ['morning', 'afternoon', 'night'],
                'default'    => 'morning',
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('it_staff');
    }

    public function down()
    {
        $this->forge->dropTable('it_staff');
    }
}
