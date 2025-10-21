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
            
            // Simple Outpatient Rooms
            ['room_number' => 'OPD-001', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => 1, 'specialization' => 'General Medicine', 'is_available' => true],
            ['room_number' => 'OPD-002', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => 2, 'specialization' => 'Cardiology', 'is_available' => true],
            ['room_number' => 'OPD-003', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => 3, 'specialization' => 'Pediatrics', 'is_available' => true],
            ['room_number' => 'Lab-001', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Laboratory', 'is_available' => true],
            ['room_number' => 'Pharmacy', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Pharmacy', 'is_available' => true],
        ];

        $this->db->table('rooms')->insertBatch($data);
    }
}
