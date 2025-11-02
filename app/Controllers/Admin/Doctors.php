<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\DoctorModel;
use App\Models\BranchModel;
use App\Models\UserModel;

class Doctors extends Controller
{
    protected DoctorModel $doctorModel;
    protected BranchModel $branchModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->doctorModel = new DoctorModel();
        $this->branchModel = new BranchModel();
        $this->userModel   = new UserModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $doctors = $this->doctorModel->getAllWithUser();

        $db = \Config\Database::connect();
        if ($db->tableExists('branches')) {
            $branches = $this->branchModel->orderBy('name', 'ASC')->findAll();
        } else {
            log_message('warning', 'Branches table not found. Skipping branch lookup for doctors module.');
            $branches = [];
        }

        $data = [
            'pageTitle' => 'Doctors',
            'title' => 'Doctors - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'doctors' => $doctors,
            'branches' => $branches,
        ];

        return view('admin/doctors/index', $data);
    }
}
