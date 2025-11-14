<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\BillingModel;
use App\Models\BillItemModel;
use App\Models\PaymentModel;
use App\Models\InsuranceModel;
use App\Models\PatientModel;
use App\Models\AppointmentModel;
use App\Models\PrescriptionModel;
use App\Models\LabTestRequestModel;

class Accounts extends Controller
{
	public function __construct()
	{
		$this->ensureBillingTables();
		$this->ensureInsuranceTables();
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
		
		// Get patients for dropdown
		$patients = $patientModel->select('id, full_name, patient_id')->orderBy('full_name', 'ASC')->findAll();
		
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
			
			$discount = floatval($postData['discount'] ?? 0);
			$tax = ($subtotal - $discount) * 0.12; // 12% tax
			$totalAmount = $subtotal - $discount + $tax;
			
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
			
			// Create bill items
			$itemErrors = [];
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
				
				$itemData = [
					'bill_id' => $billId,
					'item_type' => $item['item_type'] ?? $item['category'] ?? 'service',
					'item_name' => $item['item_name'],
					'description' => $item['description'] ?? '',
					'quantity' => floatval($item['quantity']),
					'unit_price' => floatval($item['unit_price']),
					'total_price' => floatval($item['quantity']) * floatval($item['unit_price']),
					'reference_id' => $referenceId,
				];
				
				if (!$billItemModel->insert($itemData)) {
					$itemErrors[] = $item['item_name'];
				}
			}
			
