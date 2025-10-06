<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreatePatientsTable extends Migration
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
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'age' => [
                'type'       => 'INT',
                'constraint' => 3,
            ],
            'gender' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'civil_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'contact' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'concern' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('patients', true);
    }

    public function down()
    {
        $this->forge->dropTable('patients', true);
    }
}


