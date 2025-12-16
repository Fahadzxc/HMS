<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureLabRequestColumnsExist extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('lab_test_requests')) {
            return;
        }

        $fields = $this->db->getFieldData('lab_test_requests');
        $fieldNames = array_column($fields, 'name');
        
        // Add price field if it doesn't exist
        if (!in_array('price', $fieldNames)) {
            try {
                $this->db->query("ALTER TABLE lab_test_requests ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00 NOT NULL AFTER test_type");
                log_message('info', 'Added price column to lab_test_requests');
            } catch (\Exception $e) {
                log_message('error', 'Failed to add price column: ' . $e->getMessage());
            }
        }
        
        // Add requires_specimen field if it doesn't exist
        if (!in_array('requires_specimen', $fieldNames)) {
            try {
                $this->db->query("ALTER TABLE lab_test_requests ADD COLUMN requires_specimen TINYINT(1) DEFAULT 0 NOT NULL AFTER price");
                log_message('info', 'Added requires_specimen column to lab_test_requests');
            } catch (\Exception $e) {
                log_message('error', 'Failed to add requires_specimen column: ' . $e->getMessage());
            }
        }
        
        // Add assigned_nurse_id field if it doesn't exist
        if (!in_array('assigned_nurse_id', $fieldNames)) {
            try {
                $this->db->query("ALTER TABLE lab_test_requests ADD COLUMN assigned_nurse_id INT(11) UNSIGNED NULL AFTER requires_specimen");
                log_message('info', 'Added assigned_nurse_id column to lab_test_requests');
            } catch (\Exception $e) {
                log_message('error', 'Failed to add assigned_nurse_id column: ' . $e->getMessage());
            }
        }
        
        // Add specimen_collected_by field if it doesn't exist
        if (!in_array('specimen_collected_by', $fieldNames)) {
            try {
                $this->db->query("ALTER TABLE lab_test_requests ADD COLUMN specimen_collected_by INT(11) UNSIGNED NULL AFTER assigned_nurse_id");
                log_message('info', 'Added specimen_collected_by column to lab_test_requests');
            } catch (\Exception $e) {
                log_message('error', 'Failed to add specimen_collected_by column: ' . $e->getMessage());
            }
        }
        
        // Add specimen_collected_at field if it doesn't exist
        if (!in_array('specimen_collected_at', $fieldNames)) {
            try {
                $this->db->query("ALTER TABLE lab_test_requests ADD COLUMN specimen_collected_at DATETIME NULL AFTER specimen_collected_by");
                log_message('info', 'Added specimen_collected_at column to lab_test_requests');
            } catch (\Exception $e) {
                log_message('error', 'Failed to add specimen_collected_at column: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // No rollback - this is a safety migration
    }
}
