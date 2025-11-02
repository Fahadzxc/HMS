<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTime;

class ReceptionModuleSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $this->seedPatient($db);
            $this->seedDoctorSchedule($db);
            $this->seedAppointment($db);
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        $db->transComplete();
    }

    private function seedPatient($db): void
    {
        $patientTable = $db->table('patients');
        $patientId = 'PT-' . date('Ymd') . '-001';

        $existing = $patientTable->where('patient_id', $patientId)->get()->getRowArray();
        if ($existing) {
            return;
        }

        $firstName = 'Juan';
        $middleName = 'Santos';
        $lastName = 'Dela Cruz';
        $dob = '1990-05-15';
        $age = (int) (new DateTime($dob))->diff(new DateTime())->y;

        $data = [
            'patient_id'        => $patientId,
            'first_name'        => $firstName,
            'middle_name'       => $middleName,
            'last_name'         => $lastName,
            'full_name'         => trim("{$firstName} {$middleName} {$lastName}"),
            'gender'            => 'Male',
            'date_of_birth'     => $dob,
            'age'               => $age,
            'contact'           => '09171234567',
            'email'             => 'juan.delacruz@example.com',
            'address'           => '123 Example Street, Barangay Sample, Kota',
            'address_city'      => 'General Santos',
            'address_barangay'  => 'San Isidro',
            'address_street'    => 'P. Rizal St.',
            'blood_type'        => 'O+',
            'allergies'         => null,
            'emergency_name'    => 'Maria Dela Cruz',
            'emergency_contact' => '09180000000',
            'relationship'      => 'Spouse',
            'status'            => 'active',
            'patient_type'      => 'outpatient',
            'concern'           => 'General consultation',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $patientTable->insert($data);
    }

    private function seedDoctorSchedule($db): void
    {
        $doctor = $db->table('users')->where('role', 'doctor')->orderBy('id', 'ASC')->get()->getRowArray();
        if (!$doctor) {
            return;
        }

        $scheduleTable = $db->table('doctor_schedules');
        $existing = $scheduleTable->where('doctor_id', $doctor['id'])->where('day_of_week', 'wednesday')->get()->getRowArray();
        if ($existing) {
            return;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            $scheduleTable->insert([
                'doctor_id'    => $doctor['id'],
                'day_of_week'  => $day,
                'start_time'   => '08:00:00',
                'end_time'     => '17:00:00',
                'is_available' => true,
                'notes'        => 'Default clinic hours',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedAppointment($db): void
    {
        $patient = $db->table('patients')->orderBy('id', 'ASC')->get()->getRowArray();
        $doctor  = $db->table('users')->where('role', 'doctor')->orderBy('id', 'ASC')->get()->getRowArray();
        $receptionist = $db->table('users')->where('role', 'receptionist')->orderBy('id', 'ASC')->get()->getRowArray();

        if (!$patient || !$doctor || !$receptionist) {
            return;
        }

        $appointmentDate = date('Y-m-d');
        $appointmentTime = '10:00:00';

        $appointmentTable = $db->table('appointments');
        $existing = $appointmentTable
            ->where('patient_id', $patient['id'])
            ->where('appointment_date', $appointmentDate)
            ->where('appointment_time', $appointmentTime)
            ->get()->getRowArray();

        if ($existing) {
            return;
        }

        $appointmentTable->insert([
            'patient_id'        => $patient['id'],
            'doctor_id'         => $doctor['id'],
            'appointment_date'  => $appointmentDate,
            'appointment_time'  => $appointmentTime,
            'appointment_type'  => 'consultation',
            'status'            => 'scheduled',
            'notes'             => 'Initial consultation created by seed.',
            'created_by'        => $receptionist['id'],
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
    }
}
