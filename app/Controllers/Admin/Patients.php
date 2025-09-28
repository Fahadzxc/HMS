<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Patients extends Controller
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Patients',
        ];

        return view('admin/patients', $data);
    }
}


