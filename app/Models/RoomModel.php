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
