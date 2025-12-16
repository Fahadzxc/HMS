<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class It extends BaseController
{
	public function dashboard()
	{
		// Check session data
		$isLoggedIn = session()->get('isLoggedIn');
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		$userName = session()->get('name');
		
		// Debug logging
		log_message('debug', 'IT Dashboard - isLoggedIn: ' . ($isLoggedIn ? 'true' : 'false') . ', role: ' . $userRole);
		
		// If not logged in, redirect to login
		if (!$isLoggedIn) {
			log_message('warning', 'IT Dashboard access denied: Not logged in');
			return redirect()->to('/login')->with('error', 'Please log in first.');
		}
		
		// If role is not IT or admin, redirect to login (case-insensitive check)
		if (!in_array($userRole, ['it', 'admin'], true)) {
			log_message('warning', 'IT Dashboard access denied: Invalid role - ' . $userRole);
			return redirect()->to('/login')->with('error', 'Unauthorized access. Your role: ' . $userRole);
		}

		log_message('info', 'IT Dashboard accessed by: ' . $userName . ' (role: ' . $userRole . ')');

		// Get real dashboard data
		$systemHealth = $this->checkSystemHealth();
		$activeUsers = $this->getActiveUsersCount();
		$pendingTickets = $this->getPendingTicketsCount();
		$lastBackup = $this->getLastBackupTime();
		
		// Determine overall status (Normal/Degraded/Down/Under Maintenance)
		$overallStatus = 'normal';
		foreach ($systemHealth as $component) {
			if ($component['status'] === 'offline') {
				$overallStatus = 'down';
				break;
			} elseif ($component['status'] === 'degraded' && $overallStatus === 'normal') {
				$overallStatus = 'degraded';
			}
		}

		$data = [
			'title' => 'IT Dashboard - HMS',
			'user_role' => 'it',
			'user_name' => $userName,
			'systemHealth' => $systemHealth,
			'activeUsers' => $activeUsers,
			'pendingTickets' => $pendingTickets,
			'lastBackup' => $lastBackup,
			'overallStatus' => $overallStatus,
		];

		return view('auth/dashboard', $data);
	}

	public function system()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		// Get system status data
		$systemHealth = $this->checkSystemHealth();
		$activeUsers = $this->getActiveUsersCount();
		$pendingTickets = $this->getPendingTicketsCount();
		$lastBackup = $this->getLastBackupTime();
		$itStaffStatus = $this->getItStaffStatus();
		$systemTasks = $this->getSystemTasks();
		
		// Determine overall system status
		$overallStatus = 'normal';
		foreach ($systemHealth as $component) {
			if ($component['status'] === 'offline') {
				$overallStatus = 'down';
				break;
			} elseif ($component['status'] === 'degraded' && $overallStatus === 'normal') {
				$overallStatus = 'degraded';
			}
		}

		// Get database info
		$dbInfo = [];
		try {
			$db = \Config\Database::connect();
			$result = $db->query("SELECT VERSION() as version");
			$row = $result->getRow();
			$dbInfo['version'] = $row->version ?? 'Unknown';
		} catch (\Exception $e) {
			$dbInfo['version'] = 'Unknown';
		}

		$data = [
			'title' => 'System Status - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
			'systemHealth' => $systemHealth,
			'activeUsers' => $activeUsers,
			'pendingTickets' => $pendingTickets,
			'lastBackup' => $lastBackup,
			'overallStatus' => $overallStatus,
			'dbInfo' => $dbInfo,
			'itStaffStatus' => $itStaffStatus,
			'systemTasks' => $systemTasks,
		];

		return view('it/system_status', $data);
	}

	private function checkSystemHealth()
	{
		$health = [];
		$now = time();

		// Check Database Server
		$dbStatus = 'offline';
		$dbUptime = '0%';
		$dbLastCheck = 'Just now';
		try {
			$db = \Config\Database::connect();
			$startTime = microtime(true);
			$db->query("SELECT 1");
			$responseTime = (microtime(true) - $startTime) * 1000;
			
			if ($responseTime < 100) {
				$dbStatus = 'online';
				$dbUptime = '99.9% Excellent';
			} elseif ($responseTime < 500) {
				$dbStatus = 'online';
				$dbUptime = '99.5% Good';
			} else {
				$dbStatus = 'degraded';
				$dbUptime = '95.0% Fair';
			}
			$dbLastCheck = 'Just now';
		} catch (\Exception $e) {
			$dbStatus = 'offline';
			$dbUptime = '0%';
			$dbLastCheck = 'Just now';
		}

		$health[] = [
			'name' => 'Database Server',
			'status' => $dbStatus,
			'uptime' => $dbUptime,
			'last_check' => $dbLastCheck
		];

		// Check Web Server
		$webStatus = 'online';
		$webUptime = '99.8% Good';
		$webLastCheck = 'Just now';
		// Web server is always online if we're running this code
		$health[] = [
			'name' => 'Web Server',
			'status' => $webStatus,
			'uptime' => $webUptime,
			'last_check' => $webLastCheck
		];

		// Check File Storage
		$fileStatus = 'offline';
		$fileUptime = '0%';
		$fileLastCheck = 'Just now';
		$writablePath = WRITEPATH;
		if (is_writable($writablePath)) {
			// Try to write a test file
			$testFile = $writablePath . 'test_' . time() . '.tmp';
			if (@file_put_contents($testFile, 'test') !== false) {
				@unlink($testFile);
				$fileStatus = 'online';
				$fileUptime = '100% Excellent';
			} else {
				$fileStatus = 'degraded';
				$fileUptime = '95.0% Fair';
			}
		}
		$health[] = [
			'name' => 'File Storage',
			'status' => $fileStatus,
			'uptime' => $fileUptime,
			'last_check' => $fileLastCheck
		];

		// Check Email Server (simulated - would need actual SMTP check)
		$emailStatus = 'degraded';
		$emailUptime = '95.2% Fair';
		$emailLastCheck = 'Just now';
		// In a real implementation, you would check SMTP connection here
		// For now, we'll simulate a degraded status
		$health[] = [
			'name' => 'Email Server',
			'status' => $emailStatus,
			'uptime' => $emailUptime,
			'last_check' => $emailLastCheck
		];

		return $health;
	}

	private function getActiveUsersCount()
	{
		try {
			$sessionPath = WRITEPATH . 'session';
			$activeUsers = [];
			$activeTimeout = 1800; // 30 minutes - only count users active in last 30 minutes
			$now = time();
			
			if (!is_dir($sessionPath)) {
				return 0;
			}
			
			$files = scandir($sessionPath);
			
			foreach ($files as $file) {
				// Skip non-session files
				if ($file === '.' || $file === '..' || $file === 'index.html' || strpos($file, 'ci_session') === false) {
					continue;
				}
				
				$filePath = $sessionPath . DIRECTORY_SEPARATOR . $file;
				if (!is_file($filePath)) {
					continue;
				}
				
				$fileTime = filemtime($filePath);
				// Only check files modified in last 30 minutes (truly active)
				if (($now - $fileTime) > $activeTimeout) {
					continue;
				}
				
				// Read session file content
				$sessionContent = @file_get_contents($filePath);
				if ($sessionContent === false || empty($sessionContent)) {
					continue;
				}
				
				// CodeIgniter session format: key|type:value;
				// Check if user is logged in: isLoggedIn|b:1
				if (strpos($sessionContent, 'isLoggedIn|b:1') === false) {
					continue;
				}
				
				// Extract user_id from session
				// Format: user_id|s:1:"4" or user_id|i:4
				$userId = null;
				if (preg_match('/user_id\|s:\d+:"(\d+)"/', $sessionContent, $matches)) {
					// String format: user_id|s:1:"4"
					$userId = (int)$matches[1];
				} elseif (preg_match('/user_id\|i:(\d+)/', $sessionContent, $matches)) {
					// Integer format: user_id|i:4
					$userId = (int)$matches[1];
				}
				
				// Only count if we found a valid user_id
				if ($userId !== null) {
					// Use user_id as key to avoid counting same user multiple times (multiple sessions)
					$activeUsers[$userId] = true;
				}
			}
			
			return count($activeUsers);
		} catch (\Exception $e) {
			log_message('error', 'Error counting active users: ' . $e->getMessage());
			return 0;
		}
	}

	private function getPendingTicketsCount()
	{
		try {
			$db = \Config\Database::connect();
			
			// Check if tickets table exists
			if ($db->tableExists('tickets')) {
				$count = $db->table('tickets')
					->where('status', 'pending')
					->countAllResults();
				return $count;
			}
			
			// If no tickets table, return 0
			return 0;
		} catch (\Exception $e) {
			return 0;
		}
	}

	private function getLastBackupTime()
	{
		try {
			$backupPath = WRITEPATH . 'uploads/backups/';
			
			if (!is_dir($backupPath)) {
				return 'Never';
			}
			
			$files = scandir($backupPath);
			$latestBackup = 0;
			
			foreach ($files as $file) {
				if ($file === '.' || $file === '..' || $file === 'index.html') {
					continue;
				}
				
				$filePath = $backupPath . $file;
				if (is_file($filePath)) {
					$fileTime = filemtime($filePath);
					if ($fileTime > $latestBackup) {
						$latestBackup = $fileTime;
					}
				}
			}
			
			if ($latestBackup === 0) {
				return 'Never';
			}
			
			$diff = time() - $latestBackup;
			
			if ($diff < 60) {
				return 'Just now';
			} elseif ($diff < 3600) {
				$minutes = floor($diff / 60);
				return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
			} elseif ($diff < 86400) {
				$hours = floor($diff / 3600);
				return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
			} else {
				$days = floor($diff / 86400);
				return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
			}
		} catch (\Exception $e) {
			return 'Unknown';
		}
	}

	private function getItStaffStatus()
	{
		try {
			$db = \Config\Database::connect();
			$sessionPath = WRITEPATH . 'session';
			$activeTimeout = 1800; // 30 minutes
			$now = time();
			$itStaff = [];
			
			// Get all IT staff from users table
			if ($db->tableExists('users')) {
				$itUsers = $db->table('users')
					->where('role', 'it')
					->where('status', 'active')
					->get()
					->getResultArray();
				
				// Get active session user IDs
				$activeUserIds = [];
				if (is_dir($sessionPath)) {
					$files = scandir($sessionPath);
					foreach ($files as $file) {
						if ($file === '.' || $file === '..' || strpos($file, 'ci_session') === false) {
							continue;
						}
						
						$filePath = $sessionPath . DIRECTORY_SEPARATOR . $file;
						if (!is_file($filePath)) {
							continue;
						}
						
						$fileTime = filemtime($filePath);
						if (($now - $fileTime) > $activeTimeout) {
							continue;
						}
						
						$sessionContent = @file_get_contents($filePath);
						if ($sessionContent && strpos($sessionContent, 'isLoggedIn|b:1') !== false) {
							if (preg_match('/user_id\|s:\d+:"(\d+)"/', $sessionContent, $matches)) {
								$activeUserIds[(int)$matches[1]] = true;
							} elseif (preg_match('/user_id\|i:(\d+)/', $sessionContent, $matches)) {
								$activeUserIds[(int)$matches[1]] = true;
							}
						}
					}
				}
				
				// Build IT staff status
				foreach ($itUsers as $user) {
					$userId = $user['id'];
					$isOnline = isset($activeUserIds[$userId]);
					
					// Get user's current tasks
					$currentTasks = $this->getUserTasks($userId);
					
					// Determine operational status
					$operationalStatus = 'offline';
					if ($isOnline) {
						if (count($currentTasks) > 0) {
							// Check if any task is in progress
							$hasInProgress = false;
							foreach ($currentTasks as $task) {
								if (in_array($task['status'], ['in_progress', 'assigned'])) {
									$hasInProgress = true;
									break;
								}
							}
							$operationalStatus = $hasInProgress ? 'active' : 'idle';
						} else {
							$operationalStatus = 'idle';
						}
					}
					
					$itStaff[] = [
						'user_id' => $userId,
						'name' => $user['name'],
						'email' => $user['email'],
						'operational_status' => $operationalStatus, // online/offline/idle/active
						'is_online' => $isOnline,
						'current_tasks' => count($currentTasks),
						'tasks' => $currentTasks,
						'last_activity' => $isOnline ? 'Just now' : 'Offline'
					];
				}
			}
			
			return $itStaff;
		} catch (\Exception $e) {
			log_message('error', 'Error getting IT staff status: ' . $e->getMessage());
			return [];
		}
	}

	private function getUserTasks($userId)
	{
		try {
			$db = \Config\Database::connect();
			$tasks = [];
			
			// Check if tickets table exists and get assigned tickets
			if ($db->tableExists('tickets')) {
				$tickets = $db->table('tickets')
					->where('assigned_to', $userId)
					->whereIn('status', ['pending', 'assigned', 'in_progress', 'on_hold', 'escalated'])
					->orderBy('created_at', 'DESC')
					->get()
					->getResultArray();
				
				foreach ($tickets as $ticket) {
					$tasks[] = [
						'id' => $ticket['id'],
						'type' => 'ticket',
						'title' => $ticket['subject'] ?? $ticket['title'] ?? 'Ticket #' . $ticket['id'],
						'status' => $this->normalizeTaskStatus($ticket['status']),
						'priority' => $ticket['priority'] ?? 'medium',
						'created_at' => $ticket['created_at'] ?? null
					];
				}
			}
			
			return $tasks;
		} catch (\Exception $e) {
			return [];
		}
	}

	private function getSystemTasks()
	{
		try {
			$db = \Config\Database::connect();
			$tasks = [];
			
			// Get tickets with different statuses
			if ($db->tableExists('tickets')) {
				$tickets = $db->table('tickets')
					->select('id, subject, status, priority, assigned_to, created_at')
					->orderBy('created_at', 'DESC')
					->limit(20)
					->get()
					->getResultArray();
				
				foreach ($tickets as $ticket) {
					$tasks[] = [
						'id' => $ticket['id'],
						'type' => 'ticket',
						'title' => $ticket['subject'] ?? 'Ticket #' . $ticket['id'],
						'status' => $this->normalizeTaskStatus($ticket['status']),
						'priority' => $ticket['priority'] ?? 'medium',
						'assigned_to' => $ticket['assigned_to'] ?? null,
						'created_at' => $ticket['created_at'] ?? null
					];
				}
			}
			
			return $tasks;
		} catch (\Exception $e) {
			return [];
		}
	}

	private function normalizeTaskStatus($status)
	{
		// Normalize status to standard values
		$statusMap = [
			'pending' => 'pending',
			'assigned' => 'assigned',
			'in_progress' => 'in_progress',
			'inprogress' => 'in_progress',
			'on_hold' => 'on_hold',
			'onhold' => 'on_hold',
			'hold' => 'on_hold',
			'escalated' => 'escalated',
			'resolved' => 'resolved',
			'closed' => 'closed',
			'idle' => 'idle'
		];
		
		$normalized = strtolower(trim($status ?? ''));
		return $statusMap[$normalized] ?? 'pending';
	}

	public function users()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		try {
			// Load users from database
			$db = \Config\Database::connect();
			
			if ($db->tableExists('users')) {
				// Load from database
				$users = $db->table('users')
						   ->orderBy('id', 'DESC')
						   ->get()
						   ->getResultArray();
			} else {
				// If table doesn't exist, return empty array
				$users = [];
			}

			$data = [
				'title' => 'User Management - HMS',
				'user_role' => 'it',
				'user_name' => session()->get('name'),
				'users' => $users,
			];

			return view('it/users', $data);
			
		} catch (\Exception $e) {
			// Fallback if database fails
			$data = [
				'title' => 'User Management - HMS',
				'user_role' => 'it',
				'user_name' => session()->get('name'),
				'users' => [],
				'error' => 'Error loading users: ' . $e->getMessage()
			];

			return view('it/users', $data);
		}
	}

	public function createUser()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

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
			
			// License ID field
			$licenseId = trim($request->getPost('license_id') ?? '');
			
			// Combine name parts
			$nameParts = array_filter([$firstName, $middleName, $lastName]);
			$name = implode(' ', $nameParts);
			
			// Validate required fields
			if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($phone) || empty($role) || empty($status)) {
				return redirect()->to('/it/users')->with('error', 'All required fields must be filled.');
			}
			
			// Validate specialization for Doctor
			if ($role === 'doctor' && empty($specialization)) {
				return redirect()->to('/it/users')->with('error', 'Specialization is required for Doctor role.');
			}
			
			// Validate email format
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				return redirect()->to('/it/users')->with('error', 'Invalid email format.');
			}
			
			// Validate password length
			if (strlen($password) < 6) {
				return redirect()->to('/it/users')->with('error', 'Password must be at least 6 characters long.');
			}
			
			// Check if email already exists
			$existingUser = $db->table('users')->where('email', $email)->get()->getRowArray();
			if ($existingUser) {
				return redirect()->to('/it/users')->with('error', 'Email address already exists.');
			}
			
			// Validate role
			$allowedRoles = ['admin', 'doctor', 'nurse', 'staff', 'lab', 'receptionist', 'pharmacist', 'accountant', 'it'];
			if (!in_array($role, $allowedRoles)) {
				return redirect()->to('/it/users')->with('error', 'Invalid role selected.');
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
			
			return redirect()->to('/it/users')->with('success', 'User created successfully!');
			
		} catch (\Exception $e) {
			log_message('error', 'Error creating user: ' . $e->getMessage());
			return redirect()->to('/it/users')->with('error', 'Error creating user: ' . $e->getMessage());
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

	public function updateUser()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

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
				return redirect()->to('/it/users')->with('error', 'All required fields must be filled.');
			}
			
			// Validate specialization for Doctor
			if ($role === 'doctor' && empty($specialization)) {
				return redirect()->to('/it/users')->with('error', 'Specialization is required for Doctor role.');
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
			
			return redirect()->to('/it/users')->with('success', 'User updated successfully!');
			
		} catch (\Exception $e) {
			log_message('error', 'Error updating user: ' . $e->getMessage());
			return redirect()->to('/it/users')->with('error', 'Error updating user: ' . $e->getMessage());
		}
	}

	public function deleteUser()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		try {
			$request = $this->request;
			$db = \Config\Database::connect();
			
			$userId = $request->getPost('user_id');
			
			$db->table('users')->where('id', $userId)->delete();
			
			return redirect()->to('/it/users')->with('success', 'User deleted successfully!');
			
		} catch (\Exception $e) {
			return redirect()->to('/it/users')->with('error', 'Error deleting user: ' . $e->getMessage());
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
				'constraint' => ['admin', 'staff', 'doctor', 'nurse', 'receptionist', 'lab', 'pharmacist', 'accountant', 'it'],
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

	public function backup()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		// Get list of uploaded backup files
		$backupFiles = [];
		$uploadPath = WRITEPATH . 'uploads/backups/';
		
		// Create directory if it doesn't exist
		if (!is_dir($uploadPath)) {
			mkdir($uploadPath, 0755, true);
		}
		
		// Get all files in backup directory
		if (is_dir($uploadPath)) {
			$files = scandir($uploadPath);
			foreach ($files as $file) {
				if ($file !== '.' && $file !== '..' && is_file($uploadPath . $file)) {
					$backupFiles[] = [
						'name' => $file,
						'size' => filesize($uploadPath . $file),
						'date' => date('Y-m-d H:i:s', filemtime($uploadPath . $file)),
						'path' => $uploadPath . $file
					];
				}
			}
			// Sort by date, newest first
			usort($backupFiles, function($a, $b) {
				return strtotime($b['date']) - strtotime($a['date']);
			});
		}

		$data = [
			'title' => 'Backup - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
			'backup_files' => $backupFiles,
		];

		return view('it/backup', $data);
	}

	public function uploadBackup()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$request = $this->request;
		$file = $request->getFile('backup_file');

		if (!$file || !$file->isValid()) {
			return redirect()->to('/it/backup')->with('error', 'No file uploaded or file is invalid.');
		}

		// Validate file type
		$allowedTypes = ['sql', 'zip', 'gz', 'tar', 'bak', 'db'];
		$extension = $file->getExtension();
		
		if (!in_array(strtolower($extension), $allowedTypes)) {
			return redirect()->to('/it/backup')->with('error', 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
		}

		// Validate file size (max 100MB)
		if ($file->getSize() > 100 * 1024 * 1024) {
			return redirect()->to('/it/backup')->with('error', 'File size exceeds 100MB limit.');
		}

		// Create backup directory if it doesn't exist
		$uploadPath = WRITEPATH . 'uploads/backups/';
		if (!is_dir($uploadPath)) {
			mkdir($uploadPath, 0755, true);
		}

		// Generate unique filename with timestamp
		$newName = date('Y-m-d_His') . '_' . $file->getName();
		
		if ($file->move($uploadPath, $newName)) {
			return redirect()->to('/it/backup')->with('success', 'Backup file uploaded successfully!');
		} else {
			return redirect()->to('/it/backup')->with('error', 'Failed to upload backup file.');
		}
	}

	public function deleteBackup()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$request = $this->request;
		$fileName = $request->getPost('file_name');
		
		if (empty($fileName)) {
			return redirect()->to('/it/backup')->with('error', 'No file specified.');
		}

		// Security: prevent directory traversal
		$fileName = basename($fileName);
		$filePath = WRITEPATH . 'uploads/backups/' . $fileName;

		if (file_exists($filePath) && is_file($filePath)) {
			if (unlink($filePath)) {
				return redirect()->to('/it/backup')->with('success', 'Backup file deleted successfully!');
			} else {
				return redirect()->to('/it/backup')->with('error', 'Failed to delete backup file.');
			}
		} else {
			return redirect()->to('/it/backup')->with('error', 'File not found.');
		}
	}

	public function downloadBackup()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$fileName = $this->request->getGet('file');
		
		if (empty($fileName)) {
			return redirect()->to('/it/backup')->with('error', 'No file specified.');
		}

		// Security: prevent directory traversal
		$fileName = basename($fileName);
		$filePath = WRITEPATH . 'uploads/backups/' . $fileName;

		if (file_exists($filePath) && is_file($filePath)) {
			return $this->response->download($filePath, null);
		} else {
			return redirect()->to('/it/backup')->with('error', 'File not found.');
		}
	}

	public function security()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Security - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function tickets()
	{
		$userRole = strtolower(trim(session()->get('role') ?? ''));
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Support Tickets - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}
}




