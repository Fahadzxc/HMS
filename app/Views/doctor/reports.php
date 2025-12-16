<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Medical Reports
                </h2>
                <p class="page-subtitle">
                    View your appointments, prescriptions, and lab requests
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Report Filters -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Report Filters</h2>
    </header>
    <form method="GET" action="<?= base_url('doctor/reports') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select name="type" id="reportType" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="appointments" <?= ($report_type ?? 'appointments') === 'appointments' ? 'selected' : '' ?>>Appointments</option>
                <option value="prescriptions" <?= ($report_type ?? '') === 'prescriptions' ? 'selected' : '' ?>>Prescriptions</option>
                <option value="lab_requests" <?= ($report_type ?? '') === 'lab_requests' ? 'selected' : '' ?>>Lab Requests</option>
                <option value="all" <?= ($report_type ?? '') === 'all' ? 'selected' : '' ?>>All Reports</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date From</label>
            <input type="date" name="date_from" value="<?= esc($date_from ?? date('Y-m-01')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date To</label>
            <input type="date" name="date_to" value="<?= esc($date_to ?? date('Y-m-d')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 0 0 auto;">
            <button type="submit" style="padding: 0.5rem 1.5rem; background: #4299e1; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500;">Apply Filters</button>
        </div>
    </form>
</section>


