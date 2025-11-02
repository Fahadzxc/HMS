<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
use RuntimeException;

class UpdatePatientsTableForRegistration extends Migration
{
    public function up()
    {
        $fields = [
            'patient_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'id',
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'patient_id',
            ],
            'middle_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'first_name',
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'middle_name',
            ],
            'age' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
                'after'      => 'date_of_birth',
            ],
            'address_city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'address',
            ],
            'address_barangay' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'address_city',
            ],
            'address_street' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'address_barangay',
            ],
            'allergies' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'blood_type',
            ],
            'emergency_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'allergies',
            ],
            'emergency_contact' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'emergency_name',
            ],
            'relationship' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'emergency_contact',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'after'   => 'created_at',
                'default' => null,
            ],
        ];

        $this->forge->addColumn('patients', $fields);

        // Backfill critical fields for existing records
        $this->db->query("UPDATE patients SET patient_id = CONCAT('PT-', DATE_FORMAT(COALESCE(created_at, NOW()), '%Y%m%d'), '-', LPAD(id, 3, '0')) WHERE patient_id IS NULL");
        $this->db->query("UPDATE patients SET first_name = COALESCE(first_name, full_name), last_name = COALESCE(last_name, full_name) WHERE full_name IS NOT NULL");
        $this->db->query("UPDATE patients SET address_city = COALESCE(address_city, address), address_barangay = COALESCE(address_barangay, ''), address_street = COALESCE(address_street, '') WHERE address IS NOT NULL");
        $this->db->query("UPDATE patients SET age = TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) WHERE date_of_birth IS NOT NULL AND date_of_birth <> '0000-00-00'");
        $this->db->query("UPDATE patients SET updated_at = created_at WHERE updated_at IS NULL");

        // Ensure we can safely add unique indexes
        $duplicateContact = $this->db->query("SELECT contact FROM patients WHERE contact IS NOT NULL AND contact <> '' GROUP BY contact HAVING COUNT(*) > 1 LIMIT 1")->getRowArray();
        if ($duplicateContact) {
            throw new RuntimeException('Cannot add unique index for contact because duplicate values exist (e.g., ' . $duplicateContact['contact'] . '). Please resolve duplicates before running this migration again.');
        }

        $duplicatePatientId = $this->db->query("SELECT patient_id FROM patients WHERE patient_id IS NOT NULL AND patient_id <> '' GROUP BY patient_id HAVING COUNT(*) > 1 LIMIT 1")->getRowArray();
        if ($duplicatePatientId) {
            throw new RuntimeException('Cannot add unique index for patient_id because duplicate values exist (e.g., ' . $duplicatePatientId['patient_id'] . '). Please resolve duplicates before running this migration again.');
        }

        $this->db->query('ALTER TABLE patients ADD UNIQUE KEY uniq_patient_id (patient_id)');
        $this->db->query('ALTER TABLE patients ADD UNIQUE KEY uniq_contact (contact)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE patients DROP INDEX uniq_patient_id');
        $this->db->query('ALTER TABLE patients DROP INDEX uniq_contact');

        $this->forge->dropColumn('patients', [
            'patient_id',
            'first_name',
            'middle_name',
            'last_name',
            'age',
            'address_city',
            'address_barangay',
            'address_street',
            'allergies',
            'emergency_name',
            'emergency_contact',
            'relationship',
            'updated_at',
        ]);
    }
}
