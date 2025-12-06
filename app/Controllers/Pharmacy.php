<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PrescriptionModel;
use App\Models\MedicationModel;
use App\Models\PatientModel;
use App\Models\PharmacyStockMovementModel;
use App\Models\MedicineOrderModel;
use App\Models\SettingModel;

class Pharmacy extends Controller
{
	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$db = \Config\Database::connect();
		$medicationModel = new MedicationModel();
		$stockMovementModel = new PharmacyStockMovementModel();
		$orderModel = new MedicineOrderModel();

		// Get all medications
		$medications = $medicationModel->orderBy('name', 'ASC')->findAll();

		// Get inventory data
		$inventory = [];
		$inventoryByMedId = [];
		$inventoryByName = [];
		
		if ($db->tableExists('pharmacy_inventory')) {
			$inventoryRaw = $db->table('pharmacy_inventory')
				->orderBy('name', 'ASC')
				->get()
				->getResultArray();
			
			foreach ($inventoryRaw as $item) {
				if (!empty($item['medication_id'])) {
					$inventoryByMedId[$item['medication_id']] = $item;
				}
				$inventoryByName[strtolower(trim($item['name']))] = $item;
			}
		}

		// Merge inventory with medications
		$inventoryData = [];
		$totalStockQuantity = 0;
		$lowStockCount = 0;
		$expiredCount = 0;
		$lowStockItems = [];
		$expiredItems = [];
		$today = date('Y-m-d');

		foreach ($medications as $med) {
			$medId = $med['id'];
			$medName = strtolower(trim($med['name']));
			
			$invItem = null;
			if (isset($inventoryByMedId[$medId])) {
				$invItem = $inventoryByMedId[$medId];
			} elseif (isset($inventoryByName[$medName])) {
				$invItem = $inventoryByName[$medName];
			}

			$stockQty = $invItem ? (int)($invItem['stock_quantity'] ?? 0) : 0;
			$reorderLevel = $invItem ? (int)($invItem['reorder_level'] ?? 10) : 10;
			$expirationDate = $invItem['expiration_date'] ?? null;
			$category = $invItem['category'] ?? 'General';
			
			$status = 'ok';
			if ($expirationDate && strtotime($expirationDate) < strtotime($today)) {
				$status = 'expired';
				$expiredCount++;
				$expiredItems[] = [
					'medicine' => $med,
					'inventory' => $invItem,
					'stock_quantity' => $stockQty,
					'expiration_date' => $expirationDate,
				];
			} elseif ($stockQty <= 0) {
				$status = 'out_of_stock';
			} elseif ($stockQty < $reorderLevel) {
				$status = 'low_stock';
				$lowStockCount++;
				$lowStockItems[] = [
					'medicine' => $med,
					'inventory' => $invItem,
					'stock_quantity' => $stockQty,
					'reorder_level' => $reorderLevel,
				];
			}

			$totalStockQuantity += $stockQty;

			$inventoryData[] = [
				'medicine' => $med,
				'inventory' => $invItem,
				'stock_quantity' => $stockQty,
				'reorder_level' => $reorderLevel,
				'category' => $category,
				'expiration_date' => $expirationDate,
				'status' => $status,
			];
		}

		// Get last 10 stock movements
		$stockMovements = [];
		if ($db->tableExists('pharmacy_stock_movements')) {
			try {
				$stockMovements = $stockMovementModel->getAllWithUser();
				$stockMovements = array_slice($stockMovements, 0, 10);
			} catch (\Exception $e) {
				log_message('error', 'Error fetching stock movements: ' . $e->getMessage());
				$stockMovements = [];
			}
		}

		// Get all orders
		$orders = [];
		if ($db->tableExists('medicine_orders')) {
			try {
				$orders = $orderModel
					->select('medicine_orders.*, users.name as received_by_name')
					->join('users', 'users.id = medicine_orders.received_by', 'left')
					->orderBy('medicine_orders.order_date', 'DESC')
					->orderBy('medicine_orders.created_at', 'DESC')
					->findAll();
			} catch (\Exception $e) {
				log_message('error', 'Error fetching orders: ' . $e->getMessage());
				$orders = [];
			}
		}

		$data = [
			'title' => 'Pharmacy Dashboard - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
			'stats' => [
				'total_medicines' => count($medications),
				'total_stock_quantity' => $totalStockQuantity,
				'low_stock_alerts' => $lowStockCount,
				'expired_medicines' => $expiredCount,
			],
			'inventory' => $inventoryData,
			'stockMovements' => $stockMovements,
			'orders' => $orders,
			'lowStockItems' => $lowStockItems,
			'expiredItems' => $expiredItems,
		];

