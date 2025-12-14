<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🔬</span>
                    Lab Requests Management
                </h2>
                <p class="page-subtitle">
                    Review and process lab test requests before sending to laboratory
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Statistics Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Requests</div>
                <div class="kpi-value" style="color: #f59e0b;"><?= $pending_count ?? 0 ?></div>
                <div class="kpi-change kpi-warning">Awaiting nurse action</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Sent to Lab</div>
                <div class="kpi-value" style="color: #3b82f6;"><?= $sent_count ?? 0 ?></div>
                <div class="kpi-change kpi-positive">Forwarded to lab</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Completed</div>
                <div class="kpi-value" style="color: #10b981;"><?= $completed_count ?? 0 ?></div>
                <div class="kpi-change kpi-positive">Tests completed</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Requests</div>
                <div class="kpi-value"><?= count($requests ?? []) ?></div>
                <div class="kpi-change kpi-positive">All requests</div>
            </div>
        </div>
    </div>
</section>

<!-- Filter Tabs -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <h3 class="h5 mb-0 fw-bold">Lab Requests</h3>
            <div class="filter-tabs" style="display: flex; gap: 8px;">
                <button class="filter-tab <?= ($status_filter ?? 'pending') === 'all' ? 'active' : '' ?>" 
                        onclick="filterByStatus('all')" style="padding: 6px 12px; border: 1px solid #ddd; background: <?= ($status_filter ?? 'pending') === 'all' ? '#3b82f6' : '#fff' ?>; color: <?= ($status_filter ?? 'pending') === 'all' ? '#fff' : '#333' ?>; border-radius: 4px; cursor: pointer;">
                    All
                </button>
                <button class="filter-tab <?= ($status_filter ?? 'pending') === 'pending' ? 'active' : '' ?>" 
                        onclick="filterByStatus('pending')" style="padding: 6px 12px; border: 1px solid #ddd; background: <?= ($status_filter ?? 'pending') === 'pending' ? '#f59e0b' : '#fff' ?>; color: <?= ($status_filter ?? 'pending') === 'pending' ? '#fff' : '#333' ?>; border-radius: 4px; cursor: pointer;">
                    Pending (<?= $pending_count ?? 0 ?>)
                </button>
                <button class="filter-tab <?= ($status_filter ?? 'pending') === 'sent_to_lab' ? 'active' : '' ?>" 
                        onclick="filterByStatus('sent_to_lab')" style="padding: 6px 12px; border: 1px solid #ddd; background: <?= ($status_filter ?? 'pending') === 'sent_to_lab' ? '#3b82f6' : '#fff' ?>; color: <?= ($status_filter ?? 'pending') === 'sent_to_lab' ? '#fff' : '#333' ?>; border-radius: 4px; cursor: pointer;">
                    Sent to Lab (<?= $sent_count ?? 0 ?>)
                </button>
                <button class="filter-tab <?= ($status_filter ?? 'pending') === 'completed' ? 'active' : '' ?>" 
                        onclick="filterByStatus('completed')" style="padding: 6px 12px; border: 1px solid #ddd; background: <?= ($status_filter ?? 'pending') === 'completed' ? '#10b981' : '#fff' ?>; color: <?= ($status_filter ?? 'pending') === 'completed' ? '#fff' : '#333' ?>; border-radius: 4px; cursor: pointer;">
                    Completed (<?= $completed_count ?? 0 ?>)
                </button>
            </div>
        </div>
    </header>
</section>

