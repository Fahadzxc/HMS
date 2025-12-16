<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\DoctorModel;
use App\Models\BranchModel;
use App\Models\UserModel;
use App\Models\DoctorScheduleModel;
use App\Models\AppointmentModel;

class Doctors extends Controller
{
    protected DoctorModel $doctorModel;
    protected BranchModel $branchModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->doctorModel = new DoctorModel();
        $this->branchModel = new BranchModel();
        $this->userModel   = new UserModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        // Get doctors from users table with their patient assignments from BOTH appointments AND admissions
        $db = \Config\Database::connect();
        
        // Get all doctors first
        $doctors = $db->table('users')
            ->select('id, name, email, status, created_at')
            ->where('role', 'doctor')
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();
        
        // Then get counts for each doctor
        foreach ($doctors as &$doctor) {
            $doctorId = $doctor['id'];
            
            // Count unique patients from appointments
            $apptPatients = $db->query("SELECT COUNT(DISTINCT patient_id) as cnt FROM appointments WHERE doctor_id = ? AND status != 'cancelled'", [$doctorId])->getRow()->cnt ?? 0;
            
            // Count unique patients from admissions
            $admPatients = $db->query("SELECT COUNT(DISTINCT patient_id) as cnt FROM admissions WHERE doctor_id = ?", [$doctorId])->getRow()->cnt ?? 0;
            
            // Total unique patients (simple sum for now, may have overlap)
            $doctor['total_patients'] = $apptPatients + $admPatients;
            
            // Today's appointments
            $doctor['todays_appointments'] = $db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', date('Y-m-d'))
                ->where('status !=', 'cancelled')
                ->countAllResults();
            
            // Upcoming = future appointments + current admitted patients
            $upcomingAppts = $db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date >', date('Y-m-d'))
                ->where('status !=', 'cancelled')
                ->countAllResults();
            
            $currentAdmissions = $db->table('admissions')
                ->where('doctor_id', $doctorId)
                ->where('status', 'Admitted')
                ->countAllResults();
            
            $doctor['upcoming_appointments'] = $upcomingAppts + $currentAdmissions;
        }

        // Get recent appointments AND admissions for each doctor
        foreach ($doctors as &$doctor) {
            // Get appointments
            $appointments = $db->table('appointments a')
                ->select('a.id, a.patient_id, a.appointment_date as date, a.appointment_time as time, a.status, p.full_name as patient_name, r.room_number, "appointment" as source')
                ->join('patients p', 'p.id = a.patient_id', 'left')
                ->join('rooms r', 'r.id = a.room_id', 'left')
                ->where('a.doctor_id', $doctor['id'])
                ->where('a.status !=', 'cancelled')
                ->orderBy('a.appointment_date', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
            
            // Get admissions
            $admissions = $db->table('admissions adm')
                ->select('adm.id, adm.patient_id, adm.admission_date as date, NULL as time, adm.status, p.full_name as patient_name, r.room_number, "admission" as source')
                ->join('patients p', 'p.id = adm.patient_id', 'left')
                ->join('rooms r', 'r.id = adm.room_id', 'left')
                ->where('adm.doctor_id', $doctor['id'])
                ->orderBy('adm.admission_date', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
            
            // Merge and sort by date
            $combined = array_merge($appointments, $admissions);
            usort($combined, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            $doctor['recent_appointments'] = array_slice($combined, 0, 5);
        }

        $data = [
            'pageTitle' => 'Doctors',
            'title' => 'Doctors - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'doctors' => $doctors,
        ];

        return view('admin/doctor', $data);
    }

    public function schedule($doctorId = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        if (!$doctorId) {
            return redirect()->to('/admin/doctors')->with('error', 'Doctor ID is required');
        }

        $scheduleModel = new DoctorScheduleModel();
        $db = \Config\Database::connect();
        
        // Get doctor info
        $doctor = $db->table('users')
            ->where('id', $doctorId)
            ->where('role', 'doctor')
            ->get()->getRowArray();
        
        if (!$doctor) {
            return redirect()->to('/admin/doctors')->with('error', 'Doctor not found');
        }
        
        // Get current month and year from query params or use current
        $month = (int)($this->request->getGet('month') ?? date('n'));
        $year = (int)($this->request->getGet('year') ?? date('Y'));
        
        // Validate month and year
        if ($month < 1 || $month > 12) $month = (int)date('n');
        if ($year < 2020 || $year > 2100) $year = (int)date('Y');
        
        // Calculate calendar dates
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDay);
        
        // Get doctor's schedules for this month (date-specific and weekly)
        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);
        
        // Get date-specific schedules for this month
        $dateSpecificSchedules = $scheduleModel
            ->where('doctor_id', $doctorId)
            ->where('schedule_date >=', $monthStart)
            ->where('schedule_date <=', $monthEnd)
            ->findAll();
        
        // Get weekly schedules
        $weeklySchedule = $scheduleModel->getDoctorWeeklySchedule($doctorId);
        
        // Group schedules by date
        $scheduleByDate = [];
        foreach ($dateSpecificSchedules as $sched) {
            $date = $sched['schedule_date'];
            $scheduleByDate[$date][] = $sched;
        }
        
        // Group weekly schedules by day of week
        $scheduleByDay = [];
        foreach ($weeklySchedule as $sched) {
            if (empty($sched['schedule_date'])) {
                $day = $sched['day_of_week'];
                $scheduleByDay[$day][] = $sched;
            }
        }
        
        // Get appointments for this month
        $appointmentModel = new AppointmentModel();
        $appointments = $appointmentModel
            ->where('doctor_id', $doctorId)
            ->where('appointment_date >=', $monthStart)
            ->where('appointment_date <=', $monthEnd)
            ->where('status !=', 'cancelled')
            ->findAll();
        
        // Group appointments by date
        $appointmentsByDate = [];
        foreach ($appointments as $apt) {
            $date = $apt['appointment_date'];
            if (!isset($appointmentsByDate[$date])) {
                $appointmentsByDate[$date] = [];
            }
            $appointmentsByDate[$date][] = $apt;
        }
        
        // Calculate previous and next month
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        
        // Calculate start day of week for calendar
        $startDay = date('w', $firstDay);
        $startDay = ($startDay == 0) ? 6 : $startDay - 1; // Convert Sunday=0 to Monday=0
        
        $data = [
            'title' => 'Doctor Schedule - HMS',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'doctor' => $doctor,
            'schedule' => $weeklySchedule,
            'scheduleByDay' => $scheduleByDay,
            'scheduleByDate' => $scheduleByDate,
            'appointmentsByDate' => $appointmentsByDate,
            'currentMonth' => $month,
            'currentYear' => $year,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'daysInMonth' => $daysInMonth,
            'startDay' => $startDay,
            'monthName' => date('F', $firstDay),
        ];

        return view('admin/doctor_schedule', $data);
    }

