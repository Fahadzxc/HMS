<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<style>
/* Lab Dashboard Table Alignment */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem 0.75rem;
    text-align: left;
    vertical-align: middle;
}

.data-table th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    border-bottom: 1px solid #f1f5f9;
}

.data-table tbody tr:hover {
    background: #f8fafc;
}

/* Badge styles */
.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 4px;
    text-transform: uppercase;
}

.bg-warning { background: #fef3c7; color: #92400e; }
.bg-success { background: #d1fae5; color: #065f46; }
.bg-danger { background: #fee2e2; color: #991b1b; }
.bg-info { background: #dbeafe; color: #1e40af; }
.bg-secondary { background: #e2e8f0; color: #475569; }
</style>

<section class="panel lab-section">
    <header class="panel-header lab-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🔬</span>
                    Laboratory Dashboard
                </h2>
                <p class="page-subtitle lab-role-description">
                    Laboratory Staff (Manage test requests, enter results)
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
                <div class="kpi-label">Pending Tests</div>
                <div class="kpi-value"><?= $pending_requests ?? 0 ?></div>
                <div class="kpi-change kpi-warning">Requires attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Completed Today</div>
                <div class="kpi-value"><?= $completed_today ?? 0 ?></div>
                <div class="kpi-change kpi-positive">Today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Urgent Tests</div>
                <div class="kpi-value"><?= $urgent_tests ?? 0 ?></div>
                <div class="kpi-change kpi-negative">High priority</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Critical Tests</div>
                <div class="kpi-value"><?= $critical_tests ?? 0 ?></div>
                <div class="kpi-change kpi-negative">Urgent</div>
            </div>
        </div>
    </div>
</section>

<!-- Pending Test Requests -->
<section class="panel panel-spaced lab-section">
    <header class="panel-header lab-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0 fw-bold">Pending Test Requests</h3>
            <a href="<?= base_url('lab/requests') ?>" class="btn btn-sm btn-primary">View All</a>
        </div>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr class="lab-row">
                        <th class="lab-cell">Request ID</th>
                        <th class="lab-cell">Patient</th>
                        <th class="lab-cell">Test Type</th>
                        <th class="lab-cell">Doctor</th>
                        <th class="lab-cell">Priority</th>
                        <th class="lab-cell">Requested</th>
                        <th class="lab-cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_requests)): ?>
                        <?php foreach ($recent_requests as $request): ?>
                            <?php
                            $priority = $request['priority'] ?? 'normal';
                            $priorityClass = match($priority) {
                                'low' => 'bg-secondary',
                                'normal' => 'bg-info',
                                'high' => 'bg-warning',
                                'critical' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <tr class="lab-row">
                                <td class="lab-cell"><strong>#<?= str_pad((string)($request['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td class="lab-cell"><?= esc($request['patient_name'] ?? 'N/A') ?></td>
                                <td class="lab-cell"><?= esc($request['test_type'] ?? '—') ?></td>
                                <td class="lab-cell"><?= esc($request['doctor_name'] ?? 'N/A') ?></td>
                                <td class="lab-cell">
                                    <span class="badge <?= $priorityClass ?>"><?= ucfirst($priority) ?></span>
                                </td>
                                <td class="lab-cell"><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                                <td class="lab-cell">
                                    <a href="<?= base_url('lab/results?request_id=' . $request['id'] . '&action=start') ?>" class="btn btn-sm btn-primary">Start Test</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="lab-row">
                            <td colspan="7" class="text-center py-4 text-muted">No pending test requests.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Recent Test Results -->
<section class="panel panel-spaced lab-section">
    <header class="panel-header lab-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0 fw-bold">Recent Test Results</h3>
            <a href="<?= base_url('lab/results') ?>" class="btn btn-sm btn-primary">View All</a>
        </div>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 90px;">Result ID</th>
                        <th style="width: 150px;">Patient</th>
                        <th style="width: 110px;">Test Type</th>
                        <th style="width: 250px;">Result Summary</th>
                        <th style="width: 90px;">Status</th>
                        <th style="width: 140px;">Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_results)): ?>
                        <?php foreach ($recent_results as $result): ?>
                            <?php
                            $status = $result['status'] ?? 'pending';
                            $isCritical = ($result['critical_flag'] ?? 0) == 1;
                            
                            // If critical, override status
                            if ($isCritical) {
                                $statusClass = 'bg-danger';
                                $statusText = 'Critical';
                            } else {
                                $statusClass = match($status) {
                                    'pending' => 'bg-warning',
                                    'completed' => 'bg-success',
                                    default => 'bg-secondary'
                                };
                                $statusText = ucfirst($status);
                            }
                            ?>
                        <tr>
                            <td><strong>#<?= str_pad((string)($result['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= esc($result['patient_name'] ?? 'N/A') ?></td>
                            <td><?= esc($result['test_type'] ?? '—') ?></td>
                            <td><?= esc($result['result_summary'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : (!empty($result['created_at']) ? date('M j, Y g:i A', strtotime($result['created_at'])) : '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #64748b;">No test results found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
