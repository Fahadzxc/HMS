<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\LabTestRequestModel;
use App\Models\LabTestResultModel;
use App\Models\SettingModel;

class Lab extends Controller
{
	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$requestModel = new LabTestRequestModel();
		$resultModel = new LabTestResultModel();
		
		$metrics = $requestModel->getDashboardMetrics();
		
		// Get pending test requests (limit to 10 most recent)
		$pendingRequests = $requestModel->getAllWithRelations(['status' => 'pending']);
		$pendingRequests = array_slice($pendingRequests, 0, 10);
		
		// Get recent test results (limit to 10 most recent)
		$recentResults = $resultModel->getAllWithRelations([]);
		$recentResults = array_slice($recentResults, 0, 10);
		
		// Count urgent tests (high or critical priority)
		$urgentCount = $requestModel->where('priority', 'high')
			->orWhere('priority', 'critical')
			->where('status', 'pending')
			->countAllResults();

		$data = [
			'title' => 'Laboratory Dashboard - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
			'pending_requests' => $metrics['pending_requests'] ?? 0,
			'completed_today' => $metrics['completed_today'] ?? 0,
			'critical_tests' => $metrics['critical_results'] ?? 0,
			'urgent_tests' => $urgentCount,
			'recent_requests' => $pendingRequests,
			'recent_results' => $recentResults,
		];

