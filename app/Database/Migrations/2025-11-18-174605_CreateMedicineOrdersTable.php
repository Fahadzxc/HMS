<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMedicineOrdersTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('medicine_orders')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'order_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
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
            'supplier_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'quantity_ordered' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'order_date' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'delivered', 'cancelled'],
                'default' => 'pending',
            ],
            'received_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'reference' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Invoice number or reference',
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('status');
        $this->forge->addKey('order_date');
        $this->forge->addKey('received_by');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('medicine_orders', true);
        
        // Add unique constraint on order_number after table creation
        $this->db->query("ALTER TABLE medicine_orders ADD UNIQUE KEY unique_order_number (order_number)");
    }

    public function down()
    {
        if ($this->db->tableExists('medicine_orders')) {
            $this->forge->dropTable('medicine_orders', true);
        }
    }
}
