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
            
            // Set remember me cookie if checked
            if ($remember) {
                // You can implement remember me functionality here
            }

            return redirect()->to('/dashboard');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid email or password');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'You have been logged out successfully.');
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

        // Show different dashboards based on user role
        switch ($userRole) {
            case 'admin':
                return view('admin_dashboard', $data);
            case 'doctor':
                return view('doctor_dashboard', $data);
            case 'nurse':
                return view('nurse_dashboard', $data);
            case 'staff':
                return view('staff_dashboard', $data);
            default:
                return view('admin_dashboard', $data);
        }
    }
}