<?php if (($report_type ?? 'appointments') === 'appointments' || ($report_type ?? '') === 'all'): ?>
    <!-- Appointments Report -->
    <section class="panel panel-spaced" id="appointmentsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>📅 Appointments Report</h2>
                <p>Appointments from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
            </div>
            <button onclick="printReport('appointmentsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                🖨️ Print Report
            </button>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td><?= !empty($apt['appointment_date']) ? date('M j, Y', strtotime($apt['appointment_date'])) : '—' ?></td>
                                <td><?= !empty($apt['appointment_time']) ? date('g:i A', strtotime($apt['appointment_time'])) : '—' ?></td>
                                <td><strong><?= esc($apt['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($apt['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($apt['appointment_type'] ?? 'Consultation') ?></td>
                                <td>
                                    <span class="badge badge-<?= esc($apt['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($apt['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= esc($apt['notes'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No appointments found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'appointments') === 'prescriptions' || ($report_type ?? '') === 'all'): ?>
    <!-- Prescriptions Report -->
    <section class="panel panel-spaced" id="prescriptionsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>💊 Prescriptions Report</h2>
                <p>Prescriptions from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
            </div>
            <button onclick="printReport('prescriptionsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                🖨️ Print Report
            </button>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>RX#</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Medications</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($prescriptions)): ?>
                        <?php foreach ($prescriptions as $rx): ?>
                            <?php
                            $items = json_decode($rx['items_json'] ?? '[]', true);
                            $medications = [];
                            if (!empty($items)) {
                                foreach ($items as $item) {
                                    $medications[] = ($item['medication'] ?? 'N/A') . ' (' . ($item['quantity'] ?? 0) . ')';
                                }
                            }
                            ?>
                            <tr>
                                <td><?= !empty($rx['created_at']) ? date('M j, Y g:i A', strtotime($rx['created_at'])) : '—' ?></td>
                                <td><strong>RX#<?= str_pad((string)$rx['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><strong><?= esc($rx['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($rx['patient_code'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($medications)): ?>
                                        <?= esc(implode(', ', array_slice($medications, 0, 3))) ?>
                                        <?php if (count($medications) > 3): ?>
                                            <span style="color: #64748b;">+<?= count($medications) - 3 ?> more</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= esc($rx['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($rx['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No prescriptions found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'appointments') === 'lab_requests' || ($report_type ?? '') === 'all'): ?>
    <!-- Lab Requests Report -->
    <section class="panel panel-spaced" id="labRequestsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>🔬 Lab Requests Report</h2>
                <p>Lab test requests from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
            </div>
            <button onclick="printReport('labRequestsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                🖨️ Print Report
            </button>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Request ID</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Test Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lab_requests)): ?>
                        <?php foreach ($lab_requests as $lab): ?>
                            <tr>
                                <td><?= !empty($lab['requested_at']) ? date('M j, Y g:i A', strtotime($lab['requested_at'])) : '—' ?></td>
                                <td><strong>#<?= str_pad((string)$lab['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><strong><?= esc($lab['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($lab['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($lab['test_type'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(esc($lab['priority'] ?? 'normal')) === 'urgent' ? 'danger' : (strtolower($lab['priority'] ?? 'normal') === 'high' ? 'warning' : 'info') ?>">
                                        <?= ucfirst(esc($lab['priority'] ?? 'normal')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= esc($lab['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($lab['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No lab requests found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

<script>
function printReport(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) {
        alert('Report section not found');
        return;
    }
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    
    // Get the report title and date range
    const title = section.querySelector('h2')?.textContent || 'Medical Report';
    const subtitle = section.querySelector('p')?.textContent || '';
    const printDate = new Date().toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Get table HTML
    const table = section.querySelector('table');
    if (!table) {
        alert('No data to print');
        return;
    }
    
    // Create print-friendly HTML
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title} - HMS</title>
            <style>
                @media print {
                    @page {
                        size: A4 landscape;
                        margin: 1cm;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                    }
                    .no-print {
                        display: none !important;
                    }
                }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 11px;
                    line-height: 1.4;
                    color: #000;
                    margin: 0;
                    padding: 20px;
                }
                .print-header {
                    text-align: center;
                    border-bottom: 3px solid #000;
                    padding-bottom: 15px;
                    margin-bottom: 20px;
                }
                .print-header h1 {
                    margin: 0;
                    font-size: 24px;
                    color: #000;
                    font-weight: bold;
                }
                .print-header p {
                    margin: 5px 0;
                    font-size: 12px;
                    color: #666;
                }
                .print-date {
                    text-align: right;
                    margin-bottom: 10px;
                    font-size: 10px;
                    color: #666;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                }
                table th {
                    background: #f0f0f0;
                    padding: 8px;
                    text-align: left;
                    border: 1px solid #000;
                    font-weight: 600;
                    font-size: 10px;
                }
                table td {
                    padding: 6px 8px;
                    border: 1px solid #ccc;
                    font-size: 10px;
                }
                .badge {
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-size: 9px;
                    font-weight: 600;
                    display: inline-block;
                }
                .badge-success {
                    background: #d4edda;
                    color: #155724;
                }
                .badge-danger {
                    background: #f8d7da;
                    color: #721c24;
                }
                .badge-warning {
                    background: #fff3cd;
                    color: #856404;
                }
                .badge-info {
                    background: #d1ecf1;
                    color: #0c5460;
                }
                .badge-pending {
                    background: #fff3cd;
                    color: #856404;
                }
                .badge-completed {
                    background: #d4edda;
                    color: #155724;
                }
                .print-footer {
                    margin-top: 30px;
                    padding-top: 15px;
                    border-top: 1px solid #ccc;
                    text-align: center;
                    font-size: 9px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>HOSPITAL MANAGEMENT SYSTEM</h1>
                <p>${title}</p>
                ${subtitle ? `<p>${subtitle}</p>` : ''}
            </div>
            <div class="print-date">
                Printed on: ${printDate}
            </div>
            ${table.outerHTML}
            <div class="print-footer">
                <p>This is a computer-generated document. No signature required.</p>
                <p>For inquiries, please contact the medical department.</p>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    
    // Wait for content to load, then print
    setTimeout(() => {
        printWindow.focus();
        printWindow.print();
    }, 250);
}
</script>

<style>
@media print {
    .btn-print, .panel-header button, .no-print {
        display: none !important;
    }
}
</style>

<?= $this->endSection() ?>

