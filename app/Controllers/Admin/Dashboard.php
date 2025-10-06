<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Dashboard',
            'user_role' => 'admin',
        ];

        return view('auth/dashboard', $data);
    }
}


