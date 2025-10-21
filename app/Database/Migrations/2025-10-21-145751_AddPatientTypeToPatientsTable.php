<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPatientTypeToPatientsTable extends Migration
{
    public function up()
    {
        $fields = [
            'patient_type' => [
                'type' => 'ENUM',
                'constraint' => ['outpatient', 'inpatient'],
                'default' => 'outpatient',
                'after' => 'concern'
            ],
            'admission_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'patient_type'
            ],
            'discharge_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'admission_date'
            ],
            'room_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'discharge_date'
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'discharged', 'transferred'],
                'default' => 'active',
                'after' => 'room_number'
            ]
        ];

        $this->forge->addColumn('patients', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('patients', ['patient_type', 'admission_date', 'discharge_date', 'room_number', 'status']);
    }
}
