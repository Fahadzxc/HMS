<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table            = 'patients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'patient_id',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'gender',
        'date_of_birth',
        'age',
        'contact',
        'email',
        'address',
        'address_city',
        'address_barangay',
        'address_street',
        'blood_type',
        'allergies',
        'emergency_name',
        'emergency_contact',
        'relationship',
        'status',
        'patient_type',
        'admission_date',
        'discharge_date',
        'room_number',
        'concern',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}


