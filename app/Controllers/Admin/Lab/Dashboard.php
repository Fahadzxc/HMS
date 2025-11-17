<?php

namespace App\Controllers\Admin\Lab;

use CodeIgniter\Controller;
use App\Models\LabTestRequestModel;
use App\Models\LabTestResultModel;
use App\Models\LabStaffModel;
use Throwable;

class Dashboard extends Controller
{
    protected LabTestRequestModel $requestModel;
    protected LabTestResultModel $resultModel;
    protected LabStaffModel $staffModel;

    public function __construct()
    {
        $this->requestModel = new LabTestRequestModel();
        $this->resultModel = new LabTestResultModel();
        $this->staffModel = new LabStaffModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $data = [
            'pageTitle' => 'Laboratory Dashboard',
            'title' => 'Laboratory Dashboard - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'metrics' => [
                'pendingRequests' => 0,
                'completedToday' => 0,
                'criticalResults' => 0,
                'activeStaff' => 0,
            ],
            'recentRequests' => [],
            'recentResults' => [],
            'loadError' => null,
        ];

        try {
            $today = date('Y-m-d');

            $pendingRequests = (clone $this->requestModel)
                ->where('status', 'pending')
                ->countAllResults();

            $completedToday = (clone $this->requestModel)
                ->where('status', 'completed')
                ->where('DATE(updated_at)', $today)
                ->countAllResults();

            $criticalResults = (clone $this->resultModel)
                ->where('critical_flag', 1)
                ->countAllResults();

            $activeStaff = (clone $this->staffModel)
                ->where('status', 'active')
                ->countAllResults();

            $recentRequests = $this->requestModel->getAllWithRelations([
                'date_from' => date('Y-m-d', strtotime('-7 days')),
            ]);

            $recentResults = $this->resultModel->getAllWithRelations([
                'date_from' => date('Y-m-d', strtotime('-7 days')),
            ]);

            $data['metrics'] = [
                'pendingRequests' => $pendingRequests,
                'completedToday' => $completedToday,
                'criticalResults' => $criticalResults,
                'activeStaff' => $activeStaff,
            ];
            $data['recentRequests'] = $recentRequests;
            $data['recentResults'] = $recentResults;
        } catch (Throwable $e) {
            log_message('error', 'Failed to load lab dashboard data: ' . $e->getMessage());
            $data['loadError'] = 'Laboratory data is not ready yet. Please ensure the latest migrations are run and sample data is available.';
        }

        return view('admin/lab/dashboard', $data);
    }
}
