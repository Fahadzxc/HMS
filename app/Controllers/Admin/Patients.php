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
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get patients with their most recent doctor assignment from appointments
        $db = \Config\Database::connect();
        $builder = $db->table('patients p');
        $builder->select('p.*, 
                         u.name as assigned_doctor_name,
                         a.appointment_date as last_appointment_date,
                         a.status as appointment_status');
        $builder->join('(SELECT patient_id, doctor_id, appointment_date, status, 
                                ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY appointment_date DESC, created_at DESC) as rn
                         FROM appointments 
                         WHERE status != "cancelled") a', 'a.patient_id = p.id AND a.rn = 1', 'left');
        $builder->join('users u', 'u.id = a.doctor_id', 'left');
        $builder->orderBy('p.id', 'DESC');
        
        $patients = $builder->get()->getResultArray();
        
        // Debug: Log the first patient to see the data structure
        if (!empty($patients)) {
            log_message('info', 'First patient data: ' . json_encode($patients[0]));
        }

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
            'full_name'     => 'required|min_length[2]|max_length[255]',
            'date_of_birth' => 'required',
            'gender'        => 'required|in_list[Male,Female]',
            'blood_type'    => 'permit_empty|in_list[A+,A-,B+,B-,AB+,AB-,O+,O-]',
            'contact'       => 'required|regex_match[/^09[0-9]{2} [0-9]{3} [0-9]{4}$/]',
            'email'         => 'permit_empty|valid_email',
            'address'       => 'required|in_list[Lagao,Bula,San Isidro,Calumpang,Tambler,City Heights]',
            'concern'       => 'required|min_length[3]'
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        // Custom date validation
        $dateOfBirth = $request->getPost('date_of_birth');
        $dateValidation = $this->validateDateOfBirth($dateOfBirth);
        if ($dateValidation !== true) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => ['date_of_birth' => $dateValidation]]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('patients');

        // Convert date from MM/DD/YYYY to YYYY-MM-DD format
        $dateOfBirth = $request->getPost('date_of_birth');
        if (!empty($dateOfBirth) && strpos($dateOfBirth, '/') !== false) {
            $parts = explode('/', $dateOfBirth);
            if (count($parts) === 3) {
                // Ensure proper formatting: MM/DD/YYYY -> YYYY-MM-DD
                $month = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                $day = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                $year = $parts[2];
                $dateOfBirth = $year . '-' . $month . '-' . $day;
            }
        }

        $data = [
            'full_name'     => $request->getPost('full_name'),
            'date_of_birth' => $dateOfBirth,
            'gender'        => $request->getPost('gender'),
            'blood_type'    => $request->getPost('blood_type'),
            'contact'       => str_replace(' ', '', $request->getPost('contact')), // Remove spaces for storage
            'email'         => $request->getPost('email'),
            'address'       => $request->getPost('address'),
            'concern'       => $request->getPost('concern'),
            'created_at'    => date('Y-m-d H:i:s')
        ];

        $builder->insert($data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Patient added successfully']);
    }

    private function validateDateOfBirth($date)
    {
        if (empty($date)) {
            return 'Date of birth is required';
        }

        // Check if date contains slashes (MM/DD/YYYY format)
        if (strpos($date, '/') !== false) {
            $parts = explode('/', $date);
            if (count($parts) !== 3) {
                return 'Invalid date format. Use MM/DD/YYYY';
            }

            $month = (int)$parts[0];
            $day = (int)$parts[1];
            $year = (int)$parts[2];

            // Validate month (1-12)
            if ($month < 1 || $month > 12) {
                return 'Month must be between 01 and 12';
            }

            // Validate day (1-31)
            if ($day < 1 || $day > 31) {
                return 'Day must be between 01 and 31';
            }

            // Validate year (not future, reasonable range)
            $currentYear = (int)date('Y');
            if ($year > $currentYear || $year < 1900) {
                return 'Year must be between 1900 and ' . $currentYear;
            }

            // Check if date is valid (e.g., Feb 30 doesn't exist)
            if (!checkdate($month, $day, $year)) {
                return 'Invalid date. Please check month and day.';
            }

            // Check if date is in the future
            $inputDate = new DateTime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT));
            $today = new DateTime();
            if ($inputDate > $today) {
                return 'Date of birth cannot be in the future';
            }
        }

        return true;
    }
}


