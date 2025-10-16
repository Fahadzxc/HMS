<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Administrator',
                'email' => 'admin@hms.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Dr. John Santos',
                'email' => 'doctor@hms.com',
                'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                'role' => 'doctor',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Jane Nurse',
                'email' => 'nurse@hms.com',
                'password' => password_hash('nurse123', PASSWORD_DEFAULT),
                'role' => 'nurse',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Maria Receptionist',
                'email' => 'reception@hms.com',
                'password' => password_hash('reception123', PASSWORD_DEFAULT),
                'role' => 'receptionist',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Lab Technician',
                'email' => 'lab@hms.com',
                'password' => password_hash('lab123', PASSWORD_DEFAULT),
                'role' => 'lab',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Pharmacy Manager',
                'email' => 'pharmacy@hms.com',
                'password' => password_hash('pharmacy123', PASSWORD_DEFAULT),
                'role' => 'pharmacist',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Accountant',
                'email' => 'accounts@hms.com',
                'password' => password_hash('accounts123', PASSWORD_DEFAULT),
                'role' => 'accountant',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'IT Administrator',
                'email' => 'it@hms.com',
                'password' => password_hash('it123', PASSWORD_DEFAULT),
                'role' => 'it',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
