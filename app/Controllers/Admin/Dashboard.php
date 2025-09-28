<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Dashboard',
        ];

        return view('admin/dashboard', $data);
    }
}


