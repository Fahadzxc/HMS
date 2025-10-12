<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Users extends Controller
{
    public function index()
    {
        try {
            // Try to load users from database
            $db = \Config\Database::connect();
            
            if ($db->tableExists('users')) {
                // Load from database
                $users = $db->table('users')
                           ->orderBy('id', 'DESC')
                           ->get()
                           ->getResultArray();
            } else {
                // Create users table if it doesn't exist
                $this->createUsersTable();
                
                // Add sample users
                $this->addSampleUsers();
                
                // Load the sample users
                $users = $db->table('users')
                           ->orderBy('id', 'DESC')
                           ->get()
                           ->getResultArray();
            }

            $data = [
                'pageTitle' => 'User Management',
                'users' => $users,
            ];

            return view('admin/users', $data);
            
        } catch (\Exception $e) {
            // Fallback to sample data if database fails
            $users = [
                [
                    'id' => 1,
                    'name' => 'Administrator',
                    'email' => 'admin@hms.com',
                    'role' => 'admin',
                    'status' => 'active',
                    'created_at' => '2025-10-11 10:00:00'
                ],
                [
                    'id' => 2,
                    'name' => 'John Doctor',
                    'email' => 'doctor@hms.com',
                    'role' => 'doctor',
                    'status' => 'active',
                    'created_at' => '2025-10-11 10:00:00'
                ],
                [
                    'id' => 3,
                    'name' => 'Jane Nurse',
                    'email' => 'nurse@hms.com',
                    'role' => 'nurse',
                    'status' => 'active',
                    'created_at' => '2025-10-11 10:00:00'
                ],
                [
                    'id' => 4,
                    'name' => 'Staff Member',
                    'email' => 'staff@hms.com',
                    'role' => 'staff',
                    'status' => 'active',
                    'created_at' => '2025-10-11 10:00:00'
                ]
            ];

            $data = [
                'pageTitle' => 'User Management',
                'users' => $users,
                'error' => 'Database error: ' . $e->getMessage()
            ];

            return view('admin/users', $data);
        }
    }

    public function create()
    {
        try {
            $request = $this->request;
            $db = \Config\Database::connect();
            
            // Ensure users table exists
            if (!$db->tableExists('users')) {
                $this->createUsersTable();
            }
            
            // Get form data
            $name = $request->getPost('name');
            $email = $request->getPost('email');
            $password = $request->getPost('password');
            $role = $request->getPost('role');
            $status = $request->getPost('status');
            
            // Insert new user
            $data = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->table('users')->insert($data);
            
            return redirect()->to('/admin/users')->with('success', 'User created successfully!');
            
        } catch (\Exception $e) {
            return redirect()->to('/admin/users')->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    public function update()
    {
        try {
            $request = $this->request;
            $db = \Config\Database::connect();
            
            $userId = $request->getPost('user_id');
            $name = $request->getPost('name');
            $email = $request->getPost('email');
            $role = $request->getPost('role');
            $status = $request->getPost('status');
            
            $data = [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Only update password if provided
            if ($request->getPost('password')) {
                $data['password'] = password_hash($request->getPost('password'), PASSWORD_DEFAULT);
            }
            
            $db->table('users')->where('id', $userId)->update($data);
            
            return redirect()->to('/admin/users')->with('success', 'User updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->to('/admin/users')->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $request = $this->request;
            $db = \Config\Database::connect();
            
            $userId = $request->getPost('user_id');
            
            $db->table('users')->where('id', $userId)->delete();
            
            return redirect()->to('/admin/users')->with('success', 'User deleted successfully!');
            
        } catch (\Exception $e) {
            return redirect()->to('/admin/users')->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
    
    private function createUsersTable()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();
        
        $fields = [
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'staff', 'doctor', 'nurse'],
                'default' => 'staff',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];
        
        $forge->addField($fields);
        $forge->addKey('id', true);
        $forge->createTable('users');
    }
    
    private function addSampleUsers()
    {
        $db = \Config\Database::connect();
        
        $sampleUsers = [
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
                'name' => 'John Doctor',
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
                'name' => 'Staff Member',
                'email' => 'staff@hms.com',
                'password' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'staff',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($sampleUsers as $user) {
            $db->table('users')->insert($user);
        }
    }
}
