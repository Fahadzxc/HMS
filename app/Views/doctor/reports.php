<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Medical Reports
                </h2>
                <p class="page-subtitle">
                    View your appointments, prescriptions, and lab requests
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
    <form method="GET" action="<?= base_url('doctor/reports') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select name="type" id="reportType" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="appointments" <?= ($report_type ?? 'appointments') === 'appointments' ? 'selected' : '' ?>>Appointments</option>
                <option value="prescriptions" <?= ($report_type ?? '') === 'prescriptions' ? 'selected' : '' ?>>Prescriptions</option>
                <option value="lab_requests" <?= ($report_type ?? '') === 'lab_requests' ? 'selected' : '' ?>>Lab Requests</option>
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
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Appointments</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_appointments'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Prescriptions</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_prescriptions'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Lab Requests</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_lab_requests'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Patients</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_patients'] ?? 0) ?></div>
        </div>
    </div>
</section>

<?php if (($report_type ?? 'appointments') === 'appointments' || ($report_type ?? '') === 'all'): ?>
    <!-- Appointments Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>📅 Appointments Report</h2>
            <p>Appointments from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td><?= !empty($apt['appointment_date']) ? date('M j, Y', strtotime($apt['appointment_date'])) : '—' ?></td>
                                <td><?= !empty($apt['appointment_time']) ? date('g:i A', strtotime($apt['appointment_time'])) : '—' ?></td>
                                <td><strong><?= esc($apt['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($apt['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($apt['appointment_type'] ?? 'Consultation') ?></td>
                                <td>
                                    <span class="badge badge-<?= esc($apt['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($apt['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= esc($apt['notes'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No appointments found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'appointments') === 'prescriptions' || ($report_type ?? '') === 'all'): ?>
    <!-- Prescriptions Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>💊 Prescriptions Report</h2>
            <p>Prescriptions from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>RX#</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Medications</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($prescriptions)): ?>
                        <?php foreach ($prescriptions as $rx): ?>
                            <?php
                            $items = json_decode($rx['items_json'] ?? '[]', true);
                            $medications = [];
                            if (!empty($items)) {
                                foreach ($items as $item) {
                                    $medications[] = ($item['medication'] ?? 'N/A') . ' (' . ($item['quantity'] ?? 0) . ')';
                                }
                            }
                            ?>
                            <tr>
                                <td><?= !empty($rx['created_at']) ? date('M j, Y g:i A', strtotime($rx['created_at'])) : '—' ?></td>
                                <td><strong>RX#<?= str_pad((string)$rx['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><strong><?= esc($rx['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($rx['patient_code'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($medications)): ?>
                                        <?= esc(implode(', ', array_slice($medications, 0, 3))) ?>
                                        <?php if (count($medications) > 3): ?>
                                            <span style="color: #64748b;">+<?= count($medications) - 3 ?> more</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= esc($rx['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($rx['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No prescriptions found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'appointments') === 'lab_requests' || ($report_type ?? '') === 'all'): ?>
    <!-- Lab Requests Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>🔬 Lab Requests Report</h2>
            <p>Lab test requests from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
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
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lab_requests)): ?>
                        <?php foreach ($lab_requests as $lab): ?>
                            <tr>
                                <td><?= !empty($lab['requested_at']) ? date('M j, Y g:i A', strtotime($lab['requested_at'])) : '—' ?></td>
                                <td><strong>#<?= str_pad((string)$lab['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><strong><?= esc($lab['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($lab['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($lab['test_type'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(esc($lab['priority'] ?? 'normal')) === 'urgent' ? 'danger' : (strtolower($lab['priority'] ?? 'normal') === 'high' ? 'warning' : 'info') ?>">
                                        <?= ucfirst(esc($lab['priority'] ?? 'normal')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= esc($lab['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($lab['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No lab requests found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>

