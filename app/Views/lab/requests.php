<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel lab-section">
    <header class="panel-header lab-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🔬</span>
                    Test Requests
                </h2>
                <p class="page-subtitle lab-role-description">
                    Laboratory Staff (Manage test requests, enter results)
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Context Section -->
<section class="panel panel-spaced lab-section">
    <div class="lab-context">
        <p class="lab-context-text">These are tests ordered by doctors. The laboratory staff can view pending requests and update their status.</p>
    </div>
</section>

<?php if (!empty($error ?? '')): ?>
<section class="panel panel-spaced">
    <div class="alert alert-danger" style="padding: 1rem; background: #fee; border: 1px solid #fcc; border-radius: 6px; color: #c33;">
        <strong>Error:</strong> <?= esc($error) ?>
    </div>
</section>
<?php endif; ?>

<!-- Statistics Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <?php
        $totalRequests = count($requests ?? []);
        $pendingCount = 0;
        $inProgressCount = 0;
        $criticalCount = 0;
        
        foreach ($requests ?? [] as $req) {
            $status = $req['status'] ?? 'pending';
            if ($status === 'pending') $pendingCount++;
            elseif ($status === 'in_progress') $inProgressCount++;
            
            if (($req['priority'] ?? 'normal') === 'critical') $criticalCount++;
        }
        ?>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Requests</div>
                <div class="kpi-value"><?= $totalRequests ?></div>
                <div class="kpi-change kpi-positive">All requests</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending</div>
                <div class="kpi-value"><?= $pendingCount ?></div>
                <div class="kpi-change kpi-warning">Awaiting processing</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">In Progress</div>
                <div class="kpi-value"><?= $inProgressCount ?></div>
                <div class="kpi-change kpi-positive">Being processed</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Critical</div>
                <div class="kpi-value"><?= $criticalCount ?></div>
                <div class="kpi-change kpi-negative">Urgent attention</div>
            </div>
        </div>
    </div>
</section>

<!-- Test Requests Table -->
<section class="panel panel-spaced lab-section">
    <header class="panel-header lab-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0 fw-bold">All Test Requests</h3>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm shadow-sm" style="max-width: 150px;" onchange="filterByStatus(this.value)">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select class="form-select form-select-sm shadow-sm" style="max-width: 150px;" onchange="filterByPriority(this.value)">
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
        </div>
    </header>
    
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr class="lab-row">
                        <th class="lab-cell">Request ID</th>
                        <th class="lab-cell">Patient</th>
                        <th class="lab-cell">Patient Type</th>
                        <th class="lab-cell">Doctor</th>
                        <th class="lab-cell">Test Type</th>
                        <th class="lab-cell">Priority</th>
                        <th class="lab-cell">Status</th>
                        <th class="lab-cell">Date Requested</th>
                        <th class="lab-cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $request): ?>
                            <?php
                            $priority = $request['priority'] ?? 'normal';
                            $status = $request['status'] ?? 'pending';
                            
                            $priorityClass = match($priority) {
                                'low' => 'bg-secondary',
                                'normal' => 'bg-info',
                                'high' => 'bg-warning',
                                'critical' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            
                            $statusClass = match($status) {
                                'pending' => 'bg-warning',
                                'in_progress' => 'bg-info',
                                'completed' => 'bg-success',
                                'cancelled' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                            ?>
                            <tr class="lab-row">
                                <td class="lab-cell"><strong>#<?= str_pad((string)($request['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td class="lab-cell"><?= esc($request['patient_name'] ?? 'N/A') ?></td>
                                <td class="lab-cell">
                                    <?php
                                    // Determine patient type: if admission_id exists = INPATIENT, else = OUTPATIENT
                                    $hasAdmission = !empty($request['admission_id']);
                                    $patientType = $hasAdmission ? 'inpatient' : 'outpatient';
                                    // Fallback to patient_type from patients table if available
                                    if (!empty($request['patient_type'])) {
                                        $patientType = strtolower($request['patient_type']);
                                    }
                                    $patientTypeClass = ($patientType === 'inpatient') ? 'bg-primary' : 'bg-info';
                                    ?>
                                    <span class="badge <?= $patientTypeClass ?>"><?= ucfirst($patientType) ?></span>
                                </td>
                                <td class="lab-cell"><?= esc($request['doctor_name'] ?? 'N/A') ?></td>
                                <td class="lab-cell"><?= esc($request['test_type'] ?? '—') ?></td>
                                <td class="lab-cell">
                                    <span class="badge <?= $priorityClass ?>"><?= ucfirst($priority) ?></span>
                                </td>
                                <td class="lab-cell">
                                    <span class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                                </td>
                                <td class="lab-cell"><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                                <td class="lab-cell">
                                    <?php if ($status === 'pending' || $status === 'in_progress'): ?>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="startTest(<?= $request['id'] ?>)">Start Test</button>
                                    <?php elseif ($status === 'completed'): ?>
                                        <a href="<?= base_url('lab/results?request_id=' . $request['id']) ?>" class="btn btn-sm btn-success">View Result</a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="lab-row">
                            <td colspan="9" class="text-center py-4 text-muted">No test requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
function filterByStatus(status) {
    const url = new URL(window.location.href);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}

function filterByPriority(priority) {
    const url = new URL(window.location.href);
    if (priority) {
        url.searchParams.set('priority', priority);
    } else {
        url.searchParams.delete('priority');
    }
    window.location.href = url.toString();
}

function startTest(requestId) {
    if (confirm('Start processing this test request? You will be redirected to enter the test result.')) {
        window.location.href = '<?= base_url('lab/results') ?>?request_id=' + requestId + '&action=start';
    }
}
</script>

<?= $this->endSection() ?>

