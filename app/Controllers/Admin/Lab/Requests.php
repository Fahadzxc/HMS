<?php

namespace App\Controllers\Admin\Lab;

use CodeIgniter\Controller;
use App\Models\LabTestRequestModel;
use App\Models\LabStaffModel;
use App\Models\LabDepartmentModel;

class Requests extends Controller
{
    protected LabTestRequestModel $requestModel;
    protected LabStaffModel $staffModel;
    protected LabDepartmentModel $departmentModel;

    public function __construct()
    {
        $this->requestModel = new LabTestRequestModel();
        $this->staffModel = new LabStaffModel();
        $this->departmentModel = new LabDepartmentModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $requests = $this->requestModel->getAllWithRelations();
        $staff    = $this->staffModel->getAllWithRelations();
        $data = [
            'pageTitle' => 'Laboratory Test Requests',
            'title' => 'Lab Test Requests - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'requests' => $requests,
            'staff' => $staff,
        ];

        return view('admin/lab/requests', $data);
    }

    public function reassign(int $id)
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $staffId = (int) $this->request->getPost('staff_id');

        $this->requestModel->update($id, [
            'assigned_staff_id' => $staffId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Lab staff reassigned successfully.');
    }

    public function changePriority(int $id)
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $priority = $this->request->getPost('priority');

        $this->requestModel->update($id, [
            'priority' => $priority,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Priority updated successfully.');
    }

    public function forceComplete(int $id)
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $reason = $this->request->getPost('reason');

        $this->requestModel->update($id, [
            'status' => 'completed',
            'override_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Request marked as completed.');
    }
}
