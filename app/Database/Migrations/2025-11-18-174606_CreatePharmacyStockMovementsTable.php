<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePharmacyStockMovementsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('pharmacy_stock_movements')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'medication_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'medicine_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'movement_type' => [
                'type' => 'ENUM',
                'constraint' => ['add', 'dispense', 'adjust'],
                'default' => 'add',
            ],
            'quantity_change' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'previous_stock' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'new_stock' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'action_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addKey('medication_id');
        $this->forge->addKey('movement_type');
        $this->forge->addKey('action_by');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('pharmacy_stock_movements', true);
    }

    public function down()
    {
        if ($this->db->tableExists('pharmacy_stock_movements')) {
            $this->forge->dropTable('pharmacy_stock_movements', true);
        }
    }
}

