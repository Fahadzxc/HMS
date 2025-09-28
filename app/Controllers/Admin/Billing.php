<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Billing extends Controller
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Billing & Payments',
        ];
        return view('admin/billing', $data);
    }
}
