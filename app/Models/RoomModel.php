<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomModel extends Model
{
    protected $table            = 'rooms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'room_number',
        'room_type',
        'floor',
        'capacity',
        'current_occupancy',
        'doctor_id',
        'specialization',
        'room_price',
        'is_available',
    ];

    public function getAvailableRooms($roomType = null)
    {
        $builder = $this->builder();
        
        if ($roomType) {
            $builder->where('room_type', $roomType);
        }
        
        return $builder->where('is_available', true)
                      ->where('current_occupancy <', 'capacity', false)
                      ->orderBy('room_number', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    public function getInpatientRooms()
    {
        return $this->getAvailableRooms('inpatient');
    }

    public function getOutpatientRooms()
    {
        return $this->getAvailableRooms('outpatient');
    }

    /**
     * Get rooms filtered by patient age
     * For Neonate (0-28 days) → NICU / Nursery
     * For Pediatric (1 month to 12 years) → Pedia
     * For 13 years and older → Exclude NICU and Pedia rooms
     */
    public function getRoomsByPatientAge($ageInDays, $roomType = 'inpatient')
    {
        $builder = $this->builder();
        $builder->where('room_type', $roomType);
        $builder->where('is_available', true);
        $builder->where('current_occupancy <', 'capacity', false);

        // Neonate: 0-28 days → NICU / Nursery
        if ($ageInDays >= 0 && $ageInDays <= 28) {
            $builder->where('specialization', 'NICU / Nursery');
        }
        // Pediatric: 1 month (29 days) to 12 years (4380 days) → Pedia
        else if ($ageInDays >= 29 && $ageInDays <= 4380) {
            $builder->where('specialization', 'Pedia');
        }
        // For 13 years and older (4381+ days) → Exclude NICU and Pedia rooms
        else if ($ageInDays >= 4381) {
            $builder->whereNotIn('specialization', ['NICU / Nursery', 'Pedia']);
        }
        // For other ages (negative or invalid), return all inpatient rooms

        return $builder->orderBy('room_number', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Get rooms filtered by appointment type
     * Maps appointment types to room specializations
     */
    public function getRoomsByAppointmentType($appointmentType, $roomType = 'outpatient')
    {
        // Map appointment types to room specializations (based on actual database values)
        $specializationMap = [
            'consultation' => ['General Medicine', 'Cardiology', 'Pediatrics', 'Consultation', 'General Practice', 'Family Medicine'],
            'follow-up' => ['General Medicine', 'Cardiology', 'Pediatrics', 'Consultation', 'General Practice', 'Family Medicine'],
            'procedure' => ['Procedure Room', 'Surgery', 'Operating Room', 'General Medicine', 'Cardiology', 'Pediatrics'],
            'laboratory_test' => ['Laboratory', 'Lab', 'Clinical Laboratory', 'Pathology'],
        ];

        $builder = $this->builder();
        $builder->where('room_type', $roomType);
        $builder->where('is_available', true);
        $builder->where('current_occupancy <', 'capacity', false);

        // If appointment type has specific specializations, filter by them
        if (isset($specializationMap[$appointmentType])) {
            $specializations = $specializationMap[$appointmentType];
            $builder->whereIn('specialization', $specializations);
            
            // Get filtered results
            $filteredRooms = $builder->orderBy('room_number', 'ASC')
                                   ->get()
                                   ->getResultArray();
            
            // If no rooms found with specific specialization, fallback to all outpatient rooms
            if (empty($filteredRooms) && in_array($appointmentType, ['consultation', 'follow-up', 'procedure'])) {
                // Fallback: return all available outpatient rooms
                return $this->getAvailableRooms($roomType);
            }
            
            return $filteredRooms;
        } else {
            // For unknown appointment types, return all outpatient rooms
            return $this->getAvailableRooms($roomType);
        }
    }

    public function updateOccupancy($roomId, $increment = true)
    {
        $room = $this->find($roomId);
        if (!$room) {
            return false;
        }

        $newOccupancy = $increment 
            ? $room['current_occupancy'] + 1 
            : max(0, $room['current_occupancy'] - 1);

        return $this->update($roomId, [
            'current_occupancy' => $newOccupancy,
            'is_available' => $newOccupancy < $room['capacity']
        ]);
    }
}
