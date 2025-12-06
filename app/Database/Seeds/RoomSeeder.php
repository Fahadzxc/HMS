<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        echo "\n=== RoomSeeder Started ===\n";
        
        // Check if table exists first
        echo "Checking if 'rooms' table exists...\n";
        if (!$this->db->tableExists('rooms')) {
            echo "ERROR: 'rooms' table does not exist!\n";
            echo "Please run migrations first: php spark migrate\n";
            echo "=== RoomSeeder Failed ===\n\n";
            return;
        }
        echo "✓ Table exists\n";

        // Check if rooms already exist to prevent duplicates
        echo "Checking for existing rooms...\n";
        $existingRooms = $this->db->table('rooms')->countAllResults();
        if ($existingRooms > 0) {
            echo "Rooms already exist in database ($existingRooms rooms found). Skipping seeder to prevent duplicates.\n";
            echo "=== RoomSeeder Skipped ===\n\n";
            return;
        }
        echo "✓ No existing rooms found\n";

        echo "Preparing to insert rooms...\n";

        $data = [
            // Simple Inpatient Rooms
            ['room_number' => 'Private-101', 'room_type' => 'inpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Private Room', 'room_price' => 1500.00, 'is_available' => true],
            ['room_number' => 'Private-102', 'room_type' => 'inpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Private Room', 'room_price' => 1500.00, 'is_available' => true],
            ['room_number' => 'Semi-201', 'room_type' => 'inpatient', 'floor' => '2', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Semi-Private', 'room_price' => 800.00, 'is_available' => true],
            ['room_number' => 'Semi-202', 'room_type' => 'inpatient', 'floor' => '2', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Semi-Private', 'room_price' => 800.00, 'is_available' => true],
            ['room_number' => 'Ward-301', 'room_type' => 'inpatient', 'floor' => '3', 'capacity' => 4, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Ward', 'room_price' => 200.00, 'is_available' => true],
            ['room_number' => 'ICU-401', 'room_type' => 'inpatient', 'floor' => '4', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'ICU', 'room_price' => 3000.00, 'is_available' => true],
            
            // NICU / Nursery Rooms (5 rooms) - For Neonate (0-28 days)
            ['room_number' => 'NICU-501', 'room_type' => 'inpatient', 'floor' => '5', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'NICU / Nursery', 'room_price' => 2500.00, 'is_available' => true],
            ['room_number' => 'NICU-502', 'room_type' => 'inpatient', 'floor' => '5', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'NICU / Nursery', 'room_price' => 2500.00, 'is_available' => true],
            ['room_number' => 'NICU-503', 'room_type' => 'inpatient', 'floor' => '5', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'NICU / Nursery', 'room_price' => 2500.00, 'is_available' => true],
            ['room_number' => 'NICU-504', 'room_type' => 'inpatient', 'floor' => '5', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'NICU / Nursery', 'room_price' => 2500.00, 'is_available' => true],
            ['room_number' => 'NICU-505', 'room_type' => 'inpatient', 'floor' => '5', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'NICU / Nursery', 'room_price' => 2500.00, 'is_available' => true],
            
            // Pedia Rooms (5 rooms) - For Pediatric (1 month to 12 years)
            ['room_number' => 'PEDIA-601', 'room_type' => 'inpatient', 'floor' => '6', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Pedia', 'room_price' => 1200.00, 'is_available' => true],
            ['room_number' => 'PEDIA-602', 'room_type' => 'inpatient', 'floor' => '6', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Pedia', 'room_price' => 1200.00, 'is_available' => true],
            ['room_number' => 'PEDIA-603', 'room_type' => 'inpatient', 'floor' => '6', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Pedia', 'room_price' => 1200.00, 'is_available' => true],
            ['room_number' => 'PEDIA-604', 'room_type' => 'inpatient', 'floor' => '6', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Pedia', 'room_price' => 1200.00, 'is_available' => true],
            ['room_number' => 'PEDIA-605', 'room_type' => 'inpatient', 'floor' => '6', 'capacity' => 2, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Pedia', 'room_price' => 1200.00, 'is_available' => true],
            
            // Consultation Rooms (5 rooms)
            ['room_number' => 'CON-001', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Medicine', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'CON-002', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Medicine', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'CON-003', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Consultation', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'CON-004', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Practice', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'CON-005', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Family Medicine', 'room_price' => 0.00, 'is_available' => true],
            
            // Follow-up Rooms (5 rooms) - same as consultation
            ['room_number' => 'FU-001', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Medicine', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'FU-002', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Consultation', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'FU-003', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Practice', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'FU-004', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Family Medicine', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'FU-005', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'General Medicine', 'room_price' => 0.00, 'is_available' => true],
            
            // Procedure Rooms (5 rooms)
            ['room_number' => 'PROC-001', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Procedure Room', 'room_price' => 500.00, 'is_available' => true],
            ['room_number' => 'PROC-002', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Procedure Room', 'room_price' => 500.00, 'is_available' => true],
            ['room_number' => 'PROC-003', 'room_type' => 'outpatient', 'floor' => '3', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Surgery', 'room_price' => 1000.00, 'is_available' => true],
            ['room_number' => 'PROC-004', 'room_type' => 'outpatient', 'floor' => '4', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Operating Room', 'room_price' => 2000.00, 'is_available' => true],
            ['room_number' => 'PROC-005', 'room_type' => 'outpatient', 'floor' => '4', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Procedure Room', 'room_price' => 500.00, 'is_available' => true],
            
            // Laboratory Test Rooms (5 rooms)
            ['room_number' => 'LAB-001', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Laboratory', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'LAB-002', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Lab', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'LAB-003', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Clinical Laboratory', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'LAB-004', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Pathology', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'LAB-005', 'room_type' => 'outpatient', 'floor' => '2', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Laboratory', 'room_price' => 0.00, 'is_available' => true],
            
            // Imaging / X-ray / Ultrasound Rooms (5 rooms)
            ['room_number' => 'IMG-001', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Radiology', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'IMG-002', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'X-Ray', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'IMG-003', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Imaging', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'IMG-004', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Diagnostic Imaging', 'room_price' => 0.00, 'is_available' => true],
            ['room_number' => 'IMG-005', 'room_type' => 'outpatient', 'floor' => '1', 'capacity' => 1, 'current_occupancy' => 0, 'doctor_id' => null, 'specialization' => 'Ultrasound', 'room_price' => 0.00, 'is_available' => true],
        ];

        try {
            echo "Inserting " . count($data) . " rooms...\n";
            $this->db->table('rooms')->insertBatch($data);
            $insertedCount = count($data);
            echo "✓ Successfully inserted $insertedCount rooms into database!\n";
            echo "=== RoomSeeder Completed Successfully ===\n\n";
        } catch (\Exception $e) {
            echo "✗ ERROR inserting rooms: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
            echo "=== RoomSeeder Failed ===\n\n";
            throw $e;
        }
    }
}
