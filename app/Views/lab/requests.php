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


<!-- Filter Tabs -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <h3 class="h5 mb-0 fw-bold">Test Requests</h3>
            <div class="filter-tabs" style="display: flex; gap: 8px;">
                <button class="filter-tab <?= ($status_filter ?? '') === '' ? 'active' : '' ?>" 
                        onclick="filterByStatus('')" style="padding: 6px 12px; border: 1px solid #ddd; background: <?= ($status_filter ?? '') === '' ? '#3b82f6' : '#fff' ?>; color: <?= ($status_filter ?? '') === '' ? '#fff' : '#333' ?>; border-radius: 4px; cursor: pointer;">
                    All (<?= $totalRequests ?>)
                </button>
                <button class="filter-tab <?= ($status_filter ?? '') === 'completed' ? 'active' : '' ?>" 
                        onclick="filterByStatus('completed')" style="padding: 6px 12px; border: 1px solid #ddd; background: <?= ($status_filter ?? '') === 'completed' ? '#10b981' : '#fff' ?>; color: <?= ($status_filter ?? '') === 'completed' ? '#fff' : '#333' ?>; border-radius: 4px; cursor: pointer;">
                    Completed (<?= $completedCount ?? 0 ?>)
                </button>
            </div>
        </div>
    </header>
</section>

<!-- Test Requests Table -->
<section class="panel panel-spaced">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Request ID</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Patient</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Test Type</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Doctor</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Priority</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Status</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Requested At</th>
                    <th style="padding: 12px; text-align: center; font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php 
                    $sentToLabCount = 0;
                    $completedCount = 0;
                    foreach ($requests as $request): 
                        $status = $request['status'] ?? 'pending';
                        if ($status === 'sent_to_lab') $sentToLabCount++;
                        if ($status === 'completed') $completedCount++;
                    endforeach;
                    ?>
                    <?php foreach ($requests as $request): ?>
                        <?php 
                        $status = $request['status'] ?? 'pending';
                        $priority = $request['priority'] ?? 'normal';
                        $hasAdmission = !empty($request['admission_id']);
                        $patientType = $hasAdmission ? 'inpatient' : 'outpatient';
                        if (!empty($request['patient_type'])) {
                            $patientType = strtolower($request['patient_type']);
                        }
                        ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 12px;">
                                <strong>#<?= str_pad((string)($request['id'] ?? 0), 4, '0', STR_PAD_LEFT) ?></strong>
                            </td>
                            <td style="padding: 12px;">
                                <div>
                                    <strong><?= esc($request['patient_name'] ?? 'N/A') ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        <?= ucfirst($patientType) ?>
                                        <?php if ($patientType === 'inpatient'): ?>
                                            <span style="color: #3b82f6;">🏥</span>
                                        <?php else: ?>
                                            <span style="color: #10b981;">🚶</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </td>
                            <td style="padding: 12px;">
                                <?= esc($request['test_type'] ?? 'N/A') ?>
                            </td>
                            <td style="padding: 12px;">
                                <?= esc($request['doctor_name'] ?? 'N/A') ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php
                                $priorityClass = 'badge-secondary';
                                $priorityIcon = '';
                                if ($priority === 'critical') {
                                    $priorityClass = 'badge-danger';
                                    $priorityIcon = '🔴';
                                } elseif ($priority === 'high') {
                                    $priorityClass = 'badge-warning';
                                    $priorityIcon = '🟡';
                                } elseif ($priority === 'normal') {
                                    $priorityClass = 'badge-info';
                                    $priorityIcon = '🔵';
                                } else {
                                    $priorityClass = 'badge-secondary';
                                    $priorityIcon = '⚪';
                                }
                                ?>
                                <span class="badge <?= $priorityClass ?>" style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                    <?= $priorityIcon ?> <?= ucfirst($priority) ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <?php
                                $statusClass = 'badge-secondary';
                                $statusText = ucfirst($status);
                                if ($status === 'pending') {
                                    $statusClass = 'badge-warning';
                                    $statusText = $hasAdmission ? '⏳ Pending (Inpatient)' : '⏳ Pending (Outpatient)';
                                } elseif ($status === 'sent_to_lab') {
                                    $statusClass = 'badge-info';
                                    $statusText = '📤 Sent to Lab';
                                } elseif ($status === 'in_progress') {
                                    $statusClass = 'badge-primary';
                                    $statusText = '🔄 In Progress';
                                } elseif ($status === 'completed') {
                                    $statusClass = 'badge-success';
                                    $statusText = '✅ Completed';
                                } elseif ($status === 'cancelled') {
                                    $statusClass = 'badge-danger';
                                    $statusText = '❌ Cancelled';
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>" style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                    <?= $statusText ?>
                                </span>
                                <?php if ($status === 'sent_to_lab' && !empty($request['sent_at'])): ?>
                                    <br><small style="color: #999; font-size: 10px;">
                                        Sent: <?= date('M j, g:i A', strtotime($request['sent_at'])) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <small style="color: #666;">
                                    <?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : 'N/A' ?>
                                </small>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                    <?php if ($status === 'pending' || $status === 'sent_to_lab' || $status === 'in_progress'): ?>
                                        <button type="button" 
                                                class="btn-start-test" 
                                                onclick="startTest(<?= $request['id'] ?? 0 ?>)"
                                                style="padding: 4px 8px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;"
                                                title="Start Test">
                                            🔬 Start Test
                                        </button>
                                    <?php elseif ($status === 'completed'): ?>
                                        <a href="<?= base_url('lab/results?request_id=' . $request['id']) ?>" 
                                           style="padding: 4px 8px; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; text-decoration: none; display: inline-block;"
                                           title="View Result">
                                            ✅ View Result
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #666; font-size: 11px;">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php if (!empty($request['notes'])): ?>
                            <tr style="background: #f8f9fa;">
                                <td colspan="8" style="padding: 8px 12px; font-size: 12px; color: #666;">
                                    <strong>Notes:</strong> <?= esc($request['notes']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="padding: 40px; text-align: center; color: #999;">
                            <p>No test requests found for the selected filter.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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

function startTest(requestId) {
    if (confirm('Start processing this test request? You will be redirected to enter the test result.')) {
        window.location.href = '<?= base_url('lab/results') ?>?request_id=' + requestId + '&action=start';
    }
}
</script>

<style>
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.badge-warning {
    background: #fbbf24;
    color: #78350f;
}

.badge-info {
    background: #3b82f6;
    color: white;
}

.badge-success {
    background: #10b981;
    color: white;
}

.badge-danger {
    background: #ef4444;
    color: white;
}

.badge-secondary {
    background: #6b7280;
    color: white;
}

.badge-primary {
    background: #6366f1;
    color: white;
}

.data-table tbody tr:hover {
    background-color: #f8f9fa;
}

.filter-tab:hover {
    opacity: 0.8;
}

.filter-tab.active {
    font-weight: 600;
}
</style>

<?= $this->endSection() ?>

