<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNurseTrackingToLabRequests extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('lab_test_requests')) {
            $fields = $this->db->getFieldData('lab_test_requests');
            $fieldNames = array_column($fields, 'name');
            
            // Add sent_by_nurse_id field if it doesn't exist
            if (!in_array('sent_by_nurse_id', $fieldNames)) {
                $this->forge->addColumn('lab_test_requests', [
                    'sent_by_nurse_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'assigned_staff_id',
                        'comment'    => 'Nurse who marked request as sent to lab',
                    ],
                ]);
            }
            
            // Add sent_at field if it doesn't exist
            if (!in_array('sent_at', $fieldNames)) {
                $this->forge->addColumn('lab_test_requests', [
                    'sent_at' => [
                        'type'    => 'DATETIME',
                        'null'    => true,
                        'after'   => 'sent_by_nurse_id',
                        'comment' => 'Timestamp when nurse marked as sent to lab',
                    ],
                ]);
            }
            
            // Update status enum to include 'sent_to_lab' if needed
            // Note: We'll handle this via ALTER TABLE if status is ENUM
            try {
                // Check current status column type
                $statusField = null;
                foreach ($fields as $field) {
                    if ($field->name === 'status') {
                        $statusField = $field;
                        break;
                    }
                }
                
                // If status is ENUM, we need to modify it
                if ($statusField && strpos(strtolower($statusField->type ?? ''), 'enum') !== false) {
                    // Modify ENUM to include 'sent_to_lab'
                    $this->db->query("ALTER TABLE lab_test_requests MODIFY COLUMN status ENUM('pending', 'sent_to_lab', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
                }
            } catch (\Exception $e) {
                // If status is not ENUM or modification fails, that's okay
                // The status field might be VARCHAR which already supports any value
                log_message('info', 'Status field modification note: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('lab_test_requests')) {
            $fields = $this->db->getFieldData('lab_test_requests');
            $fieldNames = array_column($fields, 'name');
            
            if (in_array('sent_by_nurse_id', $fieldNames)) {
                $this->forge->dropColumn('lab_test_requests', 'sent_by_nurse_id');
            }
            
            if (in_array('sent_at', $fieldNames)) {
                $this->forge->dropColumn('lab_test_requests', 'sent_at');
            }
        }
    }
}
