<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\RoomModel;

class Rooms extends Controller
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $roomModel = new RoomModel();
        $db = \Config\Database::connect();

        // Get all rooms EXCEPT outpatient rooms (outpatients don't need rooms)
        $rooms = $roomModel->where('room_type !=', 'outpatient')
            ->orderBy('room_type', 'ASC')
            ->orderBy('floor', 'ASC')
            ->orderBy('room_number', 'ASC')
            ->findAll();

        // Get active admissions (status = 'Admitted') grouped by room_id
        $activeAdmissions = $db->table('admissions a')
            ->select('a.room_id, COUNT(*) as occupied_count, GROUP_CONCAT(p.full_name SEPARATOR ", ") as patient_names')
            ->join('patients p', 'p.id = a.patient_id', 'left')
            ->where('a.status', 'Admitted')
            ->where('a.room_id IS NOT NULL', null, false)
            ->groupBy('a.room_id')
            ->get()
            ->getResultArray();

        // Get active appointments with room assignments (for consultations)
        $activeAppointments = $db->table('appointments a')
            ->select('a.room_id, COUNT(*) as appointment_count, GROUP_CONCAT(p.full_name SEPARATOR ", ") as patient_names')
            ->join('patients p', 'p.id = a.patient_id', 'left')
            ->whereIn('a.status', ['scheduled', 'confirmed'])
            ->where('a.room_id IS NOT NULL', null, false)
            ->groupBy('a.room_id')
            ->get()
            ->getResultArray();

        // Create a map of room_id => occupancy info
        $roomOccupancy = [];
        
        // Process admissions
        foreach ($activeAdmissions as $admission) {
            $roomId = $admission['room_id'];
            if (!isset($roomOccupancy[$roomId])) {
                $roomOccupancy[$roomId] = [
                    'count' => 0,
                    'patients' => []
                ];
            }
            $roomOccupancy[$roomId]['count'] += (int)$admission['occupied_count'];
            if (!empty($admission['patient_names'])) {
                $patientList = explode(', ', $admission['patient_names']);
                $roomOccupancy[$roomId]['patients'] = array_merge(
                    $roomOccupancy[$roomId]['patients'],
                    $patientList
                );
            }
        }
        
        // Process appointments (add to existing or create new)
        foreach ($activeAppointments as $appointment) {
            $roomId = $appointment['room_id'];
            if (!isset($roomOccupancy[$roomId])) {
                $roomOccupancy[$roomId] = [
                    'count' => 0,
                    'patients' => []
                ];
            }
            $roomOccupancy[$roomId]['count'] += (int)$appointment['appointment_count'];
            if (!empty($appointment['patient_names'])) {
                $patientList = explode(', ', $appointment['patient_names']);
                $roomOccupancy[$roomId]['patients'] = array_merge(
                    $roomOccupancy[$roomId]['patients'],
                    $patientList
                );
            }
        }
        
        // Convert patient arrays back to comma-separated strings and remove duplicates
        foreach ($roomOccupancy as $roomId => &$info) {
            $info['patients'] = implode(', ', array_unique($info['patients']));
        }
        unset($info);

        // Calculate statistics and add occupancy info to rooms
        $stats = [
            'total_rooms' => count($rooms),
            'available_rooms' => 0,
            'occupied_rooms' => 0,
            'total_beds' => 0,
            'occupied_beds' => 0,
            'available_beds' => 0,
        ];

        foreach ($rooms as &$room) {
            $roomId = $room['id'];
            $capacity = (int)($room['capacity'] ?? 0);
            
            // Get actual occupancy from admissions table
            $actualOccupancy = isset($roomOccupancy[$roomId]) ? $roomOccupancy[$roomId]['count'] : 0;
            $room['actual_occupancy'] = $actualOccupancy;
            $room['occupied_patients'] = isset($roomOccupancy[$roomId]) ? $roomOccupancy[$roomId]['patients'] : '';
            
            // Use actual occupancy instead of current_occupancy field
            $occupancy = $actualOccupancy;
            $isAvailable = (bool)($room['is_available'] ?? true);
            $hasSpace = $occupancy < $capacity;

            $stats['total_beds'] += $capacity;
            $stats['occupied_beds'] += $occupancy;
            $stats['available_beds'] += ($capacity - $occupancy);

            if ($isAvailable && $hasSpace) {
                $stats['available_rooms']++;
            } else {
                $stats['occupied_rooms']++;
            }
        }
        unset($room); // Break reference

        // Group rooms by type (exclude outpatient rooms)
        $roomsByType = [];
        foreach ($rooms as $room) {
            $type = $room['room_type'] ?? 'other';
            // Skip outpatient rooms
            if (strtolower($type) === 'outpatient') {
                continue;
            }
            if (!isset($roomsByType[$type])) {
                $roomsByType[$type] = [];
            }
            $roomsByType[$type][] = $room;
        }

        $data = [
            'pageTitle' => 'Rooms Management',
            'rooms' => $rooms,
            'roomsByType' => $roomsByType,
            'stats' => $stats,
        ];

        return view('admin/rooms', $data);
    }

    public function details($roomId)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');

        try {
            $roomModel = new RoomModel();
            $room = $roomModel->find($roomId);

            if (!$room) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Room not found.'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'room' => $room
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting room details: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting room details: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleAvailability($roomId = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->response->setContentType('application/json');

        if (!$roomId) {
            $roomId = $this->request->getPost('room_id') ?? $this->request->getJSON(true)['room_id'] ?? null;
        }

        if (!$roomId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Room ID is required']);
        }

        try {
            $roomModel = new RoomModel();
            $room = $roomModel->find($roomId);

            if (!$room) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Room not found.'
                ]);
            }

            // Toggle availability
            $newAvailability = !((bool)($room['is_available'] ?? true));
            
            $updateData = [
                'is_available' => $newAvailability
            ];

            if ($roomModel->update($roomId, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Room availability updated successfully',
                    'is_available' => $newAvailability
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update room availability'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error toggling room availability: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating room availability: ' . $e->getMessage()
            ]);
        }
    }
}
