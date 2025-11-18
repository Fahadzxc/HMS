<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScheduleDateToDoctorSchedules extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check if column already exists
        if (!$db->fieldExists('schedule_date', 'doctor_schedules')) {
            $this->forge->addColumn('doctor_schedules', [
                'schedule_date' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'day_of_week',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('schedule_date', 'doctor_schedules')) {
            $this->forge->dropColumn('doctor_schedules', 'schedule_date');
        }
    }
}
