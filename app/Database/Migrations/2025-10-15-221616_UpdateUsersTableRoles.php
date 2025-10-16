<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUsersTableRoles extends Migration
{
    public function up()
    {
        // Update the role enum to include all roles
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'staff', 'doctor', 'nurse', 'receptionist', 'lab', 'pharmacist', 'accountant', 'it'],
                'default'    => 'staff',
            ],
        ]);
    }

    public function down()
    {
        // Revert back to original roles
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'staff', 'doctor', 'nurse'],
                'default'    => 'staff',
            ],
        ]);
    }
}
