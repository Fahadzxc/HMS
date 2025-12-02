<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateRoomPrices extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('rooms')) {
            // Update inpatient room prices
            $this->db->query("UPDATE rooms SET room_price = 1500.00 WHERE room_type = 'inpatient' AND specialization LIKE '%Private%' AND (room_price IS NULL OR room_price = 0)");
            $this->db->query("UPDATE rooms SET room_price = 800.00 WHERE room_type = 'inpatient' AND specialization LIKE '%Semi%' AND (room_price IS NULL OR room_price = 0)");
            $this->db->query("UPDATE rooms SET room_price = 200.00 WHERE room_type = 'inpatient' AND specialization LIKE '%Ward%' AND (room_price IS NULL OR room_price = 0)");
            $this->db->query("UPDATE rooms SET room_price = 3000.00 WHERE room_type = 'inpatient' AND specialization LIKE '%ICU%' AND (room_price IS NULL OR room_price = 0)");
            
            // Update outpatient procedure room prices
            $this->db->query("UPDATE rooms SET room_price = 500.00 WHERE room_type = 'outpatient' AND (specialization LIKE '%Procedure%' OR specialization LIKE '%Surgery%') AND (room_price IS NULL OR room_price = 0)");
            $this->db->query("UPDATE rooms SET room_price = 2000.00 WHERE room_type = 'outpatient' AND specialization LIKE '%Operating Room%' AND (room_price IS NULL OR room_price = 0)");
            
            // Set consultation, lab, and imaging rooms to 0 (no room charge, only service charges)
            $this->db->query("UPDATE rooms SET room_price = 0.00 WHERE room_type = 'outpatient' AND (specialization LIKE '%Consultation%' OR specialization LIKE '%General Medicine%' OR specialization LIKE '%General Practice%' OR specialization LIKE '%Family Medicine%' OR specialization LIKE '%Laboratory%' OR specialization LIKE '%Lab%' OR specialization LIKE '%Pathology%' OR specialization LIKE '%Radiology%' OR specialization LIKE '%X-Ray%' OR specialization LIKE '%Imaging%' OR specialization LIKE '%Ultrasound%') AND (room_price IS NULL OR room_price = 0)");
        }
    }

    public function down()
    {
        // No need to rollback price updates
    }
}

