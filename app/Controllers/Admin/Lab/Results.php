<?php

namespace App\Controllers\Admin\Lab;

use CodeIgniter\Controller;
use App\Models\LabTestResultModel;
use App\Models\LabTestRequestModel;
use App\Models\LabStaffModel;

class Results extends Controller
{
    protected LabTestResultModel $resultModel;
    protected LabTestRequestModel $requestModel;
    protected LabStaffModel $staffModel;

    public function __construct()
    {
        $this->resultModel = new LabTestResultModel();
        $this->requestModel = new LabTestRequestModel();
        $this->staffModel = new LabStaffModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $results = $this->resultModel->getAllWithRelations();
        $staff   = $this->staffModel->getAllWithRelations();

        $data = [
            'pageTitle' => 'Laboratory Test Results',
            'title' => 'Lab Test Results - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'results' => $results,
            'staff' => $staff,
        ];

        return view('admin/lab/results', $data);
    }

    public function audit(int $id)
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $status = $this->request->getPost('status');
        $notes  = $this->request->getPost('notes');

        $this->resultModel->update($id, [
            'status' => $status,
            'audit_notes' => $notes,
            'audited_by' => session()->get('user_id'),
            'audited_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Result audit updated.');
    }
}
