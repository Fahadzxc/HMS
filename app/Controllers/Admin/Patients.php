<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PatientModel;

class Patients extends Controller
{
    public function index()
    {
        $model = new PatientModel();
        $patients = $model->orderBy('id', 'DESC')->findAll();

        $data = [
            'pageTitle' => 'Patients',
            'patients'  => $patients,
        ];

        return view('admin/patients', $data);
    }

    public function create()
    {
        $request = $this->request;

        $validationRules = [
            'full_name'   => 'required|min_length[2]|max_length[255]',
            'age'         => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[150]',
            'gender'      => 'required|in_list[Male,Female]',
            'civil_status'=> 'required|in_list[Single,Married,Widowed,Separated]',
            'contact'     => 'required|min_length[5]|max_length[50]',
            'address'     => 'required|in_list[Lagao,Bula,San Isidro,Calumpang,Tambler,City Heights]',
            'concern'     => 'required|min_length[3]'
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('patients');

        $data = [
            'full_name'   => $request->getPost('full_name'),
            'age'         => (int) $request->getPost('age'),
            'gender'      => $request->getPost('gender'),
            'civil_status'=> $request->getPost('civil_status'),
            'contact'     => $request->getPost('contact'),
            'address'     => $request->getPost('address'),
            'concern'     => $request->getPost('concern'),
            'created_at'  => date('Y-m-d H:i:s')
        ];

        $builder->insert($data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Patient added successfully']);
    }
}


