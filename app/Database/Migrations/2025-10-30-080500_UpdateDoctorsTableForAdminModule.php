<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateDoctorsTableForAdminModule extends Migration
{
    public function up()
    {
        $fields = [
            'profile_picture' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['male', 'female', 'other'],
                'null'       => true,
            ],
            'date_of_birth' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'alternate_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'emergency_contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'emergency_contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'on_leave'],
                'default'    => 'active',
            ],
            'license_expiry_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'years_of_experience' => [
                'type'       => 'INT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
            ],
            'educational_background' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'certifications' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'board_exam_passed' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'medical_council_registration_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'room_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'branch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'assigned_ward_unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'supervisor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'on_call_availability' => [
                'type'       => 'ENUM',
                'constraint' => ['none', 'weekday', 'weekend', '24_7'],
                'default'    => 'none',
            ],
            'rotation_type' => [
                'type'       => 'ENUM',
                'constraint' => ['none', 'weekly', 'bi_weekly', 'monthly'],
                'default'    => 'none',
            ],
            'duty_hours_per_week' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'latest_patient_queue_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'last_login_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'access_permissions' => [
                'type' => 'JSON',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('doctors', $fields);

        // Add foreign keys for new relations
        $db = \Config\Database::connect();
        $prefix = $db->getPrefix();

        $db->query("ALTER TABLE {$prefix}doctors ADD CONSTRAINT fk_doctors_branch_id FOREIGN KEY (branch_id) REFERENCES {$prefix}branches(id) ON DELETE SET NULL ON UPDATE CASCADE");
        $db->query("ALTER TABLE {$prefix}doctors ADD CONSTRAINT fk_doctors_supervisor_id FOREIGN KEY (supervisor_id) REFERENCES {$prefix}users(id) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $prefix = $db->getPrefix();

        $db->query("ALTER TABLE {$prefix}doctors DROP FOREIGN KEY fk_doctors_branch_id");
        $db->query("ALTER TABLE {$prefix}doctors DROP FOREIGN KEY fk_doctors_supervisor_id");

        $this->forge->dropColumn('doctors', [
            'profile_picture',
            'gender',
            'date_of_birth',
            'contact_number',
            'alternate_email',
            'address',
            'emergency_contact_name',
            'emergency_contact_number',
            'status',
            'license_expiry_date',
            'years_of_experience',
            'educational_background',
            'certifications',
            'board_exam_passed',
            'medical_council_registration_no',
            'room_number',
            'branch_id',
            'assigned_ward_unit',
            'supervisor_id',
            'on_call_availability',
            'rotation_type',
            'duty_hours_per_week',
            'latest_patient_queue_count',
            'last_login_at',
            'access_permissions',
        ]);
    }
}
