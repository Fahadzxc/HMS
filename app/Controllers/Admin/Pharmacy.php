<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\MedicationModel;
use App\Models\PharmacyStockMovementModel;
use App\Models\MedicineOrderModel;

class Pharmacy extends Controller
{
    protected MedicationModel $medicationModel;
    protected PharmacyStockMovementModel $stockMovementModel;
    protected MedicineOrderModel $orderModel;

    public function __construct()
    {
        $this->medicationModel = new MedicationModel();
        $this->stockMovementModel = new PharmacyStockMovementModel();
        $this->orderModel = new MedicineOrderModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        // Get all medications
        $medications = $this->medicationModel->orderBy('name', 'ASC')->findAll();

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
                $stockMovements = $this->stockMovementModel->getAllWithUser();
                $stockMovements = array_slice($stockMovements, 0, 10);
            } catch (\Exception $e) {
                log_message('error', 'Error fetching stock movements: ' . $e->getMessage());
                $stockMovements = [];
            }
        }

        // Get all orders
        $orders = [];
        if ($db->tableExists('medicine_orders')) {
            $orders = $this->orderModel
                ->select('medicine_orders.*, users.name as received_by_name')
                ->join('users', 'users.id = medicine_orders.received_by', 'left')
                ->orderBy('medicine_orders.order_date', 'DESC')
                ->orderBy('medicine_orders.created_at', 'DESC')
                ->findAll();
        }

        $data = [
            'pageTitle' => 'Pharmacy & Inventory Dashboard',
            'title' => 'Pharmacy & Inventory - HMS',
            'user_role' => 'admin',
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
            'medications' => $medications,
        ];

        return view('admin/pharmacy/inventory', $data);
    }

    public function getMedicineDetails($medicationId)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');

        $db = \Config\Database::connect();
        $medication = $this->medicationModel->find($medicationId);

        if (!$medication) {
            return $this->response->setJSON(['success' => false, 'message' => 'Medication not found']);
        }

        // Get inventory
        $inventory = null;
        if ($db->tableExists('pharmacy_inventory')) {
            $inventory = $db->table('pharmacy_inventory')
                ->where('medication_id', $medicationId)
                ->orWhere('name', $medication['name'])
                ->get()
                ->getRowArray();
        }

        // Get stock history
        $stockHistory = [];
        if ($db->tableExists('pharmacy_stock_movements')) {
            try {
                $allMovements = $this->stockMovementModel->getAllWithUser();
                $stockHistory = array_filter($allMovements, function($movement) use ($medicationId, $medication) {
                    return ($movement['medication_id'] == $medicationId) || 
                           (strtolower(trim($movement['medicine_name'] ?? '')) === strtolower(trim($medication['name'] ?? '')));
                });
                $stockHistory = array_slice($stockHistory, 0, 50);
            } catch (\Exception $e) {
                log_message('error', 'Error fetching stock history: ' . $e->getMessage());
                $stockHistory = [];
            }
        }

        // Get orders for this medicine
        $orders = [];
        if ($db->tableExists('medicine_orders')) {
            try {
                $allOrders = $this->orderModel->getAllWithPharmacist();
                $orders = array_filter($allOrders, function($order) use ($medicationId, $medication) {
                    return ($order['medication_id'] == $medicationId) || 
                           (strtolower(trim($order['medicine_name'] ?? '')) === strtolower(trim($medication['name'] ?? '')));
                });
            } catch (\Exception $e) {
                log_message('error', 'Error fetching orders: ' . $e->getMessage());
                $orders = [];
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'medication' => $medication,
            'inventory' => $inventory,
            'stockHistory' => $stockHistory,
            'orders' => $orders,
        ]);
    }

    public function createOrder()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
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
}

