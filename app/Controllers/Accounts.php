<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\BillingModel;
use App\Models\BillItemModel;
use App\Models\PaymentModel;
use App\Models\InsuranceModel;
use App\Models\InsuranceProviderModel;
use App\Models\PatientModel;
use App\Models\AppointmentModel;
use App\Models\PrescriptionModel;
use App\Models\LabTestRequestModel;
use App\Models\SettingModel;

class Accounts extends Controller
{
	public function __construct()
	{
		$this->ensureBillingTables();
	}
	
	/**
	 * Check and update walk-in patients who have paid bills but are still active
	 * This handles cases where payments were made before the auto-update logic was added
	 */
	private function updateWalkInPatientsWithPaidBills()
	{
		try {
			$db = \Config\Database::connect();
			$patientModel = new PatientModel();
			$billingModel = new BillingModel();
			
			// Get all active outpatient patients
			$activeOutpatients = $patientModel->where('patient_type', 'outpatient')
				->where('status', 'active')
				->findAll();
			
			$today = date('Y-m-d');
			
			foreach ($activeOutpatients as $patient) {
				// First check if patient has any appointments at all
				// If no appointments, they are newly created → keep as ACTIVE
				$hasAnyAppointment = $db->table('appointments')
					->where('patient_id', $patient['id'])
					->where('status !=', 'cancelled')
					->countAllResults() > 0;
				
				if (!$hasAnyAppointment) {
					// Newly created patient with no appointments → keep ACTIVE
					continue;
				}
				
				// Check if patient has any future appointments (including follow-ups)
				$hasFutureAppointment = $db->table('appointments')
					->where('patient_id', $patient['id'])
					->where('appointment_date >=', $today)
					->where('status !=', 'cancelled')
					->countAllResults() > 0;
				
				// If patient has future appointments, keep them active
				if ($hasFutureAppointment) {
					continue;
				}
				
				// Check if patient has any paid bills
				$paidBills = $billingModel->where('patient_id', $patient['id'])
					->where('status', 'paid')
					->findAll();
				
				if (!empty($paidBills)) {
					// Check if patient is a walk-in (no consultation, no doctor, not admitted)
					$hasConsultation = $db->table('appointments')
						->where('patient_id', $patient['id'])
						->where('status !=', 'cancelled')
						->where('appointment_type', 'consultation')
						->countAllResults() > 0;
					
					$hasDoctor = $db->table('appointments')
						->where('patient_id', $patient['id'])
						->where('status !=', 'cancelled')
						->where('doctor_id IS NOT NULL', null, false)
						->countAllResults() > 0;
					
					$isAdmitted = $db->table('admissions')
						->where('patient_id', $patient['id'])
						->where('status', 'Admitted')
						->countAllResults() > 0;
					
					// If walk-in patient with paid bills and no future appointments, set to inactive
					if (!$hasConsultation && !$hasDoctor && !$isAdmitted) {
						$patientModel->update($patient['id'], [
							'status' => 'inactive',
							'updated_at' => date('Y-m-d H:i:s')
						]);
						log_message('info', "Updated walk-in patient #{$patient['id']} to inactive (has paid bills and no future appointments)");
					}
				}
			}
		} catch (\Exception $e) {
			// Silently fail - don't break the page if this check fails
			log_message('debug', 'Error updating walk-in patients: ' . $e->getMessage());
		}
	}

	// ---------- Helpers for Lab billing ----------
	private function ensureLabBillingColumns(): void
	{
		$db = \Config\Database::connect();
		$forge = \Config\Database::forge();
		foreach (['lab_test_results','lab_test_requests'] as $tbl) {
			if ($db->tableExists($tbl)) {
				try {
					$fields = $db->getFieldData($tbl);
					$has = false;
					foreach ($fields as $f) { if (strtolower($f->name) === 'billing_status') { $has = true; break; } }
					if (!$has) {
						$forge->addColumn($tbl, [
							'billing_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true]
						]);
						// Set all existing records to 'unbilled'
						$db->table($tbl)->set('billing_status','unbilled')->where('billing_status IS NULL')->update();
					} else {
						// Column exists - update any NULL or empty values to 'unbilled'
						$db->table($tbl)
							->set('billing_status', 'unbilled')
							->groupStart()
								->where('billing_status IS NULL')
								->orWhere('billing_status', '')
							->groupEnd()
							->update();
					}
				} catch (\Exception $e) {
					log_message('debug', 'ensureLabBillingColumns skip for '.$tbl.': '.$e->getMessage());
				}
			}
		}
	}
	
	private function guessLabPrice(string $name, array $defaults, float $fallback): float
	{
		$name = strtolower(trim($name));
		foreach ($defaults as $k => $v) {
			if (strpos($name, $k) !== false) return (float)$v;
		}
		return (float)$fallback;
	}

	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$billingModel = new BillingModel();
		$paymentModel = new PaymentModel();
		
		// Get today's stats
		$today = date('Y-m-d');
		$todayRevenue = $paymentModel->selectSum('amount')
			->where('payment_date', $today)
			->where('status', 'completed')
			->get()->getRowArray();
		
		$pendingBills = $billingModel->where('status', 'pending')->countAllResults();
		$overdueBills = $billingModel->where('status', 'overdue')->countAllResults();
		
		// Get recent bills
		$recentBills = $billingModel->getBillsWithPatient(['status' => 'pending']);
		$recentBills = array_slice($recentBills, 0, 5);
		
		// Get recent payments
		$recentPayments = $paymentModel->getPaymentsWithPatient();
		$recentPayments = array_slice($recentPayments, 0, 5);

		$data = [
			'title' => 'Accounts Dashboard - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
			'today_revenue' => $todayRevenue['amount'] ?? 0,
			'pending_bills_count' => $pendingBills,
			'overdue_bills_count' => $overdueBills,
			'recent_bills' => $recentBills,
			'recent_payments' => $recentPayments,
		];

