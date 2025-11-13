<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLabModuleTables extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
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
        $this->forge->createTable('lab_departments');

        // Lab staff table
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
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'shift' => [
                'type'       => 'ENUM',
                'constraint' => ['morning', 'afternoon', 'night', 'rotating'],
                'default'    => 'morning',
            ],
            'contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
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
        $this->forge->createTable('lab_staff');

        // Lab test requests
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'patient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'doctor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'test_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'normal', 'high', 'critical'],
                'default'    => 'normal',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'completed', 'cancelled', 'critical'],
                'default'    => 'pending',
            ],
            'requested_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'assigned_staff_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'override_reason' => [
                'type' => 'TEXT',
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
        $this->forge->createTable('lab_test_requests');

        // Lab test results
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'request_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'result_summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'detailed_report_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'released_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'released_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'released', 'audited', 'rejected'],
                'default'    => 'draft',
            ],
            'critical_flag' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'audited_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'audited_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'audit_notes' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('request_id');
        $this->forge->createTable('lab_test_results');

        // Lab equipment
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'last_maintenance' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'next_maintenance' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'technician_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
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
        $this->forge->createTable('lab_equipment');

        // Lab equipment logs
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'equipment_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'activity' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'performed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'performed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'outcome' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('equipment_id');
        $this->forge->createTable('lab_equipment_logs');

        // Lab inventory items
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'reorder_point' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'expiration_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'supplier' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['ok', 'low_stock', 'out_of_stock', 'expired'],
                'default'    => 'ok',
            ],
            'lot_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
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
        $this->forge->createTable('lab_inventory_items');

        // Lab inventory logs
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'change_type' => [
                'type'       => 'ENUM',
                'constraint' => ['add', 'remove', 'adjust'],
                'default'    => 'add',
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'recorded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'recorded_at' => [
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
        $this->forge->addKey('item_id');
        $this->forge->createTable('lab_inventory_logs');

        // Lab sample transfers
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'request_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'priority_flag' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'dispatched', 'received', 'cancelled'],
                'default'    => 'pending',
            ],
            'dispatched_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'received_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'comments' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('request_id');
        $this->forge->createTable('lab_sample_transfers');

        // Add foreign keys
        $db = \Config\Database::connect();
        $prefix = $db->getPrefix();

        // Add foreign keys with error handling
        try {
            $db->query("ALTER TABLE {$prefix}lab_test_requests ADD CONSTRAINT fk_lab_requests_patient FOREIGN KEY (patient_id) REFERENCES {$prefix}patients(id) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Exception $e) {
            log_message('error', 'Failed to add fk_lab_requests_patient: ' . $e->getMessage());
        }
        
        try {
            $db->query("ALTER TABLE {$prefix}lab_test_requests ADD CONSTRAINT fk_lab_requests_doctor FOREIGN KEY (doctor_id) REFERENCES {$prefix}users(id) ON DELETE SET NULL ON UPDATE CASCADE");
        } catch (\Exception $e) {
            log_message('error', 'Failed to add fk_lab_requests_doctor: ' . $e->getMessage());
        }
        
        try {
            $db->query("ALTER TABLE {$prefix}lab_test_requests ADD CONSTRAINT fk_lab_requests_staff FOREIGN KEY (assigned_staff_id) REFERENCES {$prefix}lab_staff(id) ON DELETE SET NULL ON UPDATE CASCADE");
        } catch (\Exception $e) {
            log_message('error', 'Failed to add fk_lab_requests_staff: ' . $e->getMessage());
        }
        // Add remaining foreign keys with error handling
        $foreignKeys = [
            "ALTER TABLE {$prefix}lab_test_results ADD CONSTRAINT fk_lab_results_request FOREIGN KEY (request_id) REFERENCES {$prefix}lab_test_requests(id) ON DELETE CASCADE ON UPDATE CASCADE",
            "ALTER TABLE {$prefix}lab_test_results ADD CONSTRAINT fk_lab_results_released_by FOREIGN KEY (released_by) REFERENCES {$prefix}lab_staff(id) ON DELETE SET NULL ON UPDATE CASCADE",
            "ALTER TABLE {$prefix}lab_test_results ADD CONSTRAINT fk_lab_results_audited_by FOREIGN KEY (audited_by) REFERENCES {$prefix}users(id) ON DELETE SET NULL ON UPDATE CASCADE",
            "ALTER TABLE {$prefix}lab_equipment_logs ADD CONSTRAINT fk_lab_equipment_logs_equipment FOREIGN KEY (equipment_id) REFERENCES {$prefix}lab_equipment(id) ON DELETE CASCADE ON UPDATE CASCADE",
            "ALTER TABLE {$prefix}lab_equipment_logs ADD CONSTRAINT fk_lab_equipment_logs_staff FOREIGN KEY (performed_by) REFERENCES {$prefix}lab_staff(id) ON DELETE SET NULL ON UPDATE CASCADE",
            "ALTER TABLE {$prefix}lab_inventory_logs ADD CONSTRAINT fk_lab_inventory_logs_item FOREIGN KEY (item_id) REFERENCES {$prefix}lab_inventory_items(id) ON DELETE CASCADE ON UPDATE CASCADE",
            "ALTER TABLE {$prefix}lab_inventory_logs ADD CONSTRAINT fk_lab_inventory_logs_user FOREIGN KEY (recorded_by) REFERENCES {$prefix}users(id) ON DELETE SET NULL ON UPDATE CASCADE",
            "ALTER TABLE {$prefix}lab_sample_transfers ADD CONSTRAINT fk_lab_transfer_request FOREIGN KEY (request_id) REFERENCES {$prefix}lab_test_requests(id) ON DELETE CASCADE ON UPDATE CASCADE"
        ];
        
        foreach ($foreignKeys as $sql) {
            try {
                $db->query($sql);
            } catch (\Exception $e) {
                log_message('error', 'Failed to add foreign key: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $prefix = $db->getPrefix();

        // Drop foreign keys with error handling (they might not exist)
        $foreignKeys = [
            "ALTER TABLE {$prefix}lab_sample_transfers DROP FOREIGN KEY fk_lab_transfer_request",
            "ALTER TABLE {$prefix}lab_inventory_logs DROP FOREIGN KEY fk_lab_inventory_logs_item",
            "ALTER TABLE {$prefix}lab_inventory_logs DROP FOREIGN KEY fk_lab_inventory_logs_user",
            "ALTER TABLE {$prefix}lab_equipment_logs DROP FOREIGN KEY fk_lab_equipment_logs_equipment",
            "ALTER TABLE {$prefix}lab_equipment_logs DROP FOREIGN KEY fk_lab_equipment_logs_staff",
            "ALTER TABLE {$prefix}lab_test_results DROP FOREIGN KEY fk_lab_results_request",
            "ALTER TABLE {$prefix}lab_test_results DROP FOREIGN KEY fk_lab_results_released_by",
            "ALTER TABLE {$prefix}lab_test_results DROP FOREIGN KEY fk_lab_results_audited_by",
            "ALTER TABLE {$prefix}lab_test_requests DROP FOREIGN KEY fk_lab_requests_patient",
            "ALTER TABLE {$prefix}lab_test_requests DROP FOREIGN KEY fk_lab_requests_doctor",
            "ALTER TABLE {$prefix}lab_test_requests DROP FOREIGN KEY fk_lab_requests_staff",
            "ALTER TABLE {$prefix}lab_staff DROP FOREIGN KEY fk_lab_staff_department"
        ];

        foreach ($foreignKeys as $sql) {
            try {
                $db->query($sql);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
                log_message('debug', 'Foreign key drop skipped: ' . $e->getMessage());
            }
        }

        // Drop tables (with IF EXISTS check)
        $tables = [
            'lab_sample_transfers',
            'lab_inventory_logs',
            'lab_inventory_items',
            'lab_equipment_logs',
            'lab_equipment',
            'lab_test_results',
            'lab_test_requests',
            'lab_staff',
            'lab_departments'
        ];

        foreach ($tables as $table) {
            if ($db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }
}