			if (!empty($itemErrors)) {
				log_message('warning', 'Some bill items failed to create: ' . implode(', ', $itemErrors));
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
		
		$paymentModel = new PaymentModel();
		$billingModel = new BillingModel();
		
		$postData = $this->request->getJSON(true);
		$billId = $postData['bill_id'];
		$amount = $postData['amount'];
		
		$bill = $billingModel->find($billId);
		if (!$bill) {
			return $this->response->setJSON(['success' => false, 'message' => 'Bill not found']);
		}
		
		// Generate payment number
		$paymentNumber = $paymentModel->generatePaymentNumber();
		
		// Create payment
		$paymentData = [
			'bill_id' => $billId,
			'patient_id' => $bill['patient_id'],
			'payment_number' => $paymentNumber,
			'amount' => $amount,
			'payment_method' => $postData['payment_method'],
			'payment_date' => $postData['payment_date'] ?? date('Y-m-d'),
			'transaction_id' => $postData['transaction_id'] ?? null,
			'reference_number' => $postData['reference_number'] ?? null,
			'notes' => $postData['notes'] ?? null,
			'status' => 'completed',
			'processed_by' => session()->get('user_id'),
		];
		
		$paymentId = $paymentModel->insert($paymentData);
		
		if ($paymentId) {
			// Update bill
			$newPaidAmount = $bill['paid_amount'] + $amount;
			$newBalance = $bill['total_amount'] - $newPaidAmount;
			$newStatus = $newBalance <= 0 ? 'paid' : ($newBalance < $bill['total_amount'] ? 'partial' : 'pending');
			
			$billingModel->update($billId, [
				'paid_amount' => $newPaidAmount,
				'balance' => max(0, $newBalance),
				'status' => $newStatus,
				'payment_method' => $postData['payment_method'],
			]);
			
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Payment recorded successfully',
				'payment_id' => $paymentId,
				'payment_number' => $paymentNumber
			]);
		}
		
		return $this->response->setJSON(['success' => false, 'message' => 'Failed to record payment']);
	}
	
	public function getBillDetails($billId)
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
		}
		
		$billingModel = new BillingModel();
		$bill = $billingModel->getBillWithItems($billId);
		
		if ($bill) {
			return $this->response->setJSON(['success' => true, 'bill' => $bill]);
		}
		
		return $this->response->setJSON(['success' => false, 'message' => 'Bill not found']);
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
			$appointments = [];
			if ($db->tableExists('appointments')) {
				try {
					$appointmentModel = new AppointmentModel();
					// Get all appointments except cancelled and no-show
					$appointmentsQuery = $appointmentModel
						->where('patient_id', $patientId)
						->whereNotIn('status', ['cancelled', 'no-show']);
					
					if (!empty($billedAppointmentIds)) {
						$appointmentsQuery->whereNotIn('id', $billedAppointmentIds);
					}
					
					$appointments = $appointmentsQuery->orderBy('appointment_date', 'DESC')->findAll();
					
					foreach ($appointments as $appointment) {
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
						
						$appointmentDate = $appointment['appointment_date'] ?? date('Y-m-d H:i:s');
						
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
			
			// Get billed prescription IDs (only if bills table exists)
			$billedPrescriptionIds = [];
			if ($db->tableExists('bills')) {
				try {
					$billedPrescriptions = $db->table('bills')
						->select('prescription_id')
						->where('prescription_id IS NOT NULL')
						->get()->getResultArray();
					$billedPrescriptionIds = array_column($billedPrescriptions, 'prescription_id');
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
			
			// Get medication prices (medications table doesn't have price column, use defaults)
			$medicationPrices = [];
			// Note: medications table doesn't have a 'price' column, so we'll use default prices
			
			$defaultPrices = [
				'amoxicillin' => 50.00,
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
			
			foreach ($prescriptions as $prescription) {
				try {
					$itemsJson = $prescription['items_json'] ?? '[]';
					$items = json_decode($itemsJson, true);
					
					if (json_last_error() === JSON_ERROR_NONE && is_array($items)) {
						$medCodeCounter = 1;
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
							
							// Get price
							$unitPrice = 50.00;
							$nameLower = strtolower($medicationName);
							if (isset($medicationPrices[$nameLower])) {
								$unitPrice = $medicationPrices[$nameLower];
							} else {
								foreach ($defaultPrices as $key => $price) {
									if (strpos($nameLower, $key) !== false) {
										$unitPrice = $price;
										break;
									}
								}
							}
							
							$prescriptionDate = $prescription['created_at'] ?? date('Y-m-d H:i:s');
							
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
					}
				} catch (\Exception $e) {
					log_message('error', 'Error processing prescription: ' . $e->getMessage());
					continue;
				}
			}
			
			// Add Room/Bed Charges if patient has a room
			if ($patient && !empty($patient['room_number'])) {
				// Check if room charges already billed
				$billedRoomCharges = [];
				if ($db->tableExists('bills')) {
					try {
						$roomBills = $db->table('bills')
							->select('room_id')
							->where('patient_id', $patientId)
							->where('room_id IS NOT NULL')
							->where('bill_type', 'room')
							->get()->getResultArray();
						$billedRoomCharges = array_column($roomBills, 'room_id');
					} catch (\Exception $e) {
						log_message('warning', 'Error fetching billed room charges: ' . $e->getMessage());
					}
				}
				
				// Get room info if rooms table exists
				$roomId = null;
				$roomNumber = $patient['room_number'];
				$roomRate = 200.00; // Default room rate per day
				
				if ($db->tableExists('rooms')) {
					try {
						$room = $db->table('rooms')
							->where('room_number', $roomNumber)
							->first();
						if ($room) {
							$roomId = $room['id'] ?? null;
							// Check if room has rate field, otherwise use default
							if (isset($room['rate'])) {
								$roomRate = floatval($room['rate']);
							}
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
					
					$billableItems[] = [
						'category' => 'room',
						'code' => '100000',
						'date_time' => date('Y-m-d\TH:i', strtotime($patient['admission_date'] ?? $patient['created_at'] ?? 'now')),
						'item_name' => 'Bed Charges - ' . $roomNumber,
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
			
			// Get medication prices (medications table doesn't have price column, use defaults)
			$db = \Config\Database::connect();
			$medicationPrices = [];
			// Note: medications table doesn't have a 'price' column, so we'll use default prices
			
			// Default prices
			$defaultPrices = [
				'amoxicillin' => 50.00,
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
				
				// Get price
				$unitPrice = 50.00; // default
				$nameLower = strtolower($medicationName);
				if (isset($medicationPrices[$nameLower])) {
					$unitPrice = $medicationPrices[$nameLower];
				} else {
					foreach ($defaultPrices as $key => $price) {
						if (strpos($nameLower, $key) !== false) {
							$unitPrice = $price;
							break;
						}
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
				'created_at' => ['type' => 'DATETIME', 'null' => true],
				'updated_at' => ['type' => 'DATETIME', 'null' => true],
			]);
			$forge->addKey('id', true);
			$forge->addKey('bill_id');
			$forge->createTable('bill_items', true);
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

	public function insurance()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$insuranceModel = new InsuranceModel();
		$billingModel = new BillingModel();
		$patientModel = new PatientModel();
		
		// Get filters
		$filters = [
			'status' => $this->request->getGet('status'),
			'patient_id' => $this->request->getGet('patient_id'),
			'insurance_provider' => $this->request->getGet('insurance_provider'),
			'date_from' => $this->request->getGet('date_from'),
			'date_to' => $this->request->getGet('date_to'),
		];
		
		// Get all insurance claims
		$claims = $insuranceModel->getClaimsWithPatient($filters);
		
		// Get stats
		$totalClaims = $insuranceModel->selectSum('claim_amount')->get()->getRowArray();
		$approvedClaims = $insuranceModel->selectSum('approved_amount')
			->where('status', 'approved')
			->get()->getRowArray();
		$pendingClaims = $insuranceModel->where('status', 'pending')->orWhere('status', 'submitted')->countAllResults();
		
		// Get patients for dropdown
		$patients = $patientModel->select('id, full_name, patient_id')->orderBy('full_name', 'ASC')->findAll();
		
		// Get insurance providers list
		$providers = $insuranceModel->select('insurance_provider')
			->distinct()
			->orderBy('insurance_provider', 'ASC')
			->findAll();
		
		// Get bills that can be claimed (unpaid bills)
		$unclaimedBills = $billingModel->where('status', 'pending')
			->orWhere('status', 'partial')
			->getBillsWithPatient();

		$data = [
			'title' => 'Insurance - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
			'claims' => $claims,
			'patients' => $patients,
			'providers' => $providers,
			'unclaimed_bills' => $unclaimedBills,
			'total_claims' => $totalClaims['claim_amount'] ?? 0,
			'approved_claims' => $approvedClaims['approved_amount'] ?? 0,
			'pending_claims_count' => $pendingClaims,
			'filters' => $filters,
		];

		return view('accounts/insurance', $data);
	}
	
	public function createInsuranceClaim()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
		}
		
		$insuranceModel = new InsuranceModel();
		$billingModel = new BillingModel();
		
		$postData = $this->request->getJSON(true);
		$billId = $postData['bill_id'];
		
		$bill = $billingModel->find($billId);
		if (!$bill) {
			return $this->response->setJSON(['success' => false, 'message' => 'Bill not found']);
		}
		
		// Generate claim number
		$claimNumber = $insuranceModel->generateClaimNumber();
		
		// Create insurance claim
		$claimData = [
			'claim_number' => $claimNumber,
			'bill_id' => $billId,
			'patient_id' => $bill['patient_id'],
			'insurance_provider' => $postData['insurance_provider'],
			'policy_number' => $postData['policy_number'] ?? null,
			'member_id' => $postData['member_id'] ?? null,
			'claim_amount' => $postData['claim_amount'] ?? $bill['balance'],
			'approved_amount' => 0,
			'deductible' => $postData['deductible'] ?? 0,
			'co_payment' => $postData['co_payment'] ?? 0,
			'status' => 'submitted',
			'submitted_date' => date('Y-m-d'),
			'notes' => $postData['notes'] ?? null,
			'created_by' => session()->get('user_id'),
		];
		
		$claimId = $insuranceModel->insert($claimData);
		
		if ($claimId) {
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Insurance claim created successfully',
				'claim_id' => $claimId,
				'claim_number' => $claimNumber
			]);
		}
		
		return $this->response->setJSON(['success' => false, 'message' => 'Failed to create insurance claim']);
	}
	
	public function updateInsuranceClaim()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
		}
		
		$insuranceModel = new InsuranceModel();
		$paymentModel = new PaymentModel();
		$billingModel = new BillingModel();
		
		$postData = $this->request->getJSON(true);
		$claimId = $postData['claim_id'];
		$status = $postData['status'];
		
		$claim = $insuranceModel->find($claimId);
		if (!$claim) {
			return $this->response->setJSON(['success' => false, 'message' => 'Claim not found']);
		}
		
		$updateData = [
			'status' => $status,
		];
		
		if ($status === 'approved') {
			$updateData['approved_amount'] = $postData['approved_amount'] ?? $claim['claim_amount'];
			$updateData['approved_date'] = date('Y-m-d');
			
			// Auto-create payment if approved
			if ($postData['auto_create_payment'] ?? false) {
				$bill = $billingModel->find($claim['bill_id']);
				$paymentNumber = $paymentModel->generatePaymentNumber();
				
				$paymentData = [
					'bill_id' => $claim['bill_id'],
					'patient_id' => $claim['patient_id'],
					'payment_number' => $paymentNumber,
					'amount' => $updateData['approved_amount'],
					'payment_method' => 'insurance',
					'payment_date' => date('Y-m-d'),
					'reference_number' => $claim['claim_number'],
					'notes' => 'Insurance claim: ' . $claim['claim_number'],
					'status' => 'completed',
					'processed_by' => session()->get('user_id'),
				];
				
				$paymentId = $paymentModel->insert($paymentData);
				
				if ($paymentId && $bill) {
					$newPaidAmount = $bill['paid_amount'] + $updateData['approved_amount'];
					$newBalance = $bill['total_amount'] - $newPaidAmount;
					$newStatus = $newBalance <= 0 ? 'paid' : ($newBalance < $bill['total_amount'] ? 'partial' : 'pending');
					
					$billingModel->update($claim['bill_id'], [
						'paid_amount' => $newPaidAmount,
						'balance' => max(0, $newBalance),
						'status' => $newStatus,
					]);
				}
			}
		} elseif ($status === 'rejected') {
			$updateData['rejected_date'] = date('Y-m-d');
			$updateData['rejection_reason'] = $postData['rejection_reason'] ?? null;
		}
		
		$result = $insuranceModel->update($claimId, $updateData);
		
		if ($result) {
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Insurance claim updated successfully'
			]);
		}
		
		return $this->response->setJSON(['success' => false, 'message' => 'Failed to update insurance claim']);
	}
	
	private function ensureInsuranceTables()
	{
		$db = \Config\Database::connect();
		
		// Create insurance_claims table
		if (!$db->tableExists('insurance_claims')) {
			$forge = \Config\Database::forge();
			$forge->addField([
				'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
				'claim_number' => ['type' => 'VARCHAR', 'constraint' => 50],
				'bill_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
				'patient_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
				'insurance_provider' => ['type' => 'VARCHAR', 'constraint' => 255],
				'policy_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
				'member_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
				'claim_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
				'approved_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
				'deductible' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
				'co_payment' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
				'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'submitted', 'approved', 'rejected', 'paid', 'cancelled'], 'default' => 'pending'],
				'submitted_date' => ['type' => 'DATE', 'null' => true],
				'approved_date' => ['type' => 'DATE', 'null' => true],
				'rejected_date' => ['type' => 'DATE', 'null' => true],
				'rejection_reason' => ['type' => 'TEXT', 'null' => true],
				'notes' => ['type' => 'TEXT', 'null' => true],
				'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
				'created_at' => ['type' => 'DATETIME', 'null' => true],
				'updated_at' => ['type' => 'DATETIME', 'null' => true],
			]);
			$forge->addKey('id', true);
			$forge->addKey('bill_id');
			$forge->addKey('patient_id');
			$forge->addKey('claim_number');
			$forge->createTable('insurance_claims', true);
		}
	}

	public function reports()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Reports - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function financial()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Financial - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}
}




