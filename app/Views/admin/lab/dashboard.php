<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <h2>Laboratory Overview</h2>
        <p>Monitor lab performance and critical alerts across all branches.</p>
    </header>
    <div class="stack">
        <?php if (!empty($loadError)): ?>
            <div class="alert alert-warning">
                <?= esc($loadError) ?>
            </div>
        <?php endif; ?>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending Test Requests</div>
                    <div class="kpi-value"><?= number_format($metrics['pendingRequests'] ?? 0) ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Completed Tests Today</div>
                    <div class="kpi-value"><?= number_format($metrics['completedToday'] ?? 0) ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-critical">
                <div class="kpi-content">
                    <div class="kpi-label">Critical Results</div>
                    <div class="kpi-value"><?= number_format($metrics['criticalResults'] ?? 0) ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Active Lab Staff</div>
                    <div class="kpi-value"><?= number_format($metrics['activeStaff'] ?? 0) ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-warning">
                <div class="kpi-content">
                    <div class="kpi-label">Inventory Alerts</div>
                    <div class="kpi-value">
                        <?= number_format(($metrics['inventoryAlerts']['low_stock'] ?? 0) + ($metrics['inventoryAlerts']['expiring'] ?? 0)) ?>
                    </div>
                    <small><?= number_format($metrics['inventoryAlerts']['low_stock'] ?? 0) ?> low stock · <?= number_format($metrics['inventoryAlerts']['expiring'] ?? 0) ?> expiring</small>
                </div>
            </div>
            <div class="kpi-card kpi-warning">
                <div class="kpi-content">
                    <div class="kpi-label">Equipment Alerts</div>
                    <div class="kpi-value"><?= number_format($metrics['equipmentAlerts'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Test Requests</h2>
        <a class="btn-link" href="<?= base_url('admin/lab/requests') ?>">View all</a>
    </header>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Requesting Doctor</th>
                    <th>Test Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date Requested</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentRequests)): ?>
                    <?php foreach (array_slice($recentRequests, 0, 8) as $request): ?>
                        <tr>
                            <td><?= esc($request['patient_name'] ?? 'N/A') ?></td>
                            <td><?= esc($request['doctor_name'] ?? 'N/A') ?></td>
                            <td><?= esc($request['test_type'] ?? '—') ?></td>
                            <td><span class="badge badge-priority badge-<?= esc($request['priority']) ?>"><?= ucfirst($request['priority'] ?? 'normal') ?></span></td>
                            <td><span class="badge badge-status badge-<?= esc($request['status']) ?>"><?= ucfirst(str_replace('_', ' ', $request['status'] ?? 'pending')) ?></span></td>
                            <td><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                            <td><?= esc($request['branch_name'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No recent requests.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Test Results</h2>
        <a class="btn-link" href="<?= base_url('admin/lab/results') ?>">View all</a>
    </header>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Test Type</th>
                    <th>Result Summary</th>
                    <th>Released By</th>
                    <th>Released</th>
                    <th>Status</th>
                    <th>Critical</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentResults)): ?>
                    <?php foreach (array_slice($recentResults, 0, 8) as $result): ?>
                        <tr>
                            <td><?= esc($result['patient_name'] ?? 'N/A') ?></td>
                            <td><?= esc($result['test_type'] ?? '—') ?></td>
                            <td><?= esc($result['result_summary'] ?? '—') ?></td>
                            <td><?= esc($result['released_by_name'] ?? '—') ?></td>
                            <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                            <td><span class="badge badge-status badge-<?= esc($result['status']) ?>"><?= ucfirst(str_replace('_', ' ', $result['status'] ?? 'draft')) ?></span></td>
                            <td><?= !empty($result['critical_flag']) ? '<span class="badge badge-critical">Yes</span>' : 'No' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No recent results.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.kpi-card {
    background: #ffffff;
    border-radius: 0.75rem;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    border: 1px solid #e2e8f0;
}
.kpi-card.kpi-critical {
    border-color: #f87272;
}
.kpi-card.kpi-warning {
    border-color: #fbbf24;
}
.kpi-label { font-size: 0.9rem; color: #475569; margin-bottom: 0.35rem; }
.kpi-value { font-size: 1.75rem; font-weight: 700; color: #0f172a; }
.kpi-card small { color: #64748b; display: block; margin-top: 0.25rem; }

.table-container { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}
.badge { display: inline-block; padding: 0.25rem 0.55rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-status.badge-pending { background: #e0f2fe; color: #0369a1; }
.badge-status.badge-in_progress { background: #ede9fe; color: #5b21b6; }
.badge-status.badge-completed { background: #dcfce7; color: #15803d; }
.badge-status.badge-cancelled { background: #fee2e2; color: #b91c1c; }
.badge-status.badge-critical { background: #fee2e2; color: #b91c1c; }
.badge-status.badge-draft { background: #f1f5f9; color: #475569; }
.badge-status.badge-released { background: #dcfce7; color: #15803d; }
.badge-status.badge-audited { background: #c7d2fe; color: #3730a3; }
.badge-status.badge-rejected { background: #fee2e2; color: #b91c1c; }
.badge-priority.badge-low { background: #f1f5f9; color: #475569; }
.badge-priority.badge-normal { background: #dbeafe; color: #1d4ed8; }
.badge-priority.badge-high { background: #fef3c7; color: #b45309; }
.badge-priority.badge-critical { background: #fee2e2; color: #b91c1c; }
.badge-critical { background: #fee2e2; color: #b91c1c; }
.alert { padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid transparent; margin-bottom: 1rem; }
.alert-warning { background: #fef3c7; border-color: #f59e0b; color: #92400e; }
</style>
<?= $this->endSection() ?>
