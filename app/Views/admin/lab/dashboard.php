<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🧪</span>
                    Laboratory Dashboard
                </h2>
                <p class="page-subtitle">
                    Monitor lab performance and critical alerts across all branches
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
    <div class="stack">
        <?php if (!empty($loadError)): ?>
            <div class="alert alert-warning">
                <?= esc($loadError) ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Test Requests</div>
                <div class="kpi-value"><?= number_format($metrics['pendingRequests'] ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Awaiting action</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Completed Tests Today</div>
                <div class="kpi-value"><?= number_format($metrics['completedToday'] ?? 0) ?></div>
                <div class="kpi-change kpi-positive">Today</div>
            </div>
        </div>
        <div class="kpi-card kpi-critical">
            <div class="kpi-content">
                <div class="kpi-label">Critical Results</div>
                <div class="kpi-value"><?= number_format($metrics['criticalResults'] ?? 0) ?></div>
                <div class="kpi-change kpi-negative">Requires attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Active Lab Staff</div>
                <div class="kpi-value"><?= number_format($metrics['activeStaff'] ?? 0) ?></div>
                <div class="kpi-change kpi-positive">On duty</div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Test Requests</h2>
        <a href="#" onclick="event.preventDefault(); openAllRequestsModal();" style="color: #4299e1; text-decoration: none; font-weight: 500; cursor: pointer;">View all</a>
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
        <a href="#" onclick="event.preventDefault(); openAllResultsModal();" style="color: #4299e1; text-decoration: none; font-weight: 500; cursor: pointer;">View all</a>
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

<!-- All Test Requests Modal -->
<div id="allRequestsModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeAllRequestsModal()"></div>
    <div class="modal-dialog" style="max-width: 1200px;">
        <div class="modal-header">
            <h3>All Test Requests</h3>
            <button class="modal-close" onclick="closeAllRequestsModal()">&times;</button>
        </div>
        <div class="modal-body">
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
                            <?php foreach ($recentRequests as $request): ?>
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
                                <td colspan="7" class="text-center text-muted">No requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- All Test Results Modal -->
<div id="allResultsModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeAllResultsModal()"></div>
    <div class="modal-dialog lab-detail-modal" style="max-width: 1100px;">
        <div class="modal-header">
            <div>
                <h3>All Test Results</h3>
                <p class="text-muted" style="margin: 4px 0 0;">Full details of every result entered by laboratory staff</p>
            </div>
            <button class="modal-close" onclick="closeAllResultsModal()">&times;</button>
        </div>
        <div class="modal-body lab-detail-modal-body">
            <?php if (!empty($recentResults)): ?>
                <div class="lab-result-detail-list">
                    <?php foreach ($recentResults as $result): ?>
                        <?php
                            $status = $result['status'] ?? 'pending';
                            $isCritical = !empty($result['critical_flag']);
                            $statusLabel = ucfirst(str_replace('_', ' ', $status));
                            $statusClass = match($status) {
                                'completed' => 'badge-success',
                                'released' => 'badge-success',
                                'pending' => 'badge-warning',
                                default => 'badge-secondary'
                            };
                        ?>
                        <article class="lab-result-detail-card">
                            <header class="lab-result-detail-card-head">
                                <div>
                                    <h4><?= esc($result['patient_name'] ?? 'Unknown Patient') ?></h4>
                                    <p>
                                        <?= esc($result['test_type'] ?? '—') ?>
                                        • <?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : 'Not released' ?>
                                    </p>
                                </div>
                                <div class="lab-result-detail-status">
                                    <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                    <?php if ($isCritical): ?>
                                        <span class="badge badge-critical">Critical</span>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <section class="lab-result-detail-section">
                                <p class="lab-result-detail-label">Result Summary</p>
                                <p class="lab-result-detail-text"><?= nl2br(esc($result['result_summary'] ?? 'No summary provided')) ?></p>
                            </section>

                            <section class="lab-result-detail-section">
                                <p class="lab-result-detail-label">Detailed Notes</p>
                                <p class="lab-result-detail-text">
                                    <?= !empty($result['detailed_report_path'])
                                        ? nl2br(esc($result['detailed_report_path']))
                                        : 'No detailed notes recorded by the laboratory staff.' ?>
                                </p>
                            </section>

                            <footer class="lab-result-detail-footer">
                                <div>
                                    <span class="lab-result-detail-label">Released By</span>
                                    <p class="lab-result-detail-text mb-0"><?= esc($result['released_by_name'] ?? 'Not assigned') ?></p>
                                </div>
                                <div>
                                    <span class="lab-result-detail-label">Last Updated</span>
                                    <p class="lab-result-detail-text mb-0">
                                        <?= !empty($result['updated_at']) ? date('M j, Y g:i A', strtotime($result['updated_at'])) : '—' ?>
                                    </p>
                                </div>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted" style="margin: 2rem 0;">No test results found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openAllRequestsModal() {
    const modal = document.getElementById('allRequestsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAllRequestsModal() {
    const modal = document.getElementById('allRequestsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function openAllResultsModal() {
    const modal = document.getElementById('allResultsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAllResultsModal() {
    const modal = document.getElementById('allResultsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllRequestsModal();
        closeAllResultsModal();
    }
});
</script>

<?= $this->endSection() ?>
