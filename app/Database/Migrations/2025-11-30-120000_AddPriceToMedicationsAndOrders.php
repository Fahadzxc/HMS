<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPriceToMedicationsAndOrders extends Migration
{
    public function up()
    {
        // Add price column to medications table if it doesn't exist
        if ($this->db->tableExists('medications')) {
            $fields = $this->db->getFieldData('medications');
            $hasPrice = false;
            foreach ($fields as $field) {
                if ($field->name === 'price') {
                    $hasPrice = true;
                    break;
                }
            }
            
            if (!$hasPrice) {
                $this->forge->addColumn('medications', [
                    'price' => [
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => 0.00,
                        'null' => true,
                        'after' => 'form',
                        'comment' => 'Price per unit'
                    ],
                ]);
            }
        }

        // Add price columns to medicine_orders table if it doesn't exist
        if ($this->db->tableExists('medicine_orders')) {
            $fields = $this->db->getFieldData('medicine_orders');
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
                $this->forge->addColumn('medicine_orders', [
                    'unit_price' => [
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => 0.00,
                        'null' => true,
                        'after' => 'quantity_ordered',
                        'comment' => 'Price per unit'
                    ],
                ]);
            }
            
            if (!$hasTotalPrice) {
                $this->forge->addColumn('medicine_orders', [
                    'total_price' => [
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => 0.00,
                        'null' => true,
                        'after' => 'unit_price',
                        'comment' => 'Total price (quantity × unit_price)'
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        // Remove price column from medications
        if ($this->db->tableExists('medications')) {
            $this->forge->dropColumn('medications', 'price');
        }

        // Remove price columns from medicine_orders
        if ($this->db->tableExists('medicine_orders')) {
            $this->forge->dropColumn('medicine_orders', 'unit_price');
            $this->forge->dropColumn('medicine_orders', 'total_price');
        }
    }
}

