<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Simple Inpatient Rooms
            ['room_number' => 'Private-101', 'room_type' => 'inpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Private Room', 'is_available' => true],
            ['room_number' => 'Private-102', 'room_type' => 'inpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Private Room', 'is_available' => true],
            ['room_number' => 'Semi-201', 'room_type' => 'inpatient', 'floor' => '2', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Semi-Private', 'is_available' => true],
            ['room_number' => 'Semi-202', 'room_type' => 'inpatient', 'floor' => '2', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Semi-Private', 'is_available' => true],
            ['room_number' => 'Ward-301', 'room_type' => 'inpatient', 'floor' => '3', 'capacity' => 4, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Ward', 'is_available' => true],
            ['room_number' => 'ICU-401', 'room_type' => 'inpatient', 'floor' => '4', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'ICU', 'is_available' => true],
            
            // Consultation Rooms (5 rooms)
            ['room_number' => 'CON-001', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Medicine', 'is_available' => true],
            ['room_number' => 'CON-002', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Medicine', 'is_available' => true],
            ['room_number' => 'CON-003', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Consultation', 'is_available' => true],
            ['room_number' => 'CON-004', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Practice', 'is_available' => true],
            ['room_number' => 'CON-005', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Family Medicine', 'is_available' => true],
            
            // Follow-up Rooms (5 rooms) - same as consultation
            ['room_number' => 'FU-001', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Medicine', 'is_available' => true],
            ['room_number' => 'FU-002', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Consultation', 'is_available' => true],
            ['room_number' => 'FU-003', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Practice', 'is_available' => true],
            ['room_number' => 'FU-004', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Family Medicine', 'is_available' => true],
            ['room_number' => 'FU-005', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Medicine', 'is_available' => true],
            
            // Procedure Rooms (5 rooms)
            ['room_number' => 'PROC-001', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Procedure Room', 'is_available' => true],
            ['room_number' => 'PROC-002', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Procedure Room', 'is_available' => true],
            ['room_number' => 'PROC-003', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Surgery', 'is_available' => true],
            ['room_number' => 'PROC-004', 'room_type' => 'outpatient', 'floor' => '4', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Operating Room', 'is_available' => true],
            ['room_number' => 'PROC-005', 'room_type' => 'outpatient', 'floor' => '4', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Procedure Room', 'is_available' => true],
            
            // Laboratory Test Rooms (5 rooms)
            ['room_number' => 'LAB-001', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Laboratory', 'is_available' => true],
            ['room_number' => 'LAB-002', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Lab', 'is_available' => true],
            ['room_number' => 'LAB-003', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Clinical Laboratory', 'is_available' => true],
            ['room_number' => 'LAB-004', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Pathology', 'is_available' => true],
            ['room_number' => 'LAB-005', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Laboratory', 'is_available' => true],
            
            // Imaging / X-ray / Ultrasound Rooms (5 rooms)
            ['room_number' => 'IMG-001', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Radiology', 'is_available' => true],
            ['room_number' => 'IMG-002', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'X-Ray', 'is_available' => true],
            ['room_number' => 'IMG-003', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Imaging', 'is_available' => true],
            ['room_number' => 'IMG-004', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Diagnostic Imaging', 'is_available' => true],
            ['room_number' => 'IMG-005', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Ultrasound', 'is_available' => true],
        ];

        $this->db->table('rooms')->insertBatch($data);
    }
}
