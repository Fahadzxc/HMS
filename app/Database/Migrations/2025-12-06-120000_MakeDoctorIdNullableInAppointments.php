<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeDoctorIdNullableInAppointments extends Migration
{
    public function up()
    {
        // Check if appointments table exists
        if (!$this->db->tableExists('appointments')) {
            return;
        }

        // Get foreign key constraint name
        $fkQuery = $this->db->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'appointments' 
            AND COLUMN_NAME = 'doctor_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        
        $fkName = null;
        if ($fkQuery && $row = $fkQuery->getRowArray()) {
            $fkName = $row['CONSTRAINT_NAME'];
        }

        // Drop foreign key constraint if exists
        if ($fkName) {
            try {
                $this->db->query("ALTER TABLE appointments DROP FOREIGN KEY `{$fkName}`");
            } catch (\Exception $e) {
                // Continue even if drop fails
            }
        }

        // Temporarily disable foreign key checks
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");

        // Modify doctor_id column to allow NULL
        try {
            $this->db->query("ALTER TABLE appointments MODIFY doctor_id INT(11) UNSIGNED NULL");
        } catch (\Exception $e) {
            // Log error but continue
            log_message('error', 'Error modifying doctor_id column: ' . $e->getMessage());
        }

        // Re-enable foreign key checks
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");

        // Re-add foreign key constraint (allows NULL values)
        if ($fkName) {
            try {
                $this->db->query("ALTER TABLE appointments ADD CONSTRAINT `{$fkName}` FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE");
            } catch (\Exception $e) {
                // Foreign key might already exist or have different name
                // Try with a new name
                try {
                    $this->db->query("ALTER TABLE appointments ADD CONSTRAINT appointments_doctor_id_fk FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE");
                } catch (\Exception $e2) {
                    // Continue
                }
            }
        }
    }

    public function down()
    {
        // Revert doctor_id to NOT NULL (if needed)
        if (!$this->db->tableExists('appointments')) {
            return;
        }

        // First, update any NULL doctor_id values to a default (e.g., 1) or delete those rows
        // For safety, we'll just modify the column structure
        // Note: This will fail if there are NULL values, so handle accordingly
        
        // Drop foreign key constraint first
        try {
            $this->db->query("ALTER TABLE appointments DROP FOREIGN KEY IF EXISTS appointments_doctor_id_foreign");
        } catch (\Exception $e) {
            try {
                $this->db->query("ALTER TABLE appointments DROP FOREIGN KEY IF EXISTS appointments_ibfk_2");
            } catch (\Exception $e2) {
                // Continue
            }
        }

        // Modify doctor_id column to NOT NULL
        $this->db->query("ALTER TABLE appointments MODIFY doctor_id INT(11) UNSIGNED NOT NULL");

        // Re-add foreign key constraint
        try {
            $this->db->query("ALTER TABLE appointments ADD CONSTRAINT appointments_doctor_id_foreign FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Exception $e) {
            // Continue
        }
    }
}
