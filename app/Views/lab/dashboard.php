<?= $this->extend('template') ?>

<?= $this->section('content') ?>

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
            <table class="data-table">
                <thead>
                    <tr class="lab-row">
                        <th class="lab-cell">Result ID</th>
                        <th class="lab-cell">Patient</th>
                        <th class="lab-cell">Test Type</th>
                        <th class="lab-cell">Result Summary</th>
                        <th class="lab-cell">Status</th>
                        <th class="lab-cell">Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_results)): ?>
                        <?php foreach ($recent_results as $result): ?>
                            <?php
                            $status = $result['status'] ?? 'pending';
                            $isCritical = ($result['critical_flag'] ?? 0) == 1;
                            
                            $statusClass = match($status) {
                                'pending' => 'bg-warning',
                                'completed' => 'bg-success',
                                default => 'bg-secondary'
                            };
                            ?>
                        <tr class="lab-row">
                            <td class="lab-cell"><strong>#<?= str_pad((string)($result['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong></td>
                            <td class="lab-cell"><?= esc($result['patient_name'] ?? 'N/A') ?></td>
                            <td class="lab-cell"><?= esc($result['test_type'] ?? '—') ?></td>
                            <td class="lab-cell"><?= esc($result['result_summary'] ?? '—') ?></td>
                            <td class="lab-cell lab-status-cell">
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                <?php if ($isCritical): ?>
                                    <span class="badge bg-danger">Critical</span>
                                <?php endif; ?>
                            </td>
                            <td class="lab-cell"><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : (!empty($result['created_at']) ? date('M j, Y g:i A', strtotime($result['created_at'])) : '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="lab-row">
                            <td colspan="6" class="text-center py-4 text-muted">No test results found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
