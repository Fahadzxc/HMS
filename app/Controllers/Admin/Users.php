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
            $firstName = trim($request->getPost('first_name') ?? '');
            $middleName = trim($request->getPost('middle_name') ?? '');
            $lastName = trim($request->getPost('last_name') ?? '');
            $email = trim($request->getPost('email') ?? '');
            $password = $request->getPost('password');
            $phone = trim($request->getPost('phone') ?? '');
            $role = trim($request->getPost('role') ?? '');
            $status = trim($request->getPost('status') ?? '');
            $specialization = trim($request->getPost('specialization') ?? '');
            
            // Combine name parts
            $nameParts = array_filter([$firstName, $middleName, $lastName]);
            $name = implode(' ', $nameParts);
            
            // License ID field
            $licenseId = trim($request->getPost('license_id') ?? '');
            
            // Validate required fields
            if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($phone) || empty($role) || empty($status)) {
                return redirect()->to('/admin/users')->with('error', 'All required fields must be filled.');
            }
            
            // Validate specialization for Doctor
            if ($role === 'doctor' && empty($specialization)) {
                return redirect()->to('/admin/users')->with('error', 'Specialization is required for Doctor role.');
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return redirect()->to('/admin/users')->with('error', 'Invalid email format.');
            }
            
            // Validate password length
            if (strlen($password) < 6) {
                return redirect()->to('/admin/users')->with('error', 'Password must be at least 6 characters long.');
            }
            
            // Check if email already exists
            $existingUser = $db->table('users')->where('email', $email)->get()->getRowArray();
            if ($existingUser) {
                return redirect()->to('/admin/users')->with('error', 'Email address already exists.');
            }
            
            // Validate role
            $allowedRoles = ['admin', 'doctor', 'nurse', 'staff', 'lab', 'receptionist', 'pharmacist', 'accountant', 'it'];
            if (!in_array($role, $allowedRoles)) {
                return redirect()->to('/admin/users')->with('error', 'Invalid role selected.');
            }
            
			// Ensure columns exist in users table FIRST (before using license_id)
			$this->ensureUserColumnsExist($db);
			
			// Auto-generate License ID for Doctor, Nurse, and Lab if not provided or empty
			if (in_array($role, ['doctor', 'nurse', 'lab'])) {
				if (empty($licenseId)) {
                    // Generate random 7-digit license ID
                    $licenseId = $this->generateRandomLicenseId($db);
                } else {
                    // Check if provided license ID already exists (only if column exists)
                    try {
                        $existingLicense = $db->table('users')->where('license_id', $licenseId)->get()->getRowArray();
                        if ($existingLicense) {
                            // If exists, generate a new one
                            $licenseId = $this->generateRandomLicenseId($db);
                        }
                    } catch (\Exception $e) {
                        // If column doesn't exist yet, just generate new one
                        $licenseId = $this->generateRandomLicenseId($db);
                    }
                }
			}
            
            // Prepare data for insertion
            $data = [
                'name' => $name,
                'first_name' => $firstName,
                'middle_name' => $middleName ? $middleName : null,
                'last_name' => $lastName,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => $phone,
                'role' => $role,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Add specialization only for Doctor
            if ($role === 'doctor' && !empty($specialization)) {
                $data['specialization'] = $specialization;
            }
            
            // Add license ID for Doctor, Nurse, and Lab
            if (in_array($role, ['doctor', 'nurse', 'lab']) && !empty($licenseId)) {
                $data['license_id'] = $licenseId;
            }
            
            $db->table('users')->insert($data);
            
            return redirect()->to('/admin/users')->with('success', 'User created successfully!');
            
        } catch (\Exception $e) {
            log_message('error', 'Error creating user: ' . $e->getMessage());
            return redirect()->to('/admin/users')->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    public function update()
    {
        try {
            $request = $this->request;
            $db = \Config\Database::connect();
            
            $userId = $request->getPost('user_id');
            $firstName = trim($request->getPost('first_name') ?? '');
            $middleName = trim($request->getPost('middle_name') ?? '');
            $lastName = trim($request->getPost('last_name') ?? '');
            $email = trim($request->getPost('email') ?? '');
            $phone = trim($request->getPost('phone') ?? '');
            $role = trim($request->getPost('role') ?? '');
            $status = trim($request->getPost('status') ?? '');
            $specialization = trim($request->getPost('specialization') ?? '');
            
            // Combine name parts
            $nameParts = array_filter([$firstName, $middleName, $lastName]);
            $name = implode(' ', $nameParts);
            
            // Validate required fields
            if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($role) || empty($status)) {
                return redirect()->to('/admin/users')->with('error', 'All required fields must be filled.');
            }
            
            // Validate specialization for Doctor
            if ($role === 'doctor' && empty($specialization)) {
                return redirect()->to('/admin/users')->with('error', 'Specialization is required for Doctor role.');
            }
            
            // Ensure columns exist
            $this->ensureUserColumnsExist($db);
            
            $data = [
                'name' => $name,
                'first_name' => $firstName,
                'middle_name' => $middleName ? $middleName : null,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Only update password if provided
            if ($request->getPost('password')) {
                $data['password'] = password_hash($request->getPost('password'), PASSWORD_DEFAULT);
            }
            
            // Update specialization for Doctor
            if ($role === 'doctor') {
                if (!empty($specialization)) {
                    $data['specialization'] = $specialization;
                } else {
                    $data['specialization'] = null;
                }
            } else {
                $data['specialization'] = null;
            }
            
            $db->table('users')->where('id', $userId)->update($data);
            
            return redirect()->to('/admin/users')->with('success', 'User updated successfully!');
            
        } catch (\Exception $e) {
            log_message('error', 'Error updating user: ' . $e->getMessage());
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
                'first_name' => 'Administrator',
                'last_name' => 'User',
                'email' => 'admin@hms.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'phone' => '09123456789',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'John Doctor',
                'first_name' => 'John',
                'last_name' => 'Doctor',
                'email' => 'doctor@hms.com',
                'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                'phone' => '09123456790',
                'role' => 'doctor',
                'status' => 'active',
                'specialization' => 'General Practice',
                'license_id' => $this->generateRandomLicenseId($db),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Jane Nurse',
                'first_name' => 'Jane',
                'last_name' => 'Nurse',
                'email' => 'nurse@hms.com',
                'password' => password_hash('nurse123', PASSWORD_DEFAULT),
                'phone' => '09123456791',
                'role' => 'nurse',
                'status' => 'active',
                'license_id' => $this->generateRandomLicenseId($db),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Staff Member',
                'first_name' => 'Staff',
                'last_name' => 'Member',
                'email' => 'staff@hms.com',
                'password' => password_hash('staff123', PASSWORD_DEFAULT),
                'phone' => '09123456792',
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

    private function ensureUserColumnsExist($db)
    {
        $existingColumns = [];
        
        try {
            $query = $db->query("SHOW COLUMNS FROM users");
            $result = $query->getResultArray();
            foreach ($result as $row) {
                $existingColumns[] = $row['Field'];
            }
        } catch (\Exception $e) {
            // Table might not exist or error getting columns
            return;
        }
        
        $forge = \Config\Database::forge();
        
        // Add phone column if not exists
        if (!in_array('phone', $existingColumns)) {
            $fields = [
                'phone' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'after' => 'email'
                ]
            ];
            $forge->addColumn('users', $fields);
        }
        
        // Add first_name, middle_name, last_name columns if not exists
        if (!in_array('first_name', $existingColumns)) {
            $fields = [
                'first_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'name'
                ]
            ];
            $forge->addColumn('users', $fields);
        }
        
        if (!in_array('middle_name', $existingColumns)) {
            $fields = [
                'middle_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'first_name'
                ]
            ];
            $forge->addColumn('users', $fields);
        }
        
        if (!in_array('last_name', $existingColumns)) {
            $fields = [
                'last_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'middle_name'
                ]
            ];
            $forge->addColumn('users', $fields);
        }
        
        // Add specialization column if not exists (for Doctor)
        if (!in_array('specialization', $existingColumns)) {
            $fields = [
                'specialization' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'status'
                ]
            ];
            $forge->addColumn('users', $fields);
        }
        
        // Add license_id column if not exists
        if (!in_array('license_id', $existingColumns)) {
            $fields = [
                'license_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'unique' => true,
                    'after' => 'specialization'
                ]
            ];
            $forge->addColumn('users', $fields);
        }
    }

    private function generateRandomLicenseId($db)
    {
        // Generate random 7-digit license ID (1000000 to 9999999)
        $maxAttempts = 10; // Prevent infinite loop
        $attempts = 0;
        
        // Check if license_id column exists
        $columnExists = false;
        try {
            $query = $db->query("SHOW COLUMNS FROM users LIKE 'license_id'");
            $columnExists = $query->getNumRows() > 0;
        } catch (\Exception $e) {
            // Column doesn't exist, skip uniqueness check
        }
        
        do {
            $licenseId = (string)(rand(1000000, 9999999));
            
            // Only check uniqueness if column exists
            if ($columnExists) {
                try {
                    $exists = $db->table('users')->where('license_id', $licenseId)->get()->getRowArray();
                } catch (\Exception $e) {
                    // If error checking, assume it doesn't exist
                    $exists = null;
                }
            } else {
                $exists = null; // Column doesn't exist, so no need to check
            }
            
            $attempts++;
            
            // If we've tried too many times, append timestamp to ensure uniqueness
            if ($attempts >= $maxAttempts) {
                $licenseId = (string)(rand(1000000, 9999999)) . substr(time(), -3);
                break;
            }
        } while ($exists);
        
        return $licenseId;
    }
}
