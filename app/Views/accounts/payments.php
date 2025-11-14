<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💳</span>
                    Payments Management
                </h2>
                <p class="page-subtitle">
                    Welcome, <?= esc($user_name ?? 'Accountant') ?>
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
            <button class="btn-primary" onclick="openRecordPaymentModal()">
                <span>➕</span> Record Payment
            </button>
        </div>
    </header>
</section>

<!-- KPI Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Today's Payments</div>
                <div class="kpi-value">₱<?= number_format($today_payments ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">Today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Payments</div>
                <div class="kpi-value">₱<?= number_format($total_payments ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">All time</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Payments</div>
                <div class="kpi-value"><?= $pending_payments_count ?? 0 ?></div>
                <div class="kpi-change kpi-warning">Requires attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Records</div>
                <div class="kpi-value"><?= count($payments ?? []) ?></div>
                <div class="kpi-change">All payments</div>
            </div>
        </div>
    </div>
</section>

<!-- Payment Methods Breakdown -->
<?php if (!empty($payment_methods)): ?>
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>Payment Methods Breakdown</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Total Amount</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalAll = array_sum(array_column($payment_methods, 'total'));
                    foreach ($payment_methods as $method): 
                        $percentage = $totalAll > 0 ? ($method['total'] / $totalAll) * 100 : 0;
                    ?>
                        <tr>
                            <td><?= ucfirst(str_replace('_', ' ', esc($method['payment_method']))) ?></td>
                            <td><strong>₱<?= number_format($method['total'], 2) ?></strong></td>
                            <td><?= number_format($percentage, 1) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Filters -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>Filters</h3>
    </header>
    <div class="stack">
        <form method="GET" action="/accounts/payments" class="filter-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="refunded" <?= ($filters['status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Patient</label>
                    <select name="patient_id" class="form-input">
                        <option value="">All Patients</option>
                        <?php foreach ($patients ?? [] as $patient): ?>
                            <option value="<?= $patient['id'] ?>" <?= ($filters['patient_id'] ?? '') == $patient['id'] ? 'selected' : '' ?>>
                                <?= esc($patient['full_name']) ?> (<?= esc($patient['patient_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-input">
                        <option value="">All Methods</option>
                        <option value="cash" <?= ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="credit_card" <?= ($filters['payment_method'] ?? '') === 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
                        <option value="debit_card" <?= ($filters['payment_method'] ?? '') === 'debit_card' ? 'selected' : '' ?>>Debit Card</option>
                        <option value="insurance" <?= ($filters['payment_method'] ?? '') === 'insurance' ? 'selected' : '' ?>>Insurance</option>
                        <option value="check" <?= ($filters['payment_method'] ?? '') === 'check' ? 'selected' : '' ?>>Check</option>
                        <option value="bank_transfer" <?= ($filters['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="online" <?= ($filters['payment_method'] ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-primary">Filter</button>
                    <a href="/accounts/payments" class="btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Payments Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>All Payments</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Bill #</th>
                        <th>Patient</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Payment Date</th>
                        <th>Transaction ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><strong><?= esc($payment['payment_number'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($payment['bill_number'] ?? 'N/A') ?></td>
                                <td>
                                    <strong><?= esc($payment['patient_name'] ?? 'N/A') ?></strong>
                                </td>
                                <td><strong>₱<?= number_format($payment['amount'] ?? 0, 2) ?></strong></td>
                                <td><?= ucfirst(str_replace('_', ' ', esc($payment['payment_method'] ?? 'N/A'))) ?></td>
                                <td><?= $payment['payment_date'] ? date('M j, Y', strtotime($payment['payment_date'])) : '—' ?></td>
                                <td><?= esc($payment['transaction_id'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $payment['status'] === 'completed' ? 'success' : 
                                        ($payment['status'] === 'failed' ? 'danger' : 
                                        ($payment['status'] === 'refunded' ? 'warning' : 'warning'))
                                    ?>">
                                        <?= ucfirst(esc($payment['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/accounts/billing?bill_id=<?= $payment['bill_id'] ?>" class="btn-xs btn-primary">View Bill</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center-empty">No payments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Record Payment Modal -->
<div id="recordPaymentModal" class="modal" style="display: none;" onclick="if(event.target === this) closeRecordPaymentModal()">
    <div class="modal-dialog" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Record Payment</h3>
            <button class="modal-close" onclick="closeRecordPaymentModal()" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <form id="recordPaymentForm">
                <div class="form-group">
                    <label>Select Bill *</label>
                    <select name="bill_id" id="record_payment_bill_id" class="form-input" required onchange="updatePaymentAmount()">
                        <option value="">Select Bill</option>
                        <?php foreach ($unpaid_bills ?? [] as $bill): ?>
                            <option value="<?= $bill['id'] ?>" 
                                data-balance="<?= $bill['balance'] ?>" 
                                data-total="<?= $bill['total_amount'] ?>"
                                data-patient="<?= esc($bill['patient_name'] ?? 'N/A') ?>">
                                Bill #<?= esc($bill['bill_number']) ?> - <?= esc($bill['patient_name'] ?? 'N/A') ?> 
                                (Balance: ₱<?= number_format($bill['balance'] ?? 0, 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Patient</label>
                    <input type="text" id="record_payment_patient" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label>Bill Balance</label>
                    <input type="text" id="record_payment_balance" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label>Amount *</label>
                    <input type="number" name="amount" id="record_payment_amount" class="form-input" step="0.01" required>
                    <small class="text-muted">Enter payment amount (cannot exceed balance)</small>
                </div>
                <div class="form-group">
                    <label>Payment Method *</label>
                    <select name="payment_method" id="record_payment_method" class="form-input" required>
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="insurance">Insurance</option>
                        <option value="check">Check</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" name="payment_date" id="record_payment_date" class="form-input" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Transaction ID</label>
                    <input type="text" name="transaction_id" id="record_payment_transaction_id" class="form-input">
                </div>
                <div class="form-group">
                    <label>Reference Number</label>
                    <input type="text" name="reference_number" id="record_payment_reference" class="form-input">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" id="record_payment_notes" class="form-textarea" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Record Payment</button>
                    <button type="button" class="btn-secondary" onclick="closeRecordPaymentModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRecordPaymentModal() {
    document.getElementById('recordPaymentModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRecordPaymentModal() {
    document.getElementById('recordPaymentModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('recordPaymentForm').reset();
    document.getElementById('record_payment_patient').value = '';
    document.getElementById('record_payment_balance').value = '';
}

function updatePaymentAmount() {
    const select = document.getElementById('record_payment_bill_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const balance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
        const patient = selectedOption.getAttribute('data-patient') || '';
        
        document.getElementById('record_payment_patient').value = patient;
        document.getElementById('record_payment_balance').value = '₱' + balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('record_payment_amount').value = balance;
        document.getElementById('record_payment_amount').max = balance;
    } else {
        document.getElementById('record_payment_patient').value = '';
        document.getElementById('record_payment_balance').value = '';
        document.getElementById('record_payment_amount').value = '';
        document.getElementById('record_payment_amount').max = '';
    }
}

// Handle form submission
document.getElementById('recordPaymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Validate amount doesn't exceed balance
    const select = document.getElementById('record_payment_bill_id');
    const selectedOption = select.options[select.selectedIndex];
    const balance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
    const amount = parseFloat(data.amount) || 0;
    
    if (amount > balance) {
        alert('Payment amount cannot exceed bill balance!');
        return;
    }
    
    try {
        const response = await fetch('/accounts/recordPayment', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            alert('Payment recorded successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error recording payment: ' + error.message);
    }
});
</script>

<style>
.form-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.text-muted {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>

<?= $this->endSection() ?>

