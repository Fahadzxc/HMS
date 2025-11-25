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

<!-- Summary Cards -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Summary</h2>
    </header>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Revenue</div>
            <div style="font-size: 2rem; font-weight: 600; color: #10b981;">₱<?= number_format($summary['total_revenue'] ?? 0, 2) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Bills</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_bills'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Payments</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_payments'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Pending Bills</div>
            <div style="font-size: 2rem; font-weight: 600; color: #dc2626;"><?= number_format($summary['pending_bills'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Insurance Claims</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_claims'] ?? 0) ?></div>
        </div>
    </div>
</section>

<?php if (($report_type ?? 'bills') === 'bills' || ($report_type ?? '') === 'all'): ?>
    <!-- Bills Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>💰 Bills Report</h2>
            <p>Bills from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
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
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>💳 Payments Report</h2>
            <p>Payments from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
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
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>🏥 Insurance Claims Report</h2>
            <p>Insurance claims from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
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

<?= $this->endSection() ?>

