<!-- Accountant dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <h2>Accounts Dashboard</h2>
        <p>Welcome back, <?= session()->get('name') ?>. Here's your financial overview for today.</p>
    </header>
    <div class="stack">
        <div class="actions-grid">
            <div class="action-tile">
                <span>Today's Revenue</span>
                <strong>₱<?= number_format($today_revenue ?? 0, 2) ?></strong>
            </div>
            <div class="action-tile">
                <span>Pending Bills</span>
                <strong><?= $pending_bills_count ?? 0 ?></strong>
            </div>
            <div class="action-tile">
                <span>Insurance Claims</span>
                <strong>8</strong>
            </div>
            <div class="action-tile">
                <span>Overdue Payments</span>
                <strong><?= $overdue_bills_count ?? 0 ?></strong>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Quick Actions</h3>
    </header>
    <div class="stack">
        <div class="button-group">
            <a href="/accounts/billing" class="button button-primary">Process Billing</a>
            <a href="/accounts/payments" class="button button-secondary">Record Payments</a>
            <a href="/accounts/insurance" class="button button-secondary">Insurance Claims</a>
            <a href="/accounts/reports" class="button button-secondary">Financial Reports</a>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Pending Bills</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
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
                                <td><?= esc($bill['bill_number'] ?? 'N/A') ?></td>
                                <td><?= esc($bill['patient_name'] ?? 'N/A') ?></td>
                                <td><?= ucfirst(esc($bill['bill_type'] ?? 'N/A')) ?></td>
                                <td>₱<?= number_format($bill['total_amount'] ?? 0, 2) ?></td>
                                <td><?= $bill['due_date'] ? date('M j, Y', strtotime($bill['due_date'])) : '—' ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $bill['status'] === 'paid' ? 'success' : 
                                        ($bill['status'] === 'overdue' ? 'danger' : 
                                        ($bill['status'] === 'partial' ? 'warning' : 'warning'))
                                    ?>">
                                        <?= ucfirst(esc($bill['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/accounts/billing?bill_id=<?= $bill['id'] ?>" class="button button-small">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No pending bills</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Recent Payments</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
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
                                <td><?= esc($payment['payment_number'] ?? 'N/A') ?></td>
                                <td><?= esc($payment['patient_name'] ?? 'N/A') ?></td>
                                <td>₱<?= number_format($payment['amount'] ?? 0, 2) ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', esc($payment['payment_method'] ?? 'N/A'))) ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $payment['status'] === 'completed' ? 'success' : 
                                        ($payment['status'] === 'failed' ? 'danger' : 'warning')
                                    ?>">
                                        <?= ucfirst(esc($payment['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= $payment['payment_date'] ? date('M j, Y g:i A', strtotime($payment['created_at'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No recent payments</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Insurance Claims</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
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
                    <tr>
                        <td>C001234</td>
                        <td>Juan Dela Cruz</td>
                        <td>PhilHealth</td>
                        <td>₱5,000</td>
                        <td><span class="badge badge-warning">Processing</span></td>
                        <td><a href="#" class="button button-small">Update</a></td>
                    </tr>
                    <tr>
                        <td>C001235</td>
                        <td>Maria Garcia</td>
                        <td>Maxicare</td>
                        <td>₱8,500</td>
                        <td><span class="badge badge-success">Approved</span></td>
                        <td><a href="#" class="button button-small">View</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
