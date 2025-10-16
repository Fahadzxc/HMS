<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function login()
    {
        $data = [
            'title' => 'Login - HMS'
        ];

        return view('auth/login', $data);
    }

    public function processLogin()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        // Use direct database authentication
        $db = \Config\Database::connect();
        $user = $db->table('users')
                  ->where('email', $email)
                  ->where('status', 'active')
                  ->get()
                  ->getRowArray();

        if ($user && password_verify($password, $user['password'])) {
            // Set session data
            $sessionData = [
                'isLoggedIn' => true,
                'user_id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role']
            ];
            
            session()->set($sessionData);
            
            // Debug: Log the session data
            log_message('debug', 'Login successful for user: ' . $user['email'] . ' with role: ' . $user['role']);
            
            // Set remember me cookie if checked
            if ($remember) {
                // You can implement remember me functionality here
            }

            // Redirect to role-specific dashboard URL
            $role = $user['role'];
            switch ($role) {
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                case 'doctor':
                    return redirect()->to('/doctor/dashboard');
                case 'nurse':
                    return redirect()->to('/nurse/dashboard');
                case 'receptionist':
                    return redirect()->to('/reception/dashboard');
                case 'lab':
                    return redirect()->to('/lab/dashboard');
                case 'pharmacist':
                    return redirect()->to('/pharmacy/dashboard');
                case 'accountant':
                    return redirect()->to('/accounts/dashboard');
                case 'it':
                    return redirect()->to('/it/dashboard');
                default:
                    return redirect()->to('/dashboard');
            }
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid email or password');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out successfully.');
    }


    public function forgotPassword()
    {
        $data = [
            'title' => 'Forgot Password - HMS'
        ];

        return view('auth/forgot-password', $data);
    }

    public function dashboard()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        $data = [
            'title' => 'Dashboard - HMS',
            'user_role' => $userRole,
            'user_name' => session()->get('name')
        ];

        // Unified dashboard view; the view will include role-specific partials
        return view('auth/dashboard', $data);
    }
}