    public function saveSchedule()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $method = strtolower($this->request->getMethod());
        
        if ($method !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $scheduleModel = new DoctorScheduleModel();
        $requestData = json_decode($this->request->getBody(), true);
        $schedules = $requestData['schedules'] ?? [];
        $action = $requestData['action'] ?? 'add';
        
        if (empty($schedules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No schedules provided']);
        }

        $doctorId = $schedules[0]['doctor_id'] ?? null;
        if (!$doctorId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Doctor ID is required']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if ($action === 'add') {
                // For adding new schedules, check if schedule already exists for each day
                foreach ($schedules as $schedule) {
                    $dayOfWeek = $schedule['day_of_week'] ?? '';
                    $startTime = $schedule['start_time'] ?? '';
                    $endTime = $schedule['end_time'] ?? '';
                    
                    if (empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
                        throw new \Exception('Missing required fields: day_of_week, start_time, or end_time');
                    }
                    
                    // Check if recurring schedule already exists for this day and time
                    $existing = $scheduleModel->where('doctor_id', $doctorId)
                                             ->where('day_of_week', $dayOfWeek)
                                             ->where('schedule_date IS NULL', null, false)
                                             ->where('start_time', $startTime)
                                             ->where('end_time', $endTime)
                                             ->first();
                    
                    if (!$existing) {
                        $data = [
                            'doctor_id' => $doctorId,
                            'day_of_week' => $dayOfWeek,
                            'schedule_date' => null,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'is_available' => $schedule['is_available'] ?? true,
                            'notes' => $schedule['notes'] ?? null
                        ];
                        
                        $insertId = $scheduleModel->insert($data);
                        if (!$insertId) {
                            $errors = $scheduleModel->errors();
                            $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown error';
                            throw new \Exception('Failed to insert schedule for ' . $dayOfWeek . ': ' . $errorMsg);
                        }
                    }
                }
            } else {
                // For replacing all schedules, delete existing recurring schedules first
                $scheduleModel->where('doctor_id', $doctorId)
                             ->where('schedule_date IS NULL', null, false)
                             ->delete();

                // Insert new recurring schedules
                foreach ($schedules as $schedule) {
                    $dayOfWeek = $schedule['day_of_week'] ?? '';
                    $startTime = $schedule['start_time'] ?? '';
                    $endTime = $schedule['end_time'] ?? '';
                    
                    if (empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
                        throw new \Exception('Missing required fields: day_of_week, start_time, or end_time');
                    }
                    
                    $data = [
                        'doctor_id' => $doctorId,
                        'day_of_week' => $dayOfWeek,
                        'schedule_date' => null,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'is_available' => $schedule['is_available'] ?? true,
                        'notes' => $schedule['notes'] ?? null
                    ];
                    
                    $insertId = $scheduleModel->insert($data);
                    if (!$insertId) {
                        $errors = $scheduleModel->errors();
                        $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown error';
                        throw new \Exception('Failed to insert schedule for ' . $dayOfWeek . ': ' . $errorMsg);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to save recurring schedule'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Recurring weekly schedule saved successfully. This will apply to the whole year.'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'saveSchedule error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteSchedule()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $scheduleId = $this->request->getPost('schedule_id');
        
        if (!$scheduleId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Schedule ID is required']);
        }

        $scheduleModel = new DoctorScheduleModel();
        $scheduleModel->delete($scheduleId);
        
        return $this->response->setJSON(['status' => 'success', 'message' => 'Schedule deleted successfully']);
    }

    public function getRecurringSchedules($doctorId = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if (!$doctorId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Doctor ID is required']);
        }

        $scheduleModel = new DoctorScheduleModel();
        $schedules = $scheduleModel
            ->where('doctor_id', $doctorId)
            ->where('schedule_date IS NULL', null, false)
            ->findAll();
        
        return $this->response->setJSON(['status' => 'success', 'schedules' => $schedules]);
    }
}
