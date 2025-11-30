<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPriceToDispenseLogs extends Migration
{
    public function up()
    {
        // Add price columns to pharmacy_dispense_logs table if it doesn't exist
        if ($this->db->tableExists('pharmacy_dispense_logs')) {
            $fields = $this->db->getFieldData('pharmacy_dispense_logs');
            $hasUnitPrice = false;
            $hasTotalPrice = false;
            
            foreach ($fields as $field) {
                if ($field->name === 'unit_price') {
                    $hasUnitPrice = true;
                }
                if ($field->name === 'total_price') {
                    $hasTotalPrice = true;
                }
            }
            
            if (!$hasUnitPrice) {
                $this->forge->addColumn('pharmacy_dispense_logs', [
                    'unit_price' => [
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => 0.00,
                        'null' => true,
                        'after' => 'quantity',
                        'comment' => 'Price per unit (doubled from inventory price)'
                    ],
                ]);
            }
            
            if (!$hasTotalPrice) {
                $this->forge->addColumn('pharmacy_dispense_logs', [
                    'total_price' => [
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => 0.00,
                        'null' => true,
                        'after' => 'unit_price',
                        'comment' => 'Total price (unit_price × quantity)'
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        // Remove price columns from pharmacy_dispense_logs
        if ($this->db->tableExists('pharmacy_dispense_logs')) {
            $this->forge->dropColumn('pharmacy_dispense_logs', 'unit_price');
            $this->forge->dropColumn('pharmacy_dispense_logs', 'total_price');
        }
    }
}