<!-- Lab Requests Table -->
<section class="panel panel-spaced">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Request ID</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Patient</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Test Type</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Price</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Specimen</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Doctor</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Priority</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Status</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">Requested At</th>
                    <th style="padding: 12px; text-align: center; font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $request): ?>
                        <?php 
                        $status = $request['status'] ?? 'pending';
                        $priority = $request['priority'] ?? 'normal';
                        $patientType = strtolower($request['patient_type'] ?? 'outpatient');
                        $price = (float)($request['price'] ?? 0.00);
                        $requiresSpecimen = (int)($request['requires_specimen'] ?? 0);
                        $specimenCollected = !empty($request['specimen_collected_at']);
                        $isInpatient = ($patientType === 'inpatient');
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
                                        <?php if ($isInpatient): ?>
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
                                <strong style="color: #10b981;">₱<?= number_format($price, 2) ?></strong>
                            </td>
                            <td style="padding: 12px;">
                                <?php if ($requiresSpecimen === 1): ?>
                                    <?php if ($specimenCollected): ?>
                                        <span class="badge badge-success" style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #10b981; color: white;">
                                            ✅ Collected
                                        </span>
                                        <?php if (!empty($request['specimen_collected_at'])): ?>
                                            <br><small style="color: #666; font-size: 10px;">
                                                <?= date('M j, g:i A', strtotime($request['specimen_collected_at'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #f59e0b; color: white;">
                                            ⚠️ Needs Collection
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($isInpatient): ?>
                                    <span class="badge badge-info" style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #3b82f6; color: white;">
                                        🏥 Inpatient
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 11px;">—</span>
                                <?php endif; ?>
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
                                    $statusText = '⏳ Pending';
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
                            </td>
                            <td style="padding: 12px;">
                                <small style="color: #666;">
                                    <?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : 'N/A' ?>
                                </small>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="display: flex; gap: 4px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                    <?php if ($status === 'pending'): ?>
                                        <button type="button" 
                                                class="btn-view-patient" 
                                                onclick="viewPatient(<?= $request['patient_id'] ?? 0 ?>)"
                                                style="padding: 4px 8px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;"
                                                title="View Patient">
                                            👤 View
                                        </button>
                                        <?php if ($requiresSpecimen === 1 && !$specimenCollected): ?>
                                            <button type="button" 
                                                    class="btn-collect-specimen" 
                                                    onclick="collectSpecimen(<?= $request['id'] ?? 0 ?>)"
                                                    style="padding: 4px 8px; background: #f59e0b; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;"
                                                    title="Collect Specimen">
                                                🧪 Collect
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" 
                                                class="btn-mark-sent" 
                                                onclick="markAsSent(<?= $request['id'] ?? 0 ?>, <?= $requiresSpecimen ?>, <?= $specimenCollected ? 'true' : 'false' ?>)"
                                                style="padding: 4px 8px; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;"
                                                title="Send to Lab"
                                                <?= ($requiresSpecimen === 1 && !$specimenCollected) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                                            📤 Send to Lab
                                        </button>
                                    <?php elseif ($status === 'sent_to_lab'): ?>
                                        <span style="color: #3b82f6; font-size: 11px;">Sent to Lab</span>
                                        <?php if (!empty($request['sent_at'])): ?>
                                            <br><small style="color: #999; font-size: 10px;">
                                                <?= date('M j, g:i A', strtotime($request['sent_at'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php elseif ($status === 'completed'): ?>
                                        <span style="color: #10b981; font-size: 11px;">✅ Completed</span>
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
                        <td colspan="10" style="padding: 40px; text-align: center; color: #999;">
                            <p>No lab requests found for the selected filter.</p>
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
    url.searchParams.set('status', status);
    window.location.href = url.toString();
}

function viewPatient(patientId) {
    // Open patient details in a modal or new page
    window.open('<?= base_url('nurse/patients') ?>?patient_id=' + patientId, '_blank');
}

function collectSpecimen(requestId) {
    if (!confirm('Have you collected the specimen from the patient?\n\nThis will mark the specimen as collected.')) {
        return;
    }
    
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Collecting...';
    
    fetch('<?= site_url('nurse/collect-specimen') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            request_id: requestId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + (data.message || 'Specimen collected successfully!'));
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Failed to mark specimen as collected.'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function markAsSent(requestId, requiresSpecimen, specimenCollected) {
    if (requiresSpecimen && !specimenCollected) {
        alert('⚠️ Please collect the specimen first before sending to lab.');
        return;
    }
    
    const confirmMsg = requiresSpecimen 
        ? 'Are you sure you want to send this lab request to the laboratory?\n\nSpecimen has been collected and will be forwarded to the lab department.'
        : 'Are you sure you want to mark this lab request as sent to the laboratory?\n\nThis will forward the request to the lab department.';
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Sending...';
    
    fetch('<?= site_url('nurse/mark-lab-sent') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            request_id: requestId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + (data.message || 'Lab request marked as sent successfully!'));
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Failed to mark request as sent.'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
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