		return view('auth/dashboard', $data);
	}

	public function prescriptions()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$prescriptionModel = new PrescriptionModel();
		$patientModel = new PatientModel();
		$db = \Config\Database::connect();

		// Get filter
		$filterStatus = $this->request->getGet('status') ?? 'all';

		// Get prescriptions
		$builder = $prescriptionModel->orderBy('created_at', 'DESC');
		if ($filterStatus !== 'all') {
			$builder->where('status', $filterStatus);
		}

		$prescriptionsRaw = $builder->findAll();

		// Format prescriptions with patient and doctor names
		$prescriptions = [];
		foreach ($prescriptionsRaw as $rx) {
			$patient = $patientModel->find($rx['patient_id']);
			$doctor = $db->table('users')->where('id', $rx['doctor_id'])->get()->getRowArray();
			
			$items = json_decode($rx['items_json'] ?? '[]', true);
			
			// Calculate quantity for each item if not present
			$totalQuantity = 0;
			foreach ($items as &$item) {
				if (!isset($item['quantity']) || $item['quantity'] <= 0) {
					// Calculate quantity from duration and frequency
					$durationStr = $item['duration'] ?? '';
					$frequency = $item['frequency'] ?? '';
					$quantity = 0;
					
					if (!empty($durationStr)) {
						preg_match('/(\d+)/', $durationStr, $matches);
						if (!empty($matches[1])) {
							$durationDays = (int)$matches[1];
							
							// Estimate quantity based on frequency
							if (strpos(strtolower($frequency), '2x') !== false || 
								strpos(strtolower($frequency), 'twice') !== false ||
								strpos(strtolower($frequency), '2') !== false) {
								$quantity = $durationDays * 2;
							} elseif (strpos(strtolower($frequency), '3x') !== false || 
									 strpos(strtolower($frequency), 'thrice') !== false ||
									 strpos(strtolower($frequency), '3') !== false) {
								$quantity = $durationDays * 3;
							} elseif (strpos(strtolower($frequency), 'every 6 hours') !== false) {
								$quantity = $durationDays * 4; // 4 times per day
							} elseif (strpos(strtolower($frequency), 'every 8 hours') !== false) {
								$quantity = $durationDays * 3; // 3 times per day
							} else {
								$quantity = $durationDays; // Once a day (default)
							}
						}
					}
					
					// If still no quantity, default to 1 (single dose)
					if ($quantity <= 0) {
						$quantity = 1;
					}
					
					$item['quantity'] = $quantity;
				}
				$totalQuantity += (int)($item['quantity'] ?? 0);
			}

			$prescriptions[] = [
				'id' => $rx['id'],
				'rx_number' => 'RX#' . str_pad((string)$rx['id'], 6, '0', STR_PAD_LEFT),
				'patient_id' => $rx['patient_id'],
				'patient_name' => $patient['full_name'] ?? 'N/A',
				'patient_age' => $patient['age'] ?? null,
				'patient_gender' => $patient['gender'] ?? null,
				'doctor_id' => $rx['doctor_id'],
				'doctor_name' => $doctor['name'] ?? 'N/A',
				'items' => $items,
				'total_quantity' => $totalQuantity,
				'notes' => $rx['notes'] ?? null,
				'status' => $rx['status'],
				'created_at' => $rx['created_at'],
				'updated_at' => $rx['updated_at'],
			];
		}

		$data = [
			'title' => 'Prescriptions - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
			'prescriptions' => $prescriptions,
			'filterStatus' => $filterStatus,
		];

		return view('pharmacy/prescriptions', $data);
	}

	public function viewPrescription($id)
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		$prescriptionModel = new PrescriptionModel();
		$patientModel = new PatientModel();
		$db = \Config\Database::connect();

		$prescription = $prescriptionModel->find($id);
		if (!$prescription) {
			return $this->response->setJSON(['success' => false, 'message' => 'Prescription not found']);
		}

		$patient = $patientModel->find($prescription['patient_id']);
		$doctor = $db->table('users')->where('id', $prescription['doctor_id'])->get()->getRowArray();

		$data = [
			'success' => true,
			'prescription' => [
				'id' => $prescription['id'],
				'rx_number' => 'RX#' . str_pad((string)$prescription['id'], 6, '0', STR_PAD_LEFT),
				'patient_name' => $patient['full_name'] ?? 'N/A',
				'patient_age' => $patient['age'] ?? null,
				'patient_gender' => $patient['gender'] ?? null,
				'doctor_name' => $doctor['name'] ?? 'N/A',
				'items' => json_decode($prescription['items_json'] ?? '[]', true),
				'notes' => $prescription['notes'] ?? null,
				'status' => $prescription['status'],
				'created_at' => $prescription['created_at'],
			],
		];

		return $this->response->setJSON($data);
	}

	public function dispensePrescription()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		$prescriptionId = $this->request->getPost('prescription_id');
		if (!$prescriptionId) {
			return $this->response->setJSON(['success' => false, 'message' => 'Prescription ID is required']);
		}

		$prescriptionModel = new PrescriptionModel();
		$prescription = $prescriptionModel->find($prescriptionId);

		if (!$prescription) {
			return $this->response->setJSON(['success' => false, 'message' => 'Prescription not found']);
		}

		// Update prescription status to dispensed
		$prescriptionModel->update($prescriptionId, [
			'status' => 'dispensed',
			'updated_at' => date('Y-m-d H:i:s'),
		]);

		$db = \Config\Database::connect();
		$items = json_decode($prescription['items_json'] ?? '[]', true);
		$medicationModel = new MedicationModel();
		
		// Deduct stock from pharmacy_inventory for each medication
		foreach ($items as $item) {
			$medicineName = $item['name'] ?? '';
			
			if (empty($medicineName)) {
				continue;
			}
			
			// Calculate quantity - check if quantity field exists, otherwise calculate from dosage/frequency/duration
			$quantity = 0;
			if (isset($item['quantity']) && $item['quantity'] > 0) {
				$quantity = (int)$item['quantity'];
			} else {
				// Try to calculate quantity from duration and frequency
				$durationStr = $item['duration'] ?? '';
				$frequency = $item['frequency'] ?? '';
				
				if (!empty($durationStr)) {
					preg_match('/(\d+)/', $durationStr, $matches);
					if (!empty($matches[1])) {
						$durationDays = (int)$matches[1];
						
						// Estimate quantity based on frequency
						if (strpos(strtolower($frequency), '2x') !== false || 
							strpos(strtolower($frequency), 'twice') !== false ||
							strpos(strtolower($frequency), '2') !== false) {
							$quantity = $durationDays * 2;
						} elseif (strpos(strtolower($frequency), '3x') !== false || 
								 strpos(strtolower($frequency), 'thrice') !== false ||
								 strpos(strtolower($frequency), '3') !== false) {
							$quantity = $durationDays * 3;
						} elseif (strpos(strtolower($frequency), 'every 6 hours') !== false) {
							$quantity = $durationDays * 4; // 4 times per day
						} elseif (strpos(strtolower($frequency), 'every 8 hours') !== false) {
							$quantity = $durationDays * 3; // 3 times per day
						} else {
							$quantity = $durationDays; // Once a day (default)
						}
					}
				}
				
				// If still no quantity, default to 1 (single dose)
				if ($quantity <= 0) {
					$quantity = 1;
				}
			}
			
			// Find medication by name (try exact match first, then partial match)
			$medication = $medicationModel->where('name', $medicineName)->first();
			
			// If not found, try partial match
			if (!$medication) {
				$medication = $medicationModel->like('name', $medicineName)->first();
			}
			
			// If still not found, try reverse partial match (medicine name contains database name)
			if (!$medication) {
				$allMedications = $medicationModel->findAll();
				$medicineNameLower = strtolower($medicineName);
				foreach ($allMedications as $med) {
					$medNameLower = strtolower($med['name'] ?? '');
					if (strpos($medicineNameLower, $medNameLower) !== false || strpos($medNameLower, $medicineNameLower) !== false) {
						$medication = $med;
						break;
					}
				}
			}
			
			// Get medication price (from medications table)
			$medicationPrice = 0;
			if ($medication && isset($medication['price'])) {
				$medicationPrice = (float)$medication['price'];
			}
			
			// If no price found in database, use default prices
			if ($medicationPrice <= 0) {
				$defaultPrices = [
					'amoxicillin' => 8.00,
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
				
				$medicineNameLower = strtolower($medicineName);
				// Extract base name (remove units like 500mg)
				$baseName = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $medicineNameLower);
				$baseName = trim($baseName);
				
				foreach ($defaultPrices as $key => $price) {
					if (strpos($medicineNameLower, $key) !== false || (!empty($baseName) && strpos($baseName, $key) !== false)) {
						$medicationPrice = $price;
						break;
					}
				}
			}
			
			// Double the price for patient purchase (if original is 8, becomes 16)
			$unitPrice = $medicationPrice * 2;
			$totalPrice = $unitPrice * $quantity;
			
			if ($medication && $db->tableExists('pharmacy_inventory')) {
				// Find or create inventory record
				$inventoryRecord = $db->table('pharmacy_inventory')
					->where('medication_id', $medication['id'])
					->orWhere('name', $medicineName)
					->get()
					->getRowArray();
				
				if ($inventoryRecord) {
					$currentStock = (int)($inventoryRecord['stock_quantity'] ?? 0);
					$newStock = max(0, $currentStock - $quantity); // Don't go below 0
					
					// Update stock
					$db->table('pharmacy_inventory')
						->where('id', $inventoryRecord['id'])
						->update([
							'stock_quantity' => $newStock,
							'updated_at' => date('Y-m-d H:i:s'),
						]);
					
					// Log stock movement
					if ($db->tableExists('pharmacy_stock_movements')) {
						$db->table('pharmacy_stock_movements')->insert([
							'medication_id' => $medication['id'] ?? null,
							'medicine_name' => $medicineName,
							'movement_type' => 'dispense',
							'quantity_change' => -$quantity,
							'previous_stock' => $currentStock,
							'new_stock' => $newStock,
							'action_by' => session()->get('user_id'),
							'notes' => 'Dispensed via prescription RX#' . str_pad((string)$prescriptionId, 6, '0', STR_PAD_LEFT),
							'created_at' => date('Y-m-d H:i:s'),
						]);
					}
				}
			}
			
			// Log dispense action (if pharmacy_dispense_logs table exists)
			if ($db->tableExists('pharmacy_dispense_logs')) {
				$db->table('pharmacy_dispense_logs')->insert([
					'prescription_id' => $prescriptionId,
					'patient_id' => $prescription['patient_id'],
					'medicine_name' => $medicineName,
					'quantity' => $quantity,
					'unit_price' => $unitPrice,
					'total_price' => $totalPrice,
					'pharmacist_id' => session()->get('user_id'),
					'dispensed_at' => date('Y-m-d H:i:s'),
					'created_at' => date('Y-m-d H:i:s'),
				]);
			}
		}

		return $this->response->setJSON(['success' => true, 'message' => 'Prescription dispensed successfully']);
	}

	public function inventory()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$db = \Config\Database::connect();
		$medicationModel = new MedicationModel();

		// Get all medications
		$medications = $medicationModel->orderBy('name', 'ASC')->findAll();
		
		// Set default prices for medications that don't have prices
		$defaultPrices = [
			'amoxicillin' => 8.00,
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
		
		foreach ($medications as &$med) {
			if (empty($med['price']) || (float)$med['price'] <= 0) {
				$medNameLower = strtolower($med['name'] ?? '');
				$baseName = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $medNameLower);
				$baseName = trim($baseName);
				foreach ($defaultPrices as $key => $price) {
					if (strpos($medNameLower, $key) !== false || (!empty($baseName) && strpos($baseName, $key) !== false)) {
						$med['price'] = $price;
						break;
					}
				}
			}
		}
		unset($med);

		// Get inventory data if pharmacy_inventory table exists
		$inventory = [];
		if ($db->tableExists('pharmacy_inventory')) {
			$inventoryRaw = $db->table('pharmacy_inventory')
				->orderBy('name', 'ASC')
				->get()
				->getResultArray();
			
			$inventory = [];
			foreach ($inventoryRaw as $item) {
				$inventory[$item['medication_id'] ?? $item['name']] = $item;
			}
		}

		$data = [
			'title' => 'Inventory - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
			'medications' => $medications,
			'inventory' => $inventory,
		];

		return view('pharmacy/inventory', $data);
	}

	public function dispense()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$db = \Config\Database::connect();
		$prescriptionModel = new PrescriptionModel();
		$patientModel = new PatientModel();

		// Get dispense logs from pharmacy_dispense_logs table
		$dispenseLogs = [];
		if ($db->tableExists('pharmacy_dispense_logs')) {
			try {
				$dispenseLogsRaw = $db->table('pharmacy_dispense_logs')
					->select('pharmacy_dispense_logs.*, patients.full_name as patient_name, users.name as pharmacist_name')
					->join('patients', 'patients.id = pharmacy_dispense_logs.patient_id', 'left')
					->join('users', 'users.id = pharmacy_dispense_logs.pharmacist_id', 'left')
					->orderBy('pharmacy_dispense_logs.dispensed_at', 'DESC')
					->limit(500)
					->get()
					->getResultArray();
				
				// Calculate prices for logs that have 0.00 (old records)
				$defaultPrices = [
					'amoxicillin' => 8.00,
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
				
				$medicationModel = new MedicationModel();
				foreach ($dispenseLogsRaw as &$log) {
					// If unit_price is 0 or null, calculate it
					if (empty($log['unit_price']) || (float)$log['unit_price'] <= 0) {
						$medicineName = $log['medicine_name'] ?? '';
						$quantity = (float)($log['quantity'] ?? 1);
						
						// Try to get price from medications table
						$medication = $medicationModel->where('name', $medicineName)->first();
						if (!$medication) {
							$medication = $medicationModel->like('name', $medicineName)->first();
						}
						
						$medicationPrice = 0;
						if ($medication && isset($medication['price'])) {
							$medicationPrice = (float)$medication['price'];
						}
						
						// If no price found, use default prices
						if ($medicationPrice <= 0) {
							$medicineNameLower = strtolower($medicineName);
							$baseName = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $medicineNameLower);
							$baseName = trim($baseName);
							
							foreach ($defaultPrices as $key => $price) {
								if (strpos($medicineNameLower, $key) !== false || (!empty($baseName) && strpos($baseName, $key) !== false)) {
									$medicationPrice = $price;
									break;
								}
							}
						}
						
						// Double the price for patient and calculate total
						$log['unit_price'] = $medicationPrice * 2;
						$log['total_price'] = $log['unit_price'] * $quantity;
					}
				}
				unset($log);
				
				$dispenseLogs = $dispenseLogsRaw;
			} catch (\Exception $e) {
				log_message('error', 'Error fetching dispense logs: ' . $e->getMessage());
			}
		}

		// If no logs from dispense_logs table, try to get from prescriptions that were dispensed
		if (empty($dispenseLogs) && $db->tableExists('prescriptions')) {
			try {
				$dispensedPrescriptions = $prescriptionModel
					->where('status', 'dispensed')
					->orWhere('status', 'completed')
					->orderBy('updated_at', 'DESC')
					->limit(500)
					->findAll();

				foreach ($dispensedPrescriptions as $rx) {
					$patient = $patientModel->find($rx['patient_id']);
					$pharmacist = $db->table('users')->where('id', $rx['doctor_id'])->get()->getRowArray(); // Fallback to doctor if no pharmacist
					
					$items = json_decode($rx['items_json'] ?? '[]', true);
					
					foreach ($items as $item) {
						$medicineName = $item['name'] ?? 'Unknown';
						$quantity = (int)($item['quantity'] ?? 0);
						
						// Calculate quantity if not present
						if ($quantity <= 0) {
							$durationStr = $item['duration'] ?? '';
							$frequency = $item['frequency'] ?? '';
							if (!empty($durationStr)) {
								preg_match('/(\d+)/', $durationStr, $matches);
								if (!empty($matches[1])) {
									$durationDays = (int)$matches[1];
									if (strpos(strtolower($frequency), '2x') !== false || 
										strpos(strtolower($frequency), 'twice') !== false ||
										strpos(strtolower($frequency), '2') !== false) {
										$quantity = $durationDays * 2;
									} elseif (strpos(strtolower($frequency), '3x') !== false || 
											 strpos(strtolower($frequency), 'thrice') !== false ||
											 strpos(strtolower($frequency), '3') !== false) {
										$quantity = $durationDays * 3;
									} elseif (strpos(strtolower($frequency), 'every 6 hours') !== false) {
										$quantity = $durationDays * 4;
									} elseif (strpos(strtolower($frequency), 'every 8 hours') !== false) {
										$quantity = $durationDays * 3;
									} else {
										$quantity = $durationDays;
									}
								}
							}
							if ($quantity <= 0) $quantity = 1;
						}
						
						// Calculate prices
						$medicationModel = new MedicationModel();
						$medication = $medicationModel->where('name', $medicineName)->first();
						if (!$medication) {
							$medication = $medicationModel->like('name', $medicineName)->first();
						}
						
						$medicationPrice = 0;
						if ($medication && isset($medication['price'])) {
							$medicationPrice = (float)$medication['price'];
						}
						
						// If no price found, use default prices
						if ($medicationPrice <= 0) {
							$defaultPrices = [
								'amoxicillin' => 8.00,
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
							
							$medicineNameLower = strtolower($medicineName);
							$baseName = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $medicineNameLower);
							$baseName = trim($baseName);
							
							foreach ($defaultPrices as $key => $price) {
								if (strpos($medicineNameLower, $key) !== false || (!empty($baseName) && strpos($baseName, $key) !== false)) {
									$medicationPrice = $price;
									break;
								}
							}
						}
						
						// Double the price for patient and calculate total
						$unitPrice = $medicationPrice * 2;
						$totalPrice = $unitPrice * $quantity;

						$dispenseLogs[] = [
							'id' => null,
							'prescription_id' => $rx['id'],
							'patient_id' => $rx['patient_id'],
							'patient_name' => $patient['full_name'] ?? 'N/A',
							'medicine_name' => $medicineName,
							'quantity' => $quantity,
							'unit_price' => $unitPrice,
							'total_price' => $totalPrice,
							'pharmacist_id' => null,
							'pharmacist_name' => $pharmacist['name'] ?? 'System',
							'dispensed_at' => $rx['updated_at'] ?? $rx['created_at'],
							'created_at' => $rx['created_at'],
						];
					}
				}
			} catch (\Exception $e) {
				log_message('error', 'Error fetching dispensed prescriptions: ' . $e->getMessage());
			}
		}

		// Sort by dispensed_at descending
		usort($dispenseLogs, function($a, $b) {
			$dateA = strtotime($a['dispensed_at'] ?? '1970-01-01');
			$dateB = strtotime($b['dispensed_at'] ?? '1970-01-01');
			return $dateB - $dateA;
		});

		$data = [
			'title' => 'Dispense Logs - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
			'dispenseLogs' => $dispenseLogs,
		];

		return view('pharmacy/dispense', $data);
	}

	public function stockMovement()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$stockMovementModel = new PharmacyStockMovementModel();

		// Get all stock movements
		$stockMovements = $stockMovementModel->getAllWithUser();

		// Calculate statistics
		$totalMovements = count($stockMovements);
		$stockAdded = count(array_filter($stockMovements, fn($m) => ($m['movement_type'] ?? '') === 'add'));
		$stockDispensed = count(array_filter($stockMovements, fn($m) => ($m['movement_type'] ?? '') === 'dispense'));
		$adjustments = count(array_filter($stockMovements, fn($m) => ($m['movement_type'] ?? '') === 'adjust'));

		$data = [
			'title' => 'Stock Movement - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
			'stockMovements' => $stockMovements,
			'stats' => [
				'total' => $totalMovements,
				'added' => $stockAdded,
				'dispensed' => $stockDispensed,
				'adjustments' => $adjustments,
			],
		];

		return view('pharmacy/stock_movement', $data);
	}

	public function orders()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$orderModel = new MedicineOrderModel();
		$medicationModel = new MedicationModel();

		// Get all orders
		$orders = $orderModel->getAllWithPharmacist();

		// Get medications for dropdown
		$medications = $medicationModel->orderBy('name', 'ASC')->findAll();

		$data = [
			'title' => 'Orders - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
			'orders' => $orders,
			'medications' => $medications,
		];

		return view('pharmacy/orders', $data);
	}

	public function createOrder()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		$orderModel = new MedicineOrderModel();
		$medicationModel = new MedicationModel();

		// Get JSON data (for multiple medicines) or form data (for single medicine - backward compatibility)
		$jsonData = $this->request->getJSON(true);
		
		if ($jsonData && isset($jsonData['medicines']) && is_array($jsonData['medicines'])) {
			// Multiple medicines mode
			$supplierName = $jsonData['supplier_name'] ?? '';
			$orderDate = $jsonData['order_date'] ?? '';
			$reference = $jsonData['reference'] ?? null;
			$medicines = $jsonData['medicines'] ?? [];

			if (empty($supplierName) || empty($orderDate) || empty($medicines)) {
				return $this->response->setJSON(['success' => false, 'message' => 'Please fill all required fields']);
			}

			$baseOrderNumber = $orderModel->generateOrderNumber();
			$createdCount = 0;
			$errors = [];

			foreach ($medicines as $index => $medicine) {
				$medicationId = $medicine['medication_id'] ?? null;
				$medicineName = $medicine['medicine_name'] ?? '';
				$quantityOrdered = (int)($medicine['quantity_ordered'] ?? 0);
				$unitPrice = (float)($medicine['unit_price'] ?? 0);
				$totalPrice = (float)($medicine['total_price'] ?? 0);

				if (empty($medicineName) || $quantityOrdered <= 0 || $unitPrice <= 0) {
					$errors[] = "Medicine #" . ($index + 1) . " has invalid data";
					continue;
				}

				// Calculate total price if not provided
				if ($totalPrice <= 0) {
					$totalPrice = $unitPrice * $quantityOrdered;
				}

				// Generate unique order number for each medicine (same base, different suffix)
				$orderNumber = $baseOrderNumber;
				if ($index > 0) {
					$orderNumber = $baseOrderNumber . '-' . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);
				}

				$orderData = [
					'order_number' => $orderNumber,
					'medication_id' => $medicationId ?: null,
					'medicine_name' => $medicineName,
					'supplier_name' => $supplierName,
					'quantity_ordered' => $quantityOrdered,
					'unit_price' => $unitPrice,
					'total_price' => $totalPrice,
					'order_date' => $orderDate,
					'status' => 'pending',
					'reference' => $reference,
				];

				if ($orderModel->insert($orderData)) {
					$createdCount++;
				} else {
					$errors[] = "Failed to create order for " . $medicineName;
				}
			}

			if ($createdCount > 0) {
				$message = "Order created successfully for {$createdCount} medicine(s)";
				if (!empty($errors)) {
					$message .= ". Some errors: " . implode(', ', $errors);
				}
				return $this->response->setJSON(['success' => true, 'message' => $message, 'created_count' => $createdCount]);
			} else {
				return $this->response->setJSON(['success' => false, 'message' => 'Failed to create orders. ' . implode(', ', $errors)]);
			}
		} else {
			// Single medicine mode (backward compatibility)
			$medicineName = $this->request->getPost('medicine_name');
			$medicationId = $this->request->getPost('medication_id');
			$supplierName = $this->request->getPost('supplier_name');
			$quantityOrdered = (int)$this->request->getPost('quantity_ordered');
			$unitPrice = (float)$this->request->getPost('unit_price');
			$totalPrice = (float)$this->request->getPost('total_price');
			$orderDate = $this->request->getPost('order_date');
			$reference = $this->request->getPost('reference');

			if (empty($medicineName) || empty($supplierName) || $quantityOrdered <= 0 || empty($orderDate) || $unitPrice <= 0) {
				return $this->response->setJSON(['success' => false, 'message' => 'Please fill all required fields including unit price']);
			}

			// Get medication name and price if ID provided
			if ($medicationId && empty($medicineName)) {
				$medication = $medicationModel->find($medicationId);
				if ($medication) {
					$medicineName = $medication['name'];
					// Use medication price if unit price not provided
					if ($unitPrice <= 0 && isset($medication['price']) && $medication['price'] > 0) {
						$unitPrice = (float)$medication['price'];
					}
				}
			}

			// Calculate total price if not provided
			if ($totalPrice <= 0) {
				$totalPrice = $unitPrice * $quantityOrdered;
			}

			$orderData = [
				'order_number' => $orderModel->generateOrderNumber(),
				'medication_id' => $medicationId ?: null,
				'medicine_name' => $medicineName,
				'supplier_name' => $supplierName,
				'quantity_ordered' => $quantityOrdered,
				'unit_price' => $unitPrice,
				'total_price' => $totalPrice,
				'order_date' => $orderDate,
				'status' => 'pending',
				'reference' => $reference ?: null,
			];

			if ($orderModel->insert($orderData)) {
				return $this->response->setJSON(['success' => true, 'message' => 'Order created successfully']);
			}

			return $this->response->setJSON(['success' => false, 'message' => 'Failed to create order']);
		}
	}

	public function updateOrderStatus()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		$orderModel = new MedicineOrderModel();
		$stockMovementModel = new PharmacyStockMovementModel();
		$db = \Config\Database::connect();

		$orderId = $this->request->getPost('order_id');
		$status = $this->request->getPost('status');

		if (empty($orderId) || empty($status) || !in_array($status, ['pending', 'delivered', 'cancelled'])) {
			return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
		}

		$order = $orderModel->find($orderId);
		if (!$order) {
			return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
		}

		$updateData = ['status' => $status];

		// If status changed to DELIVERED, update stock and create movement log
		if ($status === 'delivered' && $order['status'] !== 'delivered') {
			$updateData['delivered_at'] = date('Y-m-d H:i:s');
			$updateData['received_by'] = session()->get('user_id');

			// Find or create inventory record
			$inventory = null;
			if ($order['medication_id']) {
				$inventory = $db->table('pharmacy_inventory')
					->where('medication_id', $order['medication_id'])
					->get()
					->getRowArray();
			}

			if (!$inventory && $db->tableExists('pharmacy_inventory')) {
				$inventory = $db->table('pharmacy_inventory')
					->where('name', $order['medicine_name'])
					->get()
					->getRowArray();
			}

			$previousStock = $inventory ? (int)($inventory['stock_quantity'] ?? 0) : 0;
			$newStock = $previousStock + $order['quantity_ordered'];

			// Update or create inventory
			if ($inventory) {
				$db->table('pharmacy_inventory')
					->where('id', $inventory['id'])
					->update([
						'stock_quantity' => $newStock,
						'updated_at' => date('Y-m-d H:i:s'),
					]);
			} else {
				$db->table('pharmacy_inventory')->insert([
					'medication_id' => $order['medication_id'],
					'name' => $order['medicine_name'],
					'stock_quantity' => $newStock,
					'reorder_level' => 10,
					'category' => 'General',
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				]);
			}

			// Create stock movement log
			$stockMovementModel->insert([
				'medication_id' => $order['medication_id'],
				'medicine_name' => $order['medicine_name'],
				'movement_type' => 'add',
				'quantity_change' => $order['quantity_ordered'],
				'previous_stock' => $previousStock,
				'new_stock' => $newStock,
				'action_by' => session()->get('user_id'),
				'notes' => 'Stock added from order ' . $order['order_number'] . ($order['reference'] ? ' (Ref: ' . $order['reference'] . ')' : ''),
			]);
		}

		if ($orderModel->update($orderId, $updateData)) {
			return $this->response->setJSON(['success' => true, 'message' => 'Order status updated successfully']);
		}

		return $this->response->setJSON(['success' => false, 'message' => 'Failed to update order status']);
	}

	public function reports()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$prescriptionModel = new PrescriptionModel();
		$db = \Config\Database::connect();

		// Get report filters
		$reportType = $this->request->getGet('type') ?? 'daily';
		$dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
		$dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

		// Daily/Monthly dispensing report
		$dispensingReport = [];
		if ($reportType === 'daily' || $reportType === 'monthly') {
			// Include both 'dispensed' and 'completed' statuses (completed = marked as given by nurse)
			$builder = $prescriptionModel
				->whereIn('status', ['dispensed', 'completed'])
				->where('DATE(updated_at) >=', $dateFrom)
				->where('DATE(updated_at) <=', $dateTo);
			
			$dispensingReport = $builder->orderBy('updated_at', 'DESC')->findAll();
			
			// Join with patients and doctors to get names
			if (!empty($dispensingReport)) {
				$db = \Config\Database::connect();
				foreach ($dispensingReport as &$rx) {
					// Get patient name
					$patient = $db->table('patients')->where('id', $rx['patient_id'])->get()->getRowArray();
					$rx['patient_name'] = $patient['full_name'] ?? 'N/A';
					
					// Get doctor name
					$doctor = $db->table('users')->where('id', $rx['doctor_id'])->get()->getRowArray();
					$rx['doctor_name'] = $doctor['name'] ?? 'N/A';
				}
				unset($rx);
			}
		}

		// Expiring medicines report
		$expiringMedicines = [];
		if ($db->tableExists('pharmacy_inventory')) {
			$expiringMedicines = $db->table('pharmacy_inventory')
				->where('expiration_date >=', date('Y-m-d'))
				->where('expiration_date <=', date('Y-m-d', strtotime('+90 days')))
				->orderBy('expiration_date', 'ASC')
				->get()
				->getResultArray();
		}

		$data = [
			'title' => 'Reports - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
			'reportType' => $reportType,
			'dateFrom' => $dateFrom,
			'dateTo' => $dateTo,
			'dispensingReport' => $dispensingReport,
			'expiringMedicines' => $expiringMedicines,
		];

		return view('pharmacy/reports', $data);
	}

	public function saveInventory()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		$medicationId = $this->request->getPost('medication_id');
		$medicineId = $this->request->getPost('medicine_id'); // This indicates if it's an edit
		$medicineName = $this->request->getPost('name');
		$stockQuantity = (int)$this->request->getPost('stock_quantity') ?? 0;
		$reorderLevel = (int)$this->request->getPost('reorder_level') ?? 10;
		$category = $this->request->getPost('category') ?? 'General';
		$expirationDate = $this->request->getPost('expiration_date') ?: null;
		$notes = $this->request->getPost('notes') ?: null;

		if (empty($medicineName)) {
			return $this->response->setJSON(['success' => false, 'message' => 'Medicine name is required']);
		}

		$db = \Config\Database::connect();
		
		if (!$db->tableExists('pharmacy_inventory')) {
			return $this->response->setJSON(['success' => false, 'message' => 'Inventory table does not exist']);
		}

		// Find medication by name if medication_id not provided
		if (empty($medicationId) && !empty($medicineName)) {
			$medicationModel = new MedicationModel();
			$medication = $medicationModel->where('name', $medicineName)->first();
			if ($medication) {
				$medicationId = $medication['id'];
			}
		}

		// Check if inventory record exists
		$existing = $db->table('pharmacy_inventory')
			->where('medication_id', $medicationId)
			->orWhere('name', $medicineName)
			->get()
			->getRowArray();

		if ($existing) {
			// Update existing record - DO NOT update stock_quantity when editing
			$updateData = [
				'reorder_level' => $reorderLevel,
				'category' => $category,
				'expiration_date' => $expirationDate,
				'updated_at' => date('Y-m-d H:i:s'),
			];
			
			// Only update stock_quantity if it's a new record (not editing)
			if (empty($medicineId)) {
				$updateData['stock_quantity'] = $stockQuantity;
			}
			
			$db->table('pharmacy_inventory')
				->where('id', $existing['id'])
				->update($updateData);
		} else {
			// Create new inventory record
			$db->table('pharmacy_inventory')->insert([
				'medication_id' => $medicationId,
				'name' => $medicineName,
				'stock_quantity' => $stockQuantity,
				'reorder_level' => $reorderLevel,
				'category' => $category,
				'expiration_date' => $expirationDate,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			]);
		}

		return $this->response->setJSON(['success' => true, 'message' => 'Inventory saved successfully']);
	}

	public function getInventory($medicationId)
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		$db = \Config\Database::connect();
		$medicationModel = new MedicationModel();
		
		$medication = $medicationModel->find($medicationId);
		if (!$medication) {
			return $this->response->setJSON(['success' => false, 'message' => 'Medication not found']);
		}

		$inventory = null;
		if ($db->tableExists('pharmacy_inventory')) {
			$inventory = $db->table('pharmacy_inventory')
				->where('medication_id', $medicationId)
				->orWhere('name', $medication['name'])
				->get()
				->getRowArray();
		}

		$data = [
			'success' => true,
			'medication' => $medication,
			'inventory' => $inventory ?: [
				'stock_quantity' => 0,
				'reorder_level' => 10,
				'category' => 'General',
				'expiration_date' => null,
			],
		];

		return $this->response->setJSON($data);
	}

	public function adjustStock()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$this->response->setContentType('application/json');

		$medicationId = $this->request->getPost('medication_id');
		$adjustmentType = $this->request->getPost('adjustment_type'); // 'add', 'set', or 'remove_expired'
		$quantity = (int)$this->request->getPost('quantity') ?? 0;
		$notes = $this->request->getPost('notes') ?: '';

		if (empty($medicationId) || $quantity <= 0) {
			return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters']);
		}

		$db = \Config\Database::connect();
		$medicationModel = new MedicationModel();
		
		$medication = $medicationModel->find($medicationId);
		if (!$medication) {
			return $this->response->setJSON(['success' => false, 'message' => 'Medication not found']);
		}

		if (!$db->tableExists('pharmacy_inventory')) {
			return $this->response->setJSON(['success' => false, 'message' => 'Inventory table does not exist']);
		}

		// Find or create inventory record
		$inventory = $db->table('pharmacy_inventory')
			->where('medication_id', $medicationId)
			->orWhere('name', $medication['name'])
			->get()
			->getRowArray();

		$previousStock = $inventory ? (int)($inventory['stock_quantity'] ?? 0) : 0;
		
		if ($adjustmentType === 'add') {
			$newStock = $previousStock + $quantity;
		} elseif ($adjustmentType === 'remove_expired') {
			// Remove expired medicines from stock
			$newStock = max(0, $previousStock - $quantity);
			if (empty($notes)) {
				$notes = 'Removed expired medicines from stock';
			}
		} else {
			$newStock = $quantity; // Set to specific value
		}
		
		// Validate that we don't go below 0
		if ($newStock < 0) {
			$newStock = 0;
		}

		if ($inventory) {
			// Update existing
			$db->table('pharmacy_inventory')
				->where('id', $inventory['id'])
				->update([
					'stock_quantity' => $newStock,
					'updated_at' => date('Y-m-d H:i:s'),
				]);
		} else {
			// Create new
			$db->table('pharmacy_inventory')->insert([
				'medication_id' => $medicationId,
				'name' => $medication['name'],
				'stock_quantity' => $newStock,
				'reorder_level' => 10,
				'category' => 'General',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			]);
		}

		// Log stock movement
		if ($db->tableExists('pharmacy_stock_movements')) {
			$quantityChange = 0;
			if ($adjustmentType === 'add') {
				$quantityChange = $quantity;
			} elseif ($adjustmentType === 'remove_expired') {
				$quantityChange = -$quantity; // Negative for removal
			} else {
				$quantityChange = $newStock - $previousStock;
			}
			
			$movementType = 'adjust';
			if ($adjustmentType === 'add') {
				$movementType = 'add';
			} elseif ($adjustmentType === 'remove_expired') {
				$movementType = 'expired_removal';
			}
			
			$movementNotes = $notes;
			if (empty($movementNotes)) {
				if ($adjustmentType === 'add') {
					$movementNotes = 'Stock added';
				} elseif ($adjustmentType === 'remove_expired') {
					$movementNotes = 'Expired medicines removed from stock';
				} else {
					$movementNotes = 'Stock adjusted';
				}
			}
			
			$db->table('pharmacy_stock_movements')->insert([
				'medication_id' => $medicationId,
				'medicine_name' => $medication['name'],
				'movement_type' => $movementType,
				'quantity_change' => $quantityChange,
				'previous_stock' => $previousStock,
				'new_stock' => $newStock,
				'action_by' => session()->get('user_id'),
				'notes' => $movementNotes,
				'created_at' => date('Y-m-d H:i:s'),
			]);
		}

		return $this->response->setJSON([
			'success' => true,
			'message' => 'Stock adjusted successfully',
			'previous_stock' => $previousStock,
			'new_stock' => $newStock,
		]);
	}

	public function settings()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$model = new SettingModel();
		$defaults = [
			'pharmacy_reorder_level'        => '15',
			'pharmacy_expiry_warning'       => '60',
			'pharmacy_auto_restock'         => '0',
			'pharmacy_supplier_email'       => 'purchasing@hospital.local',
			'pharmacy_dispense_doublecheck' => '1',
		];
		$settings = array_merge($defaults, $model->getAllAsMap());

		$data = [
			'title'     => 'Pharmacy Settings - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
			'pageTitle' => 'Settings',
			'settings'  => $settings,
		];

		return view('pharmacy/settings', $data);
	}

	public function saveSettings()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$model = new SettingModel();
		$post = $this->request->getPost();
		$keys = [
			'pharmacy_reorder_level',
			'pharmacy_expiry_warning',
			'pharmacy_auto_restock',
			'pharmacy_supplier_email',
			'pharmacy_dispense_doublecheck',
		];

		foreach ($keys as $key) {
			$model->setValue($key, (string)($post[$key] ?? ''), 'pharmacy');
		}

		return redirect()->to('/pharmacy/settings')->with('success', 'Settings saved successfully.');
	}
}
