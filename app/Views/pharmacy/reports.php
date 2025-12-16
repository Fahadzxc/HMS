<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📈</span>
                    Pharmacy Reports
                </h2>
                <p class="page-subtitle">
                    View dispensing reports and expiring medicines
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
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select id="reportType" onchange="updateReportType()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="daily" <?= ($reportType ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily Report</option>
                <option value="monthly" <?= ($reportType ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                <option value="expiring" <?= ($reportType ?? '') === 'expiring' ? 'selected' : '' ?>>Expiring Medicines</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date From</label>
            <input type="date" id="dateFrom" value="<?= esc($dateFrom ?? date('Y-m-01')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date To</label>
            <input type="date" id="dateTo" value="<?= esc($dateTo ?? date('Y-m-d')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div>
            <button onclick="applyReportFilters()" style="padding: 0.5rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Generate Report</button>
        </div>
    </div>
</section>

<!-- Dispensing Report -->
<?php if (($reportType ?? 'daily') !== 'expiring'): ?>
<section class="panel panel-spaced" id="dispensingReport">
    <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2><?= ($reportType ?? 'daily') === 'daily' ? 'Daily' : 'Monthly' ?> Dispensing Report</h2>
            <p>Prescriptions dispensed from <?= date('M j, Y', strtotime($dateFrom ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($dateTo ?? date('Y-m-d'))) ?></p>
        </div>
        <button onclick="printReport('dispensingReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
            🖨️ Print Report
        </button>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>RX#</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Medications</th>
                        <th>Total Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dispensingReport)): ?>
                        <?php foreach ($dispensingReport as $rx): ?>
                            <?php
                            $items = json_decode($rx['items_json'] ?? '[]', true);
                            $totalQty = 0;
                            foreach ($items as $item) {
                                $totalQty += (int)($item['quantity'] ?? 0);
                            }
                            ?>
                            <tr>
                                <td><?= !empty($rx['updated_at']) ? date('M j, Y', strtotime($rx['updated_at'])) : '—' ?></td>
                                <td><strong>RX#<?= str_pad((string)$rx['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td>
                                    <?php
                                    $patientModel = new \App\Models\PatientModel();
                                    $patient = $patientModel->find($rx['patient_id']);
                                    echo esc($patient['full_name'] ?? 'N/A');
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $db = \Config\Database::connect();
                                    $doctor = $db->table('users')->where('id', $rx['doctor_id'])->get()->getRowArray();
                                    echo esc($doctor['name'] ?? 'N/A');
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($items)): ?>
                                        <?php foreach (array_slice($items, 0, 2) as $item): ?>
                                            <div><?= esc($item['name'] ?? 'N/A') ?></div>
                                        <?php endforeach; ?>
                                        <?php if (count($items) > 2): ?>
                                            <small style="color: #64748b;">+<?= count($items) - 2 ?> more</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= number_format($totalQty) ?></strong></td>
                                <td>
                                    <span class="badge badge-success" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; background: #dcfce7; color: #15803d; text-transform: uppercase;">
                                        DISPENSED
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No dispensing records found for the selected period
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Expiring Medicines Report -->
<?php if (($reportType ?? 'daily') === 'expiring'): ?>
<section class="panel panel-spaced" id="expiringMedicinesReport">
    <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Expiring Medicines Report</h2>
            <p>Medicines expiring within the next 90 days</p>
        </div>
        <button onclick="printReport('expiringMedicinesReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
            🖨️ Print Report
        </button>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Expiration Date</th>
                        <th>Days Until Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($expiringMedicines)): ?>
                        <?php foreach ($expiringMedicines as $medicine): ?>
                            <?php
                            $expDate = $medicine['expiration_date'] ?? null;
                            $daysUntilExpiry = $expDate ? (strtotime($expDate) - strtotime('today')) / 86400 : null;
                            $statusClass = 'badge-warning';
                            $statusLabel = 'Expiring Soon';
                            
                            if ($daysUntilExpiry !== null) {
                                if ($daysUntilExpiry < 0) {
                                    $statusClass = 'badge-danger';
                                    $statusLabel = 'Expired';
                                } elseif ($daysUntilExpiry <= 7) {
                                    $statusClass = 'badge-danger';
                                    $statusLabel = 'Critical';
                                } elseif ($daysUntilExpiry <= 30) {
                                    $statusClass = 'badge-warning';
                                    $statusLabel = 'Expiring Soon';
                                }
                            }
                            ?>
                            <tr>
                                <td><strong><?= esc($medicine['name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($medicine['category'] ?? 'General') ?></td>
                                <td><strong><?= number_format($medicine['stock_quantity'] ?? 0) ?></strong></td>
                                <td>
                                    <?php if ($expDate): ?>
                                        <strong><?= date('M j, Y', strtotime($expDate)) ?></strong>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($daysUntilExpiry !== null): ?>
                                        <strong style="color: <?= $daysUntilExpiry < 0 ? '#ef4444' : ($daysUntilExpiry <= 7 ? '#f59e0b' : '#10b981') ?>;">
                                            <?= $daysUntilExpiry < 0 ? 'Expired' : number_format($daysUntilExpiry) . ' days' ?>
                                        </strong>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?>" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600;">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="viewMedicine(<?= $medicine['id'] ?? 0 ?>)" style="padding: 0.35rem 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No expiring medicines found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>


<script>
function updateReportType() {
    const reportType = document.getElementById('reportType').value;
    if (reportType === 'expiring') {
        document.getElementById('dateFrom').style.display = 'none';
        document.getElementById('dateTo').style.display = 'none';
    } else {
        document.getElementById('dateFrom').style.display = 'block';
        document.getElementById('dateTo').style.display = 'block';
    }
}

function applyReportFilters() {
    const reportType = document.getElementById('reportType').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const url = new URL(window.location.href);
    url.searchParams.set('type', reportType);
    if (reportType !== 'expiring') {
        url.searchParams.set('date_from', dateFrom);
        url.searchParams.set('date_to', dateTo);
    }
    
    window.location.href = url.toString();
}

function viewMedicine(medicineId) {
    window.location.href = '<?= base_url('pharmacy/inventory') ?>?medicine_id=' + medicineId;
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    updateReportType();
});

function printReport(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) {
        alert('Report section not found');
        return;
    }
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    
    // Get the report title and date range
    const title = section.querySelector('h2')?.textContent || 'Pharmacy Report';
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
                <p>For inquiries, please contact the pharmacy department.</p>
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

