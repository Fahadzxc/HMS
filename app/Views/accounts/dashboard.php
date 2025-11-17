<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Accounts Dashboard
                </h2>
                <p class="page-subtitle">
                    Welcome back, <?= esc(session()->get('name') ?? 'Accountant') ?>. Here's your financial overview for today.
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- KPI Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Today's Revenue</div>
                <div class="kpi-value">₱<?= number_format($today_revenue ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">Today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Bills</div>
                <div class="kpi-value"><?= $pending_bills_count ?? 0 ?></div>
                <div class="kpi-change kpi-warning">Requires attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Insurance Claims</div>
                <div class="kpi-value"><?= $insurance_claims_count ?? 8 ?></div>
                <div class="kpi-change kpi-positive">All claims</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Overdue Payments</div>
                <div class="kpi-value"><?= $overdue_bills_count ?? 0 ?></div>
                <div class="kpi-change kpi-negative">Requires attention</div>
            </div>
        </div>
    </div>
</section>

<!-- Pending Bills -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>Pending Bills</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bill ID</th>
                        <th>Patient</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_bills)): ?>
                        <?php foreach ($recent_bills as $bill): ?>
                            <tr>
                                <td><strong><?= esc($bill['bill_number'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($bill['patient_name'] ?? 'N/A') ?></td>
                                <td><?= ucfirst(esc($bill['bill_type'] ?? 'N/A')) ?></td>
                                <td><strong>₱<?= number_format($bill['total_amount'] ?? 0, 2) ?></strong></td>
                                <td><?= $bill['due_date'] ? date('M j, Y', strtotime($bill['due_date'])) : '—' ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $bill['status'] === 'paid' ? 'success' : 
                                        ($bill['status'] === 'overdue' ? 'danger' : 
                                        ($bill['status'] === 'partial' ? 'warning' : 'warning'))
                                    ?>">
                                        <?= strtoupper(esc($bill['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/accounts/billing?bill_id=<?= $bill['id'] ?>" class="btn-xs btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center-empty">No pending bills</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Recent Payments -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>Recent Payments</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Patient</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_payments)): ?>
                        <?php foreach ($recent_payments as $payment): ?>
                            <tr>
                                <td><strong><?= esc($payment['payment_number'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($payment['patient_name'] ?? 'N/A') ?></td>
                                <td><strong>₱<?= number_format($payment['amount'] ?? 0, 2) ?></strong></td>
                                <td><?= ucfirst(str_replace('_', ' ', esc($payment['payment_method'] ?? 'N/A'))) ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $payment['status'] === 'completed' ? 'success' : 
                                        ($payment['status'] === 'failed' ? 'danger' : 'warning')
                                    ?>">
                                        <?= strtoupper(esc($payment['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= $payment['payment_date'] ? date('M j, Y g:i A', strtotime($payment['created_at'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center-empty">No recent payments</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Insurance Claims -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>Insurance Claims</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Claim ID</th>
                        <th>Patient</th>
                        <th>Insurance</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_claims)): ?>
                        <?php foreach ($recent_claims as $claim): ?>
                            <tr>
                                <td><strong><?= esc($claim['claim_number'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($claim['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($claim['insurance_provider'] ?? 'N/A') ?></td>
                                <td><strong>₱<?= number_format($claim['claim_amount'] ?? 0, 2) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $claim['status'] === 'approved' ? 'success' : 
                                        ($claim['status'] === 'rejected' ? 'danger' : 
                                        ($claim['status'] === 'paid' ? 'info' : 'warning'))
                                    ?>">
                                        <?= ucfirst(esc($claim['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/accounts/insurance?claim_id=<?= $claim['id'] ?>" class="btn-xs btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center-empty">No insurance claims found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
