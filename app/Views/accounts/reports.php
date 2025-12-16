<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Accounts Reports
                </h2>
                <p class="page-subtitle">
                    View billing, payments, and insurance claims reports
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
    <form method="GET" action="<?= base_url('accounts/reports') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select name="type" id="reportType" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="bills" <?= ($report_type ?? 'bills') === 'bills' ? 'selected' : '' ?>>Bills</option>
                <option value="payments" <?= ($report_type ?? '') === 'payments' ? 'selected' : '' ?>>Payments</option>
                <option value="insurance" <?= ($report_type ?? '') === 'insurance' ? 'selected' : '' ?>>Insurance Claims</option>
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


<?php if (($report_type ?? 'bills') === 'bills' || ($report_type ?? '') === 'all'): ?>
    <!-- Bills Report -->
    <section class="panel panel-spaced" id="billsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>💰 Bills Report</h2>
                <p>Bills from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
            </div>
            <button onclick="printReport('billsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                🖨️ Print Report
            </button>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Bill Number</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Subtotal</th>
                        <th>Tax</th>
                        <th>Discount</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><?= !empty($bill['created_at']) ? date('M j, Y g:i A', strtotime($bill['created_at'])) : '—' ?></td>
                                <td><strong><?= esc($bill['bill_number'] ?? 'N/A') ?></strong></td>
                                <td><strong><?= esc($bill['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($bill['patient_code'] ?? 'N/A') ?></td>
                                <td>₱<?= number_format($bill['subtotal'] ?? 0, 2) ?></td>
                                <td>₱<?= number_format($bill['tax'] ?? 0, 2) ?></td>
                                <td>₱<?= number_format($bill['discount'] ?? 0, 2) ?></td>
                                <td><strong>₱<?= number_format($bill['total_amount'] ?? 0, 2) ?></strong></td>
                                <td>₱<?= number_format($bill['paid_amount'] ?? 0, 2) ?></td>
                                <td><strong>₱<?= number_format($bill['balance'] ?? 0, 2) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= esc($bill['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($bill['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted">No bills found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'bills') === 'payments' || ($report_type ?? '') === 'all'): ?>
    <!-- Payments Report -->
    <section class="panel panel-spaced" id="paymentsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>💳 Payments Report</h2>
                <p>Payments from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
            </div>
            <button onclick="printReport('paymentsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                🖨️ Print Report
            </button>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payment Number</th>
                        <th>Patient</th>
                        <th>Bill Number</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= !empty($payment['payment_date']) ? date('M j, Y g:i A', strtotime($payment['payment_date'])) : '—' ?></td>
                                <td><strong><?= esc($payment['payment_number'] ?? 'N/A') ?></strong></td>
                                <td><strong><?= esc($payment['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($payment['bill_number'] ?? 'N/A') ?></td>
                                <td><strong>₱<?= number_format($payment['amount'] ?? 0, 2) ?></strong></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= ucfirst(esc($payment['payment_method'] ?? 'cash')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <?= ucfirst(esc($payment['status'] ?? 'completed')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No payments found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'bills') === 'insurance' || ($report_type ?? '') === 'all'): ?>
    <!-- Insurance Claims Report -->
    <section class="panel panel-spaced" id="insuranceClaimsReport">
        <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>🏥 Insurance Claims Report</h2>
                <p>Insurance claims from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
            </div>
            <button onclick="printReport('insuranceClaimsReport')" class="btn-print" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                🖨️ Print Report
            </button>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Claim Number</th>
                        <th>Patient</th>
                        <th>Bill Number</th>
                        <th>Insurance Provider</th>
                        <th>Claim Amount</th>
                        <th>Approved Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($insurance_claims)): ?>
                        <?php foreach ($insurance_claims as $claim): ?>
                            <tr>
                                <td><?= !empty($claim['created_at']) ? date('M j, Y g:i A', strtotime($claim['created_at'])) : '—' ?></td>
                                <td><strong><?= esc($claim['claim_number'] ?? 'N/A') ?></strong></td>
                                <td><strong><?= esc($claim['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($claim['bill_number'] ?? 'N/A') ?></td>
                                <td><?= esc($claim['insurance_provider'] ?? 'N/A') ?></td>
                                <td>₱<?= number_format($claim['claim_amount'] ?? 0, 2) ?></td>
                                <td>₱<?= number_format($claim['approved_amount'] ?? 0, 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= esc($claim['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($claim['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No insurance claims found for the selected period.</td>
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
    const title = section.querySelector('h2')?.textContent || 'Accounts Report';
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
                .badge-paid {
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
                <p>For inquiries, please contact the accounts department.</p>
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

