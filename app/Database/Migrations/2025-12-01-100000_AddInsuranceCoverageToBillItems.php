<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInsuranceCoverageToBillItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('bill_items', [
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Item category for insurance mapping (room, lab, medication, professional, procedure, nursing, other)'
            ],
            'insurance_coverage_percent' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00,
                'null' => true,
                'comment' => 'Insurance coverage percentage for this item category'
            ],
            'insurance_discount_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'null' => true,
                'comment' => 'Discount amount from insurance for this item'
            ],
            'patient_pays_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Amount patient pays after insurance discount'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('bill_items', ['category', 'insurance_coverage_percent', 'insurance_discount_amount', 'patient_pays_amount']);
    }
}

