<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Appointments extends Controller
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Appointments',
        ];
        return view('admin/appointments', $data);
    }
}
