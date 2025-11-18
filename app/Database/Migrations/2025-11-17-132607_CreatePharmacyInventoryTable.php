<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePharmacyInventoryTable extends Migration
{
    public function up()
    {
        // Create pharmacy_inventory table if it doesn't exist
        if (!$this->db->tableExists('pharmacy_inventory')) {
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'stock_quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'reorder_level' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 10,
            ],
            'expiration_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
            $this->forge->addKey('name');

            $this->forge->createTable('pharmacy_inventory', true);
        }

        // Create pharmacy_dispense_logs table if it doesn't exist
        if (!$this->db->tableExists('pharmacy_dispense_logs')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'prescription_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'patient_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'medicine_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'quantity' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'pharmacist_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'dispensed_at' => [
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
            $this->forge->addKey('prescription_id');
            $this->forge->addKey('patient_id');
            $this->forge->addKey('pharmacist_id');
            $this->forge->addKey('dispensed_at');

            $this->forge->createTable('pharmacy_dispense_logs', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('pharmacy_dispense_logs')) {
            $this->forge->dropTable('pharmacy_dispense_logs', true);
        }
        if ($this->db->tableExists('pharmacy_inventory')) {
            $this->forge->dropTable('pharmacy_inventory', true);
        }
    }
}
