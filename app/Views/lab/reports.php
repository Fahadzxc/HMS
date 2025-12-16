<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Laboratory Reports
                </h2>
                <p class="page-subtitle">
                    View test requests, results, and critical findings
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
    <form method="GET" action="<?= base_url('lab/reports') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select name="type" id="reportType" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="test_requests" <?= ($report_type ?? 'test_requests') === 'test_requests' ? 'selected' : '' ?>>Test Requests</option>
                <option value="test_results" <?= ($report_type ?? '') === 'test_results' ? 'selected' : '' ?>>Test Results</option>
                <option value="critical" <?= ($report_type ?? '') === 'critical' ? 'selected' : '' ?>>Critical Results</option>
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


<?php if (($report_type ?? 'test_requests') === 'test_requests' || ($report_type ?? '') === 'all'): ?>
    <!-- Test Requests Report -->
    <section class="panel panel-spaced" id="testRequestsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>📋 Test Requests Report</h2>
                <p>Test requests from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
            </div>
            <button onclick="printReport('testRequestsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
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
                        <th>Doctor</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($test_requests)): ?>
                        <?php foreach ($test_requests as $request): ?>
                            <tr>
                                <td><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                                <td><strong>#<?= str_pad((string)$request['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><strong><?= esc($request['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($request['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($request['test_type'] ?? 'N/A') ?></td>
                                <td><?= esc($request['doctor_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(esc($request['priority'] ?? 'normal')) === 'urgent' ? 'danger' : (strtolower($request['priority'] ?? 'normal') === 'high' ? 'warning' : 'info') ?>">
                                        <?= ucfirst(esc($request['priority'] ?? 'normal')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= esc($request['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($request['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No test requests found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'test_requests') === 'test_results' || ($report_type ?? '') === 'all'): ?>
    <!-- Test Results Report -->
    <section class="panel panel-spaced" id="testResultsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>🔬 Test Results Report</h2>
                <p>Test results from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
            </div>
            <button onclick="printReport('testResultsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                🖨️ Print Report
            </button>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date Released</th>
                        <th>Result ID</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Test Type</th>
                        <th>Result Summary</th>
                        <th>Status</th>
                        <th>Critical</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($test_results)): ?>
                        <?php foreach ($test_results as $result): ?>
                            <tr>
                                <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                                <td><strong>#<?= str_pad((string)$result['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><strong><?= esc($result['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($result['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($result['test_type'] ?? 'N/A') ?></td>
                                <td><?= esc($result['result_summary'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-success">Completed</span>
                                </td>
                                <td>
                                    <?php if (!empty($result['critical_flag']) && $result['critical_flag'] == 1): ?>
                                        <span class="badge badge-danger">🔴 Critical</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Normal</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No test results found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'test_requests') === 'critical' || ($report_type ?? '') === 'all'): ?>
    <!-- Critical Results Report -->
    <?php if (!empty($critical_results)): ?>
    <section class="panel panel-spaced" id="criticalResultsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>🚨 Critical Results Report</h2>
                <p>Critical test results requiring immediate attention</p>
            </div>
            <button onclick="printReport('criticalResultsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                🖨️ Print Report
            </button>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date Released</th>
                        <th>Result ID</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Test Type</th>
                        <th>Result Summary</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($critical_results as $result): ?>
                        <tr style="background: #fef2f2;">
                            <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                            <td><strong>#<?= str_pad((string)$result['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><strong><?= esc($result['patient_name'] ?? 'N/A') ?></strong></td>
                            <td><?= esc($result['patient_code'] ?? 'N/A') ?></td>
                            <td><?= esc($result['test_type'] ?? 'N/A') ?></td>
                            <td><strong><?= esc($result['result_summary'] ?? '—') ?></strong></td>
                            <td>
                                <span class="badge badge-danger">🔴 Critical</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
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
    const title = section.querySelector('h2')?.textContent || 'Laboratory Report';
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
                <p>For inquiries, please contact the laboratory department.</p>
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

