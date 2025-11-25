<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Laboratory Reports
                </h2>
                <p class="page-subtitle">
                    View test requests, results, and critical findings
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Report Filters -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Report Filters</h2>
    </header>
    <form method="GET" action="<?= base_url('lab/reports') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select name="type" id="reportType" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="test_requests" <?= ($report_type ?? 'test_requests') === 'test_requests' ? 'selected' : '' ?>>Test Requests</option>
                <option value="test_results" <?= ($report_type ?? '') === 'test_results' ? 'selected' : '' ?>>Test Results</option>
                <option value="critical" <?= ($report_type ?? '') === 'critical' ? 'selected' : '' ?>>Critical Results</option>
                <option value="all" <?= ($report_type ?? '') === 'all' ? 'selected' : '' ?>>All Reports</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date From</label>
            <input type="date" name="date_from" value="<?= esc($date_from ?? date('Y-m-01')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date To</label>
            <input type="date" name="date_to" value="<?= esc($date_to ?? date('Y-m-d')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 0 0 auto;">
            <button type="submit" style="padding: 0.5rem 1.5rem; background: #4299e1; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500;">Apply Filters</button>
        </div>
    </form>
</section>

<!-- Summary Cards -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Summary</h2>
    </header>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Requests</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_requests'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Results</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_results'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Critical Results</div>
            <div style="font-size: 2rem; font-weight: 600; color: #dc2626;"><?= number_format($summary['critical_count'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Completion Rate</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['completion_rate'] ?? 0, 1) ?>%</div>
        </div>
    </div>
</section>

<?php if (($report_type ?? 'test_requests') === 'test_requests' || ($report_type ?? '') === 'all'): ?>
    <!-- Test Requests Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>📋 Test Requests Report</h2>
            <p>Test requests from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Request ID</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Test Type</th>
                        <th>Doctor</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($test_requests)): ?>
                        <?php foreach ($test_requests as $request): ?>
                            <tr>
                                <td><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                                <td><strong>#<?= str_pad((string)$request['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><strong><?= esc($request['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($request['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($request['test_type'] ?? 'N/A') ?></td>
                                <td><?= esc($request['doctor_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(esc($request['priority'] ?? 'normal')) === 'urgent' ? 'danger' : (strtolower($request['priority'] ?? 'normal') === 'high' ? 'warning' : 'info') ?>">
                                        <?= ucfirst(esc($request['priority'] ?? 'normal')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= esc($request['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($request['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No test requests found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'test_requests') === 'test_results' || ($report_type ?? '') === 'all'): ?>
    <!-- Test Results Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>🔬 Test Results Report</h2>
            <p>Test results from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date Released</th>
                        <th>Result ID</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Test Type</th>
                        <th>Result Summary</th>
                        <th>Status</th>
                        <th>Critical</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($test_results)): ?>
                        <?php foreach ($test_results as $result): ?>
                            <tr>
                                <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                                <td><strong>#<?= str_pad((string)$result['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><strong><?= esc($result['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($result['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($result['test_type'] ?? 'N/A') ?></td>
                                <td><?= esc($result['result_summary'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-success">Completed</span>
                                </td>
                                <td>
                                    <?php if (!empty($result['critical_flag']) && $result['critical_flag'] == 1): ?>
                                        <span class="badge badge-danger">🔴 Critical</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Normal</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No test results found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'test_requests') === 'critical' || ($report_type ?? '') === 'all'): ?>
    <!-- Critical Results Report -->
    <?php if (!empty($critical_results)): ?>
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>🚨 Critical Results Report</h2>
            <p>Critical test results requiring immediate attention</p>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date Released</th>
                        <th>Result ID</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Test Type</th>
                        <th>Result Summary</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($critical_results as $result): ?>
                        <tr style="background: #fef2f2;">
                            <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                            <td><strong>#<?= str_pad((string)$result['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><strong><?= esc($result['patient_name'] ?? 'N/A') ?></strong></td>
                            <td><?= esc($result['patient_code'] ?? 'N/A') ?></td>
                            <td><?= esc($result['test_type'] ?? 'N/A') ?></td>
                            <td><strong><?= esc($result['result_summary'] ?? '—') ?></strong></td>
                            <td>
                                <span class="badge badge-danger">🔴 Critical</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>

