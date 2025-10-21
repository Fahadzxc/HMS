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
        'full_name',
        'date_of_birth',
        'gender',
        'blood_type',
        'contact',
        'email',
        'address',
        'concern',
        'patient_type',
        'admission_date',
        'discharge_date',
        'room_number',
        'status',
        'created_at',
    ];
}


