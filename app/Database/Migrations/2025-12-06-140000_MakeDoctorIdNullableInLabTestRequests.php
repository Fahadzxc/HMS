<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeDoctorIdNullableInLabTestRequests extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('lab_test_requests')) {
            // Get foreign key constraint name
            $fkQuery = $this->db->query("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'lab_test_requests' 
                AND COLUMN_NAME = 'doctor_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            
            $fkName = null;
            if ($fkQuery && $fkQuery->getNumRows() > 0) {
                $fkRow = $fkQuery->getRowArray();
                $fkName = $fkRow['CONSTRAINT_NAME'] ?? null;
            }
            
            // Drop foreign key if exists
            if ($fkName) {
                try {
                    $this->db->query("ALTER TABLE lab_test_requests DROP FOREIGN KEY `{$fkName}`");
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
            }
            
            // Temporarily disable foreign key checks
            $this->db->query("SET FOREIGN_KEY_CHECKS=0");
            
            // Modify doctor_id column to allow NULL
            $this->db->query("ALTER TABLE lab_test_requests MODIFY doctor_id INT(11) UNSIGNED NULL");
            
            // Re-enable foreign key checks
            $this->db->query("SET FOREIGN_KEY_CHECKS=1");
            
            // Re-add foreign key constraint (allows NULL values)
            if ($fkName) {
                try {
                    $this->db->query("
                        ALTER TABLE lab_test_requests 
                        ADD CONSTRAINT `{$fkName}` 
                        FOREIGN KEY (doctor_id) REFERENCES users(id) 
                        ON DELETE CASCADE ON UPDATE CASCADE
                    ");
                } catch (\Exception $e) {
                    // Foreign key might already exist or table structure changed
                }
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('lab_test_requests')) {
            // Get foreign key constraint name
            $fkQuery = $this->db->query("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'lab_test_requests' 
                AND COLUMN_NAME = 'doctor_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            
            $fkName = null;
            if ($fkQuery && $fkQuery->getNumRows() > 0) {
                $fkRow = $fkQuery->getRowArray();
                $fkName = $fkRow['CONSTRAINT_NAME'] ?? null;
            }
            
            // Drop foreign key if exists
            if ($fkName) {
                try {
                    $this->db->query("ALTER TABLE lab_test_requests DROP FOREIGN KEY `{$fkName}`");
                } catch (\Exception $e) {
                    // Continue
                }
            }
            
            // Temporarily disable foreign key checks
            $this->db->query("SET FOREIGN_KEY_CHECKS=0");
            
            // Modify doctor_id column to NOT NULL (set default to 0 for existing NULL values)
            $this->db->query("UPDATE lab_test_requests SET doctor_id = 0 WHERE doctor_id IS NULL");
            $this->db->query("ALTER TABLE lab_test_requests MODIFY doctor_id INT(11) UNSIGNED NOT NULL");
            
            // Re-enable foreign key checks
            $this->db->query("SET FOREIGN_KEY_CHECKS=1");
            
            // Re-add foreign key constraint
            if ($fkName) {
                try {
                    $this->db->query("
                        ALTER TABLE lab_test_requests 
                        ADD CONSTRAINT `{$fkName}` 
                        FOREIGN KEY (doctor_id) REFERENCES users(id) 
                        ON DELETE CASCADE ON UPDATE CASCADE
                    ");
                } catch (\Exception $e) {
                    // Continue
                }
            }
        }
    }
}