		return view('auth/dashboard', $data);
	}

	public function billing()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		// Check and update walk-in patients with paid bills
		$this->updateWalkInPatientsWithPaidBills();

		$billingModel = new BillingModel();
		$patientModel = new PatientModel();
		$appointmentModel = new AppointmentModel();
		$prescriptionModel = new PrescriptionModel();
		$labModel = new LabTestRequestModel();
		
		// Get filters
		$filters = [
			'status' => $this->request->getGet('status'),
			'patient_id' => $this->request->getGet('patient_id'),
			'date_from' => $this->request->getGet('date_from'),
			'date_to' => $this->request->getGet('date_to'),
		];
		
		// Get all bills
		$bills = $billingModel->getBillsWithPatient($filters);
		
		// Get stats
		$totalRevenue = $billingModel->selectSum('paid_amount')->where('status', 'paid')->get()->getRowArray();
		$pendingAmount = $billingModel->selectSum('balance')->where('status', 'pending')->get()->getRowArray();
		$overdueAmount = $billingModel->selectSum('balance')->where('status', 'overdue')->get()->getRowArray();
		
		// Get patients for dropdown - filter out fully paid outpatients unless they have future follow-ups
		$db = \Config\Database::connect();
		$today = date('Y-m-d');
		
		$allPatients = $patientModel->select('id, full_name, patient_id, patient_type, status')->orderBy('full_name', 'ASC')->findAll();
		$patients = [];
		
		foreach ($allPatients as $patient) {
			$patientType = strtolower($patient['patient_type'] ?? 'outpatient');
			
			// Always include inpatients (they may have ongoing bills)
			if ($patientType === 'inpatient') {
				$patients[] = $patient;
				continue;
			}
			
            // For outpatients: check if they have future follow-up appointments
            $hasFutureFollowUp = $db->table('appointments')
				->where('patient_id', $patient['id'])
				->where('appointment_type', 'follow-up')
				->where('appointment_date >=', $today)
				->where('status !=', 'cancelled')
				->where('status !=', 'completed')
				->countAllResults() > 0;
			
			// If has future follow-up, always include
			if ($hasFutureFollowUp) {
				$patients[] = $patient;
				continue;
			}
			
			// Check if patient has any unpaid bills
			$hasUnpaidBills = $billingModel->where('patient_id', $patient['id'])
				->where('status !=', 'paid')
				->where('balance >', 0)
				->countAllResults() > 0;
			
			// Include if has unpaid bills
			if ($hasUnpaidBills) {
				$patients[] = $patient;
				continue;
			}

			// Check if patient has unbilled items (appointments, prescriptions, lab tests)
			// This includes completed walk-in lab tests that haven't been billed yet
			$hasUnbilledItems = false;
			
			// Check unbilled appointments
			$billedAppointmentIds = $db->table('bills')
				->select('appointment_id')
				->where('appointment_id IS NOT NULL')
				->get()->getResultArray();
			$billedAppointmentIds = array_column($billedAppointmentIds, 'appointment_id');
			
			$unbilledAppointments = $appointmentModel->where('patient_id', $patient['id'])
				->where('status', 'completed')
				->where('appointment_type !=', 'laboratory_test')
				->where('doctor_id IS NOT NULL', null, false);
			
			if (!empty($billedAppointmentIds)) {
				$unbilledAppointments->whereNotIn('id', $billedAppointmentIds);
			}
			
			if ($unbilledAppointments->countAllResults() > 0) {
				$hasUnbilledItems = true;
			}
			
			// Check unbilled prescriptions (only those with buy_from_hospital = true)
			if (!$hasUnbilledItems && $db->tableExists('prescriptions')) {
				$billedPrescriptionIds = $db->table('bills')
					->select('prescription_id')
					->where('prescription_id IS NOT NULL')
					->get()->getResultArray();
				$billedPrescriptionIds = array_column($billedPrescriptionIds, 'prescription_id');
				
				$unbilledPrescriptions = $prescriptionModel->where('patient_id', $patient['id'])
					->whereNotIn('status', ['cancelled']);
				
				if (!empty($billedPrescriptionIds)) {
					$unbilledPrescriptions->whereNotIn('id', $billedPrescriptionIds);
				}
				
				$prescriptions = $unbilledPrescriptions->findAll();
				foreach ($prescriptions as $prescription) {
					$itemsJson = $prescription['items_json'] ?? '[]';
					$items = json_decode($itemsJson, true);
					if (json_last_error() === JSON_ERROR_NONE && is_array($items)) {
						foreach ($items as $item) {
							if (isset($item['buy_from_hospital']) && (bool)$item['buy_from_hospital']) {
								$hasUnbilledItems = true;
								break 2;
							}
						}
					}
				}
			}
			
			// Check unbilled lab tests (including completed walk-in lab tests)
			if (!$hasUnbilledItems && $db->tableExists('lab_test_requests')) {
				// Get all lab test requests for this patient
				$allLabRequests = $db->table('lab_test_requests')
					->where('patient_id', $patient['id'])
					->get()->getResultArray();
				
				// Filter in PHP to find completed/unbilled requests
				$unbilledLabRequests = 0;
				foreach ($allLabRequests as $req) {
					$status = strtolower(trim($req['status'] ?? ''));
					$billingStatus = $req['billing_status'] ?? null;
					
					// Check if request is completed and unbilled
					$isCompleted = ($status === 'completed');
					
					// Also check if it has completed results
					if (!$isCompleted && $db->tableExists('lab_test_results')) {
						$hasCompletedResult = $db->table('lab_test_results')
							->where('request_id', $req['id'])
							->whereIn('status', ['completed', 'released', 'COMPLETED', 'RELEASED'])
							->countAllResults() > 0;
						$isCompleted = $hasCompletedResult;
					}
					
					// Check if unbilled
					$isUnbilled = ($billingStatus === null || $billingStatus === '' || $billingStatus === 'unbilled');
					
					if ($isCompleted && $isUnbilled) {
						$unbilledLabRequests++;
						break; // Found at least one, no need to continue
					}
				}
				
				if ($unbilledLabRequests > 0) {
					$hasUnbilledItems = true;
				}
			}
			
			// Include if has unbilled items
			if ($hasUnbilledItems) {
				$patients[] = $patient;
			}
			// Otherwise exclude (fully paid outpatient with no future follow-ups and no unbilled items)
		}
		
		// Get unbilled items
		$db = \Config\Database::connect();
		$billedAppointmentIds = $db->table('bills')
			->select('appointment_id')
			->where('appointment_id IS NOT NULL')
			->get()->getResultArray();
		$billedAppointmentIds = array_column($billedAppointmentIds, 'appointment_id');
		
		$unbilledAppointments = [];
		if (!empty($billedAppointmentIds)) {
			$unbilledAppointments = $appointmentModel->where('status', 'completed')
				->whereNotIn('id', $billedAppointmentIds)
				->findAll();
		} else {
			$unbilledAppointments = $appointmentModel->where('status', 'completed')->findAll();
		}
		
		$billedPrescriptionIds = $db->table('bills')
			->select('prescription_id')
			->where('prescription_id IS NOT NULL')
			->get()->getResultArray();
		$billedPrescriptionIds = array_column($billedPrescriptionIds, 'prescription_id');
		
		$unbilledPrescriptions = [];
		if (!empty($billedPrescriptionIds)) {
			$unbilledPrescriptions = $prescriptionModel->where('status', 'completed')
				->whereNotIn('id', $billedPrescriptionIds)
				->findAll();
		} else {
			$unbilledPrescriptions = $prescriptionModel->where('status', 'completed')->findAll();
		}

		$data = [
			'title' => 'Billing - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
			'bills' => $bills,
			'patients' => $patients,
			'total_revenue' => $totalRevenue['paid_amount'] ?? 0,
			'pending_amount' => $pendingAmount['balance'] ?? 0,
			'overdue_amount' => $overdueAmount['balance'] ?? 0,
			'unbilled_appointments' => $unbilledAppointments,
			'unbilled_prescriptions' => $unbilledPrescriptions,
			'filters' => $filters,
		];

		return view('accounts/billing', $data);
	}
	
	public function createBill()
	{
		try {
			if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
				return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
			}
			
			$billingModel = new BillingModel();
			$billItemModel = new BillItemModel();
			
			$postData = $this->request->getJSON(true);
			
			if (!$postData) {
				return $this->response->setJSON(['success' => false, 'message' => 'Invalid request data']);
			}
			
			// Validate required fields
			if (empty($postData['patient_id']) || empty($postData['items']) || !is_array($postData['items']) || count($postData['items']) === 0) {
				return $this->response->setJSON(['success' => false, 'message' => 'Patient and at least one item are required']);
			}
			
			// Generate bill number
			$billNumber = $billingModel->generateBillNumber();
			
			// Calculate totals
			$subtotal = 0;
			$items = $postData['items'] ?? [];
			foreach ($items as $item) {
				if (empty($item['item_name']) || empty($item['quantity']) || empty($item['unit_price'])) {
					continue; // Skip invalid items
				}
				$subtotal += (floatval($item['quantity']) * floatval($item['unit_price']));
			}
			
			if ($subtotal <= 0) {
				return $this->response->setJSON(['success' => false, 'message' => 'Bill total must be greater than zero']);
			}
			
			// Calculate discount based on patient's insurance provider
			$discount = floatval($postData['discount'] ?? 0);
			
			// Check patient type - outpatients should not have insurance deductions
			$patientModel = new PatientModel();
			$patient = $patientModel->find($postData['patient_id']);
			$isOutpatient = $patient && isset($patient['patient_type']) && strtolower($patient['patient_type']) === 'outpatient';
			
			// If discount is 0 and patient is not outpatient, check if patient has insurance
			if ($discount == 0 && !$isOutpatient) {
				$insuranceModel = new InsuranceModel();
				// Get patient's most recent insurance claim
				$insuranceClaim = $insuranceModel->where('patient_id', $postData['patient_id'])
					->whereIn('status', ['pending', 'submitted', 'approved', 'paid'])
					->orderBy('created_at', 'DESC')
					->first();
				
				if ($insuranceClaim && !empty($insuranceClaim['insurance_provider'])) {
					$provider = $insuranceClaim['insurance_provider'];
					
					// Get coverage from insurance_providers table
					$insuranceProviderModel = new InsuranceProviderModel();
					$coverage = $insuranceProviderModel->getCoverageByName($provider);
					
					// If exact match not found, try partial match
					if (!$coverage) {
						$allProviders = $insuranceProviderModel->getActiveProviders();
						$providerLower = strtolower(trim($provider));
						
						foreach ($allProviders as $prov) {
							$provNameLower = strtolower(trim($prov['name']));
							if ($providerLower === $provNameLower || 
								strpos($providerLower, $provNameLower) !== false || 
								strpos($provNameLower, $providerLower) !== false) {
								$coverage = [
									'room' => floatval($prov['coverage_room'] ?? 0),
									'laboratory' => floatval($prov['coverage_lab'] ?? 0),
									'medication' => floatval($prov['coverage_meds'] ?? 0),
									'professional' => floatval($prov['coverage_pf'] ?? 0),
									'procedure' => floatval($prov['coverage_procedure'] ?? 0),
								];
								break;
							}
						}
					}
					
					// Calculate discount based on category-based coverage
					if ($coverage) {
						// Calculate discount per item category
						$totalDiscount = 0;
						foreach ($items as $item) {
							if (empty($item['item_name']) || empty($item['quantity']) || empty($item['unit_price'])) {
								continue;
							}
							
							$itemCategory = strtolower($item['category'] ?? 'other');
							$itemAmount = floatval($item['quantity']) * floatval($item['unit_price']);
							$itemDiscount = 0;
							
							// Map category to coverage key
							$coverageKey = 'professional'; // default
							if ($itemCategory === 'room' || $itemCategory === 'room/bed') {
								$coverageKey = 'room';
							} elseif ($itemCategory === 'lab' || $itemCategory === 'laboratory') {
								$coverageKey = 'laboratory';
							} elseif ($itemCategory === 'medication' || $itemCategory === 'meds') {
								$coverageKey = 'medication';
							} elseif ($itemCategory === 'professional' || $itemCategory === 'pf') {
								$coverageKey = 'professional';
							} elseif ($itemCategory === 'procedure' || $itemCategory === 'ot') {
								$coverageKey = 'procedure';
							}
							
							$coveragePercent = $coverage[$coverageKey] ?? 0;
							$itemDiscount = $itemAmount * ($coveragePercent / 100);
							$totalDiscount += $itemDiscount;
						}
						
						$discount = $totalDiscount;
					}
				}
			}
			
			// Ensure discount doesn't exceed subtotal
			$discount = min($discount, $subtotal);
			
			// No tax - total is subtotal minus discount
			$tax = 0;
			$totalAmount = $subtotal - $discount;
			
			// Create bill
			$billData = [
				'bill_number' => $billNumber,
				'patient_id' => $postData['patient_id'],
				'appointment_id' => $postData['appointment_id'] ?? null,
				'prescription_id' => $postData['prescription_id'] ?? null,
				'lab_test_id' => $postData['lab_test_id'] ?? null,
				'room_id' => $postData['room_id'] ?? null,
				'bill_type' => $postData['bill_type'] ?? 'other',
				'subtotal' => $subtotal,
				'discount' => $discount,
				'tax' => $tax,
				'total_amount' => $totalAmount,
				'paid_amount' => 0,
				'balance' => $totalAmount,
				'status' => 'pending',
				'due_date' => $postData['due_date'] ?? date('Y-m-d', strtotime('+7 days')),
				'payment_method' => $postData['payment_method'] ?? null,
				'notes' => $postData['notes'] ?? null,
				'created_by' => session()->get('user_id'),
			];
			
			$billId = $billingModel->insert($billData);
			
			if (!$billId) {
				$errors = $billingModel->errors();
				return $this->response->setJSON(['success' => false, 'message' => 'Failed to create bill: ' . (is_array($errors) ? implode(', ', $errors) : 'Unknown error')]);
			}
			
			// Get insurance coverage for item-level tracking (only for inpatients)
			$insuranceCoverage = null;
			if ($discount > 0 && !$isOutpatient) {
				$insuranceModel = new InsuranceModel();
				$insuranceClaim = $insuranceModel->where('patient_id', $postData['patient_id'])
					->whereIn('status', ['pending', 'submitted', 'approved', 'paid'])
					->orderBy('created_at', 'DESC')
					->first();
				
				if ($insuranceClaim && !empty($insuranceClaim['insurance_provider'])) {
					$provider = $insuranceClaim['insurance_provider'];
					$insuranceProviderModel = new InsuranceProviderModel();
					$insuranceCoverage = $insuranceProviderModel->getCoverageByName($provider);
					
					if (!$insuranceCoverage) {
						$allProviders = $insuranceProviderModel->getActiveProviders();
						$providerLower = strtolower(trim($provider));
						foreach ($allProviders as $prov) {
							$provNameLower = strtolower(trim($prov['name']));
							if ($providerLower === $provNameLower || 
								strpos($providerLower, $provNameLower) !== false || 
								strpos($provNameLower, $providerLower) !== false) {
								$insuranceCoverage = [
									'room' => floatval($prov['coverage_room'] ?? 0),
									'laboratory' => floatval($prov['coverage_lab'] ?? 0),
									'medication' => floatval($prov['coverage_meds'] ?? 0),
									'professional' => floatval($prov['coverage_pf'] ?? 0),
									'procedure' => floatval($prov['coverage_procedure'] ?? 0),
								];
								break;
							}
						}
					}
				}
			}
			
			// Create bill items
			$itemErrors = [];
			$labResultIdsToBill = [];
			$labRequestIdsToBill = [];
			foreach ($items as $item) {
				if (empty($item['item_name']) || empty($item['quantity']) || empty($item['unit_price'])) {
					continue; // Skip invalid items
				}
				
				// Determine reference_id from item or use provided values
				$referenceId = $item['reference_id'] ?? null;
				if (!$referenceId) {
					if (isset($item['reference_type']) && $item['reference_type'] === 'appointment') {
						$referenceId = $appointmentId;
					} elseif (isset($item['reference_type']) && $item['reference_type'] === 'prescription') {
						$referenceId = $prescriptionId;
					} else {
						$referenceId = $postData['prescription_id'] ?? $postData['appointment_id'] ?? null;
					}
				}
				
				// Calculate insurance coverage per item (only for inpatients)
				$itemCategory = strtolower($item['category'] ?? 'other');
				$itemAmount = floatval($item['quantity']) * floatval($item['unit_price']);
				$insuranceCoveragePercent = 0;
				$insuranceDiscountAmount = 0;
				$patientPaysAmount = $itemAmount;
				
				if ($insuranceCoverage && !$isOutpatient) {
					// Map category to coverage key
					$coverageKey = 'professional'; // default
					if ($itemCategory === 'room' || $itemCategory === 'room/bed') {
						$coverageKey = 'room';
					} elseif ($itemCategory === 'lab' || $itemCategory === 'laboratory') {
						$coverageKey = 'laboratory';
					} elseif ($itemCategory === 'medication' || $itemCategory === 'meds') {
						$coverageKey = 'medication';
					} elseif ($itemCategory === 'professional' || $itemCategory === 'pf' || $itemCategory === 'nursing') {
						$coverageKey = 'professional';
					} elseif ($itemCategory === 'procedure' || $itemCategory === 'ot') {
						$coverageKey = 'procedure';
					}
					
					$insuranceCoveragePercent = $insuranceCoverage[$coverageKey] ?? 0;
					$insuranceDiscountAmount = $itemAmount * ($insuranceCoveragePercent / 100);
					$patientPaysAmount = $itemAmount - $insuranceDiscountAmount;
				}
				
				$itemData = [
					'bill_id' => $billId,
					'item_type' => $item['item_type'] ?? $item['category'] ?? 'service',
					'item_name' => $item['item_name'],
					'description' => $item['description'] ?? '',
					'quantity' => floatval($item['quantity']),
					'unit_price' => floatval($item['unit_price']),
					'total_price' => floatval($item['quantity']) * floatval($item['unit_price']),
					'reference_id' => $referenceId,
					'category' => $itemCategory,
					'insurance_coverage_percent' => $insuranceCoveragePercent,
					'insurance_discount_amount' => $insuranceDiscountAmount,
					'patient_pays_amount' => $patientPaysAmount,
				];
				
				if (!$billItemModel->insert($itemData)) {
					$itemErrors[] = $item['item_name'];
				}
				
				// Track lab items to mark as billed
				if (!empty($item['reference_type']) && !empty($item['reference_id'])) {
					if ($item['reference_type'] === 'lab_result') {
						$labResultIdsToBill[] = (int)$item['reference_id'];
					} elseif ($item['reference_type'] === 'lab_request') {
						$labRequestIdsToBill[] = (int)$item['reference_id'];
					}
				}
			}
			
			if (!empty($itemErrors)) {
				log_message('warning', 'Some bill items failed to create: ' . implode(', ', $itemErrors));
			}
			
			// Mark lab rows as billed
			if (!empty($labResultIdsToBill) || !empty($labRequestIdsToBill)) {
				$this->ensureLabBillingColumns();
				$db = \Config\Database::connect();
				if (!empty($labResultIdsToBill) && $db->tableExists('lab_test_results')) {
					try {
						$db->table('lab_test_results')->whereIn('id', $labResultIdsToBill)->update(['billing_status' => 'billed']);
					} catch (\Exception $e) {
						log_message('warning', 'Failed to mark lab_test_results billed: ' . $e->getMessage());
					}
				}
				if (!empty($labRequestIdsToBill) && $db->tableExists('lab_test_requests')) {
					try {
						$db->table('lab_test_requests')->whereIn('id', $labRequestIdsToBill)->update(['billing_status' => 'billed']);
					} catch (\Exception $e) {
						log_message('warning', 'Failed to mark lab_test_requests billed: ' . $e->getMessage());
					}
				}
			}
			
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Bill created successfully',
				'bill_id' => $billId,
				'bill_number' => $billNumber
			]);
			
		} catch (\Exception $e) {
			log_message('error', 'Error creating bill: ' . $e->getMessage());
			return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
		}
	}
	
	public function recordPayment()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
		}
		
		try {
			$paymentModel = new PaymentModel();
			$billingModel = new BillingModel();
			$insuranceModel = new InsuranceModel();
			
			$postData = $this->request->getJSON(true);
			
			// Validate required fields
			if (!$postData) {
				return $this->response->setJSON(['success' => false, 'message' => 'Invalid request data']);
			}
			
			if (empty($postData['bill_id'])) {
				return $this->response->setJSON(['success' => false, 'message' => 'Bill ID is required']);
			}
			
			if (empty($postData['amount']) || floatval($postData['amount']) <= 0) {
				return $this->response->setJSON(['success' => false, 'message' => 'Payment amount must be greater than zero']);
			}
			
			if (empty($postData['payment_method'])) {
				return $this->response->setJSON(['success' => false, 'message' => 'Payment method is required']);
			}
			
			$billId = (int)$postData['bill_id'];
			$amount = floatval($postData['amount']);
			$paymentMethod = $postData['payment_method'];
			
			$bill = $billingModel->find($billId);
			if (!$bill) {
				return $this->response->setJSON(['success' => false, 'message' => 'Bill not found']);
			}
			
			// Check if bill balance is already zero
			$currentBalance = floatval($bill['balance'] ?? 0);
			if ($currentBalance <= 0) {
				return $this->response->setJSON([
					'success' => false, 
					'message' => 'This bill is already fully paid. Cannot record additional payment.'
				]);
			}
			
			// Check if there's already an insurance payment for this bill
			if ($paymentMethod === 'cash' || $paymentMethod === 'credit_card' || $paymentMethod === 'debit_card') {
				$existingInsurancePayment = $paymentModel->where('bill_id', $billId)
					->where('payment_method', 'insurance')
					->where('status', 'completed')
					->first();
				
				if ($existingInsurancePayment) {
					// Check if there's an approved insurance claim
					$approvedClaim = $insuranceModel->where('bill_id', $billId)
						->where('status', 'approved')
						->first();
					
					if ($approvedClaim) {
						$insuranceAmount = floatval($existingInsurancePayment['amount']);
						$remainingBalance = floatval($bill['total_amount'] ?? 0) - floatval($bill['paid_amount'] ?? 0);
						
						// If insurance already paid the full amount or more, don't allow cash payment
						if ($insuranceAmount >= floatval($bill['total_amount'] ?? 0)) {
							return $this->response->setJSON([
								'success' => false, 
								'message' => 'This bill is already fully paid by insurance. Cannot record additional cash payment.'
							]);
						}
						
						// If trying to pay more than remaining balance after insurance
						if ($amount > $remainingBalance) {
							return $this->response->setJSON([
								'success' => false, 
								'message' => 'Payment amount exceeds remaining balance. Remaining balance after insurance: ₱' . number_format($remainingBalance, 2)
							]);
						}
					}
				}
			}
			
			// Validate amount doesn't exceed balance
			if ($amount > $currentBalance) {
				return $this->response->setJSON([
					'success' => false, 
					'message' => 'Payment amount cannot exceed bill balance. Current balance: ₱' . number_format($currentBalance, 2)
				]);
			}
			
			if ($amount > $currentBalance) {
				return $this->response->setJSON([
					'success' => false, 
					'message' => 'Payment amount cannot exceed bill balance. Current balance: ₱' . number_format($currentBalance, 2)
				]);
			}
			
			// Generate payment number
			$paymentNumber = $paymentModel->generatePaymentNumber();
			if (empty($paymentNumber)) {
				return $this->response->setJSON(['success' => false, 'message' => 'Failed to generate payment number']);
			}
			
			// Create payment
			$paymentData = [
				'bill_id' => $billId,
				'patient_id' => $bill['patient_id'] ?? null,
				'payment_number' => $paymentNumber,
				'amount' => $amount,
				'payment_method' => $paymentMethod,
				'payment_date' => $postData['payment_date'] ?? date('Y-m-d'),
				'transaction_id' => $postData['transaction_id'] ?? null,
				'reference_number' => $postData['reference_number'] ?? null,
				'notes' => $postData['notes'] ?? null,
				'status' => 'completed',
				'processed_by' => session()->get('user_id'),
			];
			
			$paymentId = $paymentModel->insert($paymentData);
			
			if (!$paymentId) {
				$errors = $paymentModel->errors();
				return $this->response->setJSON([
					'success' => false, 
					'message' => 'Failed to create payment record. ' . (is_array($errors) ? implode(', ', $errors) : 'Database error')
				]);
			}
			
			// Update bill
			$newPaidAmount = floatval($bill['paid_amount'] ?? 0) + $amount;
			$newBalance = floatval($bill['total_amount'] ?? 0) - $newPaidAmount;
			$newStatus = $newBalance <= 0 ? 'paid' : ($newBalance < floatval($bill['total_amount'] ?? 0) ? 'partial' : 'pending');
			
			$billingModel->update($billId, [
				'paid_amount' => $newPaidAmount,
				'balance' => max(0, $newBalance),
				'status' => $newStatus,
			]);
			
			// Get updated bill data
			$updatedBill = $billingModel->find($billId);
			
			// If bill is fully paid, update related records
			if ($newStatus === 'paid' && $updatedBill && $updatedBill['patient_id']) {
				$db = \Config\Database::connect();
				$appointmentModel = new \App\Models\AppointmentModel();
				$patientModel = new \App\Models\PatientModel();
				
				// Auto-complete appointment if bill is linked to an appointment
				if (!empty($updatedBill['appointment_id'])) {
					$appointment = $appointmentModel->find($updatedBill['appointment_id']);
					
					if ($appointment) {
						$appointmentDate = $appointment['appointment_date'] ?? null;
						$appointmentType = strtolower($appointment['appointment_type'] ?? '');
						$today = date('Y-m-d');
						
						// Don't auto-complete future follow-up appointments
						if ($appointmentType === 'follow-up' && $appointmentDate && $appointmentDate > $today) {
							log_message('info', "Skipping auto-complete for future follow-up appointment #{$updatedBill['appointment_id']} (date: {$appointmentDate})");
						} elseif ($appointment['status'] !== 'completed' && (!$appointmentDate || $appointmentDate <= $today)) {
							// For follow-up appointments, check if prescription exists before completing
							if ($appointmentType === 'follow-up') {
								// Check if there's a prescription for this follow-up appointment
								$prescription = $db->table('prescriptions')
									->where('appointment_id', $updatedBill['appointment_id'])
									->where('status !=', 'cancelled')
									->get()
									->getRowArray();
								
								if (!$prescription) {
									log_message('info', "Skipping auto-complete for follow-up appointment #{$updatedBill['appointment_id']} - no prescription found. Doctor must provide prescription first.");
									// Don't complete - prescription is required for follow-up appointments
								} else {
									// Prescription exists and payment is made - can complete
									$appointmentModel->update($updatedBill['appointment_id'], [
										'status' => 'completed',
										'updated_at' => date('Y-m-d H:i:s')
									]);
									log_message('info', "Auto-completed follow-up appointment #{$updatedBill['appointment_id']} after prescription provided and bill #{$billId} was fully paid");
								}
							} else {
								// For non-follow-up appointments, complete as before
								$appointmentModel->update($updatedBill['appointment_id'], [
									'status' => 'completed',
									'updated_at' => date('Y-m-d H:i:s')
								]);
								log_message('info', "Auto-completed appointment #{$updatedBill['appointment_id']} after bill #{$billId} was fully paid");
							}
						}
					}
				} else {
					// If bill doesn't have appointment_id, try to find appointment by patient_id and date
					// Check if there are any appointments for this patient on the bill date or recent dates
					$billDate = $updatedBill['created_at'] ?? date('Y-m-d');
					$billDateFormatted = date('Y-m-d', strtotime($billDate));
					$today = date('Y-m-d');
					
					// Find appointments for this patient within 7 days of bill creation
					// Exclude future follow-up appointments
					$startDate = date('Y-m-d', strtotime($billDateFormatted . ' -7 days'));
					$endDate = date('Y-m-d', strtotime($billDateFormatted . ' +1 day'));
					
					$appointments = $db->table('appointments')
						->where('patient_id', $updatedBill['patient_id'])
						->where('appointment_date >=', $startDate)
						->where('appointment_date <=', $endDate)
						->where('appointment_date <=', $today) // Only appointments on or before today
						->where('status !=', 'completed')
						->where('status !=', 'cancelled')
						->where("(appointment_type != 'follow-up' OR appointment_date <= '{$today}')", null, false) // Exclude future follow-ups
						->orderBy('appointment_date', 'DESC')
						->orderBy('appointment_time', 'DESC')
						->get()
						->getResultArray();
					
					// Update the most recent appointment that matches
					if (!empty($appointments)) {
						$appointmentToUpdate = $appointments[0]; // Most recent
						$appointmentTypeToUpdate = strtolower($appointmentToUpdate['appointment_type'] ?? '');
						
						// For follow-up appointments, check if prescription exists before completing
						if ($appointmentTypeToUpdate === 'follow-up') {
							// Check if there's a prescription for this follow-up appointment
							$prescription = $db->table('prescriptions')
								->where('appointment_id', $appointmentToUpdate['id'])
								->where('status !=', 'cancelled')
								->get()
								->getRowArray();
							
							if (!$prescription) {
								log_message('info', "Skipping auto-complete for follow-up appointment #{$appointmentToUpdate['id']} (matched by patient_id and date) - no prescription found. Doctor must provide prescription first.");
								// Don't complete - prescription is required for follow-up appointments
							} else {
								// Prescription exists and payment is made - can complete
								$appointmentModel->update($appointmentToUpdate['id'], [
									'status' => 'completed',
									'updated_at' => date('Y-m-d H:i:s')
								]);
								log_message('info', "Auto-completed follow-up appointment #{$appointmentToUpdate['id']} (matched by patient_id and date) after prescription provided and bill #{$billId} was fully paid");
								
								// Also update the bill to link it to this appointment for future reference
								$billingModel->update($billId, [
									'appointment_id' => $appointmentToUpdate['id']
								]);
							}
						} else {
							// For non-follow-up appointments, complete as before
							$appointmentModel->update($appointmentToUpdate['id'], [
								'status' => 'completed',
								'updated_at' => date('Y-m-d H:i:s')
							]);
							log_message('info', "Auto-completed appointment #{$appointmentToUpdate['id']} (matched by patient_id and date) after bill #{$billId} was fully paid");
							
							// Also update the bill to link it to this appointment for future reference
							$billingModel->update($billId, [
								'appointment_id' => $appointmentToUpdate['id']
							]);
						}
					}
				}
				
				// Check if patient should be set to inactive
				$patient = $patientModel->find($updatedBill['patient_id']);
				if ($patient) {
					// Check if this bill is for a completed follow-up appointment
					$isFollowUpBill = false;
					$completedFollowUpId = null;
					
					if (!empty($updatedBill['appointment_id'])) {
						$appointmentForBill = $appointmentModel->find($updatedBill['appointment_id']);
						if ($appointmentForBill && strtolower($appointmentForBill['appointment_type'] ?? '') === 'follow-up' && $appointmentForBill['status'] === 'completed') {
							$isFollowUpBill = true;
							$completedFollowUpId = $updatedBill['appointment_id'];
						}
					}
					
					// If this is a follow-up bill that was just paid and the appointment is completed
					if ($isFollowUpBill && $completedFollowUpId) {
						// Check if there are any other pending follow-up appointments
						$hasPendingFollowUps = $db->table('appointments')
							->where('patient_id', $updatedBill['patient_id'])
							->where('appointment_type', 'follow-up')
							->where('status !=', 'completed')
							->where('status !=', 'cancelled')
							->where('id !=', $completedFollowUpId)
							->countAllResults() > 0;
						
						// If no pending follow-ups, set patient to inactive
						if (!$hasPendingFollowUps) {
							$patientModel->update($updatedBill['patient_id'], [
								'status' => 'inactive',
								'updated_at' => date('Y-m-d H:i:s')
							]);
							log_message('info', "Set patient #{$updatedBill['patient_id']} to inactive after follow-up appointment #{$completedFollowUpId} was completed and bill #{$billId} was fully paid");
						}
					} else {
						// Original logic for walk-in patients
						// Check if patient has any consultation appointments
						$hasConsultation = $db->table('appointments')
							->where('patient_id', $updatedBill['patient_id'])
							->where('status !=', 'cancelled')
							->where('appointment_type', 'consultation')
							->countAllResults() > 0;
						
						// Check if patient has any doctor assignments
						$hasDoctor = $db->table('appointments')
							->where('patient_id', $updatedBill['patient_id'])
							->where('status !=', 'cancelled')
							->where('doctor_id IS NOT NULL', null, false)
							->countAllResults() > 0;
						
						// Check if patient is admitted
						$isAdmitted = $db->table('admissions')
							->where('patient_id', $updatedBill['patient_id'])
							->where('status', 'Admitted')
							->countAllResults() > 0;
						
						// If walk-in patient (no consultation, no doctor, not admitted) and bill is paid, set to inactive
						if (!$hasConsultation && !$hasDoctor && !$isAdmitted && strtolower($patient['patient_type'] ?? '') === 'outpatient') {
							$patientModel->update($updatedBill['patient_id'], [
								'status' => 'inactive',
								'updated_at' => date('Y-m-d H:i:s')
							]);
							log_message('info', "Set walk-in patient #{$updatedBill['patient_id']} to inactive after bill #{$billId} was fully paid");
						}
					}
				}
				
				// Update admission billing_cleared for inpatients
				$admission = $db->table('admissions')
					->where('patient_id', $updatedBill['patient_id'])
					->orderBy('id', 'DESC')
					->get()->getRowArray();
				
				if ($admission) {
					$db->table('admissions')
						->where('id', $admission['id'])
						->update(['billing_cleared' => 1]);
				}
				
				// Update lab test request status to completed if bill is linked to a lab test
				$labTestRequestModel = new LabTestRequestModel();
				$labRequestIdsToComplete = [];
				
				// Check if bill has lab_test_id directly
				if (!empty($updatedBill['lab_test_id']) && $db->tableExists('lab_test_requests')) {
					$labRequestIdsToComplete[] = (int)$updatedBill['lab_test_id'];
				}
				
				// Also check bill_items for lab test requests
				if ($db->tableExists('bill_items')) {
					// Check if reference_type column exists
					$columns = $db->getFieldNames('bill_items');
					$hasReferenceType = in_array('reference_type', $columns);
					
					$builder = $db->table('bill_items')
						->where('bill_id', $billId)
						->where('reference_id IS NOT NULL', null, false);
					
					// Only filter by reference_type if the column exists
					if ($hasReferenceType) {
						$builder->where('reference_type', 'lab_request');
					} else {
						// Fallback: filter by item_type or item_name containing lab-related keywords
						$builder->groupStart()
							->like('item_type', 'lab', 'both')
							->orLike('item_name', 'lab', 'both')
							->orLike('item_name', 'test', 'both')
							->orLike('item_name', 'urine', 'both')
							->orLike('item_name', 'blood', 'both')
							->groupEnd();
					}
					
					$billItems = $builder->get()->getResultArray();
					
					foreach ($billItems as $item) {
						if (!empty($item['reference_id'])) {
							$labRequestIdsToComplete[] = (int)$item['reference_id'];
						}
					}
					
					// Fallback: Check bill_items by item_name containing lab test names
					// This handles cases where reference_type might not be set correctly
					if (empty($labRequestIdsToComplete)) {
						$allBillItems = $db->table('bill_items')
							->where('bill_id', $billId)
							->get()
							->getResultArray();
						
						foreach ($allBillItems as $item) {
							$itemName = strtolower($item['item_name'] ?? '');
							// If item name looks like a lab test, try to find matching lab test request
							if (strpos($itemName, 'urine') !== false || 
								strpos($itemName, 'blood') !== false || 
								strpos($itemName, 'culture') !== false ||
								strpos($itemName, 'x-ray') !== false ||
								strpos($itemName, 'test') !== false) {
								
								// Find lab test request by patient_id and matching test_type
								$matchingRequest = $labTestRequestModel
									->where('patient_id', $updatedBill['patient_id'])
									->where('status !=', 'completed')
									->like('test_type', $item['item_name'])
									->orderBy('requested_at', 'DESC')
									->first();
								
								if ($matchingRequest) {
									$labRequestIdsToComplete[] = (int)$matchingRequest['id'];
								}
							}
						}
					}
				}
				
				// Final fallback: Find any incomplete lab test requests for this patient created around the bill date
				if (empty($labRequestIdsToComplete) && $db->tableExists('lab_test_requests')) {
					$billDate = $updatedBill['created_at'] ?? date('Y-m-d H:i:s');
					$startDate = date('Y-m-d H:i:s', strtotime($billDate . ' -1 day'));
					$endDate = date('Y-m-d H:i:s', strtotime($billDate . ' +1 day'));
					
					$matchingRequests = $db->table('lab_test_requests')
						->where('patient_id', $updatedBill['patient_id'])
						->where('status !=', 'completed')
						->where('requested_at >=', $startDate)
						->where('requested_at <=', $endDate)
						->orderBy('requested_at', 'DESC')
						->get()
						->getResultArray();
					
					foreach ($matchingRequests as $req) {
						$labRequestIdsToComplete[] = (int)$req['id'];
					}
				}
				
				// Update all lab test requests to completed
				$labRequestIdsToComplete = array_unique($labRequestIdsToComplete);
				foreach ($labRequestIdsToComplete as $labRequestId) {
					$labTestRequest = $labTestRequestModel->find($labRequestId);
					
					if ($labTestRequest && $labTestRequest['status'] !== 'completed') {
						$labTestRequestModel->update($labRequestId, [
							'status' => 'completed',
							'updated_at' => date('Y-m-d H:i:s')
						]);
						log_message('info', "Auto-completed lab test request #{$labRequestId} after bill #{$billId} was fully paid");
					}
				}
				
				// Update insurance claim status to paid
				$insuranceClaim = $db->table('insurance_claims')
					->where('patient_id', $updatedBill['patient_id'])
					->orderBy('id', 'DESC')
					->get()->getRowArray();
				
				if ($insuranceClaim) {
					$db->table('insurance_claims')
						->where('id', $insuranceClaim['id'])
						->update([
							'bill_id' => $billId,
							'claim_amount' => $updatedBill['total_amount'],
							'approved_amount' => $updatedBill['total_amount'],
							'status' => 'paid',
							'approved_date' => date('Y-m-d H:i:s'),
							'updated_at' => date('Y-m-d H:i:s')
						]);
				}
			}
			
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Payment recorded successfully',
				'payment_id' => $paymentId,
				'payment_number' => $paymentNumber
			]);
		} catch (\Exception $e) {
			log_message('error', 'Error recording payment: ' . $e->getMessage());
			log_message('error', 'Stack trace: ' . $e->getTraceAsString());
			return $this->response->setJSON([
				'success' => false, 
				'message' => 'Error recording payment: ' . $e->getMessage()
			])->setStatusCode(500);
		}
		
		return $this->response->setJSON(['success' => false, 'message' => 'Failed to record payment']);
	}
	
	public function voidPayment()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
		}
		
		try {
			$paymentModel = new PaymentModel();
			$billingModel = new BillingModel();
			
			$postData = $this->request->getJSON(true);
			$paymentId = $postData['payment_id'] ?? null;
			
			if (!$paymentId) {
				return $this->response->setJSON(['success' => false, 'message' => 'Payment ID is required']);
			}
			
			$payment = $paymentModel->find($paymentId);
			if (!$payment) {
				return $this->response->setJSON(['success' => false, 'message' => 'Payment not found']);
			}
			
			if ($payment['status'] !== 'completed') {
				return $this->response->setJSON(['success' => false, 'message' => 'Only completed payments can be voided']);
			}
			
			$bill = $billingModel->find($payment['bill_id']);
			if (!$bill) {
				return $this->response->setJSON(['success' => false, 'message' => 'Bill not found']);
			}
			
			// Update payment status to refunded
			$paymentModel->update($paymentId, [
				'status' => 'refunded',
				'notes' => ($payment['notes'] ?? '') . ' [VOIDED]'
			]);
			
			// Recalculate bill paid amount and balance
			$db = \Config\Database::connect();
			$allPayments = $db->table('payments')
				->where('bill_id', $payment['bill_id'])
				->where('status', 'completed')
				->selectSum('amount')
				->get()
				->getRowArray();
			
			$newPaidAmount = floatval($allPayments['amount'] ?? 0);
			$newBalance = $bill['total_amount'] - $newPaidAmount;
			$newStatus = $newBalance <= 0 ? 'paid' : ($newBalance < $bill['total_amount'] ? 'partial' : 'pending');
			
			$billingModel->update($payment['bill_id'], [
				'paid_amount' => $newPaidAmount,
				'balance' => max(0, $newBalance),
				'status' => $newStatus,
			]);
			
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Payment voided successfully'
			]);
			
		} catch (\Exception $e) {
			log_message('error', 'Error voiding payment: ' . $e->getMessage());
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Error: ' . $e->getMessage()
			]);
		}
	}
	
	public function getBillDetails($billId)
	{
		$userRole = session()->get('role');
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['accountant', 'admin'])) {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
		}
		
		$billingModel = new BillingModel();
		$bill = $billingModel->getBillWithItems($billId);
		
		if ($bill) {
			return $this->response->setJSON(['success' => true, 'bill' => $bill]);
		}
		
		return $this->response->setJSON(['success' => false, 'message' => 'Bill not found']);
	}
	
	public function getPatientInsuranceDiscount($patientId)
	{
		$userRole = session()->get('role');
		if (!session()->get('isLoggedIn') || !in_array($userRole, ['accountant', 'admin'])) {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
		}
		
		try {
			// Check patient type - outpatients should not have insurance
			$patientModel = new PatientModel();
			$patient = $patientModel->find($patientId);
			
			if ($patient && isset($patient['patient_type']) && strtolower($patient['patient_type']) === 'outpatient') {
				// Outpatients don't have insurance coverage
				return $this->response->setJSON([
					'success' => true,
					'has_insurance' => false,
					'has_philhealth' => false,
					'discount_percentage' => 0,
					'copay_percentage' => 100,
					'provider' => null,
					'policy_number' => null,
					'member_id' => null,
					'coverage' => null,
					'debug' => [
						'insurance_claim_found' => false,
						'provider_name' => null,
						'discount_calculated' => 0,
						'coverage_found' => false,
						'reason' => 'Outpatient - insurance not applicable'
					]
				]);
			}
			
			$insuranceModel = new InsuranceModel();
			
			// Get patient's most recent insurance claim (any record with valid provider, regardless of bill_id or status)
			$insuranceClaim = $insuranceModel->where('patient_id', $patientId)
				->where('insurance_provider IS NOT NULL')
				->where('insurance_provider !=', '')
				->where('insurance_provider !=', 'None')
				->where('insurance_provider !=', 'none')
				->where('insurance_provider !=', 'None / Self-Pay')
				->orderBy('created_at', 'DESC')
				->first();
			
			// Check all insurance records for this patient for debugging
			$allInsuranceRecords = $insuranceModel->where('patient_id', $patientId)->findAll();
			log_message('debug', 'All insurance records for patient ' . $patientId . ': ' . json_encode($allInsuranceRecords));
			
			$hasInsurance = !empty($insuranceClaim) && !empty($insuranceClaim['insurance_provider']);
			$provider = $hasInsurance ? $insuranceClaim['insurance_provider'] : null;
			
			// Log for debugging
			log_message('debug', 'Insurance lookup for patient ' . $patientId . ': hasInsurance=' . ($hasInsurance ? 'true' : 'false') . ', provider=' . ($provider ?? 'null'));
			if ($insuranceClaim) {
				log_message('debug', 'Insurance claim found: ' . json_encode($insuranceClaim));
			} else {
				log_message('warning', 'No insurance claim found for patient ' . $patientId);
			}
			
			// Get coverage from insurance_providers table
			$coverage = null;
			$discountPercentage = 0;
			$coPaymentPercentage = 100;
			
			if ($hasInsurance && $provider) {
				$insuranceProviderModel = new InsuranceProviderModel();
			
				// Try to get coverage by provider name
				$coverage = $insuranceProviderModel->getCoverageByName($provider);
				
				// If exact match not found, try partial match
				if (!$coverage) {
					$allProviders = $insuranceProviderModel->getActiveProviders();
					$providerLower = strtolower(trim($provider));
					
					foreach ($allProviders as $prov) {
						$provNameLower = strtolower(trim($prov['name']));
						if ($providerLower === $provNameLower || 
							strpos($providerLower, $provNameLower) !== false || 
							strpos($provNameLower, $providerLower) !== false) {
							$coverage = [
								'room' => floatval($prov['coverage_room'] ?? 0),
								'laboratory' => floatval($prov['coverage_lab'] ?? 0),
								'medication' => floatval($prov['coverage_meds'] ?? 0),
								'professional' => floatval($prov['coverage_pf'] ?? 0),
								'procedure' => floatval($prov['coverage_procedure'] ?? 0),
							];
							log_message('debug', 'Provider match found: ' . $provider . ' -> ' . $prov['name']);
							break;
		}
	}
				}
				
				if ($coverage) {
					// Calculate average discount percentage for backward compatibility
					$avgCoverage = (
						$coverage['room'] + 
						$coverage['laboratory'] + 
						$coverage['medication'] + 
						$coverage['professional'] + 
						$coverage['procedure']
					) / 5;
					$discountPercentage = round($avgCoverage, 2);
					$coPaymentPercentage = 100 - $discountPercentage;
				} else {
					log_message('warning', 'No coverage found for provider: ' . $provider);
				}
			}
			
			return $this->response->setJSON([
				'success' => true,
				'has_insurance' => $hasInsurance,
				'has_philhealth' => $hasInsurance && stripos($provider ?? '', 'philhealth') !== false, // For backward compatibility
				'discount_percentage' => $discountPercentage,
				'copay_percentage' => $coPaymentPercentage,
				'provider' => $provider,
				'policy_number' => $insuranceClaim['policy_number'] ?? null,
				'member_id' => $insuranceClaim['member_id'] ?? null,
				'coverage' => $coverage, // Category-based coverage percentages
				'debug' => [
					'insurance_claim_found' => !empty($insuranceClaim),
					'provider_name' => $provider,
					'discount_calculated' => $discountPercentage,
					'coverage_found' => !empty($coverage)
				]
			]);
		} catch (\Exception $e) {
			log_message('error', 'Error getting patient insurance discount: ' . $e->getMessage());
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Error: ' . $e->getMessage()
			]);
		}
	}
	
	public function getPatientBillableItems($patientId)
	{
		try {
			if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
				return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
			}
			
			// Ensure billing tables exist
			$this->ensureBillingTables();
			
			$db = \Config\Database::connect();
			$billableItems = [];
			
			// Get patient info for room charges
			$patientModel = new PatientModel();
			$patient = $patientModel->find($patientId);
			
			// Load unbilled Laboratory items (results then requests)
			$this->ensureLabBillingColumns();
			
			$defaultLabPrices = [
				'cbc' => 350.00,
				'urinalysis' => 250.00,
				'x-ray' => 900.00,
				'fecalysis' => 250.00,
				'ct scan' => 4500.00,
				'mri' => 8000.00,
				'pregnancy test' => 300.00,
				'covid' => 1500.00,
				'blood sugar' => 280.00,
			];
			
			// lab_test_results
			if ($db->tableExists('lab_test_results')) {
				try {
					$results = $db->table('lab_test_results r')
						->select('r.id, r.released_at, rq.test_type, rq.patient_id, r.status as result_status')
						->join('lab_test_requests rq', 'rq.id = r.request_id', 'left')
						->where('rq.patient_id', $patientId)
						->whereIn('r.status', ['completed','released'])
						->groupStart()
							->where('r.billing_status', 'unbilled')
							->orWhere('r.billing_status IS NULL')
							->orWhere('r.billing_status', '')
						->groupEnd()
						->orderBy('r.released_at', 'DESC')
						->get()->getResultArray();
					
					foreach ($results as $row) {
						$testType = trim(strtolower($row['test_type'] ?? 'Lab Test'));
						
						// Try to get price from lab_test_requests table first
						$unitPrice = 0.00;
						if (!empty($row['request_id']) && $db->tableExists('lab_test_requests')) {
							try {
								$request = $db->table('lab_test_requests')
									->select('price')
									->where('id', $row['request_id'])
									->get()
									->getRowArray();
								if ($request && isset($request['price']) && $request['price'] > 0) {
									$unitPrice = (float)$request['price'];
								}
							} catch (\Exception $e) {
								// Ignore errors, fall back to defaults
							}
						}
						
						// If no price from request, try lab_tests_master
						if ($unitPrice <= 0 && $db->tableExists('lab_tests_master')) {
							try {
								$masterTest = $db->table('lab_tests_master')
									->select('price')
									->where('test_name', $row['test_type'] ?? '')
									->where('is_active', 1)
									->get()
									->getRowArray();
								if ($masterTest && isset($masterTest['price']) && $masterTest['price'] > 0) {
									$unitPrice = (float)$masterTest['price'];
								}
							} catch (\Exception $e) {
								// Ignore errors, fall back to defaults
							}
						}
						
						// Fall back to default prices if still no price found
						if ($unitPrice <= 0) {
							$unitPrice = $defaultLabPrices[$testType] ?? $this->guessLabPrice($testType, $defaultLabPrices, 500.00);
						}
						
						$billableItems[] = [
							'category' => 'laboratory',
							'code' => 'LAB-' . str_pad((string)$row['id'], 6, '0', STR_PAD_LEFT),
							'date_time' => !empty($row['released_at']) ? date('Y-m-d\TH:i', strtotime($row['released_at'])) : date('Y-m-d\TH:i'),
							'item_name' => strtoupper($row['test_type'] ?? 'Lab Test'),
							'unit_price' => $unitPrice,
							'quantity' => 1,
							'reference_id' => $row['id'],
							'reference_type' => 'lab_result'
						];
					}
				} catch (\Exception $e) {
					log_message('warning', 'Error fetching lab results: ' . $e->getMessage());
				}
			}
			
			// lab_test_requests (include all requests that haven't been billed)
			if ($db->tableExists('lab_test_requests')) {
				try {
					// Ensure billing_status column exists and update existing records
					$this->ensureLabBillingColumns();
					
					// Get ALL lab requests for this patient first (we'll filter in PHP)
					// This ensures we don't miss any due to column issues
					// Check if appointment_id column exists before selecting it
					$fields = $db->getFieldData('lab_test_requests');
					$hasAppointmentId = false;
					foreach ($fields as $f) {
						if (strtolower($f->name) === 'appointment_id') {
							$hasAppointmentId = true;
							break;
						}
					}
					
					$selectFields = 'rq.id, rq.test_type, rq.requested_at, rq.status, rq.doctor_id';
					
					// Check if price column exists
					$hasPrice = false;
					foreach ($fields as $f) {
						if (strtolower($f->name) === 'price') {
							$hasPrice = true;
							break;
						}
					}
					if ($hasPrice) {
						$selectFields .= ', rq.price';
					}
					
					if ($hasAppointmentId) {
						$selectFields .= ', rq.appointment_id';
					}
					
					// Get all lab test requests for this patient first
					$allRequestsRaw = $db->table('lab_test_requests rq')
						->select($selectFields)
						->where('rq.patient_id', $patientId)
						->orderBy('rq.requested_at', 'DESC')
						->get()->getResultArray();
					
					// Filter in PHP to include:
					// 1. Requests with status = 'completed' (case-insensitive)
					// 2. Requests that have completed/released results (even if request status is not completed)
					$allRequests = [];
					foreach ($allRequestsRaw as $req) {
						$status = strtolower(trim($req['status'] ?? ''));
						
						// Check if request status is completed
						if ($status === 'completed') {
							$allRequests[] = $req;
							continue;
						}
						
						// Check if request has completed/released results
						if ($db->tableExists('lab_test_results')) {
							$hasCompletedResult = $db->table('lab_test_results')
								->where('request_id', $req['id'])
								->whereIn('status', ['completed', 'released', 'COMPLETED', 'RELEASED'])
								->countAllResults() > 0;
							
							if ($hasCompletedResult) {
								$allRequests[] = $req;
							}
						}
					}
					
					// Try to get billing_status if column exists
					if (!empty($allRequests)) {
						try {
							$requestIds = array_column($allRequests, 'id');
							$requestsWithBilling = $db->table('lab_test_requests')
								->select('id, billing_status')
								->whereIn('id', $requestIds)
								->get()->getResultArray();
							
							$billingStatusMap = [];
							foreach ($requestsWithBilling as $req) {
								$billingStatusMap[$req['id']] = $req['billing_status'] ?? null;
							}
							
							// Add billing_status to each request
							foreach ($allRequests as &$req) {
								$req['billing_status'] = $billingStatusMap[$req['id']] ?? null;
							}
						} catch (\Exception $e) {
							// Column doesn't exist or error - assume all are unbilled
							foreach ($allRequests as &$req) {
								$req['billing_status'] = null;
							}
						}
					}
					
					// Filter: Include if billing_status is NULL, empty, or 'unbilled'
					// Also include if billing_status column doesn't exist (null)
					$allRequests = array_filter($allRequests, function($req) {
						$status = $req['billing_status'] ?? null;
						return ($status === null || $status === '' || $status === 'unbilled');
					});
					
					// Get request IDs that already have billed results
					$requestIdsWithBilledResults = [];
					if ($db->tableExists('lab_test_results') && !empty($allRequests)) {
						$requestIds = array_column($allRequests, 'id');
						$billedResultsCheck = $db->table('lab_test_results')
							->select('request_id')
							->whereIn('request_id', $requestIds)
							->where('billing_status', 'billed')
							->get()->getResultArray();
						$requestIdsWithBilledResults = array_column($billedResultsCheck, 'request_id');
					}
					
					// Include all unbilled requests (whether they have results or not, as long as not billed)
					$requests = [];
					foreach ($allRequests as $row) {
						// Only exclude if the result is already billed
						if (!in_array($row['id'], $requestIdsWithBilledResults)) {
							$requests[] = $row;
						}
					}
					
					// Log for debugging
					log_message('info', 'Patient ' . $patientId . ': Found ' . count($allRequests) . ' total lab requests, ' . count($requests) . ' will be added to billing');
					
					foreach ($requests as $row) {
						$testType = trim(strtolower($row['test_type'] ?? 'Lab Test'));
						
						// Get price from lab_test_requests table first (this is the actual price saved)
						$unitPrice = 0.00;
						if (isset($row['price']) && $row['price'] > 0) {
							$unitPrice = (float)$row['price'];
						}
						
						// If no price in request, try lab_tests_master
						if ($unitPrice <= 0 && $db->tableExists('lab_tests_master')) {
							try {
								$masterTest = $db->table('lab_tests_master')
									->select('price')
									->where('test_name', $row['test_type'] ?? '')
									->where('is_active', 1)
									->get()
									->getRowArray();
								if ($masterTest && isset($masterTest['price']) && $masterTest['price'] > 0) {
									$unitPrice = (float)$masterTest['price'];
								}
							} catch (\Exception $e) {
								// Ignore errors, fall back to defaults
							}
						}
						
						// Fall back to default prices if still no price found
						if ($unitPrice <= 0) {
							$unitPrice = $defaultLabPrices[$testType] ?? $this->guessLabPrice($testType, $defaultLabPrices, 500.00);
						}
						
						// Check if this request is linked to an appointment
						$appointmentInfo = '';
						if (isset($row['appointment_id']) && !empty($row['appointment_id']) && $db->tableExists('appointments')) {
							try {
								$appointment = $db->table('appointments')
									->select('appointment_date, appointment_time')
									->where('id', $row['appointment_id'])
									->get()->getRowArray();
								if ($appointment) {
									$appointmentInfo = ' (Appointment: ' . date('M d, Y', strtotime($appointment['appointment_date'] ?? '')) . ')';
								}
							} catch (\Exception $e) {
								// Ignore errors
							}
						}
						
						$billableItems[] = [
							'category' => 'laboratory',
							'code' => 'LABRQ-' . str_pad((string)$row['id'], 6, '0', STR_PAD_LEFT),
							'date_time' => !empty($row['requested_at']) ? date('Y-m-d\TH:i', strtotime($row['requested_at'])) : date('Y-m-d\TH:i'),
							'item_name' => strtoupper($row['test_type'] ?? 'Lab Test') . $appointmentInfo,
							'unit_price' => $unitPrice,
							'quantity' => 1,
							'reference_id' => $row['id'],
							'reference_type' => 'lab_request'
						];
					}
				} catch (\Exception $e) {
					log_message('warning', 'Error fetching lab requests: ' . $e->getMessage());
				}
			}
			
			// Get billed appointment IDs (only if bills table exists)
			$billedAppointmentIds = [];
			if ($db->tableExists('bills')) {
				try {
					$billedAppointments = $db->table('bills')
						->select('appointment_id')
						->where('appointment_id IS NOT NULL')
						->get()->getResultArray();
					$billedAppointmentIds = array_column($billedAppointments, 'appointment_id');
				} catch (\Exception $e) {
					log_message('warning', 'Error fetching billed appointments: ' . $e->getMessage());
					$billedAppointmentIds = [];
				}
			}
			
			// Get unbilled appointments (Doctor Fees) - get all non-cancelled appointments
			// Exclude lab test appointments (they are billed via lab_test_requests)
			$appointments = [];
			if ($db->tableExists('appointments')) {
				try {
					$appointmentModel = new AppointmentModel();
					// Get all appointments except cancelled, no-show, and lab test appointments
					$appointmentsQuery = $appointmentModel
						->where('patient_id', $patientId)
						->whereNotIn('status', ['cancelled', 'no-show'])
						->where('appointment_type !=', 'laboratory_test') // Exclude lab test appointments
						->where('doctor_id IS NOT NULL', null, false); // Only appointments with doctors
					
					if (!empty($billedAppointmentIds)) {
						$appointmentsQuery->whereNotIn('id', $billedAppointmentIds);
					}
					
					$appointments = $appointmentsQuery->orderBy('appointment_date', 'DESC')->findAll();
					
					$today = date('Y-m-d');
					
					foreach ($appointments as $appointment) {
						$appointmentDate = $appointment['appointment_date'] ?? date('Y-m-d');
						
						// For follow-up appointments, only add to bill if appointment date is today or in the past
						// Don't bill future follow-up appointments
						if (isset($appointment['appointment_type']) && strtolower($appointment['appointment_type']) === 'follow-up') {
							if ($appointmentDate > $today) {
								// Follow-up is in the future - skip billing
								continue;
							}
						}
						
						// Get doctor name
						$doctorName = 'Doctor';
						if (isset($appointment['doctor_id']) && $db->tableExists('users')) {
							try {
								$doctor = $db->table('users')->where('id', $appointment['doctor_id'])->get()->getRowArray();
								$doctorName = $doctor ? ($doctor['name'] ?? 'Doctor') : 'Doctor';
							} catch (\Exception $e) {
								log_message('warning', 'Error fetching doctor: ' . $e->getMessage());
							}
						}
						
						$billableItems[] = [
							'category' => 'professional',
							'code' => '500000',
							'date_time' => date('Y-m-d\TH:i', strtotime($appointmentDate)),
							'item_name' => 'Dr. ' . $doctorName . ' - Consultation',
							'unit_price' => 500.00, // Default consultation fee
							'quantity' => 1,
							'reference_id' => $appointment['id'],
							'reference_type' => 'appointment'
						];
					}
				} catch (\Exception $e) {
					log_message('error', 'Error fetching appointments: ' . $e->getMessage());
				}
			}
			
			// Get unbilled admissions (Doctor Fees for inpatients) - check admissions table
			if ($db->tableExists('admissions')) {
				try {
					// Get billed admission IDs (check by item_type and description containing 'Inpatient')
					$billedAdmissionIds = [];
					if ($db->tableExists('bill_items')) {
						$billedAdmissions = $db->table('bill_items')
							->select('reference_id')
							->where('item_type', 'professional')
							->like('item_name', 'Inpatient Care')
							->get()->getResultArray();
						$billedAdmissionIds = array_column($billedAdmissions, 'reference_id');
					}
					
					// Include both Admitted and Discharged for billing (need to bill before or after discharge)
					$admissionsQuery = $db->table('admissions')
						->where('patient_id', $patientId)
						->whereIn('status', ['Admitted', 'Discharged']);
					
					if (!empty($billedAdmissionIds)) {
						$admissionsQuery->whereNotIn('id', $billedAdmissionIds);
					}
					
					$admissions = $admissionsQuery->orderBy('admission_date', 'DESC')->get()->getResultArray();
					
					foreach ($admissions as $admission) {
						// Get doctor name
						$doctorName = 'Doctor';
						if (isset($admission['doctor_id']) && $db->tableExists('users')) {
							try {
								$doctor = $db->table('users')->where('id', $admission['doctor_id'])->get()->getRowArray();
								$doctorName = $doctor ? ($doctor['name'] ?? 'Doctor') : 'Doctor';
							} catch (\Exception $e) {
								log_message('warning', 'Error fetching doctor: ' . $e->getMessage());
							}
						}
						
						$admissionDate = $admission['admission_date'] ?? date('Y-m-d');
						
						$billableItems[] = [
							'category' => 'professional',
							'code' => '500000',
							'date_time' => date('Y-m-d\TH:i', strtotime($admissionDate)),
							'item_name' => 'Dr. ' . $doctorName . ' - Inpatient Care',
							'unit_price' => 500.00, // Default professional fee
							'quantity' => 1,
							'reference_id' => $admission['id'],
							'reference_type' => 'admission'
						];
					}
				} catch (\Exception $e) {
					log_message('error', 'Error fetching admissions for billing: ' . $e->getMessage());
				}
			}
			
			// Get billed prescription IDs - check both bills table and bill_items table
			$billedPrescriptionIds = [];
			if ($db->tableExists('bills')) {
				try {
					// Check bills table for prescription_id
					$billedPrescriptions = $db->table('bills')
						->select('prescription_id')
						->where('prescription_id IS NOT NULL')
						->get()->getResultArray();
					$billedPrescriptionIds = array_column($billedPrescriptions, 'prescription_id');
					
					// Also check bill_items for medications that reference prescriptions
					if ($db->tableExists('bill_items')) {
						$billedPrescriptionItems = $db->table('bill_items bi')
							->select('bi.reference_id')
							->join('bills b', 'b.id = bi.bill_id', 'inner')
							->where('bi.reference_type', 'prescription')
							->where('bi.reference_id IS NOT NULL')
							->where('b.patient_id', $patientId)
							->where('b.status', 'paid') // Only count paid bills
							->get()->getResultArray();
						$billedPrescriptionItemIds = array_column($billedPrescriptionItems, 'reference_id');
						$billedPrescriptionIds = array_unique(array_merge($billedPrescriptionIds, $billedPrescriptionItemIds));
					}
				} catch (\Exception $e) {
					log_message('warning', 'Error fetching billed prescriptions: ' . $e->getMessage());
					$billedPrescriptionIds = [];
				}
			}
			
			// Get unbilled prescriptions (Medications) - get all non-cancelled prescriptions
			$prescriptions = [];
			if ($db->tableExists('prescriptions')) {
				try {
					$prescriptionModel = new PrescriptionModel();
					// Get all prescriptions except cancelled
					$prescriptionsQuery = $prescriptionModel
						->where('patient_id', $patientId)
						->whereNotIn('status', ['cancelled']);
					
					if (!empty($billedPrescriptionIds)) {
						$prescriptionsQuery->whereNotIn('id', $billedPrescriptionIds);
					}
					
					$prescriptions = $prescriptionsQuery->orderBy('created_at', 'DESC')->findAll();
				} catch (\Exception $e) {
					log_message('error', 'Error fetching prescriptions: ' . $e->getMessage());
					$prescriptions = [];
				}
			}
			
			// Get medication prices from database or defaults, then double for patient billing
			$medicationPrices = [];
			
			// Default base prices (will be doubled for patient) - only used if not found in database
			$defaultPrices = [
				'amoxicillin' => 8.00, // Updated to match actual supplier price
				'paracetamol' => 25.00,
				'ibuprofen' => 30.00,
				'aspirin' => 20.00,
				'metformin' => 40.00,
				'losartan' => 45.00,
				'atorvastatin' => 60.00,
				'omeprazole' => 35.00,
				'cefuroxime' => 80.00,
				'azithromycin' => 75.00,
			];
			
			// Get prices from medications table and double them
			if ($db->tableExists('medications')) {
				$medications = $db->table('medications')->get()->getResultArray();
				foreach ($medications as $med) {
					$name = strtolower(trim($med['name'] ?? ''));
					if (!empty($name) && isset($med['price'])) {
						$basePrice = floatval($med['price']);
						// Only add if price is greater than 0
						if ($basePrice > 0) {
							// Double the price for patient billing
							$medicationPrices[$name] = $basePrice * 2;
						}
					}
				}
			}
			
			// Fill in defaults for medications not in database (doubled)
			foreach ($defaultPrices as $key => $basePrice) {
				$nameLower = $key;
				if (!isset($medicationPrices[$nameLower])) {
					$medicationPrices[$nameLower] = $basePrice * 2; // Double for patient
				}
			}
			
			foreach ($prescriptions as $prescription) {
				try {
					$prescriptionStatus = $prescription['status'] ?? 'pending';
					$itemsJson = $prescription['items_json'] ?? '[]';
					$items = json_decode($itemsJson, true);
					
					// Check if all medications from this prescription have been paid
					// by checking if all buy_from_hospital medications exist in paid bill_items
					if ($db->tableExists('bill_items') && $db->tableExists('bills') && 
						json_last_error() === JSON_ERROR_NONE && is_array($items) && !empty($items)) {
						
						$prescriptionMedications = [];
						foreach ($items as $item) {
							if (!is_array($item)) continue;
							$buyFromHospital = isset($item['buy_from_hospital']) ? (bool)$item['buy_from_hospital'] : true;
							if ($buyFromHospital) {
								$medName = $item['name'] ?? $item['medication'] ?? $item['med_name'] ?? '';
								if (!empty($medName)) {
									$prescriptionMedications[] = strtolower(trim($medName));
								}
							}
						}
						
						// If prescription has medications, check if all are already paid
						if (!empty($prescriptionMedications)) {
							$paidMedications = $db->table('bill_items bi')
								->join('bills b', 'b.id = bi.bill_id', 'inner')
								->select('LOWER(bi.item_name) as item_name')
								->where('b.patient_id', $patientId)
								->where('b.status', 'paid')
								->where('bi.item_type', 'medication')
								->get()
								->getResultArray();
							
							$paidMedNames = array_map(function($med) {
								$name = strtolower(trim($med['item_name'] ?? ''));
								// Remove dosage info for comparison
								return preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $name);
							}, $paidMedications);
							
							$allMedicationsPaid = true;
							foreach ($prescriptionMedications as $presMed) {
								$presMedClean = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $presMed);
								$found = false;
								foreach ($paidMedNames as $paidMed) {
									if (strpos($paidMed, $presMedClean) !== false || strpos($presMedClean, $paidMed) !== false) {
										$found = true;
										break;
									}
								}
								if (!$found) {
									$allMedicationsPaid = false;
									break;
								}
							}
							
							// If all medications from this prescription are paid, skip the entire prescription
							if ($allMedicationsPaid) {
								continue; // Skip this prescription - all medications already paid
							}
						}
					}
					
					// Get nurse name if prescription is completed
					$nurseName = null;
					if ($prescriptionStatus === 'completed' && $db->tableExists('treatment_updates')) {
						try {
							$nurseUpdate = $db->table('treatment_updates')
								->where('patient_id', $patientId)
								->where('nurse_name IS NOT NULL')
								->where('nurse_name !=', '')
								->orderBy('created_at', 'DESC')
                                ->get()
                                ->getRowArray();
							if ($nurseUpdate && !empty($nurseUpdate['nurse_name'])) {
								$nurseName = $nurseUpdate['nurse_name'];
							}
						} catch (\Exception $e) {
							log_message('warning', 'Error fetching nurse name: ' . $e->getMessage());
						}
					}
					
					if (json_last_error() === JSON_ERROR_NONE && is_array($items)) {
						// Check if all medications from this prescription have been paid
						// by checking if all buy_from_hospital medications exist in paid bill_items
						if ($db->tableExists('bill_items') && $db->tableExists('bills') && !empty($items)) {
							$prescriptionMedications = [];
							foreach ($items as $item) {
								if (!is_array($item)) continue;
								$buyFromHospital = isset($item['buy_from_hospital']) ? (bool)$item['buy_from_hospital'] : true;
								if ($buyFromHospital) {
									$medName = $item['name'] ?? $item['medication'] ?? $item['med_name'] ?? '';
									if (!empty($medName)) {
										$prescriptionMedications[] = strtolower(trim($medName));
									}
								}
							}
							
							// If prescription has medications, check if all are already paid
							if (!empty($prescriptionMedications)) {
								$paidMedications = $db->table('bill_items bi')
									->join('bills b', 'b.id = bi.bill_id', 'inner')
									->select('LOWER(bi.item_name) as item_name')
									->where('b.patient_id', $patientId)
									->where('b.status', 'paid')
									->where('bi.item_type', 'medication')
									->get()
									->getResultArray();
								
								$paidMedNames = array_map(function($med) {
									$name = strtolower(trim($med['item_name'] ?? ''));
									// Remove dosage info for comparison
									return preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $name);
								}, $paidMedications);
								
								$allMedicationsPaid = true;
								foreach ($prescriptionMedications as $presMed) {
									$presMedClean = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $presMed);
									$found = false;
									foreach ($paidMedNames as $paidMed) {
										if (strpos($paidMed, $presMedClean) !== false || strpos($presMedClean, $paidMed) !== false) {
											$found = true;
											break;
										}
									}
									if (!$found) {
										$allMedicationsPaid = false;
										break;
									}
								}
								
								// If all medications from this prescription are paid, skip the entire prescription
								if ($allMedicationsPaid) {
									continue; // Skip this prescription - all medications already paid
								}
							}
						}
						
						$medCodeCounter = 1;
						foreach ($items as $item) {
							if (!is_array($item)) continue;
							
							// Check if patient is buying from hospital - only add to bill if true
							$buyFromHospital = isset($item['buy_from_hospital']) ? (bool)$item['buy_from_hospital'] : true;
							if (!$buyFromHospital) {
								continue; // Skip medications not bought from hospital
							}
							
							$medicationName = $item['name'] ?? $item['medication'] ?? $item['med_name'] ?? '';
							if (empty($medicationName)) continue;
							
							// Check if this specific medication has already been billed and paid
							// Check bill_items for paid bills with matching medication name (regardless of reference_id)
							if ($db->tableExists('bill_items') && $db->tableExists('bills')) {
								$medicationNameLower = strtolower(trim($medicationName));
								$medicationNameClean = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $medicationNameLower);
								$medicationNameClean = trim($medicationNameClean);
								
								// Check if this medication name (or similar) has been billed in paid bills
								$alreadyBilled = $db->table('bill_items bi')
									->join('bills b', 'b.id = bi.bill_id', 'inner')
									->where('b.patient_id', $patientId)
									->where('b.status', 'paid') // Only check paid bills
									->where('bi.item_type', 'medication')
									->groupStart()
										->like('LOWER(bi.item_name)', $medicationNameLower, 'both') // Exact match
										->orLike('LOWER(bi.item_name)', $medicationNameClean, 'both') // Match without dosage
									->groupEnd()
									->countAllResults() > 0;
								
								if ($alreadyBilled) {
									continue; // Skip this medication - already billed and paid
								}
							}
							
							// Calculate quantity
							$quantity = 1;
							if (isset($item['quantity']) && $item['quantity'] > 0) {
								$quantity = floatval($item['quantity']);
							} else {
								$durationStr = $item['duration'] ?? '';
								if (!empty($durationStr)) {
									preg_match('/(\d+)/', $durationStr, $matches);
									if (!empty($matches[1])) {
										$durationDays = (int)$matches[1];
										$frequency = strtolower($item['frequency'] ?? '');
										if (strpos($frequency, '2x') !== false || strpos($frequency, 'twice') !== false) {
											$quantity = $durationDays * 2;
										} elseif (strpos($frequency, '3x') !== false || strpos($frequency, 'thrice') !== false) {
											$quantity = $durationDays * 3;
										} else {
											$quantity = $durationDays;
										}
									}
								}
							}
							
							// Get price (already doubled from medicationPrices array)
							$unitPrice = 0;
							$nameLower = strtolower(trim($medicationName));
							
							// Extract base medication name (remove numbers, units like mg, ml, etc.)
							$baseName = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $nameLower);
							$baseName = trim($baseName);
							
							// First try exact match
							if (isset($medicationPrices[$nameLower])) {
								$unitPrice = $medicationPrices[$nameLower];
							} elseif (!empty($baseName) && isset($medicationPrices[$baseName])) {
								$unitPrice = $medicationPrices[$baseName];
							} else {
								// Try to match by partial name (check if medication name contains key or vice versa)
								$found = false;
								foreach ($medicationPrices as $key => $price) {
									if (strpos($nameLower, $key) !== false || strpos($key, $nameLower) !== false) {
										$unitPrice = $price;
										$found = true;
										break;
									}
								}
								
								// If still not found, try with base name
								if (!$found && !empty($baseName)) {
									foreach ($medicationPrices as $key => $price) {
										if (strpos($baseName, $key) !== false || strpos($key, $baseName) !== false) {
											$unitPrice = $price;
											$found = true;
											break;
										}
									}
								}
								
								// If still not found, try default prices and double
								if (!$found) {
									foreach ($defaultPrices as $key => $basePrice) {
										if (strpos($nameLower, $key) !== false || (!empty($baseName) && strpos($baseName, $key) !== false)) {
											$unitPrice = $basePrice * 2; // Double for patient
											$found = true;
											break;
										}
									}
								}
								
								// If still not found, use default doubled price (8 * 2 = 16)
								if (!$found || $unitPrice <= 0) {
									$unitPrice = 16.00; // default doubled price for amoxicillin
								}
							}
							
							$prescriptionDate = $prescription['created_at'] ?? date('Y-m-d H:i:s');
							
							// Add medication item
							$billableItems[] = [
								'category' => 'medication',
								'code' => 'MED' . str_pad($medCodeCounter++, 4, '0', STR_PAD_LEFT),
								'date_time' => date('Y-m-d\TH:i', strtotime($prescriptionDate)),
								'item_name' => $medicationName,
								'unit_price' => $unitPrice,
								'quantity' => $quantity,
								'reference_id' => $prescription['id'],
								'reference_type' => 'prescription'
							];
						}
						
						// Nurse fees removed - no longer charging for nursing services
					}
				} catch (\Exception $e) {
					log_message('error', 'Error processing prescription: ' . $e->getMessage());
					continue;
				}
			}
			
			// Nurse fees removed - no longer charging for nursing services
			
			// Add Room/Bed Charges if patient has a room (inpatient or outpatient)
			if ($patient && !empty($patient['room_number'])) {
				// Check if room charges already billed for this patient
				$billedRoomCharges = [];
				if ($db->tableExists('bills') && $db->tableExists('bill_items')) {
					try {
						$roomBills = $db->table('bills b')
							->select('bi.reference_id as room_id')
							->join('bill_items bi', 'bi.bill_id = b.id', 'left')
							->where('b.patient_id', $patientId)
							->where('bi.item_type', 'room')
							->get()->getResultArray();
						$billedRoomCharges = array_filter(array_column($roomBills, 'room_id'));
					} catch (\Exception $e) {
						log_message('warning', 'Error fetching billed room charges: ' . $e->getMessage());
					}
				}
				
				// Get room info if rooms table exists
				$roomId = null;
				$roomNumber = $patient['room_number'] ?? 'General Ward';
				$roomRate = 200.00; // Default room rate per day
				
				// Determine room rate based on room type
				if (!empty($patient['room_type'])) {
					switch(strtolower($patient['room_type'])) {
						case 'private':
							$roomRate = 1500.00;
							break;
						case 'semi':
							$roomRate = 800.00;
							break;
						case 'ward':
							$roomRate = 200.00;
							break;
						default:
							$roomRate = 200.00;
					}
				}
				
				if ($db->tableExists('rooms') && !empty($patient['room_number'])) {
					try {
						$room = $db->table('rooms')
							->where('room_number', $patient['room_number'])
							->get()
							->getRowArray();
						if ($room) {
							$roomId = $room['id'] ?? null;
							// Check if room has room_price field, otherwise use default
							if (isset($room['room_price']) && $room['room_price'] > 0) {
								$roomRate = floatval($room['room_price']);
							}
							$roomNumber = $room['room_number'] ?? $patient['room_number'];
						}
					} catch (\Exception $e) {
						log_message('warning', 'Error fetching room info: ' . $e->getMessage());
					}
				}
				
				// Only add if not already billed
				if (!$roomId || !in_array($roomId, $billedRoomCharges)) {
					// Calculate days (default to 1 day if no admission date)
					$days = 1;
					if (!empty($patient['admission_date'])) {
						$checkInDate = new \DateTime($patient['admission_date']);
						$today = new \DateTime();
						$days = max(1, $checkInDate->diff($today)->days + 1);
					} elseif (!empty($patient['created_at'])) {
						$checkInDate = new \DateTime($patient['created_at']);
						$today = new \DateTime();
						$days = max(1, $checkInDate->diff($today)->days + 1);
					}
					
					$roomTypeLabel = !empty($patient['room_type']) ? ucfirst($patient['room_type']) : 'Ward';
					$billableItems[] = [
						'category' => 'room',
						'code' => '100000',
						'date_time' => date('Y-m-d\TH:i', strtotime($patient['admission_date'] ?? $patient['created_at'] ?? 'now')),
						'item_name' => 'Bed Charges - ' . $roomTypeLabel . ($roomNumber !== 'General Ward' ? ' (' . $roomNumber . ')' : ''),
						'unit_price' => $roomRate,
						'quantity' => $days,
						'reference_id' => $roomId,
						'reference_type' => 'room'
					];
				}
			}
			
			return $this->response->setJSON([
				'success' => true,
				'items' => $billableItems,
				'count' => count($billableItems)
			]);
			
		} catch (\Exception $e) {
			log_message('error', 'Error in getPatientBillableItems: ' . $e->getMessage());
			log_message('error', 'Stack trace: ' . $e->getTraceAsString());
			log_message('error', 'File: ' . $e->getFile() . ' Line: ' . $e->getLine());
			
			// Return error but don't set 500 status to avoid breaking the frontend
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Error loading billable items: ' . $e->getMessage(),
				'items' => []
			]);
		}
	}

	public function getPrescriptionDetails($prescriptionId)
	{
		try {
			if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
				return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
			}
			
			$prescriptionModel = new PrescriptionModel();
			$prescription = $prescriptionModel->find($prescriptionId);
			
			if (!$prescription) {
				return $this->response->setJSON(['success' => false, 'message' => 'Prescription not found']);
			}
			
			// Parse items
			$itemsJson = $prescription['items_json'] ?? '[]';
			$items = json_decode($itemsJson, true);
			
			if (json_last_error() !== JSON_ERROR_NONE || !is_array($items)) {
				$items = [];
			}
			
			// Get medication prices from database or defaults, then double for patient billing
			$db = \Config\Database::connect();
			$medicationPrices = [];
			
			// Default base prices (will be doubled for patient) - only used if not found in database
			$defaultPrices = [
				'amoxicillin' => 8.00, // Updated to match actual supplier price
				'paracetamol' => 25.00,
				'ibuprofen' => 30.00,
				'aspirin' => 20.00,
				'metformin' => 40.00,
				'losartan' => 45.00,
				'atorvastatin' => 60.00,
				'omeprazole' => 35.00,
				'cefuroxime' => 80.00,
				'azithromycin' => 75.00,
			];
			
			// Get prices from medications table and double them
			if ($db->tableExists('medications')) {
				$medications = $db->table('medications')->get()->getResultArray();
				foreach ($medications as $med) {
					$name = strtolower(trim($med['name'] ?? ''));
					if (!empty($name) && isset($med['price'])) {
						$basePrice = floatval($med['price']);
						// Only add if price is greater than 0
						if ($basePrice > 0) {
							// Double the price for patient billing
							$medicationPrices[$name] = $basePrice * 2;
						}
					}
				}
			}
			
			// Fill in defaults for medications not in database (doubled)
			foreach ($defaultPrices as $key => $basePrice) {
				$nameLower = $key;
				if (!isset($medicationPrices[$nameLower])) {
					$medicationPrices[$nameLower] = $basePrice * 2; // Double for patient
				}
			}
			
			// Process items with prices
			$processedItems = [];
			foreach ($items as $item) {
				if (!is_array($item)) continue;
				
				$medicationName = $item['name'] ?? $item['medication'] ?? $item['med_name'] ?? '';
				if (empty($medicationName)) continue;
				
				// Calculate quantity
				$quantity = 1;
				if (isset($item['quantity']) && $item['quantity'] > 0) {
					$quantity = floatval($item['quantity']);
				} else {
					$durationStr = $item['duration'] ?? '';
					if (!empty($durationStr)) {
						preg_match('/(\d+)/', $durationStr, $matches);
						if (!empty($matches[1])) {
							$durationDays = (int)$matches[1];
							$frequency = strtolower($item['frequency'] ?? '');
							if (strpos($frequency, '2x') !== false || strpos($frequency, 'twice') !== false) {
								$quantity = $durationDays * 2;
							} elseif (strpos($frequency, '3x') !== false || strpos($frequency, 'thrice') !== false) {
								$quantity = $durationDays * 3;
							} else {
								$quantity = $durationDays;
							}
						}
					}
				}
				
				// Get price (already doubled from medicationPrices array)
				$unitPrice = 0;
				$nameLower = strtolower(trim($medicationName));
				
				// Extract base medication name (remove numbers, units like mg, ml, etc.)
				$baseName = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $nameLower);
				$baseName = trim($baseName);
				
				// First try exact match
				if (isset($medicationPrices[$nameLower])) {
					$unitPrice = $medicationPrices[$nameLower];
				} elseif (!empty($baseName) && isset($medicationPrices[$baseName])) {
					$unitPrice = $medicationPrices[$baseName];
				} else {
					// Try to match by partial name (check if medication name contains key or vice versa)
					$found = false;
					foreach ($medicationPrices as $key => $price) {
						if (strpos($nameLower, $key) !== false || strpos($key, $nameLower) !== false) {
							$unitPrice = $price;
							$found = true;
							break;
						}
					}
					
					// If still not found, try with base name
					if (!$found && !empty($baseName)) {
						foreach ($medicationPrices as $key => $price) {
							if (strpos($baseName, $key) !== false || strpos($key, $baseName) !== false) {
								$unitPrice = $price;
								$found = true;
								break;
							}
						}
					}
					
					// If still not found, try default prices and double
					if (!$found) {
						foreach ($defaultPrices as $key => $basePrice) {
							if (strpos($nameLower, $key) !== false || (!empty($baseName) && strpos($baseName, $key) !== false)) {
								$unitPrice = $basePrice * 2; // Double for patient
								$found = true;
								break;
							}
						}
					}
					
					// If still not found, use default doubled price (8 * 2 = 16)
					if (!$found || $unitPrice <= 0) {
						$unitPrice = 16.00; // default doubled price for amoxicillin
					}
				}
				
				// Build description
				$description = [];
				if (!empty($item['dosage'])) $description[] = "Dosage: {$item['dosage']}";
				if (!empty($item['frequency'])) $description[] = "Frequency: {$item['frequency']}";
				if (!empty($item['meal_instruction'])) $description[] = "Meal: {$item['meal_instruction']}";
				if (!empty($item['duration'])) $description[] = "Duration: {$item['duration']}";
				
				$processedItems[] = [
					'item_name' => $medicationName,
					'description' => implode(', ', $description),
					'quantity' => $quantity,
					'unit_price' => $unitPrice,
				];
			}
			
			return $this->response->setJSON([
				'success' => true,
				'prescription' => [
					'id' => $prescription['id'],
					'patient_id' => $prescription['patient_id'],
					'items' => $processedItems,
				]
			]);
			
		} catch (\Exception $e) {
			log_message('error', 'Error in getPrescriptionDetails: ' . $e->getMessage());
			log_message('error', 'Stack trace: ' . $e->getTraceAsString());
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Error loading prescription: ' . $e->getMessage()
			])->setStatusCode(500);
		}
	}
	
	private function ensureBillingTables()
	{
		$db = \Config\Database::connect();
		
		// Create bills table
		if (!$db->tableExists('bills')) {
			$forge = \Config\Database::forge();
			$forge->addField([
				'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
				'bill_number' => ['type' => 'VARCHAR', 'constraint' => 50],
				'patient_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
				'appointment_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
				'prescription_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
				'lab_test_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
				'room_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
				'bill_type' => ['type' => 'ENUM', 'constraint' => ['appointment', 'prescription', 'lab_test', 'room', 'consultation', 'procedure', 'other'], 'default' => 'other'],
				'subtotal' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
				'discount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
				'tax' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
				'total_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
				'paid_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
				'balance' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
				'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'], 'default' => 'pending'],
				'due_date' => ['type' => 'DATE', 'null' => true],
				'payment_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
				'notes' => ['type' => 'TEXT', 'null' => true],
				'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
				'created_at' => ['type' => 'DATETIME', 'null' => true],
				'updated_at' => ['type' => 'DATETIME', 'null' => true],
			]);
			$forge->addKey('id', true);
			$forge->addKey('patient_id');
			$forge->addKey('bill_number');
			$forge->createTable('bills', true);
		}
		
		// Create bill_items table
		if (!$db->tableExists('bill_items')) {
			$forge = \Config\Database::forge();
			$forge->addField([
				'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
				'bill_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
				'item_type' => ['type' => 'VARCHAR', 'constraint' => 50],
				'item_name' => ['type' => 'VARCHAR', 'constraint' => 255],
				'description' => ['type' => 'TEXT', 'null' => true],
				'quantity' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 1],
				'unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
				'total_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
				'reference_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
				'reference_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'Type of reference: appointment, prescription, lab_request, lab_result, room, admission'],
				'category' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'Item category for insurance mapping'],
				'insurance_coverage_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00, 'null' => true, 'comment' => 'Insurance coverage percentage'],
				'insurance_discount_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00, 'null' => true, 'comment' => 'Discount amount from insurance'],
				'patient_pays_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'comment' => 'Amount patient pays after insurance'],
				'created_at' => ['type' => 'DATETIME', 'null' => true],
				'updated_at' => ['type' => 'DATETIME', 'null' => true],
			]);
			$forge->addKey('id', true);
			$forge->addKey('bill_id');
			$forge->createTable('bill_items', true);
		} else {
			// Check if reference_type column exists, if not add it
			$columns = $db->getFieldNames('bill_items');
			if (!in_array('reference_type', $columns)) {
				$forge = \Config\Database::forge();
				$forge->addColumn('bill_items', [
					'reference_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'Type of reference: appointment, prescription, lab_request, lab_result, room, admission', 'after' => 'reference_id']
				]);
			}
		}
		
		// Create payments table
		if (!$db->tableExists('payments')) {
			$forge = \Config\Database::forge();
			$forge->addField([
				'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
				'bill_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
				'patient_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
				'payment_number' => ['type' => 'VARCHAR', 'constraint' => 50],
				'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
				'payment_method' => ['type' => 'ENUM', 'constraint' => ['cash', 'credit_card', 'debit_card', 'insurance', 'check', 'bank_transfer', 'online'], 'default' => 'cash'],
				'payment_date' => ['type' => 'DATE'],
				'transaction_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
				'reference_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
				'notes' => ['type' => 'TEXT', 'null' => true],
				'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'completed', 'failed', 'refunded'], 'default' => 'completed'],
				'processed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
				'created_at' => ['type' => 'DATETIME', 'null' => true],
				'updated_at' => ['type' => 'DATETIME', 'null' => true],
			]);
			$forge->addKey('id', true);
			$forge->addKey('bill_id');
			$forge->addKey('patient_id');
			$forge->addKey('payment_number');
			$forge->createTable('payments', true);
		}
	}

	public function payments()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		// Check and update walk-in patients with paid bills
		$this->updateWalkInPatientsWithPaidBills();

		$paymentModel = new PaymentModel();
		$billingModel = new BillingModel();
		$patientModel = new PatientModel();
		
		// Get filters
		$filters = [
			'status' => $this->request->getGet('status'),
			'patient_id' => $this->request->getGet('patient_id'),
			'payment_method' => $this->request->getGet('payment_method'),
			'date_from' => $this->request->getGet('date_from'),
			'date_to' => $this->request->getGet('date_to'),
		];
		
		// Get all payments
		$payments = $paymentModel->getPaymentsWithPatient($filters);
		
		// Get stats
		$today = date('Y-m-d');
		$todayPayments = $paymentModel->selectSum('amount')
			->where('payment_date', $today)
			->where('status', 'completed')
			->get()->getRowArray();
		
		$totalPayments = $paymentModel->selectSum('amount')
			->where('status', 'completed')
			->get()->getRowArray();
		
		$pendingPayments = $paymentModel->where('status', 'pending')->countAllResults();
		
		// Get patients for dropdown
		$patients = $patientModel->select('id, full_name, patient_id')->orderBy('full_name', 'ASC')->findAll();
		
		// Get payment methods stats
		$paymentMethods = $paymentModel->select('payment_method, SUM(amount) as total')
			->where('status', 'completed')
			->groupBy('payment_method')
			->get()->getResultArray();
		
		// Get pending bills (bills that can be paid)
		$pendingBills = $billingModel->getBillsWithPatient([
			'status' => 'pending'
		]);
		$partialBills = $billingModel->getBillsWithPatient([
			'status' => 'partial'
		]);
		$unpaidBills = array_merge($pendingBills, $partialBills);

		$data = [
			'title' => 'Payments - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
			'payments' => $payments,
			'patients' => $patients,
			'unpaid_bills' => $unpaidBills,
			'today_payments' => $todayPayments['amount'] ?? 0,
			'total_payments' => $totalPayments['amount'] ?? 0,
			'pending_payments_count' => $pendingPayments,
			'payment_methods' => $paymentMethods,
			'filters' => $filters,
		];

		return view('accounts/payments', $data);
	}

	public function reports()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$billingModel = new BillingModel();
		$paymentModel = new PaymentModel();
		
		// Get filters
		$reportType = $this->request->getGet('type') ?? 'bills';
		$dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
		$dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

		$data = [
			'title' => 'Accounts Reports - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
			'report_type' => $reportType,
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'bills' => [],
			'payments' => [],
			'summary' => [
				'total_revenue' => 0,
				'total_bills' => 0,
				'total_payments' => 0,
				'pending_bills' => 0,
			]
		];

		try {
			// Bills Report
			$bills = $billingModel
				->select('bills.*, patients.full_name as patient_name, patients.patient_id as patient_code')
				->join('patients', 'patients.id = bills.patient_id', 'left')
				->where('DATE(bills.created_at) >=', $dateFrom)
				->where('DATE(bills.created_at) <=', $dateTo)
				->orderBy('bills.created_at', 'DESC')
				->findAll();
			
			$data['bills'] = $bills;
			$data['summary']['total_bills'] = count($bills);
			$data['summary']['pending_bills'] = count(array_filter($bills, function($b) {
				return ($b['status'] ?? 'pending') === 'pending';
			}));

			// Payments Report
			$payments = $paymentModel->getPaymentsWithPatient([
				'date_from' => $dateFrom,
				'date_to' => $dateTo,
				'status' => 'completed'
			]);
			
			$data['payments'] = $payments;
			$data['summary']['total_payments'] = count($payments);
			$data['summary']['total_revenue'] = array_sum(array_column($payments, 'amount'));

		} catch (\Exception $e) {
			log_message('error', 'Error fetching accounts reports: ' . $e->getMessage());
		}

		return view('accounts/reports', $data);
	}

	public function financial()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$db = \Config\Database::connect();
		$billingModel = new BillingModel();
		$paymentModel = new PaymentModel();
		
		// Get today's revenue
		$today = date('Y-m-d');
		$todayRevenue = $db->table('payments')
			->selectSum('amount')
			->where('payment_date', $today)
			->where('status', 'completed')
			->get()->getRowArray();
		
		// Get pending bills
		$pendingBills = $billingModel->getBillsWithPatient(['status' => 'pending']);
		
		// Get insurance claims count
		$insuranceClaims = $db->table('insurance_claims')->countAllResults();
		
		// Get overdue payments count
		$overduePayments = $db->table('bills')
			->where('status', 'overdue')
			->countAllResults();
		
		// Get recent payments with patient info
		$recentPayments = $db->table('payments p')
			->select('p.*, pat.full_name as patient_name, b.bill_number')
			->join('patients pat', 'pat.id = p.patient_id', 'left')
			->join('bills b', 'b.id = p.bill_id', 'left')
			->orderBy('p.created_at', 'DESC')
			->limit(10)
			->get()->getResultArray();

		$data = [
			'title' => 'Financial - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
			'today_revenue' => $todayRevenue['amount'] ?? 0,
			'pending_bills' => $pendingBills,
			'pending_bills_count' => count($pendingBills),
			'insurance_claims' => $insuranceClaims,
			'overdue_payments' => $overduePayments,
			'recent_payments' => $recentPayments,
		];

		return view('accounts/dashboard', $data);
	}

	public function settings()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$model = new SettingModel();
		$defaults = [
			'accounts_invoice_prefix'     => 'ACC',
			'accounts_invoice_start'      => '1000',
			'accounts_tax_rate'           => '12',
			'accounts_due_days'           => '30',
			'accounts_payment_terms'      => 'Net 30',
			'accounts_auto_reminders'     => '1',
			'accounts_notification_email' => session()->get('email') ?? 'accounts@hospital.local',
			'accounts_statement_footer'   => 'Thank you for choosing MediCare Hospital.',
			'accounts_show_currency'      => 'PHP',
		];
		$settings = array_merge($defaults, $model->getAllAsMap());

		$data = [
			'title'     => 'Accountant Settings - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
			'pageTitle' => 'Settings',
			'settings'  => $settings,
		];

		return view('accounts/settings', $data);
	}

	public function saveSettings()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$model = new SettingModel();
		$post  = $this->request->getPost();

		$keys = [
			'accounts_invoice_prefix',
			'accounts_invoice_start',
			'accounts_tax_rate',
			'accounts_due_days',
			'accounts_payment_terms',
			'accounts_auto_reminders',
			'accounts_notification_email',
			'accounts_statement_footer',
			'accounts_show_currency',
		];

		foreach ($keys as $key) {
			$model->setValue($key, (string) ($post[$key] ?? ''), 'accounts');
		}

		return redirect()->to('/accounts/settings')->with('success', 'Account settings saved.');
	}
}




