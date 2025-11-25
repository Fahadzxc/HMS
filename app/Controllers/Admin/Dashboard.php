<?php
namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\PatientModel;
use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Models\BillingModel;
use App\Models\PaymentModel;
use App\Models\PrescriptionModel;
use App\Models\LabTestRequestModel;
use App\Models\LabTestResultModel;
use App\Models\PharmacyStockMovementModel;
use App\Models\MedicationModel;

class Dashboard extends Controller
{
    public function index()
    {
        try {
            $db = \Config\Database::connect();
            $patientModel = new PatientModel();
            $appointmentModel = new AppointmentModel();
            $userModel = new UserModel();
            $billingModel = new BillingModel();
            $paymentModel = new PaymentModel();
            $labRequestModel = new LabTestRequestModel();
            $labResultModel = new LabTestResultModel();
            $stockMovementModel = new PharmacyStockMovementModel();

            $today = date('Y-m-d');
            $thisWeekStart = date('Y-m-d', strtotime('monday this week'));
            $thisWeekEnd = date('Y-m-d', strtotime('sunday this week'));
            $thisMonthStart = date('Y-m-01');
            $thisMonthEnd = date('Y-m-t');

            // ========== KPI SUMMARY CARDS ==========
            
            // Total Patients
            $totalPatients = $patientModel->countAllResults();

            // Total Doctors
            $totalDoctors = $userModel->where('role', 'doctor')->countAllResults();

            // Total Nurses
            $totalNurses = $userModel->where('role', 'nurse')->countAllResults();

            // Today's Appointments
            $todayAppointments = $appointmentModel
                ->where('DATE(appointment_date)', $today)
                ->where('status !=', 'cancelled')
                ->countAllResults();

            // Pending Appointments
            $pendingAppointments = $appointmentModel
                ->where('status', 'pending')
                ->countAllResults();

            // Active Lab Tests
            $activeLabTests = $labRequestModel
                ->groupStart()
                ->where('status', 'pending')
                ->orWhere('status', 'in_progress')
                ->groupEnd()
                ->countAllResults();

            // Low Stock Medicines
            $lowStockMedicines = 0;
            if ($db->tableExists('pharmacy_inventory')) {
                $lowStockMedicines = $db->table('pharmacy_inventory')
                    ->where('stock_quantity <=', 'reorder_level')
                    ->where('stock_quantity >', 0)
                    ->countAllResults();
            }

            // Unpaid Bills
            $unpaidBills = 0;
            if ($db->tableExists('bills')) {
                $unpaidBills = $db->table('bills')
                    ->where('status', 'unpaid')
                    ->orWhere('status', 'partial')
                    ->countAllResults();
            }

            // ========== APPOINTMENTS OVERVIEW ==========
            
            // Upcoming appointments count
            $upcomingAppointments = $appointmentModel
                ->where('appointment_date >=', $today)
                ->where('status !=', 'cancelled')
                ->countAllResults();

            // Appointments scheduled this week
            $appointmentsThisWeek = $appointmentModel
                ->where('appointment_date >=', $thisWeekStart)
                ->where('appointment_date <=', $thisWeekEnd)
                ->where('status !=', 'cancelled')
                ->countAllResults();

            // Latest 5 appointments
            $latestAppointments = $appointmentModel
                ->select('appointments.*, patients.full_name as patient_name, users.name as doctor_name')
                ->join('patients', 'patients.id = appointments.patient_id', 'left')
                ->join('users', 'users.id = appointments.doctor_id', 'left')
                ->orderBy('appointments.appointment_date', 'DESC')
                ->orderBy('appointments.appointment_time', 'DESC')
                ->limit(5)
                ->findAll();

            // ========== LABORATORY OVERVIEW ==========
            
            // Pending tests
            $pendingLabTests = $labRequestModel
                ->where('status', 'pending')
                ->countAllResults();

            // Completed tests today
            $completedTestsToday = $labRequestModel
                ->where('status', 'completed')
                ->where('DATE(updated_at)', $today)
                ->countAllResults();

            // Critical results
            $criticalResults = 0;
            if ($db->tableExists('lab_test_results')) {
                try {
                    $criticalResults = $labResultModel
                        ->where('critical_flag', 1)
                        ->countAllResults();
                } catch (\Exception $e) {
                    log_message('error', 'Error fetching critical results: ' . $e->getMessage());
                    $criticalResults = 0;
                }
            }

            // Latest 3 lab requests
            $latestLabRequests = $labRequestModel
                ->select('lab_test_requests.*, patients.full_name as patient_name')
                ->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
                ->orderBy('lab_test_requests.created_at', 'DESC')
                ->limit(3)
                ->findAll();

            // ========== PHARMACY & INVENTORY OVERVIEW ==========
            
            // Low stock count
            $lowStockCount = $lowStockMedicines;

            // Expiring soon count (within 30 days)
            $expiringSoonCount = 0;
            if ($db->tableExists('pharmacy_inventory')) {
                $expiringSoonCount = $db->table('pharmacy_inventory')
                    ->where('expiration_date >=', $today)
                    ->where('expiration_date <=', date('Y-m-d', strtotime('+30 days')))
                    ->where('stock_quantity >', 0)
                    ->countAllResults();
            }

            // Stock movements today
            $stockMovementsToday = 0;
            if ($db->tableExists('pharmacy_stock_movements')) {
                $stockMovementsToday = $stockMovementModel
                    ->where('DATE(created_at)', $today)
                    ->countAllResults();
            }

            // Latest 3 stock activities
            $latestStockMovements = [];
            if ($db->tableExists('pharmacy_stock_movements')) {
                $latestStockMovements = $stockMovementModel
                    ->orderBy('created_at', 'DESC')
                    ->limit(3)
                    ->findAll();
            }

            // ========== BILLING & PAYMENTS OVERVIEW ==========
            
            // Total revenue this month
            $totalRevenueThisMonth = 0;
            if ($db->tableExists('payments')) {
                $revenueResult = $paymentModel
                    ->selectSum('amount')
                    ->where('DATE(created_at) >=', $thisMonthStart)
                    ->where('DATE(created_at) <=', $thisMonthEnd)
                    ->first();
                $totalRevenueThisMonth = (float)($revenueResult['amount'] ?? 0);
            }

            // Outstanding unpaid invoices
            $outstandingInvoices = $unpaidBills;

            // Latest 3 payments
            $latestPayments = [];
            if ($db->tableExists('payments')) {
                $latestPayments = $paymentModel
                    ->select('payments.*, patients.full_name as patient_name')
                    ->join('patients', 'patients.id = payments.patient_id', 'left')
                    ->orderBy('payments.created_at', 'DESC')
                    ->limit(3)
                    ->findAll();
            }

            // ========== RECENT ACTIVITY FEED ==========
            
            $activityFeed = [];

            // New patients added
            $recentPatients = $patientModel
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->findAll();
            foreach ($recentPatients as $patient) {
                $activityFeed[] = [
                    'description' => 'New patient added: ' . ($patient['full_name'] ?? 'N/A'),
                    'module' => 'Patients',
                    'timestamp' => $patient['created_at'],
                ];
            }

            // Appointments created/updated
            $recentAppts = $appointmentModel
                ->select('appointments.*, patients.full_name as patient_name')
                ->join('patients', 'patients.id = appointments.patient_id', 'left')
                ->orderBy('appointments.updated_at', 'DESC')
                ->limit(5)
                ->findAll();
            foreach ($recentAppts as $apt) {
                $activityFeed[] = [
                    'description' => 'Appointment ' . ($apt['status'] === 'pending' ? 'created' : 'updated') . ': ' . ($apt['patient_name'] ?? 'N/A'),
                    'module' => 'Appointments',
                    'timestamp' => $apt['updated_at'] ?? $apt['created_at'],
                ];
            }

            // Lab test completed
            if ($db->tableExists('lab_test_results')) {
                try {
                    $recentLabResults = $labResultModel
                        ->select('lab_test_results.*, patients.full_name as patient_name')
                        ->join('lab_test_requests', 'lab_test_requests.id = lab_test_results.request_id', 'left')
                        ->join('patients', 'patients.id = lab_test_requests.patient_id', 'left')
                        ->orderBy('lab_test_results.created_at', 'DESC')
                        ->limit(5)
                        ->findAll();
                    foreach ($recentLabResults as $result) {
                        $activityFeed[] = [
                            'description' => 'Lab test completed: ' . ($result['patient_name'] ?? 'N/A'),
                            'module' => 'Laboratory',
                            'timestamp' => $result['created_at'],
                        ];
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error fetching lab results: ' . $e->getMessage());
                }
            }

            // Medicine order received
            if ($db->tableExists('medicine_orders')) {
                $recentOrders = $db->table('medicine_orders')
                    ->where('status', 'delivered')
                    ->orderBy('delivered_at', 'DESC')
                    ->limit(5)
                    ->get()
                    ->getResultArray();
                foreach ($recentOrders as $order) {
                    $activityFeed[] = [
                        'description' => 'Medicine order received: ' . ($order['medicine_name'] ?? 'N/A'),
                        'module' => 'Pharmacy',
                        'timestamp' => $order['delivered_at'] ?? $order['created_at'],
                    ];
                }
            }

            // Billing transaction processed
            if ($db->tableExists('payments')) {
                $recentBilling = $paymentModel
                    ->select('payments.*, patients.full_name as patient_name')
                    ->join('patients', 'patients.id = payments.patient_id', 'left')
                    ->orderBy('payments.created_at', 'DESC')
                    ->limit(5)
                    ->findAll();
                foreach ($recentBilling as $payment) {
                    $activityFeed[] = [
                        'description' => 'Billing transaction processed: ' . ($payment['patient_name'] ?? 'N/A') . ' - ₱' . number_format($payment['amount'] ?? 0, 2),
                        'module' => 'Billing',
                        'timestamp' => $payment['created_at'],
                    ];
                }
            }

            // Sort activity feed by timestamp
            usort($activityFeed, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            $activityFeed = array_slice($activityFeed, 0, 15);

            $data = [
                'pageTitle' => 'Dashboard',
                'title' => 'Admin Dashboard - HMS',
                'user_role' => 'admin',
                'user_name' => session()->get('name'),
                // KPI Cards
                'totalPatients' => $totalPatients,
                'totalDoctors' => $totalDoctors,
                'totalNurses' => $totalNurses,
                'todayAppointments' => $todayAppointments,
                'pendingAppointments' => $pendingAppointments,
                'activeLabTests' => $activeLabTests,
                'lowStockMedicines' => $lowStockMedicines,
                'unpaidBills' => $unpaidBills,
                // Appointments Overview
                'upcomingAppointments' => $upcomingAppointments,
                'appointmentsThisWeek' => $appointmentsThisWeek,
                'latestAppointments' => $latestAppointments,
                // Laboratory Overview
                'pendingLabTests' => $pendingLabTests,
                'completedTestsToday' => $completedTestsToday,
                'criticalResults' => $criticalResults,
                'latestLabRequests' => $latestLabRequests,
                // Pharmacy Overview
                'lowStockCount' => $lowStockCount,
                'expiringSoonCount' => $expiringSoonCount,
                'stockMovementsToday' => $stockMovementsToday,
                'latestStockMovements' => $latestStockMovements,
                // Billing Overview
                'totalRevenueThisMonth' => $totalRevenueThisMonth,
                'outstandingInvoices' => $outstandingInvoices,
                'latestPayments' => $latestPayments,
                // Activity Feed
                'activityFeed' => $activityFeed,
            ];

            return view('auth/dashboard', $data);
        } catch (\Exception $e) {
            log_message('error', 'Admin Dashboard Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            // Return error view or redirect
            $data = [
                'pageTitle' => 'Dashboard Error',
                'title' => 'Admin Dashboard - HMS',
                'user_role' => 'admin',
                'user_name' => session()->get('name'),
                'error' => 'An error occurred while loading the dashboard. Please try again later.',
            ];
            
            return view('auth/dashboard', $data);
        }
    }
}
