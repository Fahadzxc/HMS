<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\NurseModel;
use App\Models\UserModel;

class Nurses extends Controller
{
    protected NurseModel $nurseModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->nurseModel = new NurseModel();
        $this->userModel  = new UserModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $nurses = $this->nurseModel->getAllWithUser();

        $data = [
            'pageTitle' => 'Nurses',
            'title' => 'Nurses - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'nurses' => $nurses,
        ];

        return view('admin/nurses/index', $data);
    }
}
