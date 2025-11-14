<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💰</span>
                    Billing & Payments
                </h2>
                <p class="page-subtitle">
                    Manage invoices, payments, and financial records
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
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-value">₱<?= number_format($total_revenue ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">All time</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Invoices</div>
                <div class="kpi-value">₱<?= number_format($pending_amount ?? 0, 2) ?></div>
                <div class="kpi-change kpi-warning">Awaiting payment</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Overdue Payments</div>
                <div class="kpi-value">₱<?= number_format($overdue_amount ?? 0, 2) ?></div>
                <div class="kpi-change kpi-negative">Requires attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">This Month</div>
                <div class="kpi-value">₱<?= number_format($this_month_revenue ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">Current month</div>
            </div>
        </div>
    </div>
</section>

<!-- Filters -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>Filters</h3>
    </header>
    <div class="stack">
        <form method="GET" action="/admin/billing" class="filter-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="partial" <?= ($filters['status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                        <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
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
                    <a href="/admin/billing" class="btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Recent Invoices -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Invoices</h2>
        <div class="row">
            <button class="btn-secondary" onclick="createBillsForCompleted()">Create Bills for Completed Prescriptions</button>
            <button class="btn-secondary" onclick="window.print()">Export</button>
        </div>
    </header>
        
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><strong><?= esc($bill['bill_number'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <strong><?= esc($bill['patient_name'] ?? 'N/A') ?></strong>
                                    <?php if (!empty($bill['contact'])): ?>
                                        <p class="text-muted" style="margin: 0; font-size: 0.875rem;"><?= esc($bill['contact']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td><?= ucfirst(esc($bill['bill_type'] ?? 'N/A')) ?></td>
                                <td><strong>₱<?= number_format($bill['total_amount'] ?? 0, 2) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $bill['status'] === 'paid' ? 'success' : 
                                        ($bill['status'] === 'overdue' ? 'danger' : 
                                        ($bill['status'] === 'partial' ? 'warning' : 'warning'))
                                    ?>">
                                        <?= strtoupper(esc($bill['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= $bill['created_at'] ? date('M j, Y', strtotime($bill['created_at'])) : '—' ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', esc($bill['payment_method'] ?? '—'))) ?></td>
                                <td>
                                    <a href="/accounts/billing?bill_id=<?= $bill['id'] ?>" class="btn-xs btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center-empty">No invoices found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
async function createBillsForCompleted() {
    if (!confirm('Create bills for all completed prescriptions that don\'t have bills yet?')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/billing/createBillsForCompleted', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>

<?= $this->endSection() ?>