		return view('lab/dashboard', $data);
	}

	public function requests()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		try {
		$requestModel = new LabTestRequestModel();
		$db = \Config\Database::connect();
		
		// Get filters from query string
		$filters = [
			'status' => $this->request->getGet('status'),
			'priority' => $this->request->getGet('priority'),
		];

		// Try to get requests with relations, fallback to simple query if joins fail
		$requests = [];
		try {
			$requests = $requestModel->getAllWithRelations($filters);
		} catch (\Exception $e) {
			log_message('error', 'Error getting requests with relations: ' . $e->getMessage());
			// Fallback to simple query
			$patientModel = new \App\Models\PatientModel();
			$builder = $requestModel;
			if (!empty($filters['status'])) {
				$builder = $builder->where('status', $filters['status']);
			}
			if (!empty($filters['priority'])) {
				$builder = $builder->where('priority', $filters['priority']);
			}
			$requestsRaw = $builder->orderBy('requested_at', 'DESC')->findAll();
			
			// Manually add patient and doctor names
			foreach ($requestsRaw as $req) {
				$patient = $patientModel->find($req['patient_id']);
				$doctor = $db->table('users')->where('id', $req['doctor_id'])->get()->getRowArray();
				
				$req['patient_name'] = $patient['full_name'] ?? 'N/A';
				$req['doctor_name'] = $doctor['name'] ?? 'N/A';
				$req['staff_name'] = null;
				
				$requests[] = $req;
			}
		}

		$data = [
			'title' => 'Test Requests - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
			'requests' => $requests,
		];

			return view('lab/requests', $data);
			
		} catch (\Exception $e) {
			log_message('error', 'Error in Lab::requests: ' . $e->getMessage());
			log_message('error', 'Stack trace: ' . $e->getTraceAsString());
			
			// Return error view or redirect
			return view('lab/requests', [
				'title' => 'Test Requests - HMS',
				'user_role' => 'lab',
				'user_name' => session()->get('name'),
				'requests' => [],
				'patients' => [],
				'doctors' => [],
				'error' => 'An error occurred while loading test requests. Please try again later.',
			]);
		}
	}
	
	public function createRequest()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		try {
			$requestModel = new LabTestRequestModel();
			
			$patientId = $this->request->getPost('patient_id');
			$doctorId = $this->request->getPost('doctor_id');
			$testType = $this->request->getPost('test_type');
			$priority = $this->request->getPost('priority') ?? 'normal';
			$notes = $this->request->getPost('notes') ?? '';
			
			// Validation
			if (empty($patientId) || empty($doctorId) || empty($testType)) {
				return $this->response->setJSON([
					'success' => false,
					'message' => 'Please fill in all required fields (Patient, Doctor, Test Type).'
				]);
			}
			
			$data = [
				'patient_id' => $patientId,
				'doctor_id' => $doctorId,
				'test_type' => $testType,
				'priority' => $priority,
				'status' => 'pending',
				'requested_at' => date('Y-m-d H:i:s'),
				'notes' => $notes,
			];
			
			$requestModel->insert($data);
			
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Lab test request created successfully!'
			]);
			
		} catch (\Exception $e) {
			log_message('error', 'Error creating lab request: ' . $e->getMessage());
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Error creating request: ' . $e->getMessage()
			]);
		}
	}
	
	public function updateRequestStatus()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		try {
			$requestModel = new LabTestRequestModel();
			
			$requestId = $this->request->getPost('request_id');
			$status = $this->request->getPost('status');
			
			if (empty($requestId) || empty($status)) {
				return $this->response->setJSON(['success' => false, 'message' => 'Missing parameters']);
			}
			
			$requestModel->update($requestId, [
				'status' => $status,
				'updated_at' => date('Y-m-d H:i:s'),
			]);
			
			return $this->response->setJSON(['success' => true]);
			
		} catch (\Exception $e) {
			return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
		}
	}

	public function results()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$resultModel = new LabTestResultModel();
		$requestModel = new LabTestRequestModel();
		
		// Get filters from query string
		$filters = [
			'status' => $this->request->getGet('status'),
			'critical' => $this->request->getGet('critical'),
		];

		$results = $resultModel->getAllWithRelations($filters);
		
		// Get pending requests that can have results entered
		$pendingRequests = $requestModel->getAllWithRelations(['status' => 'pending']);
		$inProgressRequests = $requestModel->getAllWithRelations(['status' => 'in_progress']);
		$pendingRequests = array_merge($pendingRequests, $inProgressRequests);
		
		// Get request_id from URL if coming from "Start Test"
		$openRequestId = $this->request->getGet('request_id');
		$action = $this->request->getGet('action');

		$data = [
			'title' => 'Test Results - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
			'results' => $results,
			'pending_requests' => $pendingRequests,
			'open_request_id' => ($action === 'start' && $openRequestId) ? $openRequestId : null,
		];

		return view('lab/results', $data);
	}
	
	public function saveResult()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		try {
			$resultModel = new LabTestResultModel();
			$requestModel = new LabTestRequestModel();
			
			$requestId = $this->request->getPost('request_id');
			$resultSummary = $this->request->getPost('result_summary');
			$detailedReport = $this->request->getPost('detailed_report') ?? '';
			$criticalFlag = $this->request->getPost('critical_flag') ? 1 : 0;
			
			// Validation
			if (empty($requestId) || empty($resultSummary)) {
				return $this->response->setJSON([
					'success' => false,
					'message' => 'Request ID and Result Summary are required.'
				]);
			}
			
			// Check if request exists
			$request = $requestModel->find($requestId);
			if (!$request) {
				return $this->response->setJSON([
					'success' => false,
					'message' => 'Test request not found.'
				]);
			}
			
			// Check if result already exists
			$existingResult = $resultModel->where('request_id', $requestId)->first();
			
			$resultData = [
				'request_id' => $requestId,
				'result_summary' => $resultSummary,
				'detailed_report_path' => !empty($detailedReport) ? $detailedReport : null,
				'status' => 'completed',
				'critical_flag' => $criticalFlag,
				'released_at' => date('Y-m-d H:i:s'),
			];
			
			if ($existingResult) {
				// Update existing result
				$resultModel->update($existingResult['id'], $resultData);
			} else {
				// Create new result
				$resultModel->insert($resultData);
			}
			
			// Update request status to completed
			$requestModel->update($requestId, [
				'status' => 'completed',
				'updated_at' => date('Y-m-d H:i:s'),
			]);
			
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Test result saved successfully!'
			]);
			
		} catch (\Exception $e) {
			log_message('error', 'Error saving lab result: ' . $e->getMessage());
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Error saving result: ' . $e->getMessage()
			]);
		}
	}

	public function reports()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$requestModel = new LabTestRequestModel();
		$resultModel = new LabTestResultModel();
		
		// Get filters
		$reportType = $this->request->getGet('type') ?? 'test_requests';
		$dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
		$dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

		$data = [
			'title' => 'Laboratory Reports - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
			'report_type' => $reportType,
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'test_requests' => [],
			'test_results' => [],
			'critical_results' => [],
			'summary' => [
				'total_requests' => 0,
				'total_results' => 0,
				'critical_count' => 0,
				'completion_rate' => 0,
			]
		];

		try {
			// Test Requests Report
			$requests = $requestModel
				->select('lab_test_requests.*, patients.full_name as patient_name, patients.patient_id as patient_code, users.name as doctor_name')
				->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
				->join('users', 'users.id = lab_test_requests.doctor_id', 'left')
				->where('DATE(lab_test_requests.requested_at) >=', $dateFrom)
				->where('DATE(lab_test_requests.requested_at) <=', $dateTo)
				->orderBy('lab_test_requests.requested_at', 'DESC')
				->findAll();
			
			$data['test_requests'] = $requests;
			$data['summary']['total_requests'] = count($requests);

			// Test Results Report
			$db = \Config\Database::connect();
			if ($db->tableExists('lab_test_results')) {
				try {
					$results = $resultModel
						->select('lab_test_results.*, lab_test_requests.test_type, patients.full_name as patient_name, patients.patient_id as patient_code')
						->join('lab_test_requests', 'lab_test_requests.id = lab_test_results.request_id', 'left')
						->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
						->where('DATE(lab_test_results.released_at) >=', $dateFrom)
						->where('DATE(lab_test_results.released_at) <=', $dateTo)
						->orderBy('lab_test_results.released_at', 'DESC')
						->findAll();
					
					$data['test_results'] = $results;
					$data['summary']['total_results'] = count($results);
					
					// Critical Results
					$critical = array_filter($results, function($r) {
						return !empty($r['critical_flag']) && $r['critical_flag'] == 1;
					});
					$data['critical_results'] = array_values($critical);
					$data['summary']['critical_count'] = count($critical);
					
					// Completion Rate
					$data['summary']['completion_rate'] = count($requests) > 0 
						? round((count($results) / count($requests)) * 100, 2) 
						: 0;
				} catch (\Exception $e) {
					log_message('error', 'Error fetching lab results in reports: ' . $e->getMessage());
					$data['test_results'] = [];
				}
			} else {
				$data['test_results'] = [];
			}
		} catch (\Exception $e) {
			log_message('error', 'Error fetching laboratory reports: ' . $e->getMessage());
		}

		return view('lab/reports', $data);
	}

	public function settings()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$model = new SettingModel();
		$defaults = [
			'lab_default_priority'      => 'normal',
			'lab_auto_assign_staff'     => '1',
			'lab_urgent_threshold'      => '6',
			'lab_notification_email'    => session()->get('email') ?? 'lab@hospital.local',
			'lab_report_signature'      => "Laboratory Department\nMediCare Hospital",
		];
		$settings = array_merge($defaults, $model->getAllAsMap());

		$data = [
			'title'     => 'Lab Settings - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
			'pageTitle' => 'Settings',
			'settings'  => $settings,
		];

		return view('lab/settings', $data);
	}

	public function saveSettings()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$model = new SettingModel();
		$post = $this->request->getPost();
		$keys = [
			'lab_default_priority',
			'lab_auto_assign_staff',
			'lab_urgent_threshold',
			'lab_notification_email',
			'lab_report_signature',
		];

		foreach ($keys as $key) {
			$model->setValue($key, (string)($post[$key] ?? ''), 'lab');
		}

		return redirect()->to('/lab/settings')->with('success', 'Settings saved successfully.');
	}
}
